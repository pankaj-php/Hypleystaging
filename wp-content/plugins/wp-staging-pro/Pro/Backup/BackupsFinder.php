<?php

namespace WPStaging\Pro\Backup;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Pro\Backup\Service\Compressor;
use WPStaging\Vendor\Symfony\Component\Finder\SplFileInfo;

/**
 * Class BackupsFinder
 *
 * Finds the .wsptg backups in the filesystem.
 *
 * @package WPStaging\Pro\Backup
 */
class BackupsFinder
{
    private $directory;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    /**
     * @return \AppendIterator An AppendIterator with instances of DirectoryIterator that points to folders that holds backup files.
     */
    private function getBackupDirectories()
    {
        // Backups that the user uploaded.
        $pluginUploadsDir = $this->directory->getPluginUploadsDirectory();
        $pluginUploadsDir = ABSPATH . str_replace(ABSPATH, '', $pluginUploadsDir);

        // Backups that the user generated.
        $exportDir = $pluginUploadsDir . Compressor::EXPORT_DIR_NAME;
        $exportDir = ABSPATH . str_replace(ABSPATH, '', $exportDir);

        // Make sure paths exists
        wp_mkdir_p($pluginUploadsDir);
        wp_mkdir_p($exportDir);

        $it = new \AppendIterator();

        try {
            $it->append(new \DirectoryIterator($pluginUploadsDir));
        } catch (\Exception $e) {
            if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
                error_log("Could not create iterator because directory $pluginUploadsDir does not exist.");
            }
        }

        try {
            $it->append(new \DirectoryIterator($exportDir));
        } catch (\Exception $e) {
            if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
                error_log("Could not create iterator because directory $exportDir does not exist.");
            }
        }

        return $it;
    }

    /**
     * @return \array<\SplFileInfo> An array of SplFileInfo objects of .wpstg backup files.
     */
    public function findBackups()
    {
        $it = $this->getBackupDirectories();

        $backupsFound = [];

        /** @var SplFileInfo $file */
        foreach ($it as $file) {
            if ($file->isFile() && !$file->isLink() && $file->getExtension() === 'wpstg') {
                $backupsFound[] = clone $file;
            }
        }

        return $backupsFound;
    }

    /**
     * @return \array<\SplFileInfo> An array of SplFileInfo objects of legacy .sql backup files.
     */
    public function findLegacyDatabaseBackups()
    {
        $it = $this->getBackupDirectories();

        $legacyDatabaseBackupsFound = [];

        /** @var SplFileInfo $file */
        foreach ($it as $file) {
            if ($file->isFile() && !$file->isLink() && $file->getExtension() === 'sql') {
                $legacyDatabaseBackupsFound[] = clone $file;
            }
        }

        return $legacyDatabaseBackupsFound;
    }
}
