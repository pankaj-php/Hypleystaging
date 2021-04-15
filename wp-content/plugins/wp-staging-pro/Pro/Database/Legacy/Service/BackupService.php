<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types & type-hints
// TODO PHP7.1; constant visibility

namespace WPStaging\Pro\Database\Legacy\Service;

use Exception;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Pro\Database\Legacy\Command\CreateBackupCommand;
use WPStaging\Pro\Database\Legacy\Command\DeleteBackupCommand;
use WPStaging\Pro\Database\Legacy\Command\Dto\ExportDto;
use WPStaging\Pro\Database\Legacy\Command\Dto\BackupDto;
use WPStaging\Pro\Database\Legacy\Command\Exception\BackupCommandException;
use WPStaging\Pro\Database\Legacy\Command\ExportBackupCommand;
use WPStaging\Pro\Database\Legacy\Entity\Backup;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Core\WPStaging;

class BackupService
{
    const EXPORT_DIR_NAME = 'backups/database';

    const PREFIX_TMP = 'wpstgtmp';

    /** @var DatabaseHelper */
    private $databaseHelper;

    /** @var AdapterHelper */
    private $adapterHelper;

    /** @var Directory */
    private $directory;

    public function __construct(DatabaseHelper $databaseHelper, AdapterHelper $adapterHelper, Directory $directory)
    {
        $this->databaseHelper = $databaseHelper;
        $this->adapterHelper = $adapterHelper;
        $this->directory = $directory;
    }

    /**
     * @param BackupDto $dto
     *
     * @return Backup|null
     */
    public function create(BackupDto $dto)
    {
        $command = new CreateBackupCommand($this);
        $command->setDto($dto);
        $command->execute();

        return $command->getBackup();
    }

    /**
     * @param string $prefix
     * @param bool $skipValidation
     */
    public function delete($prefix, $skipValidation = false)
    {
        $dto = new BackupDto();
        $dto->setTargetPrefix($prefix);

        $command = new DeleteBackupCommand($this);
        $command->setDto($dto);
        $command->setSkipValidation($skipValidation);

        try {
            $command->execute();
        } catch (BackupCommandException $e) {
            // TODO log?
            $this->getDatabaseHelper()->getRepository()->deleteById($prefix);
        }
    }

    /**
     * @param string|null $prefix
     *
     * @return string
     * @throws Exception
     * @throws NotCompatibleException
     */
    public function export($prefix = null)
    {
        if (!class_exists('PDO')) {
            throw new NotCompatibleException();
        }

        if ($prefix === null) {
            $prefix = $this->databaseHelper->getDatabase()->getPrefix();
        }

        $exportDirectory = trailingslashit(path_join($this->directory->getPluginUploadsDirectory(), self::EXPORT_DIR_NAME));
        $fs = new Filesystem();
        $fs->delete($exportDirectory);
        $fs->mkdir($exportDirectory, true);

        $dto = (new ExportDto())->hydrate([
            'prefix' => $prefix,
            'directory' => $exportDirectory,
            'format' => $this->provideExportFormat(),
            'version' => WPStaging::getVersion(),
        ]);

        $tableService = $this->databaseHelper->getTableService();
        $logger = $this->adapterHelper->getLogger();

        $command = new ExportBackupCommand($dto, $tableService, $logger);
        $command->execute();
        return $dto->getFullPath();
    }

    /**
     * @return DatabaseHelper
     */
    public function getDatabaseHelper()
    {
        return $this->databaseHelper;
    }

    /**
     * @return AdapterHelper
     */
    public function getAdapterHelper()
    {
        return $this->adapterHelper;
    }

    /**
     * @return string
     */
    private function provideExportFormat()
    {
        if (!function_exists('gzwrite')) {
            return ExportBackupCommand::FORMAT_SQL;
        }
        return ExportBackupCommand::FORMAT_GZIP;
    }
}
