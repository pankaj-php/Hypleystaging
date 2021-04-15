<?php

// TODO PHP7.x; declare(strict_types=1);
// TODO PHP7.x; return types && type-hints

namespace WPStaging\Pro\Backup\Service\Dto;

use JsonSerializable;
use WPStaging\Core\WPStaging;
use WPStaging\Framework\Traits\HydrateTrait;
use WPStaging\Framework\Filesystem\File;
use WPStaging\Framework\Utils\Urls;
use WPStaging\Pro\Backup\Abstracts\Dto\Traits\DateCreatedTrait;
use WPStaging\Pro\Backup\Abstracts\Dto\Traits\IsExportingTrait;

class ExportFileHeadersDto implements JsonSerializable
{
    use HydrateTrait;
    use IsExportingTrait;
    use DateCreatedTrait;

    /** @var int */
    private $headerStart;

    /** @var int */
    private $headerEnd;

    /** @var string */
    private $version;

    /** @var array */
    private $directories = [];

    /** @var int */
    private $totalFiles;

    /** @var int */
    private $totalDirectories;

    /** @var string */
    private $databaseFile;

    /** @var string */
    private $dirWpContent;

    /** @var string */
    private $dirUploads;

    /** @var string */
    private $dirPlugins;

    /** @var string */
    private $dirMuPlugins;

    /** @var string */
    private $dirThemes;

    /** @var string */
    private $siteUrl;

    /** @var string */
    private $abspath;

    /** @var string */
    private $prefix;

    /** @var bool */
    private $singleOrMulti;

    /** @var string */
    private $name;

    /** @var string */
    private $note;

    /** @var bool If true, this backup was generated automatically, eg: When pushing a Staging site into Production. */
    private $isAutomatedBackup = false;

    /** @var bool If true, this was a .SQL database backup that was converted to a .WPSTG file. */
    private $isLegacyConverted = false;

    /**
     * ExportFileHeadersDto constructor.
     *
     * Set some reasonable defaults on construct.
     */
    public function __construct()
    {
        $this->setVersion(WPStaging::getVersion());
        $this->setAbspath(ABSPATH);
        $this->setSiteUrl((new Urls())->getHomeUrlWithoutScheme());
        $this->setDateCreated(current_time('timestamp'));
        $this->setDateCreatedTimezone(wp_timezone_string());
        $this->setSingleOrMulti(is_multisite() ? 'multi' : 'single');
        $this->setDirWpContent(WP_CONTENT_DIR);
        $this->setDirMuPlugins(WPMU_PLUGIN_DIR);
        $this->setDirPlugins(WP_PLUGIN_DIR);
        $this->setDirThemes(get_theme_root());
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        $array                 = get_object_vars($this);
        $array['dirWpContent'] = $this->getDirWpContent();
        $array['dirPlugins']   = $this->getDirPlugins();
        $array['dirMuPlugins'] = $this->getDirMuPlugins();
        $array['dirThemes']    = $this->getDirThemes();

        return $array;
    }

    /**
     * @throws \RuntimeException
     */
    private function hydrateByFile(File $file)
    {
        $strJson = $file->readBottomLines(1);

        // Expected info: ['', '{FILE_HEADERS_JSON_HERE}']
        if (!is_array($strJson) || count($strJson) !== 2) {
            throw new \RuntimeException('Could not read the existing headers from the existing backup.');
        }

        if (!is_object(json_decode($strJson[1]))) {
            throw new \RuntimeException('Could not read valid existing headers from the existing backup.');
        }

        $data = json_decode($strJson[1], true);

        return (new self())->hydrate($data);
    }

    /**
     * @throws \RuntimeException
     */
    public function hydrateByFilePath($filePath)
    {
        return $this->hydrateByFile(new File($filePath));
    }

    /**
     * @return int
     */
    public function getHeaderStart()
    {
        return $this->headerStart;
    }

    /**
     * @param int $headerStart
     */
    public function setHeaderStart($headerStart)
    {
        $this->headerStart = $headerStart;
    }

    /**
     * @return int
     */
    public function getHeaderEnd()
    {
        return $this->headerEnd;
    }

