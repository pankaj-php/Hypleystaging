<?php

namespace WPStaging\Pro\Backup\Job\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\AbstractDto;

class JobSiteRestoreRequestDto extends AbstractDto
{
    /** @var string */
    private $id;

    /** @var string */
    private $file;

    /** @var bool */
    private $mergeMediaFiles;

    /** @var array */
    private $search = [];

    /** @var array */
    private $replace = [];

    /**
     * @return string
     */
    public function getId()
    {
        if (!$this->id) {
            $this->id = time();
        }

        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = preg_replace('#[^a-zA-Z0-9]+#', '', $id);
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
        if ($this->id) {
            return;
        }

        $id = rtrim($file, '.wpstg');
        $id = substr($id, strrpos($id, '_') + 1, strlen($id));
        $this->setId($id);
    }

    /**
     * @return bool
     */
    public function isMergeMediaFiles()
    {
        return $this->mergeMediaFiles;
    }

    /**
     * @param bool $mergeMediaFiles
     */
    public function setMergeMediaFiles($mergeMediaFiles)
    {
        $this->mergeMediaFiles = (bool)$mergeMediaFiles;
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
}
