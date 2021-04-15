<?php

namespace WPStaging\Pro\Backup\Service;

use WPStaging\Framework\Utils\Cache\BufferedCache;
use WPStaging\Pro\Backup\BackupsFinder;
use WPStaging\Pro\Backup\Service\Dto\ExportFileHeadersDto;

class LegacyDatabaseConverter
{
    private $bufferedCache;
    private $backupsFinder;

    public function __construct(BufferedCache $bufferedCache, BackupsFinder $backupsFinder)
    {
        $this->bufferedCache = $bufferedCache;
        $this->backupsFinder = $backupsFinder;
    }

    public function convert()
    {
        $backupFiles = $this->backupsFinder->findLegacyDatabaseBackups();

        if (empty($backupFiles)) {
            return;
        }

        /** @var \SplFileInfo $backupFile */
        foreach ($backupFiles as $backupFile) {
            // Store some information from the original file before we rename it
            $sqlFileRelativePath = str_replace(ABSPATH, '', $backupFile->getRealPath());
            $originalFileSize    = $backupFile->getSize();

            // Rename .sql to .wpstg
            $destinationDir  = trailingslashit(dirname($backupFile->getRealPath()));
            $destinationFile = $backupFile->getBasename('.sql') . '.wpstg';
            $destinationFile = $destinationDir . wp_unique_filename($destinationDir, $destinationFile);
            rename($backupFile->getRealPath(), $destinationFile);

            // Buffered cache can append to files without storing them in memory
            $this->bufferedCache->setPath($destinationFile);

            // Simulate a cache index that would be generated on a normal export
            $index = $sqlFileRelativePath . '|0:' . $originalFileSize;
            $this->bufferedCache->append($index);

            // Build the header
            $headersToAdd = new ExportFileHeadersDto();
            $headersToAdd->setIsExportingDatabase(true);
            $headersToAdd->setIsLegacyConverted(true);
            $headersToAdd->setName('Legacy Database Backup (' . $backupFile->getBasename() . ')');

            // This path will be used when extracting the contents of this .wpstg file. In this case,
            // we tell where to extract the .SQL file to then import it.
            $headersToAdd->setDatabaseFile($sqlFileRelativePath);

            // Simulate a header start and end in bytes that would be generated on a normal export
            clearstatcache();
            $fileSizeWithIndex = filesize($destinationFile);
            $headersToAdd->setHeaderStart(($fileSizeWithIndex - strlen($index)));
            $headersToAdd->setHeaderEnd($fileSizeWithIndex);

            // Store the headers at the bottom of the .SQL file, making it a functional .WPSTG file.
            $fileHeaders = json_encode($headersToAdd);
            $this->bufferedCache->append($fileHeaders);
        }
    }
}
