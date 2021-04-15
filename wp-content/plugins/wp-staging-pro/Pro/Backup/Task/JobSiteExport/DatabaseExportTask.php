<?php

namespace WPStaging\Pro\Backup\Task\JobSiteExport;

use DateTime;
use Exception;
use WPStaging\Pro\Backup\Task\JobSiteExport\Dto\DatabaseExportDto;
use WPStaging\Pro\Backup\Task\JobSiteExport\Dto\DatabaseExportResponseDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Pro\Backup\Abstracts\Dto\TaskResponseDto;
use WPStaging\Pro\Backup\Abstracts\Task\AbstractTask;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Database\TableService;
use WPStaging\Framework\Database\DatabaseDumper;
use WPStaging\Pro\Backup\Service\Compressor;
use WPStaging\Pro\Backup\Service\Dto\ExporterDto;

class DatabaseExportTask extends AbstractTask
{
    use ResourceTrait;

    const FILE_FORMAT = 'sql';
    const REQUEST_NOTATION = 'database.export';
    const REQUEST_DTO_CLASS = DatabaseExportDto::class;
    const TASK_NAME = 'database_export';
    const TASK_TITLE = 'Export database';

    /**  @var DatabaseDumper */
    private $service;

    /** @var TableService */
    private $tableService;

    /** @var Compressor */
    private $exporter;

    /** @var ExporterDto */
    private $exporterDto;

    /** @var DatabaseExportDto */
    protected $requestDto;

    public function __construct(Compressor $exporter, LoggerInterface $logger, Cache $cache, DatabaseDumper $service)
    {
        parent::__construct($logger, $cache);
        $this->exporter = $exporter;
        $this->exporterDto = $this->exporter->getDto();
        $this->service = $service;
        $this->tableService = new TableService(new Database());
    }

    /**
     * @return object|TaskResponseDto
     * @throws Exception
     */
    public function execute()
    {
        $this->prepare();

        $this->setTimeLimit(DatabaseDumper::MAX_EXECUTION_TIME_SECONDS);

        $result = $this->generateSqlFile();

        $this->requestDto->getSteps()->setCurrent($this->service->getTableIndex());
        $this->requestDto->setTableRowsExported($this->service->getTableRowsExported());
        $this->requestDto->setTableRowsOffset($this->service->getTableRowsOffset());

        $this->writeLog();

        $response = $this->generateResponse();

        if (!$result) {
            // Not finished - continue with current table
            $this->requestDto->getSteps()->setCurrent($this->service->getTableIndex());
            return $response;
        }

        $this->requestDto->getSteps()->setTotal(0);
        $response->setFilePath($result);
        return $response;
    }

    /**
     * @param null|string $filePath
     * @return DatabaseExportResponseDto
     */
    public function generateResponse($filePath = null)
    {
        /** @var DatabaseExportResponseDto $response */
        $response = parent::generateResponse();
        $response->setFilePath($filePath);
        return $response;
    }

    protected function getResponseDto()
    {
        return new DatabaseExportResponseDto();
    }

    protected function writeLog()
    {
        if ($this->requestDto && $this->requestDto->getTableRowsExported()) {
            $this->logger->info(sprintf(__('Exporting database... %s records saved', 'wp-staging'), number_format_i18n($this->requestDto->getTableRowsExported())));
        } else {
            $this->logger->info('Exporting database...');
        }
    }

    /**
     * @return string|null
     */
    protected function generateSqlFile()
    {
        $this->service->setTables($this->getIncludeTables());
        $this->service->setFileName($this->getStoragePath());
        $this->service->setTableIndex($this->requestDto->getSteps()->getCurrent());
        $this->service->setTableRowsOffset($this->requestDto->getTableRowsOffset());
        $this->service->setTableRowsExported($this->requestDto->getTableRowsExported());

        $this->requestDto->getSteps()->setTotal(count($this->getIncludeTables()) + 1);

        try {
            return $this->service->export([$this, 'isThreshold']);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }

    /**
     * @return array
     */
    protected function getIncludeTables()
    {
        $tables = $this->tableService->findTableNamesStartWith($this->service->getDatabase()->getPrefix());
        $views = $this->tableService->findViewsNamesStartWith($this->service->getDatabase()->getPrefix());
        // Add views to bottom of the array to make sure they can be created. Views are based on tables. So tables need to be created before views
        $tablesAndViews = array_merge($tables, $views);
        return $tablesAndViews;
    }

    /**
     * @return string
     */
    private function getStoragePath()
    {
        if (!$this->requestDto->getFileName()) {
            $this->requestDto->setFileName(sprintf(
                '%s_%s_%s.%s',
                rtrim($this->service->getDatabase()->getPrefix(), '_-'),
                (new DateTime())->format('Y-m-d_H-i-s'),
                md5(mt_rand()),
                self::FILE_FORMAT
            ));
        }

        $this->exporterDto->setFilePath($this->exporter->findDestinationDirectory() . $this->requestDto->getFileName());
        return $this->exporter->findDestinationDirectory() . $this->requestDto->getFileName();
    }
}
