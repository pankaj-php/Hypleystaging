<?php

// TODO PHP7.x declare(strict_types=1);
// TODO PHP7.x type-hints & return types

namespace WPStaging\Pro\Backup\Job\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\AbstractDto;
use WPStaging\Pro\Backup\Abstracts\Dto\Traits\IsExportingTrait;

class JobSiteExportRequestDto extends AbstractDto
{
    use IsExportingTrait;

    /** @var string|null */
    private $name;

    /** @var string|null */
    private $notes;

    /** @var array|null */
    private $directories;

    /** @var array */
    private $excludedDirectories = [];

    /** @var bool */
    private $includeOtherFilesInWpContent;

    /** @var array|null */
    private $includedDirectories;

    /** @var bool */
    private $isAutomatedBackup = false;

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
     * @return array|null
     */
    public function getExcludedDirectories()
    {
        return (array)$this->excludedDirectories;
    }

    public function setExcludedDirectories(array $excludedDirectories = [])
    {
        $this->excludedDirectories = $excludedDirectories;
    }

    /**
     * @return bool
     */
    public function isIncludeOtherFilesInWpContent()
    {
        return (bool)$this->includeOtherFilesInWpContent;
    }

    /**
     * @param bool $includeOtherFilesInWpContent
     */
    public function setIncludeOtherFilesInWpContent($includeOtherFilesInWpContent)
    {
        $this->includeOtherFilesInWpContent = $includeOtherFilesInWpContent === true || $includeOtherFilesInWpContent === 'true';
    }

    /**
     * @return array|null
     */
    public function getIncludedDirectories()
    {
        return $this->includedDirectories;
    }

    /**
     * @param array|null $includedDirectories
     */
    public function setIncludedDirectories($includedDirectories)
    {
        $this->includedDirectories = $includedDirectories;
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
