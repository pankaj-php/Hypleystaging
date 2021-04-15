<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Queue\Queue;
use WPStaging\Framework\Queue\Storage\CacheStorage;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Abstracts\Task\RestoreHandlers\RestoreTaskProcessor;
use WPStaging\Pro\Backup\Abstracts\Task\RestoreTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\RestoreMergeFilesDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

class RestoreUploadsTask extends RestoreTask
{
    const REQUEST_NOTATION  = 'backup.site.restore.uploads';
    const REQUEST_DTO_CLASS = RestoreMergeFilesDto::class;
    const TASK_NAME         = 'backup_site_restore_uploads';
    const TASK_TITLE        = 'Restoring Uploads Folder';

    /** @var RestoreMergeFilesDto */
    protected $requestDto;

    private $directory;

    public function __construct(LoggerInterface $logger, Cache $cache, Filesystem $filesystem, Queue $restoreQueue, CacheStorage $restoreQueueStorage, RestoreTaskProcessor $taskProcessor, Directory $directory)
    {
        parent::__construct($logger, $cache, $filesystem, $restoreQueue, $restoreQueueStorage, $taskProcessor);
        $this->directory = $directory;
    }

    /**
     * The most critical step because it has to run in one request
     */
    protected function buildQueue()
    {
        $uploadsToRestore = $this->getUploadsToRestore();

        $uploadsRoot = trailingslashit($this->directory->getUploadsDirectory());

        foreach ($uploadsToRestore as $id => $fileInfo) {
            /*
             * Scenario: Restoring an upload that already exists
             * 1. Backup old upload
             * 2. Restore new upload
             * 3. Delete backup
             */
            $this->enqueueOverwrite($uploadsToRestore[$id]['path'], $uploadsRoot . $fileInfo['relativePath']);
        }
    }

    /**
     * @return array An array of paths of uploads to restore.
     */
    private function getUploadsToRestore()
    {
        $tmpDir = $this->requestDto->getSource();
        $tmpDir = (string)apply_filters('wpstg.restore.uploads.tmpDir', $tmpDir);

        return $this->findUploadsInDir($tmpDir);
    }

    /**
     * @param string $path Folder to look for uploads, eg: '/var/www/wp-content/uploads'
     *
     * @return array An array of paths of uploads found in the root of given directory,
     *               where the index is the relative path of the upload, and the value it's absolute path.
     * @example [
     *              '2020/01/image.jpg' => '/var/www/wp-content/uploads/2020/01/image.jpg',
     *              'debug.log' => '/var/www/wp-content/uploads/debug.log',
     *          ]
     *
     */
    private function findUploadsInDir($path)
    {
        $path = untrailingslashit($path);
        $it   = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $it   = new \RecursiveIteratorIterator($it);

        $uploads = [];

        /** @var \SplFileInfo $item */
        foreach ($it as $item) {
            // Early bail: We don't want dots, links or anything that is not a file.
            if (!$item->isFile() || $item->isLink()) {
                continue;
            }

            // Allocate pathname to a variable because we use it multiple times below.
            $pathName = $item->getPathname();

            $relativePath = str_replace($path, '', $pathName);

            $uploads[] = [
                'path'         => $pathName,
                'relativePath' => $relativePath,
            ];
        }

        return $uploads;
    }
}
