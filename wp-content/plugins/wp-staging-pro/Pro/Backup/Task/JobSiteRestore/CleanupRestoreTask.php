<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore;

use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\CleanUpDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Pro\Backup\Abstracts\Task\AbstractTask;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Filesystem\Filesystem;

class CleanupRestoreTask extends AbstractTask
{
    use ResourceTrait;

    const REQUEST_NOTATION  = 'backup.site.restore.cleanUp';
    const REQUEST_DTO_CLASS = CleanUpDto::class;
    const TASK_NAME         = 'backup_site_restore_cleanUp';
    const TASK_TITLE        = 'Cleaning Up Extracted Restore Files';

    /** @var CleanUpDto */
    protected $requestDto;

    private $filesystem;

    public function __construct(LoggerInterface $logger, Cache $cache, Filesystem $filesystem)
    {
        parent::__construct($logger, $cache);
        $this->filesystem = $filesystem;
    }

    public function execute()
    {
        $this->prepare();

        // Make sure path to clean is inside WordPress
        $pathToClean = trailingslashit(wp_normalize_path(ABSPATH)) . str_replace(wp_normalize_path(ABSPATH), '', $this->requestDto->getToClean());

        $pathToClean = untrailingslashit($pathToClean);

        if (!file_exists($pathToClean)) {
            // We don't care if $pathToClean is a file or a dir
            $this->logger->info(sprintf(
                __('%s: No need to cleanup %s, as path does not exist.', 'wp-staging'),
                $this->getStatusTitle(),
                $pathToClean
            ));
        } else {
            try {
                $deleted = $this->filesystem
                    ->setRecursive(true)
                    ->setShouldStop(function () {
                        return $this->isThreshold();
                    })
                    ->deleteNew($pathToClean);
            } catch (\Exception $e) {
                $this->logger->warning(sprintf(
                    __('%s: Could not cleanup path %s. May be a permission issue?', 'wp-staging'),
                    $this->getStatusTitle(),
                    $pathToClean
                ));
            }

            if ($deleted) {
                // Successfully deleted
                $this->logger->info(sprintf(
                    __('%s: Path %s successfully cleaned up.', 'wp-staging'),
                    $this->getStatusTitle(),
                    $pathToClean
                ));
            } else {
                /*
                 * Not successfully deleted.
                 * This can happen if the folder to delete is too large
                 * to be deleted in a single request. We continue
                 * deleting it in the next request...
                 */
                $response = $this->generateResponse();
                $response->setStatus(false);
                $this->requestDto->getSteps()->setCurrent(1);

                $this->logger->info(sprintf(
                    __('%s: Re-enqueing path %s for deletion, as it couldn\'t be deleted in a single request without
                        hitting execution limits. If you see this message in a loop, PHP might not be able to delete
                        this directory, so you might want to try to delete it manually.', 'wp-staging'),
                    $this->getStatusTitle(),
                    $pathToClean
                ));

                // Early bail: Response modified for repeating
                return $response;
            }
        }

        return $this->generateResponse();
    }

    public function findRequestDto()
    {
        parent::findRequestDto();
        if ($this->requestDto->getSteps()->getTotal() === 1) {
            return;
        }

        $this->requestDto->getSteps()->setTotal(1);
    }
}
