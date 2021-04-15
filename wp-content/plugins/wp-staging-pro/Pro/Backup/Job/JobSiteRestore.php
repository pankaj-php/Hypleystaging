<?php

namespace WPStaging\Pro\Backup\Job;

use RuntimeException;
use WPStaging\Pro\Backup\Abstracts\Job\AbstractQueueJob;
use WPStaging\Pro\Backup\Abstracts\Dto\QueueJobDto;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Queue\Queue;
use WPStaging\Framework\Queue\Storage\CacheStorage;
use WPStaging\Framework\Traits\BenchmarkTrait;
use WPStaging\Framework\Traits\RequestNotationTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Job\Dto\JobSiteRestoreDto;
use WPStaging\Pro\Backup\Job\Dto\JobSiteRestoreRequestDto;
use WPStaging\Pro\Backup\Service\Dto\ExportFileHeadersDto;
use WPStaging\Pro\Backup\Task\JobSiteRestore\ExtractFilesTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\CleanupRestoreTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\RequirementsCheckTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\RestoreDatabaseTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\RestoreUploadsTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\RestoreMuPluginsTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\RestorePluginsTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\RestoreThemesTask;
use WPStaging\Framework\Filesystem\Filesystem;

// Need to make sure the destination is not already there before we extract or will create problems with file contents
class JobSiteRestore extends AbstractQueueJob
{
    use BenchmarkTrait;
    use RequestNotationTrait;

    const JOB_NAME         = 'backup_site_restore';
    const REQUEST_NOTATION = 'jobs.backup.site.restore';

    const TMP_DIRECTORY = 'tmp/restore/';

    /** @var JobSiteRestoreDto */
    protected $dto;

    /** @var JobSiteRestoreRequestDto */
    protected $requestDto;

    /** @var ExportFileHeadersDto */
    private $exportFileHeadersDto;

    /** @var array The array of tasks to execute for this job. Populated at init(). */
    private $tasks = [];

    public function __construct(
        Cache $jobCache,
        Queue $queue,
        CacheStorage $queueCache,
        QueueJobDto $dto,
        ExportFileHeadersDto $exportFileHeadersDto,
        Directory $directory,
        Filesystem $filesystem
    ) {
        $this->exportFileHeadersDto = $exportFileHeadersDto;

        // This must be last.
        parent::__construct($jobCache, $queue, $queueCache, $dto, $filesystem, $directory);
    }

    public function initiateTasks()
    {
        $this->addTasks($this->tasks);
    }

    public function execute()
    {
        $this->startBenchmark();

        $this->prepare();
        $response = $this->getResponse($this->currentTask->execute());

        $this->finishBenchmark(get_class($this->currentTask));

        return $response;
    }

    /**
     * @inheritDoc
     */
    public function getJobName()
    {
        return self::JOB_NAME;
    }

    protected function init()
    {
        // We need request DTO so we know the file path
        $this->provideRequestDto();

        if ($this->dto->getFileHeaders()) {
            return;
        }

        try {
            $fileHeaders = $this->exportFileHeadersDto->hydrateByFilePath($this->requestDto->getFile());
        } catch (\Exception $e) {
            throw $e;
        }

        if (!$fileHeaders->getHeaderStart()) {
            throw new RuntimeException('Failed to get File Headers');
        }

        $this->dto->setFileHeaders($fileHeaders);

        $this->tasks[] = RequirementsCheckTask::class;
        $this->tasks[] = CleanupRestoreTask::class; // Delete extracted files on start
        $this->tasks[] = ExtractFilesTask::class;

        if ($fileHeaders->getIsExportingUploads() || $fileHeaders->getIsExportingOtherWpContentFiles()) {
            $this->tasks[] = RestoreUploadsTask::class;
        }

        if ($fileHeaders->getIsExportingThemes()) {
            $this->tasks[] = RestoreThemesTask::class;
        }

        if ($fileHeaders->getIsExportingPlugins()) {
            $this->tasks[] = RestorePluginsTask::class;
        }

        if ($fileHeaders->getIsExportingMuPlugins()) {
            $this->tasks[] = RestoreMuPluginsTask::class;
        }

        if ($fileHeaders->getIsExportingDatabase()) {
            $this->tasks[] = RestoreDatabaseTask::class;
        }

        $this->tasks[] = CleanupRestoreTask::class; // Delete extracted files on finish
    }

