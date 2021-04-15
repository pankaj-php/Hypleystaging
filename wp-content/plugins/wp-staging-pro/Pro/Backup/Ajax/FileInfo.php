<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Backup\Ajax;

use WPStaging\Framework\Adapter\Directory;
use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Framework\TemplateEngine\TemplateEngine;
use WPStaging\Pro\Backup\Service\Compressor;
use WPStaging\Pro\Backup\Service\Dto\ExportFileHeadersDto;

class FileInfo extends AbstractTemplateComponent
{
    /** @var Compressor */
    private $exporter;
    /**
     * @var Directory
     */
    private $directory;

    public function __construct(Compressor $exporter, Directory $directory, TemplateEngine $templateEngine)
    {
        parent::__construct($templateEngine);
        $this->exporter  = $exporter;
        $this->directory = $directory;
    }

    public function render()
    {
        if (!$this->canRenderAjax()) {
            return;
        }

        // Replace & add ABSPATH back is in a way a security measure to not fiddle with other directories
        $path = $this->directory->getPluginUploadsDirectory();
        $file = $path . str_replace($path, null, $_POST['filePath']);
        try {
            $info = (new ExportFileHeadersDto())->hydrateByFilePath($file);
        } catch (\Exception $e) {
            wp_send_json([
                'error'   => true,
                'message' => $e->getMessage(),
            ]);
        }

        $result = $this->templateEngine->render(
            'Backend/views/backup/site/info.php',
            [
                'info' => $info,
            ]
        );

        wp_send_json($result);
    }
}