    /**
     * @param int $headerEnd
     */
    public function setHeaderEnd($headerEnd)
    {
        $this->headerEnd = $headerEnd;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return array
     */
    public function getDirectories()
    {
        return (array)$this->directories;
    }

    /**
     * @param array $directories
     */
    public function setDirectories($directories)
    {
        $this->directories = (array)$directories;
    }

    /**
     * @return int
     */
    public function getTotalFiles()
    {
        return $this->totalFiles;
    }

    /**
     * @param int $totalFiles
     */
    public function setTotalFiles($totalFiles)
    {
        $this->totalFiles = $totalFiles;
    }

    /**
     * @return int
     */
    public function getTotalDirectories()
    {
        return $this->totalDirectories;
    }

    /**
     * @param int $totalDirectories
     */
    public function setTotalDirectories($totalDirectories)
    {
        $this->totalDirectories = $totalDirectories;
    }

    /**
     * @return string
     */
    public function getDatabaseFile()
    {
        return $this->databaseFile;
    }

    /**
     * @param string $databaseFile
     */
    public function setDatabaseFile($databaseFile)
    {
        $this->databaseFile = str_replace(ABSPATH, null, $databaseFile);
    }

    /**
     * @return string
     */
    public function getDirWpContent()
    {
        return $this->dirWpContent;
    }

    /**
     * @param string $dirWpContent
     */
    public function setDirWpContent($dirWpContent)
    {
        $this->dirWpContent = str_replace(ABSPATH, null, $dirWpContent);
    }

    /**
     * @return string
     */
    public function getDirUploads()
    {
        return $this->dirUploads;
    }

    /**
     * @param string $dirUploads
     */
    public function setDirUploads($dirUploads)
    {
        $this->dirUploads = str_replace(ABSPATH, null, $dirUploads);
    }

    /**
     * @return string
     */
    public function getDirPlugins()
    {
        return $this->dirPlugins;
    }

    /**
     * @param string $dirPlugins
     */
    public function setDirPlugins($dirPlugins)
    {
        $this->dirPlugins = str_replace(ABSPATH, null, $dirPlugins);
    }

    /**
     * @return string
     */
    public function getDirMuPlugins()
    {
        return $this->dirMuPlugins;
    }

    /**
     * @param string $dirMuPlugins
     */
    public function setDirMuPlugins($dirMuPlugins)
    {
        $this->dirMuPlugins = str_replace(ABSPATH, null, $dirMuPlugins);
    }

    /**
     * @return string
     */
    public function getDirThemes()
    {
        return $this->dirThemes;
    }

    /**
     * @param string $dirThemes
     */
    public function setDirThemes($dirThemes)
    {
        $this->dirThemes = str_replace(ABSPATH, null, $dirThemes);
    }

    /**
     * @return string
     */
    public function getSiteUrl()
    {
        return $this->siteUrl;
    }

    /**
     * @param string $siteUrl
     */
    public function setSiteUrl($siteUrl)
    {
        $this->siteUrl = $siteUrl;
    }

    /**
     * @return string
     */
    public function getAbspath()
    {
        return $this->abspath;
    }

    /**
     * @param string $abspath
     */
    public function setAbspath($abspath)
    {
        $this->abspath = $abspath;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * @return bool
     */
    public function getSingleOrMulti()
    {
        return $this->singleOrMulti;
    }

    /**
     * @param bool $singleOrMulti
     */
    public function setSingleOrMulti($singleOrMulti)
    {
        $this->singleOrMulti = $singleOrMulti;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string $note
     */
    public function setNote($note)
    {
        $this->note = $note;
    }

    /**
     * @return bool
     */
    public function getIsAutomatedBackup()
    {
        return $this->isAutomatedBackup;
    }
    /**
     * @param bool $isAutomatedBackup
     */
    public function setIsAutomatedBackup($isAutomatedBackup)
    {
        $this->isAutomatedBackup = $isAutomatedBackup;
    }
    /**
     * @return bool
     */
    public function getIsLegacyConverted()
    {
        return (bool)$this->isLegacyConverted;
    }
    /**
     * @param bool $isLegacyConverted
     */
    public function setIsLegacyConverted($isLegacyConverted)
    {
        $this->isLegacyConverted = (bool)$isLegacyConverted;
    }
}
