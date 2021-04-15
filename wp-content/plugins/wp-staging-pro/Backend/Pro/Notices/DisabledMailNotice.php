<?php

namespace WPStaging\Backend\Pro\Notices;

use WPStaging\Backend\Notices\BooleanNotice;
use WPStaging\Framework\Staging\FirstRun;
use WPStaging\Framework\SiteInfo;

/**
 * Class DisabledCacheNotice
 *
 * This class is used to show notice if mails sending is disabled on staging site
 *
 * @package WPStaging\Backend\Pro\Notices;
 */
class DisabledMailNotice extends BooleanNotice
{
    /**
     * The option name to store the visibility of disabled mail notice
     */
    const OPTION_NAME = 'wpstg_disabled_mail_notice';

    public function getOptionName()
    {
        return self::OPTION_NAME;
    }

    /**
     * overriding the original function to include additional checks
     * Make sure only show notice if enabled,
     * if it is on staging site and
     * Mails are disabled on staging site
     *
     * @return boolean
     */
    public function isEnabled()
    {
        return parent::isEnabled() && (new SiteInfo())->isStaging() &&
            ((bool)get_option(FirstRun::MAILS_DISABLED_KEY) === true);
    }
}
