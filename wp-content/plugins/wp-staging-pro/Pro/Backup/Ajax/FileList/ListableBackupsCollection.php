<?php

namespace WPStaging\Pro\Backup\Ajax\FileList;

use WPStaging\Framework\Adapter\DateTimeAdapter;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Pro\Backup\BackupsFinder;
use WPStaging\Pro\Backup\Entity\ListableBackup;
use WPStaging\Pro\Backup\Service\Dto\ExportFileHeadersDto;
use WPStaging\Vendor\Symfony\Component\Finder\SplFileInfo;

class ListableBackupsCollection
{
    private $directory;
    private $dateTimeAdapter;
    private $backupsFinder;

    public function __construct(DateTimeAdapter $dateTimeAdapter, BackupsFinder $backupsFinder, Directory $directory)
    {
        $this->dateTimeAdapter = $dateTimeAdapter;
        $this->directory       = $directory;
        $this->backupsFinder   = $backupsFinder;
    }

    /**
     * @return array<ListableBackup>
     */
    public function getListableBackups()
    {
        $backupFiles = $this->backupsFinder->findBackups();

        // Early bail: No backup files found.
        if (empty($backupFiles)) {
            return [];
        }

        $backups = [];

        /** @var SplFileInfo $file */
        foreach ($backupFiles as $file) {
            try {
                $fileHeaders = new ExportFileHeadersDto();
                $fileHeaders = $fileHeaders->hydrateByFilePath($file->getRealPath());
            } catch (\Exception $e) {
                if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
                    error_log('WPSTAGING: Could not hydrate backup file to show on the backup list. ' . wp_json_encode($file));
                }
                continue;
            }

            /*
             * Prevent listing the same file twice if it's generated and also uploaded.
             * Uploaded files takes precedence as their iterator is appended first.
             */
            if (array_key_exists($fileHeaders->getIdByDateCreated(), $backups)) {
                continue;
            }

            // Replace ABSTPATH with site_url() to get the URL of a path
            $downloadUrl = str_replace(wp_normalize_path(untrailingslashit(ABSPATH)), site_url(), wp_normalize_path($file->getRealPath()));

            $listableBackup                                 = new ListableBackup();
            $listableBackup->type                           = 'site';
            $listableBackup->automatedBackup                = $fileHeaders->getIsAutomatedBackup();
            $listableBackup->legacy                         = $fileHeaders->getIsLegacyConverted();
            $listableBackup->backupName                     = $fileHeaders->getName();
            $listableBackup->dateCreatedTimestamp           = $fileHeaders->getDateCreated();
            $listableBackup->dateCreatedFormatted           = $this->dateTimeAdapter->transformToWpFormat((new \DateTime())->setTimestamp($fileHeaders->getDateCreated()));
            $listableBackup->downloadUrl                    = $downloadUrl;
            $listableBackup->fullPath                       = str_replace($this->directory->getPluginUploadsDirectory(), '', $file->getRealPath());
            $listableBackup->id                             = $fileHeaders->getIdByDateCreated();
            $listableBackup->isExportingDatabase            = $fileHeaders->getIsExportingDatabase();
            $listableBackup->isExportingMuPlugins           = $fileHeaders->getIsExportingMuPlugins();
            $listableBackup->isExportingOtherWpContentFiles = $fileHeaders->getIsExportingOtherWpContentFiles();
            $listableBackup->isExportingPlugins             = $fileHeaders->getIsExportingPlugins();
            $listableBackup->isExportingThemes              = $fileHeaders->getIsExportingThemes();
            $listableBackup->isExportingUploads             = $fileHeaders->getIsExportingUploads();
            $listableBackup->name                           = $file->getFilename();
            $listableBackup->notes                          = $fileHeaders->getNote();
            $listableBackup->size                           = size_format($file->getSize());
            $listableBackup->md5BaseName                    = md5($file->getBasename());

            $backups[$fileHeaders->getIdByDateCreated()] = $listableBackup;
        }

        return $backups;
    }
}
