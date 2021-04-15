<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Backup\Entity\ListableBackup;
use WPStaging\Pro\Backup\Ajax\FileList\ListableBackupsCollection;
use WPStaging\Pro\Backup\Service\LegacyDatabaseConverter;

class FileList extends AbstractTemplateComponent
{
    /** @var ListableBackupsCollection */
    private $listableBackupsCollection;

    /** @var LegacyDatabaseConverter */
    private $legacyDatabaseConverter;

    public function __construct(ListableBackupsCollection $listableBackupsCollection, LegacyDatabaseConverter $legacyDatabaseConverter, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->listableBackupsCollection = $listableBackupsCollection;
        $this->legacyDatabaseConverter   = $legacyDatabaseConverter;
    }

    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        // Convert legacy .sql to .wpstg
        $this->legacyDatabaseConverter->convert();

        // Discover the .wpstg backups in the filesystem
        $listableBackups = $this->listableBackupsCollection->getListableBackups();

        if (empty($listableBackups)) {
            wp_send_json([]);
        }

        /**
         * Javascript expects an array with keys in natural order
         *
         * @var ListableBackup[] $listableBackups
         */
        $listableBackups = array_values($listableBackups);

        // Sort backups by their creation date, newest first.
        usort($listableBackups, function ($item, $nextItem) {
            /**
             * @var ListableBackup $item
             * @var ListableBackup $nextItem
             */
            return $item->dateCreatedTimestamp < $nextItem->dateCreatedTimestamp;
        });

        // Returns a HTML template
        if (isset($_GET['withTemplate']) && $_GET['withTemplate'] == 'true') {
            $output = '';

            /** @var ListableBackup $listable */
            foreach ($listableBackups as $listable) {
                $output .= $this->renderTemplate(
                    'Backend/views/backup/listing-single-backup.php',
                    [
                        'backup' => $listable,
                    ]
                );
            }

            wp_send_json($output);
        }

        // Returns a JSON response
        wp_send_json($listableBackups);
    }
}
