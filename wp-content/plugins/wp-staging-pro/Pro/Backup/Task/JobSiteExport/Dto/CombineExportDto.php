<?php

// TODO PHP7.x declare(strict_types=1);
// TODO PHP7.x type-hints & return types

namespace WPStaging\Pro\Backup\Task\JobSiteExport\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\AbstractRequestDto;
use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;
use WPStaging\Pro\Backup\Abstracts\Dto\Traits\DateCreatedTrait;
use WPStaging\Pro\Backup\Abstracts\Dto\Traits\IsExportingTrait;

class CombineExportDto extends AbstractRequestDto
{
    //const DEFAULT_NAME = 'backup';
    use HydrateTrait;
    use ArrayableTrait;
    use IsExportingTrait;
    use DateCreatedTrait;

    /** @var int|null */
    private $id;

    /** @var string|null */
    private $name;

    /** @var string|null */
    private $notes;

    /** @var array|null */
    private $directories;

    /** @var string */
    private $databaseFile;

    /** @var int */
    private $totalFiles;

    /** @var int */
    private $totalDirectories;

    /** @var bool */
    private $isAutomatedBackup;

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    }

    /**
     * @return array|null
     */
    public function getDirectories()
    {
        return $this->directories;
    }

    public function setDirectories(array $directories = null)
    {
        $this->directories = $directories;
    }

    /**
     * @return string
     */
    public function getDatabaseFile()
    {
        return $this->databaseFile;
    }

    /**
     * @param string $databaseFile
     */
    public function setDatabaseFile($databaseFile)
    {
        $this->databaseFile = $databaseFile;
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
     * @return bool
     */
    public function getIsAutomatedBackup()
    {
        return (bool)$this->isAutomatedBackup;
    }
    /**
     * @param bool $isAutomatedBackup
     */
    public function setIsAutomatedBackup($isAutomatedBackup)
    {
        $this->isAutomatedBackup = $isAutomatedBackup;
    }
}
