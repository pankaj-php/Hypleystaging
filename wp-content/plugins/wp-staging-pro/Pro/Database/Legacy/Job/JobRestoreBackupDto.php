<?php

namespace WPStaging\Pro\Database\Legacy\Job;

use WPStaging\Pro\Backup\Abstracts\Dto\QueueJobDto;
use WPStaging\Pro\Database\Legacy\Collection\OptionCollection;
use WPStaging\Pro\Database\Legacy\Entity\Backup;

class JobRestoreBackupDto extends QueueJobDto
{
    /** @var OptionCollection|Backup[]|null */
    private $backups;

    /**
     * @return OptionCollection|Backup[]|null
     */
    public function getBackups()
    {
        return $this->backups;
    }

    /**
     * @param OptionCollection|Backup[]|null $backups
     */
    public function setBackups($backups)
    {
        // Hack for < PHP 7.x
        if (is_array($backups)) {
            $collection = new OptionCollection(Backup::class);
            $collection->attachAllByArray($backups);
            $backups = $collection;
        }

        $this->backups = $backups;
    }
}
