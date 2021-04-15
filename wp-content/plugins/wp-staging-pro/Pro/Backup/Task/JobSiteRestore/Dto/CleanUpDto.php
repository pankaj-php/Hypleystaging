<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\AbstractRequestDto;

class CleanUpDto extends AbstractRequestDto
{
    private $toClean;

    public function getToClean()
    {
        return $this->toClean;
    }

    public function setToClean($path)
    {
        $this->toClean = $path;
    }
}
