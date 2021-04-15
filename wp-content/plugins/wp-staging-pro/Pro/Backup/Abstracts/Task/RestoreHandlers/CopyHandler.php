<?php

namespace WPStaging\Pro\Backup\Abstracts\Task\RestoreHandlers;

class CopyHandler extends RestoreHandler
{
    public function handle($source, $destination)
    {
        $this->logger->debug(sprintf(
            __('%s: Copying %s to %s', 'wp-staging'),
            $this->restoreTask->getStatusTitle(),
            $source,
            $destination
        ));

        $parentDirectory = dirname($destination);

        if (!is_dir($parentDirectory)) {
            $parentDirectoryCreated = wp_mkdir_p($parentDirectory);

            if ($parentDirectoryCreated) {
                $this->logger->debug(sprintf(
                    __('%s: Parent directory of destination did not exist and was created: %s', 'wp-staging'),
                    $this->restoreTask->getStatusTitle(),
                    $parentDirectory
                ));
            } else {
                $this->logger->warning(sprintf(
                    __('%s: Parent directory of destination did not exist and could not be created, skipping! Parent directory: %s File that was skipped: %s', 'wp-staging'),
                    $this->restoreTask->getStatusTitle(),
                    $parentDirectory,
                    $destination
                ));

                return;
            }
        }

        $copy = @rename($source, $destination);

        if ($copy) {
            $this->logger->debug(sprintf(
                __('%s: %s was copied to %s successfully', 'wp-staging'),
                $this->restoreTask->getStatusTitle(),
                $source,
                $destination
            ));
        } else {
            $this->logger->warning(sprintf(
                __('%s: There was an unknown error when trying to move %s to %s. May be a file permission issue?', 'wp-staging'),
                $this->restoreTask->getStatusTitle(),
                $source,
                $destination
            ));

            return;
        }
    }
}
