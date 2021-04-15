<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore;

use WPStaging\Pro\Backup\Abstracts\Task\AbstractTask;
use WPStaging\Pro\Backup\Service\Dto\ExportFileHeadersDto;
use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\ExtractFilesDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use RuntimeException;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Service\Extractor;
use WPStaging\Pro\Backup\Service\Dto\ExtractorDto;

class ExtractFilesTask extends AbstractTask
{
    use ResourceTrait;

    const REQUEST_NOTATION  = 'backup.site.extract.files';
    const REQUEST_DTO_CLASS = ExtractFilesDto::class;
    const TASK_NAME         = 'backup_site_extract_files';
    const TASK_TITLE        = 'Extracting Files';

    /** @var ExtractFilesDto */
    protected $requestDto;

    /** @var Extractor */
    protected $extractorService;

    public function __construct(Extractor $extractor, LoggerInterface $logger, Cache $cache)
    {
        parent::__construct($logger, $cache);
        $this->extractorService = $extractor;
    }

    public function execute()
    {
        $this->prepare();

        try {
            $extractDto = $this->provideExtractDto();
        } catch (RuntimeException $e) {
            $this->logger->critical($e->getMessage());

            return $this->generateResponse();
        }

        $steps = $this->requestDto->getSteps();

        $steps->setTotal($extractDto->getFileHeadersDto()->getTotalFiles());

        $this->extractorService->setDto($extractDto, $this->requestDto->getTmpDirectory());
        $this->extractorService->extract([$this, 'isThreshold']);
        $steps->setCurrent($extractDto->getProcessedFiles());

        $this->logger->info(sprintf('Extracted %d/%d files', $steps->getCurrent(), $steps->getTotal()));

        return $this->generateResponse();
    }

    protected function provideExtractDto()
    {
        try {
            $fileHeaders = (new ExportFileHeadersDto())->hydrateByFilePath($this->requestDto->getFilePath());
        } catch (\Exception $e) {
            throw $e;
        }
        $extractDto = new ExtractorDto();
        $extractDto->setId($this->requestDto->getId());
        $extractDto->setFileHeadersDto($fileHeaders);
        $extractDto->setFullPath($this->requestDto->getFilePath());
        $extractDto->setSeekToHeader($this->requestDto->getHeaderStartsAt());
        $extractDto->setSeekToFile($this->requestDto->getFileStartsAt());

        return $extractDto;
    }
}
