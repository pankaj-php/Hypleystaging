<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Component\Task\Database;

use WPStaging\Pro\Database\Legacy\Component\AbstractRequestDto;

class RenameTablesRequestDto extends AbstractRequestDto
{

    /** @var string */
    private $source;

    /** @var string */
    private $target;

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
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
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }
}
