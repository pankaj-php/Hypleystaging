<?php

namespace WPStaging\Pro\Backup;

use WPStaging\Framework\Utils\Cache\BufferedCache;
use WPStaging\Pro\Backup\Service\Dto\ExportFileHeadersDto;

class BackupHeaderEditor
{
    private $bufferedCache;
    public function __construct(BufferedCache $bufferedCache)
    {
        $this->bufferedCache = $bufferedCache;
    }

    public function replaceHeaders($backupPath, ExportFileHeadersDto $newHeaders)
    {
        $this->bufferedCache->setPath($backupPath);

        $info = $this->bufferedCache->readLines(1, null, BufferedCache::POSITION_BOTTOM);

        // Expected info: ['', '{FILE_HEADERS_JSON_HERE}']
        if (!is_array($info) || count($info) !== 2) {
            throw new \RuntimeException('Could not read the existing headers from the existing backup.');
        }

        $existingHeaders = end($info);

        if (!is_object(json_decode($existingHeaders))) {
            throw new \RuntimeException('Could not read valid existing headers from the existing backup.');
        }

        // @todo Should we use mb_strlen($_writtenBytes, '8bit') instead of strlen?
        $this->bufferedCache->deleteBottomBytes(strlen($existingHeaders));
        $this->bufferedCache->append(json_encode($newHeaders));
    }
}
