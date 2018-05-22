<?php

/**
 * Plugin Name:       Gravity Forms: GDPR Add-On
 * Plugin URI:        https://codelight.eu/wordpress-gdpr-framework/
 * Description:       The easiest way to make your Gravity Forms GDPR-compliant. Fully documented, extendable and developer-friendly.
 * Version:           1.0.1
 * Author:            Codelight
 * Author URI:        https://codelight.eu/
 * Text Domain:       gdpr
 * Domain Path:       /languages
 */

if (!defined('WPINC')) {
    die;
}

add_action('plugins_loaded', function () {

    if (!class_exists('\GFForms')) {
        add_action('admin_notices', function () {
            $class   = 'notice notice-error';
            $message = __('It seems your Gravity Forms plugin is not activated. Gravity Forms GDPR Add-On will not function.', 'gdpr-admin');

            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
        });
        return;
    }

    if (!function_exists('gdpr')) {
        add_action('admin_notices', function () {
            $class   = 'notice notice-error';
            $message =
                sprintf(
                    __("Gravity Forms GDPR Add-On currently requires %sThe GDPR Framework%s to function. Get it from the %sofficial WordPress plugin repository%s - it's free and fully documented!", 'gdpr-admin'),
                    '<a href="https://wordpress.org/plugins/gdpr-framework/" target="_blank">',
                    '</a>',
                    '<a href="https://wordpress.org/plugins/gdpr-framework/" target="_blank">',
                    '</a>'
                );

            printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);
        });
        return;
    }

    require_once('src/GravityForms.php');
    require_once('src/GravityFormsGDPRAddOn.php');

    gdpr()->make(\Codelight\GDPR\Modules\GravityForms\GravityForms::class);
}, 5);