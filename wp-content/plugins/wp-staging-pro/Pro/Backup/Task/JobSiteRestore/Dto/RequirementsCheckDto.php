<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore\Dto;

use WPStaging\Pro\Backup\Abstracts\Dto\AbstractRequestDto;
use WPStaging\Framework\Traits\ArrayableTrait;
use WPStaging\Framework\Traits\HydrateTrait;

class RequirementsCheckDto extends AbstractRequestDto
{
    use HydrateTrait;
    use ArrayableTrait;

    /** @var string */
    private $singleOrMulti;

    /**
     * @return string
     */
    public function getSingleOrMulti()
    {
        return $this->singleOrMulti;
    }

    /**
     * @param string $singleOrMulti "single" or "multi"
     */
    public function setSingleOrMulti($singleOrMulti)
    {
        $this->singleOrMulti = $singleOrMulti;
    }
}
