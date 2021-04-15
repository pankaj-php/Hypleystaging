<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore;

use WPStaging\Pro\Backup\Abstracts\Task\RestoreTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\RestoreFilesDto;
use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\RestoreMergeFilesDto;

class RestorePluginsTask extends RestoreTask
{
    const REQUEST_NOTATION  = 'backup.site.restore.plugins';
    const REQUEST_DTO_CLASS = RestoreMergeFilesDto::class;
    const TASK_NAME         = 'backup_site_restore_plugins';
    const TASK_TITLE        = 'Restoring Plugins From Backup';

    /** @var RestoreFilesDto */
    protected $requestDto;

    protected function buildQueue()
    {
        $pluginsToRestore = $this->getPluginsToRestore();
        $existingPlugins  = $this->getExistingPlugins();

        $pluginRoot = trailingslashit(WP_PLUGIN_DIR);

        foreach ($pluginsToRestore as $pluginSlug => $pluginPath) {
            /*
             * Scenario: Restoring a plugin that already exists
             * 1. Backup old plugin
             * 2. Restore new plugin
             * 3. Delete backup
             */
            if (array_key_exists($pluginSlug, $existingPlugins)) {
                $this->enqueueMove($existingPlugins[$pluginSlug], "$pluginRoot$pluginSlug.wpstgRestore.original");
                $this->enqueueMove($pluginsToRestore[$pluginSlug], "$pluginRoot$pluginSlug");
                $this->enqueueDelete("$pluginRoot$pluginSlug.wpstgRestore.original");
                continue;
            }

            /*
             * Scenario 2: Restoring a plugin that does not yet exist
             */
            $this->enqueueMove($pluginsToRestore[$pluginSlug], "$pluginRoot$pluginSlug");
        }
    }

    /**
     * @return array An array of paths of plugins to restore.
     */
    private function getPluginsToRestore()
    {
        $tmpDir = $this->requestDto->getSource();
        $tmpDir = (string)apply_filters('wpstg.restore.plugins.tmpDir', $tmpDir);

        return $this->findPluginsInDir($tmpDir);
    }

    /**
     * @return array An array of paths of existing plugins.
     */
    private function getExistingPlugins()
    {
        $destDir = WP_PLUGIN_DIR;
        $destDir = (string)apply_filters('wpstg.restore.plugins.destDir', $destDir);

        return $this->findPluginsInDir($destDir);
    }

    /**
     * @param string $path Folder to look for plugins, eg: '/var/www/wp-content/plugins'
     *
     * @example [
     *              'foo' => '/var/www/wp-content/plugins/foo',
     *              'foo.php' => '/var/www/wp-content/plugins/foo.php',
     *          ]
     *
     * @return array An array of paths of plugins found in the root of given directory,
     *               where the index is the name of the plugin, and the value it's path.
     */
    private function findPluginsInDir($path)
    {
        $it = new \DirectoryIterator($path);

        $plugins = [];

        /** @var \DirectoryIterator $fileInfo */
        foreach ($it as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            if ($fileInfo->isLink()) {
                continue;
            }

            // wp-content/plugins/foo
            if ($fileInfo->isDir()) {
                $plugins[$fileInfo->getBasename()] = $fileInfo->getPathname();

                continue;
            }

            // wp-content/plugins/foo.php
            if ($fileInfo->isFile() && $fileInfo->getExtension() === 'php' && $fileInfo->getBasename() !== 'index.php') {
                $plugins[$fileInfo->getBasename()] = $fileInfo->getPathname();

                continue;
            }
        }

        return $plugins;
    }
}
