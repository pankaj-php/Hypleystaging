<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore\Dto;

use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;

class RestoreMergeFilesDto extends RestoreFilesDto
{
    use HydrateTrait;
    use ArrayableTrait;

    /** @var bool */
    private $mergeFiles;

    /**
     * @var string The directory that is the source of what is being currently migrated.
     *             This directory points to the relative path to the "plugins", "themes", "uploads"
     *             folders inside wp-content/uploads/wp-staging/tmp/restore.
     */
    private $source;

    /**
     * @return bool
     */
    public function isMergeFiles()
    {
        return $this->mergeFiles;
    }

    /**
     * @param bool $mergeFiles
     */
    public function setMergeFiles($mergeFiles)
    {
        $this->mergeFiles = $mergeFiles;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }
}
