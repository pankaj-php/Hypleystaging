<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Backup\Task\JobSiteExport;

use Exception;
use WPStaging\Pro\Backup\Entity\ListableBackup;
use WPStaging\Pro\Backup\Task\JobSiteExport\Dto\CombineExportDto;
use WPStaging\Pro\Backup\Task\JobSiteExport\Dto\CombineExportResponseDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Pro\Backup\Abstracts\Task\AbstractTask;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Service\Compressor;

class CombineExportTask extends AbstractTask
{
    use ResourceTrait;

    const REQUEST_NOTATION  = 'backup.site.export.join';
    const REQUEST_DTO_CLASS = CombineExportDto::class;
    const TASK_NAME         = 'backup_site_export_join';
    const TASK_TITLE        = 'Finalizing Backup Export';

    /** @var CombineExportDto */
    protected $requestDto;

    /** @var Compressor */
    private $exporter;

    // TODO reduce args
    public function __construct(Compressor $exporter, LoggerInterface $logger, Cache $cache)
    {
        parent::__construct($logger, $cache);
        $this->exporter = $exporter;
    }

    public function execute()
    {
        $this->prepare();

        $dto = $this->exporter->getDto();
        $dto->setOffset($this->requestDto->getSteps()->getCurrent());

        $dtoData = $dto->getFileHeaders();
        $dtoData->setDirectories($this->requestDto->getDirectories());
        $dtoData->setTotalDirectories($this->requestDto->getTotalDirectories());
        $dtoData->setTotalFiles($this->requestDto->getTotalFiles());
        $dtoData->setDatabaseFile($this->requestDto->getDatabaseFile());
        $dtoData->setName($this->requestDto->getName() ?: __('Backup', 'wp-staging'));
        $dtoData->setIsAutomatedBackup($this->requestDto->getIsAutomatedBackup());

        $dtoData->setIsExportingPlugins($this->requestDto->getIsExportingPlugins());
        $dtoData->setIsExportingMuPlugins($this->requestDto->getIsExportingMuPlugins());
        $dtoData->setIsExportingThemes($this->requestDto->getIsExportingThemes());
        $dtoData->setIsExportingUploads($this->requestDto->getIsExportingUploads());
        $dtoData->setIsExportingOtherWpContentFiles($this->requestDto->getIsExportingOtherWpContentFiles());
        $dtoData->setIsExportingDatabase($this->requestDto->getIsExportingDatabase());

        $exportFilePath = null;
        try {
            $exportFilePath = $this->exporter->combine();
        } catch (Exception $e) {
            $this->logger->critical('Failed to generate backup file: ' . $e->getMessage());
        }

        if ($exportFilePath) {
            $this->requestDto->getSteps()->finish();

            return $this->generateResponse($this->makeListableBackup($exportFilePath));
        }

        $steps = $this->requestDto->getSteps();
        $steps->setCurrent($dto->getOffset());
        $steps->setTotal($dto->getFileSize());

        $this->logger->info(sprintf('Written %d bytes to compressed export', $dto->getWrittenBytes()));

        return $this->generateResponse();
    }

    /**
     * @param null|ListableBackup $backup
     *
     * @return CombineExportResponseDto
     */
    public function generateResponse(ListableBackup $backup = null)
    {
        /** @var CombineExportResponseDto $response */
        $response = parent::generateResponse();
        $response->setBackupMd5($backup ? $backup->md5BaseName : null);
        $response->setBackupSize($backup ? size_format($backup->size) : null);

        return $response;
    }

    public function getCaches()
    {
        $caches   = parent::getCaches();
        $caches[] = $this->exporter->getCacheIndex();
        $caches[] = $this->exporter->getCacheCompressed();

        return $caches;
    }

    protected function getResponseDto()
    {
        return new CombineExportResponseDto();
    }

    /**
     * This is used to display the "Download Modal" after the backup completes.
     *
     * @see string src/Backend/public/js/wpstg-admin.js, search for "wpstg--backups--create"
     *
     * @param string $exportFilePath
     *
     * @return ListableBackup
     */
    protected function makeListableBackup($exportFilePath)
    {
        clearstatcache();
        $backup              = new ListableBackup();
        $backup->md5BaseName = md5(basename($exportFilePath));
        $backup->size        = filesize($exportFilePath);

        return $backup;
    }
}
