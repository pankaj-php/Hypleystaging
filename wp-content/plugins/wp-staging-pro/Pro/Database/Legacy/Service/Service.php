<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types & type-hints
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Database\Legacy\Service;

use WPStaging\Pro\Database\Legacy\Repository\BackupRepository;
use WPStaging\Pro\Database\Legacy\Service\BackupService as DatabaseService;
use WPStaging\Pro\Database\Legacy\Entity\Backup;

class Service
{
    /** @var BackupRepository */
    private $repository;

    /** @var DatabaseService */
    private $serviceDatabase;

    public function __construct(
        BackupRepository $repository,
        DatabaseService $serviceDatabase
    ) {
        $this->repository = $repository;
        $this->serviceDatabase = $serviceDatabase;
    }

    /**
     * @param string $id
     */
    public function deleteById($id)
    {
        $backup = $this->repository->find($id);
        if (!$backup) {
            return;
        }

        $this->delete($backup);
    }

    /**
     * @param Backup $backup
     * @param bool   $skipDatabaseValidation
     */
    public function delete(Backup $backup, $skipDatabaseValidation = false)
    {
        $this->serviceDatabase->delete($backup->getId(), $skipDatabaseValidation);
    }

    /**
     * @param string $prefix
     */
    public function deleteTablesByPrefix($prefix)
    {
        if (!$prefix) {
            return;
        }

        $this->serviceDatabase->delete($prefix, true);
    }
}
