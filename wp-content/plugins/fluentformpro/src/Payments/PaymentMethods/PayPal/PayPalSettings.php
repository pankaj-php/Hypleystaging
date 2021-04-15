<?php

namespace FluentFormPro\Payments\PaymentMethods\PayPal;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class PayPalSettings
{
    public static function getSettings()
    {
        $defaults = [
            'paypal_email' => '',
            'payment_mode' => 'test',
            'is_active' => 'no',
            'disable_ipn_verification' => 'yes'
        ];

        $settings = get_option('fluentform_payment_settings_paypal', []);

        $settings = wp_parse_args($settings, $defaults);

        return $settings;
    }

    public static function getPayPalEmail()
    {
        $settings = self::getSettings();
        return $settings['paypal_email'];
    }

    public static function isLive()
    {
        $settings = self::getSettings();
        return $settings['payment_mode'] == 'live';
    }

    public static function getPaypalRedirect($ssl_check = false, $ipn = false)
    {
        $protocol = 'http://';
        if (is_ssl() || !$ssl_check) {
            $protocol = 'https://';
        }

        $isLive = self::isLive();
        // Check the current payment mode
        if ($isLive) {
            // Test mode
            if ($ipn) {
                $paypal_uri = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
            } else {
                $paypal_uri = $protocol . 'www.sandbox.paypal.com/cgi-bin/webscr';
            }
        } else {
            // Live mode
            if ($ipn) {
                $paypal_uri = 'https://ipnpb.paypal.com/cgi-bin/webscr';
            } else {
                $paypal_uri = $protocol . 'www.paypal.com/cgi-bin/webscr';
            }
        }
        return apply_filters('fluentform_paypal_url', $paypal_uri, $ssl_check, $ipn, $isLive);
    }

}