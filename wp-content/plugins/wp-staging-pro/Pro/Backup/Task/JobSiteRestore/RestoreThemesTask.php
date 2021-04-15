<?php

namespace WPStaging\Pro\Backup\Task\JobSiteRestore;

use WPStaging\Pro\Backup\Abstracts\Task\RestoreTask;
use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\RestoreFilesDto;
use WPStaging\Pro\Backup\Task\JobSiteRestore\Dto\RestoreMergeFilesDto;

class RestoreThemesTask extends RestoreTask
{
    const REQUEST_NOTATION  = 'backup.site.restore.themes';
    const REQUEST_DTO_CLASS = RestoreMergeFilesDto::class;
    const TASK_NAME         = 'backup_site_restore_themes';
    const TASK_TITLE        = 'Restoring Themes From Backup';

    /** @var RestoreFilesDto */
    protected $requestDto;

    protected function buildQueue()
    {
        $themesToRestore = $this->getThemesToRestore();
        $existingThemes  = $this->getExistingThemes();

        $themeRoot = trailingslashit(get_theme_root());

        foreach ($themesToRestore as $themeName => $themePath) {
            /*
             * Scenario: Restoring a theme that already exists
             * 1. Backup old theme
             * 2. Restore new theme
             * 3. Delete backup
             */
            if (array_key_exists($themeName, $existingThemes)) {
                $this->enqueueMove($existingThemes[$themeName], "$themeRoot$themeName.wpstgRestore.original");
                $this->enqueueMove($themesToRestore[$themeName], "$themeRoot$themeName");
                $this->enqueueDelete("$themeRoot$themeName.wpstgRestore.original");
                continue;
            }

            /*
             * Scenario 2: Restoring a theme that does not yet exist
             */
            $this->enqueueMove($themesToRestore[$themeName], "$themeRoot$themeName");
        }
    }

    /**
     * @return array An array of paths of themes to restore.
     */
    private function getThemesToRestore()
    {
        $tmpDir = $this->requestDto->getSource();
        $tmpDir = (string)apply_filters('wpstg.restore.themes.tmpDir', $tmpDir);

        return $this->findThemesInDir($tmpDir);
    }

    /**
     * @return array An array of paths of existing themes.
     */
    private function getExistingThemes()
    {
        $destDir = get_theme_root();
        $destDir = (string)apply_filters('wpstg.restore.themes.destDir', $destDir);

        return $this->findThemesInDir($destDir);
    }

    /**
     * @param string $path Folder to look for themes, eg: '/var/www/wp-content/themes'
     *
     * @example [
     *              'twentynineteen' => '/var/www/wp-content/themes/twentynineteen',
     *              'twentytwenty' => '/var/www/wp-content/themes/twentytwenty',
     *          ]
     *
     * @return array An array of paths of themes found in the root of given directory,
     *               where the index is the name of the theme, and the value it's path.
     */
    private function findThemesInDir($path)
    {
        $it = new \DirectoryIterator($path);

        $themes = [];

        /** @var \DirectoryIterator $item */
        foreach ($it as $item) {
            if ($item->isDir()) {
                if (file_exists(trailingslashit($item->getPathname()) . 'style.css')) {
                    $themes[$item->getBasename()] = $item->getPathname();
                }
            }
        }

        return $themes;
    }
}
