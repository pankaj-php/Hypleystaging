<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore;

use WPStaging\Pro\Backup\Abstracts\Task\RestoreTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\RestoreFilesDto;
use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\RestoreMergeFilesDto;

class RestoreMuPluginsTask extends RestoreTask
{
    const REQUEST_NOTATION  = 'backup.site.restore.muPlugins';
    const REQUEST_DTO_CLASS = RestoreMergeFilesDto::class;
    const TASK_NAME         = 'backup_site_restore_muPlugins';
    const TASK_TITLE        = 'Restoring Mu-Plugins From Backup';

    /** @var RestoreFilesDto */
    protected $requestDto;

    protected function buildQueue()
    {
        $muPluginsToRestore = $this->getMuPluginsToRestore();
        $existingMuPlugins  = $this->getExistingMuPlugins();

        $muPluginRoot = trailingslashit(WPMU_PLUGIN_DIR);

        foreach ($muPluginsToRestore as $muPluginSlug => $muPluginPath) {
            /*
             * Scenario: Restoring a mu-plugin that already exists
             * 1. Backup old mu-plugin
             * 2. Restore new mu-plugin
             * 3. Delete backup
             */
            if (array_key_exists($muPluginSlug, $existingMuPlugins)) {
                $this->enqueueMove($existingMuPlugins[$muPluginSlug], "$muPluginRoot$muPluginSlug.wpstgRestore.original");
                $this->enqueueMove($muPluginsToRestore[$muPluginSlug], "$muPluginRoot$muPluginSlug");
                $this->enqueueDelete("$muPluginRoot$muPluginSlug.wpstgRestore.original");
                continue;
            }

            /*
             * Scenario 2: Restoring a plugin that does not yet exist
             */
            $this->enqueueMove($muPluginsToRestore[$muPluginSlug], "$muPluginRoot$muPluginSlug");
        }
    }

    /**
     * @return array An array of paths of mu-plugins to restore.
     */
    private function getMuPluginsToRestore()
    {
        $tmpDir = $this->requestDto->getSource();
        $tmpDir = (string)apply_filters('wpstg.restore.muPlugins.tmpDir', $tmpDir);

        return $this->findMuPluginsInDir($tmpDir);
    }

    /**
     * @return array An array of paths of existing mu-plugins.
     */
    private function getExistingMuPlugins()
    {
        $destDir = WPMU_PLUGIN_DIR;
        $destDir = (string)apply_filters('wpstg.restore.muPlugins.destDir', $destDir);

        return $this->findMuPluginsInDir($destDir);
    }

    /**
     * @param string $path Folder to look for mu-plugins, eg: '/var/www/wp-content/mu-plugins'
     *
     * @example [
     *              'foo' => '/var/www/wp-content/mu-plugins/foo',
     *              'foo.php' => '/var/www/wp-content/mu-plugins/foo.php',
     *          ]
     *
     * @return array An array of paths of mu-plugins found in the root of given directory,
     *               where the index is the name of the mu-plugin, and the value it's path.
     */
    private function findMuPluginsInDir($path)
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
