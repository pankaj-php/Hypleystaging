<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types && type-hints
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Database\Legacy\Job;

use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Database\LegacyDatabaseInfo;
use WPStaging\Pro\Database\Legacy\Component\Task\Database\RenameTablesTask;
use WPStaging\Pro\Database\Legacy\Repository\BackupRepository;
use WPStaging\Pro\Database\Legacy\Service\BackupService;
use WPStaging\Pro\Database\Legacy\Task\CreateBackupTask;
use WPStaging\Pro\Backup\Abstracts\Job\AbstractQueueJob;
use WPStaging\Core\WPStaging;

class JobRestoreBackup extends AbstractQueueJob
{
    const JOB_NAME = 'backup_database_restore';

    /** @var JobRestoreBackupDto */
    protected $dto;

    public function __destruct()
    {
        parent::__destruct();
        if (!$this->dto->isFinished() || !$this->dto->getBackups()) {
            return;
        }

        /** @var BackupRepository $repository */
        $repository = WPStaging::getInstance()->get(BackupRepository::class);
        $backups = $this->dto->getBackups();
        $repository->save($backups);
    }

    /**
     * @inheritDoc
     */
    public function getJobName()
    {
        return self::JOB_NAME;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->injectTaskRequest(
            CreateBackupTask::REQUEST_NOTATION,
            [
                'target' => LegacyDatabaseInfo::PREFIX_TMP . '_',
                'type' => CreateBackupTask::TEMP,
            ]
        );

        /** @var Database $database */
        $database = WPStaging::getInstance()->get(Database::class);

        $this->injectTaskRequest(
            RenameTablesTask::REQUEST_NOTATION,
            [
                'source' => LegacyDatabaseInfo::PREFIX_TMP . '_',
                'target' => $database->getPrefix(),
            ]
        );

        return $this->getResponse($this->currentTask->execute());
    }

    protected function init()
    {
        /** @var BackupRepository $repository */
        $repository = WPStaging::getInstance()->get(BackupRepository::class);
        $this->dto->setBackups($repository->findAll());
    }

    protected function initiateTasks()
    {
        $this->addTasks([
            CreateBackupTask::class,
            RenameTablesTask::class,
        ]);
    }
}
