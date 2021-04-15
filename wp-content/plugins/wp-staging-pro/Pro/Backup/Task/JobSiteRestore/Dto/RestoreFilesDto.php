<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\AbstractRequestDto;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;

class RestoreFilesDto extends AbstractRequestDto
{
    use HydrateTrait;
    use ArrayableTrait;

    /** @var int */
    protected $id;

    /** @var string */
    protected $tmpDirectory;

    private $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getTmpDirectory()
    {
        return $this->tmpDirectory;
    }

    /**
     * @param string $source
     */
    public function setTmpDirectory($path)
    {
        $this->tmpDirectory = trailingslashit($this->filesystem->safePath($path));
    }
}
