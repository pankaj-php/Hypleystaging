<?php

namespace WPStaging\Pro\Backup\Service;

use Exception;
use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Filesystem\File;
use WPStaging\Framework\Filesystem\Filesystem;
use WPStaging\Pro\Backup\Service\Dto\ExtractorDto;
use WPStaging\Pro\Backup\Service\Dto\ExtractorFileDto;
use WPStaging\Pro\Backup\Service\Exceptions\ExtractorException;

class Extractor
{
    /** @var Directory */
    private $directory;

    /** @var ExtractorDto */
    private $dto;

    /** @var ExtractorFileDto|null */
    private $fileDto;

    /** @var File */
    private $file;

    /** @var string */
    private $dirRestore;

    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    public function setDto(ExtractorDto $dto, $tmpDirectory)
    {
        $this->dto = $dto;
        $this->setup($tmpDirectory);
    }

    /**
     * @param callable|null $shouldStop
     *
     * @return ExtractorDto
     */
    public function extract(callable $shouldStop = null)
    {
        while (!$shouldStop() && !$this->dto->isFinished()) {
            $this->extractCurrentFile($shouldStop);
        }

        // Move DB export directly under extract target
        $fileHeaders = $this->dto->getFileHeadersDto();
        if ($this->dto->isFinished() && $fileHeaders->getIsExportingDatabase()) {
            $dbExport = $this->dirRestore . $fileHeaders->getDatabaseFile();
            try {
                rename($dbExport, $this->dirRestore . basename($fileHeaders->getDatabaseFile()));
            } catch (Exception $e) {
                $e->getMessage();
            }
        }

        return $this->dto;
    }

    private function extractCurrentFile(callable $shouldStop = null)
    {
        // Move file to the current header / index position
        $this->file->fseek($this->dto->getSeekToHeader());
        // Get file info from the position
        $this->fileDto = new ExtractorFileDto($this->file, $this->dto);
        // Move cursor to file position
        $this->file->fseek($this->fileDto->findSeekTo());

        // Create / Write to File
        try {
            $this->fileBatchWrite($shouldStop);
        } catch (Exception $e) {
            // Set this file as "written", so that we can skip to the next file.
            $this->fileDto->setWrittenBytes($this->fileDto->getTotalBytes());

            /** @todo Show errors to user */
            if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
                error_log($e->getMessage());
            }
        }

        if (!$this->fileDto->isFinished()) {
            $this->dto->addSeekToFile($this->fileDto->getWrittenBytes());

            return;
        }

        $this->dto->incrementProcessedFiles();
        $this->dto->addSeekToHeader(strlen($this->fileDto->getLine()));
    }

    /**
     * @param callable|null $shouldStop
     *
     * @throws ExtractorException
     */
    private function fileBatchWrite(callable $shouldStop = null)
    {
        $isStop          = false;
        $restoreFilePath = $this->dirRestore . $this->fileDto->getRelativePath();

        if (file_exists($restoreFilePath)) {
            throw ExtractorException::fileAlreadyExists($restoreFilePath);
        }

        if (!ctype_print($restoreFilePath)) {
            if (defined('WPSTG_DEBUG') && WPSTG_DEBUG) {
                error_log('Export: Trying to create a folder with a path that does not seem to be valid.');
            }
        }

        (new Filesystem())->mkdir(dirname($restoreFilePath));
        $targetFile = new File($restoreFilePath, File::MODE_APPEND);

        while (!$isStop) {
            $length          = $this->fileDto->findReadTo();
            $partialContents = '';
            if ($length > 0) {
                /*
                 * @todo: Add option to delete from .wpstg file as we extract, for filesystems with low disk space.
                 */
                $partialContents = $this->file->fread($this->fileDto->findReadTo());
            }
            $writtenBytes = $targetFile->fwrite($partialContents);
            $this->fileDto->addWrittenBytes($writtenBytes);
            $isStop = $shouldStop() || $this->fileDto->isFinished();
        }
    }

    private function setup($tmpDirectory)
    {
        if ($this->dto->getFullPath()) {
            $this->file = new File($this->dto->getFullPath());
        }

        if ($this->dto->getId()) {
            $this->dirRestore = (new Filesystem())->mkdir($tmpDirectory);
        }

        $this->dto->getFileHeadersDto()->setDirUploads($this->directory->getUploadsDirectory());
    }
}
