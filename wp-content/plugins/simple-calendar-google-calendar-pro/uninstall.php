<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package SimpleCalendar
 */

// Exit if not uninstalling from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// For security reasons, remove auth code and OAuth data when uninstalling.
$settings = get_option( 'simple-calendar_settings_feeds' );
unset( $settings['google-pro'] );
update_option( 'simple-calendar_settings_feeds', $settings );
delete_option( 'simple-calendar_google-pro-token' );
