<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Database\Legacy\Entity\Backup;
use WPStaging\Framework\Database\TableService;
use WPStaging\Pro\Database\Legacy\Repository\BackupRepository;

class ConfirmDelete extends AbstractTemplateComponent
{

    /** @var BackupRepository  */
    private $backupRepository;

    public function __construct(BackupRepository $backupRepository, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->backupRepository = $backupRepository;
    }

    public function render()
    {
        if (! $this->canRenderAjax()) {
            return;
        }

        $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';
        $backup = $this->backupRepository->find($id);
        if (!$backup) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Backup %s not found.', 'wp-staging'), $id),
                ]);
        }

        if ($backup->getType() === Backup::TYPE_DATABASE) {
            $this->renderDatabase($backup);
            return;
        }

        $this->renderSite($backup);
    }

    private function renderDatabase(Backup $backup)
    {
        $tables = (new TableService())->findTableStatusStartsWith($backup->getId());
        if (!$tables || $tables->count() < 1) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(
                    __('Database tables for backup %1$s not found. You can still <a href="#" id="wpstg-backup-force-delete" data-id="%1$s">delete the listed backup entry</a>.', 'wp-staging'),
                    $backup->getId()
                ),
            ]);
        }

        $result = $this->templateEngine->render(
            'Backend/views/database/legacy/confirm-delete.php',
            [
                'backup' => $backup,
                'tables' => $tables,
            ]
        );
        wp_send_json($result);
    }

    private function renderSite(Backup $backup)
    {
        $result = $this->templateEngine->render(
            'Backend/views/database/legacy/confirm-delete.php',
            [
                'backup' => $backup,
            ]
        );
        wp_send_json($result);
    }
}
