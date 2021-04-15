<?php

namespace WPStaging\Pro\Database\Legacy\Command;

use WPStaging\Pro\Database\Legacy\Command\Exception\BackupCommandException;
use WPStaging\Framework\Database\TableService;
use WPStaging\Pro\Database\Legacy\Service\BackupService;

class DeleteBackupCommand extends AbstractBackupCommand
{
    /** @var bool */
    private $skipValidation;

    /**
     * @param bool $skipValidation
     */
    public function setSkipValidation($skipValidation)
    {
        $this->skipValidation = $skipValidation;
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function execute()
    {
        $this->validateBackup();

        $tables = (new TableService())->findTableStatusStartsWith($this->dto->getTargetPrefix());

        if (!$tables || $tables->count() < 1) {
            throw new BackupCommandException('Delete backup tables do not exist: ' . $this->dto->getTargetPrefix());
        }

        $this->database->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables as $table) {
            $this->database->exec('DROP TABLE IF EXISTS ' . $table->getName());
        }
        $this->database->exec('SET FOREIGN_KEY_CHECKS = 1');

        $this->saveBackups();
    }

    protected function saveBackups()
    {
        $this->service
            ->getDatabaseHelper()
            ->getRepository()
            ->deleteById($this->dto->getTargetPrefix())
        ;
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    protected function validateBackup()
    {
        parent::validateBackup();

        if ($this->skipValidation) {
            return;
        }

        if (!$this->backups->doesIncludeId($this->dto->getTargetPrefix())) {
            throw new BackupCommandException(
                'DeleteBackup prefix does not exist: ' . $this->dto->getTargetPrefix()
            );
        }
    }
}
