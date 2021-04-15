<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Task\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\TaskResponseDto;

class ExportBackupResponseDto extends TaskResponseDto
{
    /** @var string */
    private $filePath;

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
    }
}
