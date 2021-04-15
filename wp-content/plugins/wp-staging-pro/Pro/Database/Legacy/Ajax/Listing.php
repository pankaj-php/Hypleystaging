<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Ajax;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Database\Legacy\Repository\BackupRepository;

class Listing extends AbstractTemplateComponent
{

    /** @var Directory */
    private $directory;

    private $backupRepository;

    public function __construct(Directory $directory, BackupRepository $backupRepository, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->backupRepository = $backupRepository;
        $this->directory = $directory;
    }

    public function render()
    {
        if (! $this->canRenderAjax()) {
            return;
        }

        $backups = $this->backupRepository->findAll();
        if ($backups) {
            $backups->sortBy('updatedAt');
        }

        $directories = [
            'uploads' => $this->directory->getUploadsDirectory(),
            'themes' => trailingslashit(get_theme_root()),
            'plugins' => trailingslashit(WP_PLUGIN_DIR),
            'muPlugins' => trailingslashit(WPMU_PLUGIN_DIR),
            'wpContent' => trailingslashit(WP_CONTENT_DIR),
            'wpStaging' => $this->directory->getPluginUploadsDirectory(),
        ];

        $result = $this->templateEngine->render(
            'Backend/views/database/legacy/listing.php',
            [
                'backups' => $backups ?: [],
                'directory' => $this->directory,
                'directories' => $directories,
                'urlAssets' => trailingslashit(WPSTG_PLUGIN_URL) . 'assets/',
            ]
        );
        wp_send_json($result);
    }
}
