<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Task\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\TaskResponseDto;

class CreateBackupResponseDto extends TaskResponseDto
{
    /** @var int|null */
    private $backupId;

    /**
     * @return int|null
     */
    public function getBackupId()
    {
        return $this->backupId;
    }

    /**
     * @param int|null $backupId
     */
    public function setBackupId($backupId)
    {
        $this->backupId = $backupId;
    }
}
