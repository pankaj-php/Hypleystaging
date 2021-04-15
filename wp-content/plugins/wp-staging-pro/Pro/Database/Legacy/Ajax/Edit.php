<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Database\Legacy\Command\Dto\BackupDto;
use WPStaging\Pro\Database\Legacy\Entity\Backup;
use WPStaging\Pro\Database\Legacy\Repository\BackupRepository;
use WPStaging\Framework\Utils\Strings;

class Edit extends AbstractTemplateComponent
{
    /** @var BackupRepository */
    private $repository;

    public function __construct(BackupRepository $repository, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->repository = $repository;
    }

    public function render()
    {
        if (! $this->canRenderAjax()) {
            return;
        }

        $id = sanitize_text_field(isset($_POST['id']) ? $_POST['id'] : '');
        $name = sanitize_text_field(isset($_POST['name']) ? $_POST['name'] : '');
        $notes = (new Strings())->sanitizeTextareaField(isset($_POST['notes']) ? $_POST['notes'] : '');

        $backups = $this->repository->findAll();
        if (!$backups) {
            wp_send_json([
                'error' => true,
                'message' => __('No backups exist in the system', 'wp-staging'),
            ]);
            return;
        }

        /** @var Backup|null $backup */
        $backup = $backups->findById($id);
        if (!$backup) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Backup ID: %s not found', 'wp-staging'), $id),
            ]);
            return;
        }

        $backup->setName($name ?: BackupDto::BACKUP_DEFAULT_NAME);
        $backup->setNotes($notes ?: null);

        if (!$this->repository->save($backups)) {
            wp_send_json([
                'error' => true,
                'message' => sprintf(__('Failed to update backup ID: %s', 'wp-staging'), $id),
            ]);
            return;
        }

        wp_send_json(true);
    }
}
