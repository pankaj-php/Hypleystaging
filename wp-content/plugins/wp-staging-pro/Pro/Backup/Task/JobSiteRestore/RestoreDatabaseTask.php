<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore;

use WPStaging\Framework\Database\DatabaseDumper;
use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\RestoreDatabaseDto;
use WPStaging\Vendor\Psr\Log\LoggerInterface;
use WPStaging\Framework\Utils\Cache\Cache;
use WPStaging\Pro\Backup\Abstracts\Task\AbstractTask;
use WPStaging\Framework\Traits\MaintenanceTrait;
use WPStaging\Framework\Traits\ResourceTrait;
use WPStaging\Framework\Database\DatabaseRestore;
use WPStaging\Framework\Database\SearchReplace;

class RestoreDatabaseTask extends AbstractTask
{
    use ResourceTrait;

    //use MaintenanceTrait;

    const REQUEST_NOTATION  = 'backup.site.restore.database';
    const REQUEST_DTO_CLASS = RestoreDatabaseDto::class;
    const TASK_NAME         = 'backup_site_restore_database';
    const TASK_TITLE        = 'Importing Database';

    /** @var RestoreDatabaseDto */
    protected $requestDto;

    /** @var DatabaseRestore */
    private $service;

    private $sessionManager;

    private $session;

    private $sessionToken;

    public function __construct(DatabaseRestore $service, LoggerInterface $logger, Cache $cache)
    {
        parent::__construct($logger, $cache);
        //$this->skipMaintenanceMode();
        $this->service = $service;
        $this->service->setLogger($this->logger);
    }

    public function __destruct()
    {
        parent::__destruct();
        //$this->enableMaintenance(false);
    }

    public function init()
    {
        //$this->enableMaintenance(true);
    }

    public function execute()
    {
        $this->prepare();
        $this->service->restore();

        $steps = $this->requestDto->getSteps();
        $steps->setCurrent($this->service->getCurrentLine());
        $this->logger->info(sprintf('Executed %d/%d queries', $steps->getCurrent(), $steps->getTotal()));

        return $this->generateResponse();
    }

    public function prepare()
    {
        parent::prepare();

        $this->service->setShouldStop([$this, 'isThreshold']);
        $this->service->setFile($this->requestDto->getFile());
        $this->service->seekLine($this->requestDto->getSteps()->getCurrent());

        if (!$this->requestDto->getSteps()->getTotal()) {
            $this->requestDto->getSteps()->setTotal($this->service->getTotalLines());
        }

        $searchDefault = [
            DatabaseDumper::DB_REPLACE_SITE_URL,
            DatabaseDumper::DB_REPLACE_ABSPATH,
        ];

        $replaceDefault = [
            get_site_url(),
            ABSPATH,
        ];

        $search  = array_merge($searchDefault, $this->requestDto->getSearch());
        $replace = array_merge($replaceDefault, $this->requestDto->getReplace());

        $searchReplace = (new SearchReplace())
            ->setSearch($search)
            ->setReplace($replace);

        $this->service->setSearchReplace($searchReplace);
    }
}
