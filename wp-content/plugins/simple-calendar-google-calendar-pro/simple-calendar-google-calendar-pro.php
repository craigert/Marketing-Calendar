<?php
/**
 * Plugin Name: Simple Calendar - Google Calendar Pro Add-on
 * Plugin URI:  https://simplecalendar.io
 * Description: Google Calendar Pro add-on for Simple Calendar.
 * Author:      Moonstone Media
 * Author URI:  https://simplecalendar.io
 * Version:     1.0.4
 * Text Domain: simple-calendar-google-calendar-pro
 * Domain Path: i18n/
 *
 * @copyright   2015-2016 Moonstone Media/Phil Derksen. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} elseif ( version_compare( PHP_VERSION, '5.3.0' ) !== - 1 ) {
	$const = array(
		'SIMPLE_CALENDAR_GOOGLE_PRO_VERSION'   => '1.0.4',
		'SIMPLE_CALENDAR_GOOGLE_PRO_MAIN_FILE' => __FILE__,
	);
	foreach ( $const as $k => $v ) {
		if ( ! defined( $k ) ) {
			define( $k, $v );
		}
	}
	include_once 'includes/add-on-google-pro.php';
}
