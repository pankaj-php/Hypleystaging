<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Backup\Task\JobSiteExport\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\TaskResponseDto;

class CombineExportResponseDto extends TaskResponseDto
{
    /** @var int|null */
    private $backupMd5;

    /** @var int|null */
    private $backupSize;

    /**
     * @return int|null
     */
    public function getBackupMd5()
    {
        return $this->backupMd5;
    }

    /**
     * @param int|null $backupMd5
     */
    public function setBackupMd5($backupMd5)
    {
        $this->backupMd5 = $backupMd5;
    }

    /**
     * @return int|null
     */
    public function getBackupSize()
    {
        return $this->backupMd5;
    }

    /**
     * @param int|null $backupSize
     */
    public function setBackupSize($backupSize)
    {
        $this->backupSize = $backupSize;
    }
}
