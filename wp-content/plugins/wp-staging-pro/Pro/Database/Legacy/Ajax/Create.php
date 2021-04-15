<?php

// TODO PHP7.x; declare(strict_type=1);
// TODO PHP7.x; type hints & return types

namespace WPStaging\Pro\Database\Legacy\Ajax;

use WPStaging\Framework\Component\AbstractTemplateComponent;
use WPStaging\Pro\Database\Legacy\Job\JobCreateBackup;
use WPStaging\Core\WPStaging;

class Create extends AbstractTemplateComponent
{
    public function render()
    {
        if (! $this->canRenderAjax()) {
            return;
        }

        $job = $this->getJob();
        $response = $job->execute();

        // Trigger __destruct()
        unset($job);

        wp_send_json($response);
    }

    /**
     * @return JobCreateBackup
     */
    private function getJob()
    {
        return WPStaging::getInstance()->get(JobCreateBackup::class);
    }
}
