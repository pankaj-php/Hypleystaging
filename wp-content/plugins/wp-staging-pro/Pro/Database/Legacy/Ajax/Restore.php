<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Pro\Database\Legacy\Job\JobRestoreBackup;
use WPStaging\Core\WPStaging;

class Restore extends AbstractTemplateComponent
{
    public function render()
    {
        if (! $this->canRenderAjax()) {
            return;
        }

        $job = WPStaging::getInstance()->get(JobRestoreBackup::class);

        $response = $job->execute();

        // Trigger JobRestoreBackup::__destruct()
        unset($job);

        wp_send_json($response);
    }
}
