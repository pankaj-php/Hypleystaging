<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Ajax;

use Exception;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Database\Legacy\Collection\Collection;
use WPStaging\Pro\Database\Legacy\Entity\Backup;
use WPStaging\Pro\Database\Legacy\Repository\BackupRepository;
use WPStaging\Pro\Database\Legacy\Service\NotCompatibleException;
use WPStaging\Pro\Database\Legacy\Service\BackupService;

class Export extends AbstractTemplateComponent
{
    /** @var BackupService */
    private $service;

    /** @var BackupRepository */
    private $backupRepository;

    public function __construct(BackupService $service, BackupRepository $backupRepository, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->service            = $service;
        $this->backupRepository = $backupRepository;
    }

    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : null;

        try {
            $path   = $this->service->export($id);
            $result = $this->pathToUrl($path);

            // Trigger __destruct
            unset($this->service);

            // Store the file path in the database
            $backupsCollection = $this->backupRepository->findAll();
            if ($backupsCollection instanceof Collection) {
                /** @var Backup $backupEntity */
                $backupEntity = $backupsCollection->findById($id);
                if ($backupEntity instanceof Backup) {
                    $backupEntity->setFilePath($path);
                    $this->backupRepository->save($backupsCollection);
                }
            }

            wp_send_json_success($result);
        } catch (NotCompatibleException $e) {
            // Trigger __destruct
            unset($this->service);

            wp_send_json([
                'error'   => true,
                'message' => $e->getMessage(),
            ]);
        } catch (Exception $e) {
            // Trigger __destruct
            unset($this->service);

            wp_send_json([
                'error'   => true,
                'message' => sprintf(__('Failed to export the backup tables %s', 'wp-staging'), $id),
            ]);
        }
    }

    /**
     * @param string $dir
     *
     * @return string
     */
    private function pathToUrl($dir)
    {
        $relativePath = str_replace(ABSPATH, null, $dir);

        return site_url() . '/' . $relativePath;
    }
}
