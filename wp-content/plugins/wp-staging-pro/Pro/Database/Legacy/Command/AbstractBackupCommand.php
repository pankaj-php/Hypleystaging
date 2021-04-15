<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; type-hints & return types

namespace WPStaging\Pro\Database\Legacy\Command;

use WPStaging\Pro\Database\Legacy\Command\Dto\BackupDto;
use WPStaging\Pro\Database\Legacy\Command\Exception\BackupCommandException;
use WPStaging\Pro\Database\Legacy\Entity\Backup;
use WPStaging\Pro\Database\Legacy\Repository\BackupRepository;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Pro\Database\Legacy\Collection\OptionCollection;
use WPStaging\Framework\Command\CommandInterface;
use WPStaging\Pro\Database\Legacy\Service\BackupService;

abstract class AbstractBackupCommand implements CommandInterface
{

    /** @var BackupService */
    protected $service;

    /** @var Database */
    protected $database;

    /** @var BackupDto */
    protected $dto;

    /** @var Backup[]|OptionCollection */
    protected $backups;

    abstract protected function saveBackups();

    public function __construct(BackupService $service)
    {
        $this->service = $service;
        $this->database = $service->getDatabaseHelper()->getDatabase();
        $this->backups = $service->getDatabaseHelper()->getRepository()->findAll() ?:
            new OptionCollection(Backup::class)
        ;
    }

    /**
     * @param BackupDto $dto
     */
    public function setDto(BackupDto $dto = null)
    {
        if (!$dto) {
            return;
        }

        $this->dto = $dto;

        if (!$this->dto->getSourcePrefix()) {
            $this->dto->setSourcePrefix($this->database->getPrefix());
        }
    }

    /**
     * @throws BackupCommandException
     */
    protected function validateBackup()
    {
        if ($this->database->getPrefix() === $this->dto->getTargetPrefix()) {
            throw new BackupCommandException('You are trying to process production tables!');
        }

        if ($this->dto->getSourcePrefix() === $this->dto->getTargetPrefix()) {
            throw new BackupCommandException('You are trying to process same tables!');
        }
    }
}
