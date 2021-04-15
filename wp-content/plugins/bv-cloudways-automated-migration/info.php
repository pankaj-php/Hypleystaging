<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('CWSInfo')) :
	class CWSInfo {
		public $settings;
		public $config;
		public $plugname = 'cloudways';
		public $brandname = 'Cloudways Migrate';
		public $badgeinfo = 'cwsbadge';
		public $ip_header_option = 'cwsipheader';
		public $brand_option = 'cwsbrand';
		public $version = '4.54';
		public $webpage = 'https://www.cloudways.com';
		public $appurl = 'https://migrate.blogvault.net';
		public $slug = 'bv-cloudways-automated-migration/cloudways.php';
		public $plug_redirect = 'cwsredirect';
		public $logo = '/aseets/img/clouwdways-logo.png';
		public $brand_icon = '/assets/img/favicon.ico';
		public $services_option_name = 'BVSERVICESOPTIONNAME';

		public function __construct($settings) {
			$this->settings = $settings;
			$this->config = $this->settings->getOption($this->services_option_name);
		}

		public function canSetCWBranding() {
			if (CWSWPSiteInfo::isCWServer()) {

				$bot_protect_accounts = CWSAccount::accountsByType($this->settings, 'botprotect');
				if (sizeof($bot_protect_accounts) >= 1)
					return true;

				$bot_protect_accounts = CWSAccount::accountsByPattern($this->settings, 'email', '/@cw_user\.com$/');
				if (sizeof($bot_protect_accounts) >= 1)
					return true;
			}

			return false;
		}

		public function getBrandInfo() {
			return $this->settings->getOption($this->brand_option);
		}

		public function getBrandName() {
			$brand = $this->getBrandInfo();
			if ($brand && array_key_exists('menuname', $brand)) {
				return $brand['menuname'];
			}
		  
			return $this->brandname;
		}

		public function getBrandIcon() {
			$brand = $this->getBrandInfo();
			if ($brand && array_key_exists('brand_icon', $brand)) {
				return $brand['brand_icon'];
			}
			return $this->brand_icon;
		}

		public function getWatchTime() {
			$time = $this->settings->getOption('bvwatchtime');
			return ($time ? $time : 0);
		}

		public function appUrl() {
			if (defined('BV_APP_URL')) {
				return BV_APP_URL;
			} else {
				$brand = $this->getBrandInfo();
				if ($brand && array_key_exists('appurl', $brand)) {
					return $brand['appurl'];
				}
				return $this->appurl;
			}
		}

		public function isActivePlugin() {
			$expiry_time = time() - (3 * 24 * 3600);
			return ($this->getWatchTime() > $expiry_time);
		}

		public function isProtectModuleEnabled() {
			return ($this->settings->getOption('bvptplug') === $this->plugname) &&
				$this->isActivePlugin();
		}

		public function isDynSyncModuleEnabled() {
			return ($this->settings->getOption('bvdynplug') === $this->plugname) &&
				$this->isActivePlugin();
		}

		public function isServiceActive($service) {
			$bvconfig = $this->config;
			if ($bvconfig && array_key_exists('services', $bvconfig)) {
				return in_array($service, $bvconfig['services']) && $this->isActivePlugin();
			}
			return false;
		}

		public function isActivateRedirectSet() {
			return ($this->settings->getOption($this->plug_redirect) === 'yes') ? true : false;
		}

		public function isMalcare() {
			return $this->getBrandName() === 'MalCare - Pro';
		}

		public function isBlogvault() {
			return $this->getBrandName() === 'BlogVault';
		}

		public function info() {
			return array(
				"bvversion" => $this->version,
				"sha1" => "true",
				"plugname" => $this->plugname
			);
		}
	}
endif;