<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore;

use WPStaging\Pro\Backup\Abstracts\Task\AbstractTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\RequirementsCheckDto;
use RuntimeException;
use WPStaging\Framework\Traits\ResourceTrait;

class RequirementsCheckTask extends AbstractTask
{
    use ResourceTrait;

    const REQUEST_NOTATION  = 'backup.site.extract.requirementsCheck';
    const REQUEST_DTO_CLASS = RequirementsCheckDto::class;
    const TASK_NAME         = 'backup_site_requirements_check';
    const TASK_TITLE        = 'Requirements Check';

    /** @var RequirementsCheckDto */
    protected $requestDto;

    public function execute()
    {
        $this->prepare();

        try {
            $this->cannotImportSingleSiteExportIntoMultisiteAndViceVersa();
        } catch (RuntimeException $e) {
            $this->logger->critical($e->getMessage());

            return $this->generateResponse();
        }

        $this->logger->info(__('Requirements check passed...', 'wp-staging'));

        return $this->generateResponse();
    }

    /**
     * @throws RuntimeException When trying to import a .wpstg file generated from a multi-site
     *                          installation into a single-site and vice-versa.
     */
    private function cannotImportSingleSiteExportIntoMultisiteAndViceVersa()
    {
        if ($this->requestDto->getSingleOrMulti() === 'single' && !is_multisite()) {
            // Early bail: .wpstg file is for "single" site, and we are in single-site.
            return;
        }

        if ($this->requestDto->getSingleOrMulti() === 'multi' && is_multisite()) {
            // Early bail: .wpstg file is for "multi" site, and we are in multi-site.
            return;
        }

        if ($this->requestDto->getSingleOrMulti() === 'single' && is_multisite()) {
            throw new \RuntimeException('This export file was generated from a single-site WordPress installation. This website uses a multi-site WordPress installation, therefore the importer cannot proceed.');
        }

        if ($this->requestDto->getSingleOrMulti() === 'multi' && !is_multisite()) {
            throw new \RuntimeException('This export file was generated from a multi-site WordPress installation. This website uses a single-site WordPress installation, therefore the importer cannot proceed.');
        }

        throw new \RuntimeException('This export is in an unknown format. It was not possible to determine whether it was generated from a single-site WordPress installation, or a multi-site WordPress installation, therefore the importer cannot proceed.');
    }
}