    protected function injectRequests()
    {
        if (!$this->currentTask) {
            return;
        }

        switch (get_class($this->currentTask)) {
            case RequirementsCheckTask::class:
                $this->injectTaskRequest(
                    RequirementsCheckTask::REQUEST_NOTATION,
                    [
                        'singleOrMulti' => $this->dto->getFileHeaders()->getSingleOrMulti(),
                    ]
                );
                break;
            case CleanupRestoreTask::class:
                $this->injectTaskRequest(
                    CleanupRestoreTask::REQUEST_NOTATION,
                    [
                        // wp-content/uploads/wp-staging/uploads/tmp/restore
                        'toClean' => trailingslashit($this->directory->getPluginUploadsDirectory()) . static::TMP_DIRECTORY,
                    ]
                );
                break;
            case ExtractFilesTask::class:
                $this->injectTaskRequest(
                    ExtractFilesTask::REQUEST_NOTATION,
                    [
                        'id'           => $this->requestDto->getId(),
                        'filePath'     => $this->requestDto->getFile(),
                        'tmpDirectory' => $this->getTmpDirectory(),
                    ]
                );
                break;
            case RestoreUploadsTask::class:
                $this->injectTaskRequest(
                    RestoreUploadsTask::REQUEST_NOTATION,
                    [
                        'id'           => $this->requestDto->getId(),
                        'source'       => $this->withExtractedPath($this->dto->getFileHeaders()->getDirUploads()),
                        'mergeFiles'   => $this->requestDto->isMergeMediaFiles(),
                        'tmpDirectory' => $this->getTmpDirectory(),
                    ]
                );
                break;
            case RestoreThemesTask::class:
                $this->injectTaskRequest(
                    RestoreThemesTask::REQUEST_NOTATION,
                    [
                        'source'       => $this->withExtractedPath($this->dto->getFileHeaders()->getDirThemes()),
                        'tmpDirectory' => $this->getTmpDirectory(),
                    ]
                );
                break;
            case RestoreDatabaseTask::class:
                $this->injectTaskRequest(
                    RestoreDatabaseTask::REQUEST_NOTATION,
                    [
                        'file'          => $this->withExtractedPath() . basename($this->dto->getFileHeaders()->getDatabaseFile()),
                        'search'        => $this->requestDto->getSearch(),
                        'replace'       => $this->requestDto->getReplace(),
                        'sourceAbspath' => $this->dto->getFileHeaders()->getAbspath(),
                        'sourceSiteUrl' => $this->dto->getFileHeaders()->getSiteUrl(),
                    ]
                );
                break;
            case RestorePluginsTask::class:
                $this->injectTaskRequest(
                    RestorePluginsTask::REQUEST_NOTATION,
                    [
                        'source'       => $this->withExtractedPath($this->dto->getFileHeaders()->getDirPlugins()),
                        'tmpDirectory' => $this->getTmpDirectory(),
                    ]
                );
                break;
            case RestoreMuPluginsTask::class:
                $this->injectTaskRequest(
                    RestoreMuPluginsTask::REQUEST_NOTATION,
                    [
                        'source'       => $this->withExtractedPath($this->dto->getFileHeaders()->getDirMuPlugins()),
                        'tmpDirectory' => $this->getTmpDirectory(),
                    ]
                );
                break;
        }
    }

    protected function provideRequestDto()
    {
        $this->requestDto = $this->initializeRequestDto(
            JobSiteRestoreRequestDto::class,
            self::REQUEST_NOTATION
        );
    }

    private function withExtractedPath($relativePath = null)
    {
        $dir = $this->getTmpDirectory();

        return trailingslashit($this->filesystem->safePath($dir . $relativePath));
    }

    public function getTmpDirectory()
    {
        $dir = $this->directory->getPluginUploadsDirectory() . static::TMP_DIRECTORY . $this->requestDto->getId();
        $this->filesystem->mkdir($dir);

        return trailingslashit($dir);
    }
}
