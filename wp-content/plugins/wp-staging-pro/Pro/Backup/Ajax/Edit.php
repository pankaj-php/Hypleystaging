<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Framework\Utils\Cache\BufferedCache;
use WPStaging\Pro\Backup\BackupHeaderEditor;
use WPStaging\Pro\Backup\BackupsFinder;
use WPStaging\Framework\Utils\Strings;
use WPStaging\Pro\Backup\Service\Dto\ExportFileHeadersDto;

class Edit extends AbstractTemplateComponent
{
    private $backupHeaderEditor;
    private $backupsFinder;

    public function __construct(BackupsFinder $backupsFinder, BackupHeaderEditor $backupHeaderEditor, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->backupsFinder      = $backupsFinder;
        $this->backupHeaderEditor = $backupHeaderEditor;
    }

    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        $md5   = sanitize_text_field(isset($_POST['md5']) ? $_POST['md5'] : '');
        $name  = sanitize_text_field(isset($_POST['name']) ? $_POST['name'] : '');
        $notes = (new Strings())->sanitizeTextareaField(isset($_POST['notes']) ? $_POST['notes'] : '');

        if (strlen($md5) !== 32) {
            wp_send_json([
                'error'   => true,
                'message' => __('Invalid request.', 'wp-staging'),
            ]);
        }

        $backups = $this->backupsFinder->findBackups();

        // Early bail: No backups found, nothing to edit
        if (empty($backups)) {
            wp_send_json([
                'error'   => true,
                'message' => __('No backups found, nothing to edit.', 'wp-staging'),
            ]);
        }

        // Name must not be empty.
        if (empty($name)) {
            $name = __('Backup', 'wp-staging');
        }

        /** @var \SplFileInfo $backup */
        foreach ($backups as $backup) {
            if ($md5 === md5($backup->getBasename())) {
                try {
                    $newFileHeaders = (new ExportFileHeadersDto())->hydrateByFilePath($backup->getRealPath());
                    $newFileHeaders->setName($name);
                    $newFileHeaders->setNote($notes);

                    $this->backupHeaderEditor->replaceHeaders($backup->getRealPath(), $newFileHeaders);
                } catch (\Exception $e) {
                    wp_send_json([
                        'error'   => true,
                        'message' => esc_html__($e->getMessage(), 'wp-staging'),
                    ]);
                }
            }
        }


        wp_send_json(true);
    }
}
