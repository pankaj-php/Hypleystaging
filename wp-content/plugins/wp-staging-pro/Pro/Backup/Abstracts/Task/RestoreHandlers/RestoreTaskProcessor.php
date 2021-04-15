<?php

namespace WPStaging\Pro\Backup\Abstracts\Task\RestoreHandlers;

use WPStaging\Pro\Backup\Abstracts\Task\RestoreTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

/**
 * Class RestoreTaskProcessor
 *
 * This class applies the Chain of Responsibility pattern.
 *
 * @package WPStaging\Pro\Backup\Abstracts\Task\RestoreHandlers
 */
class RestoreTaskProcessor
{
    private $moveHandler;
    private $copyHandler;
    private $deleteHandler;

    public function __construct(MoveHandler $moveHandler, CopyHandler $copyHandler, DeleteHandler $deleteHandler)
    {
        $this->moveHandler   = $moveHandler;
        $this->copyHandler   = $copyHandler;
        $this->deleteHandler = $deleteHandler;
    }

    public function handle($action, $source, $destination, RestoreTask $restoreTask, LoggerInterface $logger)
    {
        $this->moveHandler->setContext($restoreTask, $logger);
        $this->copyHandler->setContext($restoreTask, $logger);
        $this->deleteHandler->setContext($restoreTask, $logger);

        switch ($action) {
            case 'move':
                $this->moveHandler->handle($source, $destination);
                break;
            case 'copy':
                $this->copyHandler->handle($source, $destination);
                break;
            case 'delete':
                $this->deleteHandler->handle($source, $destination);
                break;
        }
    }
}
