<?php

namespace WPStaging\Pro\Staging;

use WPStaging\Backend\Pro\Notices\DisabledMailNotice;

class Notices
{
    /**
     * @var DisabledMailNotice
     */
    protected $disabledMailNotice;

    /**
     * @param DisabledMailNotice $disabledMailNotice
     */
    public function __construct(DisabledMailNotice $disabledMailNotice)
    {
        $this->disabledMailNotice = $disabledMailNotice;
    }

    /**
     * Ajax Hide Disabled Mail Notice shown on staging site
     *
     * @action wp_ajax_wpstg_hide_disabled_mail_notice
     */
    public function ajaxHideDisabledMailNotice()
    {
        if ($this->disabledMailNotice->disable() !== false) {
            wp_send_json(true);
        }

        wp_send_json(null);
    }
}
