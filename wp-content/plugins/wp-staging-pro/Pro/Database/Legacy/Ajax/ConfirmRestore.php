<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Framework\Database\TableDto;
use WPStaging\Framework\Database\TableService;
use WPStaging\Pro\Database\Legacy\Repository\BackupRepository;
use WPStaging\Framework\Adapter\Database;
use WPStaging\Framework\Collection\Collection;

class ConfirmRestore extends AbstractTemplateComponent
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

        $tblService = new TableService();

        $prodTables = $tblService->findTableStatusStartsWith();
        if (!$prodTables || $prodTables->count() < 1) {
            wp_send_json([
                'error' => true,
                'message' => __('Production (live) database tables not found.', 'wp-staging'),
            ]);
        }

        $backupTables = $tblService->findTableStatusStartsWith($id);
        if (!$backupTables || $backupTables->count() < 1) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Database tables for backup %s not found.', 'wp-staging'), $id),
            ]);
        }

        // TODO RPoC; perhaps just check; isNotSame
        $prefixProd = (new Database())->getPrefix();
        $result = $this->templateEngine->render(
            'Backend/views/database/legacy/confirm-restore.php',
            [
                'backup' => $backup,
                'backupTables' => $backupTables,
                'prodTables' => $prodTables,
                'isTableChanged' => static function (TableDto $table, Collection $oppositeCollection) use ($id, $prefixProd) {
                    $tableName = str_replace([$id, $prefixProd], null, $table->getName());
                    /** @var TableDto $item */
                    foreach ($oppositeCollection as $item) {
                        $itemName = str_replace([$id, $prefixProd], null, $item->getName());
                        if ($tableName !== $itemName) {
                            continue;
                        }

                        return $item->getSize() !== $table->getSize();
                    }
                    return false;
                },
            ]
        );
        wp_send_json($result);
    }
}
