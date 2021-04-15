<?php

// TODO PHP7.x declare(strict_types=1);
// TODO PHP7.x type-hints & return types

namespace WPStaging\Pro\Backup\Task\JobSiteExport\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\AbstractRequestDto;
use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;

class SiteExportDto extends AbstractRequestDto
{
    use HydrateTrait;
    use ArrayableTrait;

    /** @var string */
    private $currentFile;

    /** @var int */
    private $offset;

    /**
     * @return string
     */
    public function getCurrentFile()
    {
        return $this->currentFile;
    }

    /**
     * @param string $currentFile
     */
    public function setCurrentFile($currentFile)
    {
        $this->currentFile = $currentFile;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }
}
