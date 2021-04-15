<?php

namespace WPStaging\Pro\Backup\Abstracts\Task\RestoreHandlers;

use WPStaging\Framework\Filesystem\Filesystem;

class DeleteHandler extends RestoreHandler
{
    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    public function handle($source, $destination)
    {
        try {
            $deleted = $this->filesystem
                ->setRecursive(true)
                ->setShouldStop(function () {
                    return $this->restoreTask->isThreshold();
                })
                ->deleteNew($destination, true, true);
        } catch (\Exception $e) {
            $this->logger->warning(sprintf(
                __('%s: PHP does not have permission to delete %s! This folder might still be in your filesystem, please clear it manually.', 'wp-staging'),
                $this->restoreTask->getStatusTitle(),
                $destination
            ));

            return;
        }

        if ($deleted) {
            $this->logger->debug(sprintf(
                __('%s: %s was deleted successfully', 'wp-staging'),
                $this->restoreTask->getStatusTitle(),
                $destination
            ));
        } else {
            $this->restoreTask->enqueueDelete($destination, 'first');
            $this->logger->debug(sprintf(
                __('%s: %s could not be entirely deleted in this request. Enqueuing it again for retry...', 'wp-staging'),
                $this->restoreTask->getStatusTitle(),
                $destination
            ));
        }
    }
}
