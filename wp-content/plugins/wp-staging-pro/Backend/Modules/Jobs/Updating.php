<?php

namespace WPStaging\Backend\Modules\Jobs;

use WPStaging\Core\Utils\Logger;
use WPStaging\Core\WPStaging;
use WPStaging\Core\Utils\Helper;
use WPStaging\Framework\Utils\WpDefaultDirectories;

/**
 * Class Cloning
 * @package WPStaging\Backend\Modules\Jobs
 */
class Updating extends Job
{

    /**
     * External Database Used
     * @var bool
     */
    public $isExternalDb;

    /**
     * @var mixed|null
     */
    private $db;

    /**
     * Initialize is called in \Job
     */
    public function initialize()
    {
        $this->db = WPStaging::getInstance()->get("wpdb");
    }

    /**
     * Save Chosen Cloning Settings
     * @return bool
     * @throws \Exception
     */
    public function save()
    {
        if (!isset($_POST) || !isset($_POST["cloneID"])) {
            return false;
        }

        // Delete files to copy listing
        $this->cache->delete("files_to_copy");

        // Generate Options
        $this->options->clone = preg_replace("#\W+#", '-', strtolower($_POST["cloneID"]));
        $this->options->cloneDirectoryName = preg_replace("#\W+#", '-', strtolower($this->options->clone));
        $this->options->cloneNumber = 1;
        $this->options->includedDirectories = [];
        $this->options->excludedDirectories = [];
        $this->options->extraDirectories = [];
        $this->options->excludedFiles = [
            '.htaccess',
            '.DS_Store',
            '*.git',
            '*.svn',
            '*.tmp',
            'desktop.ini',
            '.gitignore',
            '*.log',
            'object-cache.php',
            'web.config' // Important: Windows IIS configuration file. Do not copy this to the staging site is staging site is placed into subfolder

        ];

        $this->options->excludedFilesFullPath = [
            'wp-content' . DIRECTORY_SEPARATOR . 'db.php',
            'wp-content' . DIRECTORY_SEPARATOR . 'object-cache.php',
            'wp-content' . DIRECTORY_SEPARATOR . 'advanced-cache.php'
        ];

        // Define mainJob to differentiate between cloning, updating and pushing
        $this->options->mainJob = 'updating';

        // Job
        $this->options->job = new \stdClass();

        // Check if clone data already exists and use that one
        if (isset($this->options->existingClones[$this->options->clone])) {
            $this->options->cloneNumber = $this->options->existingClones[$this->options->clone]['number'];
            $this->options->databaseUser = $this->options->existingClones[$this->options->clone]['databaseUser'];
            $this->options->databasePassword = $this->options->existingClones[$this->options->clone]['databasePassword'];
            $this->options->databaseDatabase = $this->options->existingClones[$this->options->clone]['databaseDatabase'];
            $this->options->databaseServer = $this->options->existingClones[$this->options->clone]['databaseServer'];
            $this->options->databasePrefix = $this->options->existingClones[$this->options->clone]['databasePrefix'];
            $this->options->destinationHostname = $this->options->existingClones[$this->options->clone]['url'];
            $this->options->uploadsSymlinked = isset($this->options->existingClones[strtolower($this->options->clone)]['uploadsSymlinked']) ? $this->options->existingClones[strtolower($this->options->clone)]['uploadsSymlinked'] : false;
            $this->options->prefix = $this->options->existingClones[$this->options->clone]['prefix'];
            //$this->options->prefix = $this->getStagingPrefix();
            $helper = new Helper();
            $this->options->homeHostname = $helper->getHomeUrlWithoutScheme();
        } else {
            wp_die('Fatal Error: Can not update clone because there is no clone data.');
        }

        $this->isExternalDb = !(empty($this->options->databaseUser) && empty($this->options->databasePassword));

        // Included Tables
        if (isset($_POST["includedTables"]) && is_array($_POST["includedTables"])) {
            $this->options->tables = $_POST["includedTables"];
        } else {
            $this->options->tables = [];
        }

        // Add upload folder to list of excluded directories for push if symlink option is enabled
        if ($this->options->uploadsSymlinked) {
            $wpUploadsFolder = (new WpDefaultDirectories())->getUploadPath();
            $excludedDirectories[] = rtrim($wpUploadsFolder, '/\\');
        }

        // delete uploads folder before copying if uploads is not symlinked
        $this->options->deleteUploadsFolder = !$this->options->uploadsSymlinked && isset($_POST['cleanUploadsDir']) && $_POST['cleanUploadsDir'] === 'true';
        // should not backup uploads during update process
        $this->options->backupUploadsFolder = false;
        // clean plugins and themes dir before updating
        $this->options->deletePluginsAndThemes = isset($_POST['cleanPluginsThemes']) && $_POST['cleanPluginsThemes'] === 'true';
        // set default statuses for backup of uploads dir and cleaning of uploads, themes and plugins dirs
        $this->options->statusBackupUploadsDir = 'skipped';
        $this->options->statusContentCleaner = 'pending';

        // Excluded Directories
        if (isset($_POST["excludedDirectories"]) && is_array($_POST["excludedDirectories"])) {
            $this->options->excludedDirectories = wpstg_urldecode($_POST["excludedDirectories"]);
        }

        // Excluded Directories TOTAL
        // Do not copy these folders and plugins
        $excludedDirectories = [
            WPStaging::getWPpath() . 'wp-content' . DIRECTORY_SEPARATOR . 'cache',
            WPStaging::getWPpath() . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'wps-hide-login',
            WPStaging::getWPpath() . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'wp-super-cache',
            WPStaging::getWPpath() . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'peters-login-redirect',
        ];

        $this->options->excludedDirectories = array_merge($excludedDirectories, $this->options->excludedDirectories);

        // Included Directories
        if (isset($_POST["includedDirectories"]) && is_array($_POST["includedDirectories"])) {
            $this->options->includedDirectories = wpstg_urldecode($_POST["includedDirectories"]);
        }

        // Extra Directories
        if (isset($_POST["extraDirectories"]) && !empty($_POST["extraDirectories"])) {
            $this->options->extraDirectories = wpstg_urldecode($_POST["extraDirectories"]);
        }

        $this->options->cloneDir = '';
        if (isset($_POST["cloneDir"]) && !empty($_POST["cloneDir"])) {
            $this->options->cloneDir = wpstg_urldecode(trailingslashit($_POST["cloneDir"]));
        }

        $this->options->destinationDir = $this->getDestinationDir();

        $this->options->cloneHostname = $this->options->destinationHostname;

        // Make sure it is always enabled for free version
        $this->options->emailsAllowed = true;
        if (defined('WPSTGPRO_VERSION')) {
            $this->options->emailsAllowed = isset($_POST['emailsAllowed']) && $_POST['emailsAllowed'] !== "false";
        }

        // Directories to Copy
        $this->options->directoriesToCopy = array_merge(
            $this->options->includedDirectories,
            $this->options->extraDirectories
        );

        array_unshift($this->options->directoriesToCopy, ABSPATH);

        // Process lock state
        $this->options->isRunning = true;

        return $this->saveOptions();
    }

    /**
     * Get Destination Directory including staging subdirectory
     * @return string
     */
    private function getDestinationDir()
    {
        if (empty($this->options->cloneDir)) {
            return trailingslashit(WPStaging::getWPpath() . $this->options->cloneDirectoryName);
        }
        return trailingslashit($this->options->cloneDir);
    }

    /**
     * Start the cloning job
     * not used but is abstract
     */
    public function start()
    {
    }
}
