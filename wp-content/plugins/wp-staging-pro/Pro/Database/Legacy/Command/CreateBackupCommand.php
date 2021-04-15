<?php

namespace WPStaging\Pro\Database\Legacy\Command;

use DateTime;
use WPStaging\Pro\Database\Legacy\Command\Exception\BackupCommandException;
use WPStaging\Pro\Database\Legacy\Entity\Backup;
use WPStaging\Framework\Database\TableDto;
use WPStaging\Framework\Database\TableService;

class CreateBackupCommand extends AbstractBackupCommand
{
    /** @var null|Backup */
    private $backup;

    /** @noinspection PhpUnhandledExceptionInspection */
    public function execute()
    {
        $this->validateBackup();

        if ($this->dto->getStep() === null) {
            $this->executeAll();
            return;
        }

        $this->executeStep();
    }

    /**
     * @return Backup|null
     */
    public function getBackup()
    {
        return $this->backup;
    }

    protected function executeAll()
    {
        foreach ($this->findTables() as $table) {
            $this->backupTable($table);
        }

        $this->saveBackups();
    }

    /**
     * @noinspection PhpUnhandledExceptionInspection
     */
    protected function executeStep()
    {
        /** @var array $tables */
        $tables = $this->findTables();

        if (!isset($tables[$this->dto->getStep()])) {
            throw new BackupCommandException('failed to get tables with prefix: ' . $this->dto->getSourcePrefix());
        }

        $this->backupTable($tables[$this->dto->getStep()]);

        // This was the last step, save the backup
        if (count($tables) === $this->dto->getStep() + 1) {
            $this->saveBackups();
        }
    }

    /**
     * @return TableDto[]|null
     */
    protected function findTables()
    {
        $tables = (new TableService())->findTableStatusStartsWith($this->dto->getSourcePrefix());
        if (!$tables) {
            return null;
        }
        return $tables->toArray();
    }

    protected function backupTable(TableDto $tableDto)
    {
        $newTableName = $this->dto->getTargetPrefix() . str_replace($this->dto->getSourcePrefix(), null, $tableDto->getName());
        $this->database->exec('OPTIMIZE TABLE ' . $tableDto->getName());
        $this->database->exec('DROP TABLE IF EXISTS ' . $newTableName);
        $this->database->exec('CREATE TABLE ' . $newTableName . ' LIKE ' . $tableDto->getName());
        $this->database->exec('INSERT INTO ' . $newTableName . ' SELECT * FROM ' . $tableDto->getName());
        $this->database->exec('OPTIMIZE TABLE ' . $newTableName);
    }

    protected function saveBackups()
    {
        if (!$this->dto->isSaveRecords()) {
            return;
        }

        /** @var Backup $backup */
        $backup = $this->backups->findById($this->dto->getTargetPrefix());

        if ($backup) {
            $backup->setUpdatedAt(new DateTime());
            $this->service->getDatabaseHelper()->getRepository()->save($this->backups);
            return;
        }

        $this->backup = new Backup();
        $this->backup->setId($this->dto->getTargetPrefix());
        $this->backup->setName($this->dto->getName());
        $this->backup->setNotes($this->dto->getNotes());
        $this->backup->setCreatedAt(new DateTime());

        $this->backups->attach($this->backup);
        $this->service->getDatabaseHelper()->getRepository()->save($this->backups);
    }
}
