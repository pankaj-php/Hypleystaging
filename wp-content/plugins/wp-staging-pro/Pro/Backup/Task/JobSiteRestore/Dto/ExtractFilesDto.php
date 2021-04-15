<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\AbstractRequestDto;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;

class ExtractFilesDto extends AbstractRequestDto
{
    use HydrateTrait;
    use ArrayableTrait;

    /** @var int */
    private $id;

    /** @var string */
    private $filePath;

    /** @var int|null */
    private $headerStartsAt;

    /** @var int|null */
    private $fileStartsAt;

    /** @var string */
    protected $tmpDirectory;

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

    /**
     * @return int|null
     */
    public function getHeaderStartsAt()
    {
        return $this->headerStartsAt;
    }

    /**
     * @param int|null $headerStartsAt
     */
    public function setHeaderStartsAt($headerStartsAt)
    {
        $this->headerStartsAt = $headerStartsAt;
    }

    /**
     * @return int|null
     */
    public function getFileStartsAt()
    {
        return $this->fileStartsAt;
    }

    /**
     * @param int|null $fileStartsAt
     */
    public function setFileStartsAt($fileStartsAt)
    {
        $this->fileStartsAt = $fileStartsAt;
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
        $this->tmpDirectory = trailingslashit($path);
    }
}
