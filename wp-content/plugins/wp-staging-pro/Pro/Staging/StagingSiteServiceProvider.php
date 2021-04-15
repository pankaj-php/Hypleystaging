<?php

namespace WPStaging\Pro\Staging;

use WPStaging\Framework\DI\ServiceProvider;

class StagingSiteServiceProvider extends ServiceProvider
{
    public function registerClasses()
    {
        //no-op
    }

    public function addHooks()
    {
        add_filter('site_transient_update_plugins', [$this->container->make(PluginUpdates::class), 'disablePluginUpdateChecksOnStagingSite'], 10, 1);
        $stagingSettingTab = $this->container->make(SettingsTabs::class);
        add_filter('wpstg_main_settings_tabs', [$stagingSettingTab, 'addMailSettingsTabOnStagingSite'], 10, 1);
        add_action("wp_ajax_wpstg_update_staging_mail_settings", [$stagingSettingTab, "ajaxUpdateStagingMailSettings"]);
        add_action("wp_ajax_wpstg_hide_disabled_mail_notice", [$this->container->make(Notices::class), "ajaxHideDisabledMailNotice"]);
    }
}
