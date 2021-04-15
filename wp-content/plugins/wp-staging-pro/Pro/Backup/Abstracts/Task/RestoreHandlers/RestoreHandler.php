<?php

namespace WPStaging\Pro\Backup\Abstracts\Task\RestoreHandlers;

use WPStaging\Pro\Backup\Abstracts\Task\RestoreTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;

abstract class RestoreHandler
{
    protected $restoreTask;
    protected $logger;

    public function setContext(RestoreTask $restoreTask, LoggerInterface $logger)
    {
        $this->restoreTask = $restoreTask;
        $this->logger      = $logger;
    }

    abstract public function handle($source, $destination);
}
