<?php

namespace WPStaging\Pro\Backup;

use WPStaging\Framework\Filesystem\FileScanner;
use WPStaging\Pro\Backup\Abstracts\Dto\QueueJobDto;
use WPStaging\Framework\DI\ServiceProvider;
use WPStaging\Pro\Backup\Ajax\Cancel;
use WPStaging\Pro\Backup\Ajax\Create;
use WPStaging\Pro\Backup\Ajax\Delete;
use WPStaging\Pro\Backup\Ajax\Edit;
use WPStaging\Pro\Backup\Ajax\Listing;
use WPStaging\Pro\Backup\Ajax\Restore;
use WPStaging\Pro\Backup\Ajax\FileInfo;
use WPStaging\Pro\Backup\Ajax\FileList;
use WPStaging\Pro\Backup\Ajax\Status;
use WPStaging\Pro\Backup\Ajax\Upload;
use WPStaging\Pro\Backup\Job\JobSiteExport;
use WPStaging\Pro\Backup\Job\Dto\JobSiteExportDto;
use WPStaging\Pro\Backup\Job\JobSiteRestore;
use WPStaging\Pro\Backup\Job\Dto\JobSiteRestoreDto;
use WPStaging\Pro\Backup\Task\JobSiteExport\FileScannerTask;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Pro\Database\Legacy\Ajax\Listing as LegacyDatabaseListing;
use WPStaging\Pro\Database\Legacy\Ajax\Create as LegacyDatabaseCreate;
use WPStaging\Pro\Database\Legacy\Ajax\Export as LegacyDatabaseExport;
use WPStaging\Pro\Database\Legacy\Ajax\ConfirmDelete as LegacyDatabaseDeleteConfirm;
use WPStaging\Pro\Database\Legacy\Ajax\Delete as LegacyDatabaseDelete;
use WPStaging\Pro\Database\Legacy\Ajax\ConfirmRestore as LegacyDatabaseRestoreConfirm;
use WPStaging\Pro\Database\Legacy\Ajax\Restore as LegacyDatabaseRestore;
use WPStaging\Pro\Database\Legacy\Job\JobRestoreBackup as LegacyDatabaseJobRestoreBackup;
use WPStaging\Pro\Database\Legacy\Job\JobRestoreBackupDto as LegacyDatabaseJobRestoreBackupDto;
use WPStaging\Pro\Database\Legacy\Ajax\Edit as LegacyDatabaseEdit;

class BackupServiceProvider extends ServiceProvider
{
    public function registerClasses()
    {
        // @todo: Remove this once this is merged: https://github.com/lucatume/di52/pull/32
        $this->container->bind(QueueJobDto::class, '');

        // Jobs
        $this->container->singleton(JobSiteRestore::class);

        // Contextual binds
        $this->container->when(JobSiteRestore::class)
                        ->needs(QueueJobDto::class)
                        ->give(JobSiteRestoreDto::class);

        $this->container->when(JobSiteExport::class)
                        ->needs(QueueJobDto::class)
                        ->give(JobSiteExportDto::class);

        $this->container->when(LegacyDatabaseJobRestoreBackup::class)
                        ->needs(QueueJobDto::class)
                        ->give(LegacyDatabaseJobRestoreBackupDto::class);

        $this->sharedLoggerForFileScanner();
    }

    public function addHooks()
    {
        if (wp_doing_ajax()) {
            // Backup export/import
            add_action('wp_ajax_wpstg--backups--create', $this->container->callback(Create::class, 'render'));
            add_action('wp_ajax_wpstg--backups--listing', $this->container->callback(Listing::class, 'render'));
            add_action('wp_ajax_wpstg--backups--delete', $this->container->callback(Delete::class, 'render'));
            add_action('wp_ajax_wpstg--backups--cancel', $this->container->callback(Cancel::class, 'render'));
            add_action('wp_ajax_wpstg--backups--edit', $this->container->callback(Edit::class, 'render'));
            add_action('wp_ajax_wpstg--backups--status', $this->container->callback(Status::class, 'render'));
            add_action('wp_ajax_wpstg--backups--import--file-list', $this->container->callback(FileList::class, 'render'));
            add_action('wp_ajax_wpstg--backups--import--file-info', $this->container->callback(FileInfo::class, 'render'));
            add_action('wp_ajax_wpstg--backups--import--file-upload', $this->container->callback(Upload::class, 'render'));
            add_action('wp_ajax_wpstg--backups--site--restore', $this->container->callback(Restore::class, 'render'));

            // Legacy database
            add_action('wp_ajax_wpstg--backups--database-legacy-create', $this->container->callback(LegacyDatabaseCreate::class, 'render'));
            add_action('wp_ajax_wpstg--backups--database-legacy-listing', $this->container->callback(LegacyDatabaseListing::class, 'render'));
            add_action('wp_ajax_wpstg--backups--database-legacy-delete', $this->container->callback(LegacyDatabaseDelete::class, 'render'));
            add_action('wp_ajax_wpstg--backups--database-legacy-edit', $this->container->callback(LegacyDatabaseEdit::class, 'render'));
            add_action('wp_ajax_wpstg--backups--database-legacy-export', $this->container->callback(LegacyDatabaseExport::class, 'render'));
            add_action('wp_ajax_wpstg--backups--database-legacy-delete-confirm', $this->container->callback(LegacyDatabaseDeleteConfirm::class, 'render'));
            add_action('wp_ajax_wpstg--backups--database-legacy-restore-confirm', $this->container->callback(LegacyDatabaseRestoreConfirm::class, 'render'));
            add_action('wp_ajax_wpstg--backups--database-legacy-restore', $this->container->callback(LegacyDatabaseRestore::class, 'render'));

            // Nopriv
            add_action('wp_ajax_nopriv_wpstg--backups--database-legacy-listing', $this->container->callback(LegacyDatabaseListing::class, 'render'));
            add_action('wp_ajax_nopriv_wpstg--backups--database-legacy-restore', $this->container->callback(LegacyDatabaseRestore::class, 'render'));
            add_action('wp_ajax_nopriv_wpstg--backups--site--restore', $this->container->callback(Restore::class, 'render'));
        }
    }

    private function sharedLoggerForFileScanner()
    {
        $fileScannerLogger = clone $this->container->make(LoggerInterface::class);

        $this->container->when(FileScannerTask::class)
                        ->needs(LoggerInterface::class)
                        ->give($fileScannerLogger);

        $this->container->when(FileScanner::class)
                        ->needs(LoggerInterface::class)
                        ->give($fileScannerLogger);
    }
}
