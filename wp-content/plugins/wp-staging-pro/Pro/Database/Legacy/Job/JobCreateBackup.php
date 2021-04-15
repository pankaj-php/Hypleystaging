<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Job;

use WPStaging\Pro\Database\Legacy\Task\Dto\BackupCreateDto;
use WPStaging\Pro\Database\Legacy\Task\CreateBackupTask;

class JobCreateBackup
{
    const JOB_NAME = 'backup_database_create';

    /** @var CreateBackupTask */
    private $task;

    public function __construct(CreateBackupTask $task)
    {
        $this->task = $task;
        $task->setJobName(self::JOB_NAME);
    }

    // TODO Remove after Processing.php is removed. This is backward compatibility only
    public function setRequest(array $data = [])
    {
        $dto = (new BackupCreateDto())->hydrate($data);
        $this->task->setRequestDto($dto);
    }

    public function execute()
    {
        $response = $this->task->execute();
        $response->setJob('CreateBackup');
        return $response;
    }
}
