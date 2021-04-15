<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore\Dto;

class RestoreDatabaseDto extends RestoreFilesDto
{
    /** @var string */
    private $file = '';

    /** @var array */
    private $search = [];

    /** @var array */
    private $replace = [];

    /** @var string */
    private $sourceAbspath = '';

    /** @var string */
    private $sourceSiteUrl = '';

    public function getFile()
    {
        return (string)$this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getSearch()
    {
        return (array)$this->search;
    }

    public function setSearch(array $search = [])
    {
        $this->search = $search;
    }

    public function getReplace()
    {
        return (array)$this->replace;
    }

    public function setReplace(array $replace = [])
    {
        $this->replace = $replace;
    }

    /**
     * @return string
     */
    public function getSourceAbspath()
    {
        return (string)$this->sourceAbspath;
    }

    /**
     * @param string $sourceAbspath
     */
    public function setSourceAbspath($sourceAbspath)
    {
        $this->sourceAbspath = $sourceAbspath;
    }

    /**
     * @return string
     */
    public function getSourceSiteUrl()
    {
        return (string)$this->sourceSiteUrl;
    }

    /**
     * @param string $sourceSiteUrl
     */
    public function setSourceSiteUrl($sourceSiteUrl)
    {
        $this->sourceSiteUrl = $sourceSiteUrl;
    }
}
