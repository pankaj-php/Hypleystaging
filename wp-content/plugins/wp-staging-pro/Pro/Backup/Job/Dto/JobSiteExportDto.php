<?php

namespace WPStaging\Pro\Backup\Job\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\QueueJobDto;

class JobSiteExportDto extends QueueJobDto
{
    /** @var int */
    private $totalDirectories;

    /** @var int */
    private $totalFiles;

    /** @var string */
    private $databaseExportPath;

    /**
     * @return int
     */
    public function getTotalDirectories()
    {
        return $this->totalDirectories;
    }

    /**
     * @param int $totalDirectories
     */
    public function setTotalDirectories($totalDirectories)
    {
        $this->totalDirectories = $totalDirectories;
    }

    /**
     * @return int
     */
    public function getTotalFiles()
    {
        return $this->totalFiles;
    }

    /**
     * @param int $totalFiles
     */
    public function setTotalFiles($totalFiles)
    {
        $this->totalFiles = $totalFiles;
    }

    /**
     * @return string
     */
    public function getDatabaseExportPath()
    {
        return $this->databaseExportPath;
    }

    /**
     * @param string $databaseExportPath
     */
    public function setDatabaseExportPath($databaseExportPath)
    {
        $this->databaseExportPath = $databaseExportPath;
    }
}
