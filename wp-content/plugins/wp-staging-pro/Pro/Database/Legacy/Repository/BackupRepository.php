<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x type hints & return types

namespace WPStaging\Pro\Database\Legacy\Repository;

use WPStaging\Pro\Database\Legacy\Entity\Backup;
use WPStaging\Pro\Database\Legacy\Collection\OptionCollection;

class BackupRepository
{
    const OPTION_NAME = 'wpstg_backups';

    /**
     * @return OptionCollection|null
     */
    public function findAll()
    {
        $backups = get_option(self::OPTION_NAME, []);
        if (!$backups || !is_array($backups)) {
            return null;
        }

        $collection = new OptionCollection(Backup::class);
        $collection->attachAllByArray($backups);

        return $collection;
    }

    /**
     * @param string $id
     *
     * @return Backup
     */
    public function find($id)
    {
        $backups = $this->findAll();
        if (!$backups) {
            return null;
        }

        /** @var Backup|null $backup */
        $backup = $backups->findById($id);
        return $backup;
    }

    public function save(OptionCollection $backups)
    {
        $data = $backups->toArray();
        $existing = $this->findAll();
        if ($existing && $data === $existing->toArray()) {
            return true;
        }

        return update_option(self::OPTION_NAME, $data, false);
    }

    public function delete(Backup $backup)
    {
        $backups = $this->findAll();
        if (!$backups || !$backups->doesIncludeId($backup->getId())) {
            return true;
        }

        $backups->removeById($backup->getId());
        return $this->save($backups);
    }

    public function deleteById($id)
    {
        $backups = $this->findAll();
        if (!$backups) {
            return true;
        }

        /** @var Backup|null $backup */
        $backup = $backups->findById($id);
        if (!$backup) {
            return true;
        }

        $backups->detach($backup);
        return $this->save($backups);
    }
}
