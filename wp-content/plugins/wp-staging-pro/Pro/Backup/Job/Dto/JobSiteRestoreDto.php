<?php

namespace WPStaging\Pro\Backup\Job\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\QueueJobDto;
use WPStaging\Pro\Backup\Service\Dto\ExportFileHeadersDto;

class JobSiteRestoreDto extends QueueJobDto
{
    /** @var ExportFileHeadersDto */
    private $fileHeaders;

    /**
     * @return ExportFileHeadersDto
     */
    public function getFileHeaders()
    {
        return $this->fileHeaders;
    }

    /**
     * @param ExportFileHeadersDto $fileHeaders
     */
    public function setFileHeaders(ExportFileHeadersDto $fileHeaders)
    {
        $this->fileHeaders = $fileHeaders;
    }
}
