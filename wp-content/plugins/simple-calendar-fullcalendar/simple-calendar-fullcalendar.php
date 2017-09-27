<?php
/**
 * Plugin Name: Simple Calendar - FullCalendar Add-on
 * Plugin URI:  https://simplecalendar.io
 * Description: FullCalendar add-on for Simple Calendar.
 * Author:      Moonstone Media
 * Author URI:  https://simplecalendar.io
 * Version:     1.0.1
 * Text Domain: simple-calendar-fullcalendar
 * Domain Path: i18n/
 *
 * @copyright   2015-2016 Moonstone Media/Phil Derksen. All rights reserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} elseif ( version_compare( PHP_VERSION, '5.3.3' ) !== -1 ) {

	$this_plugin_dir = plugin_dir_url( __FILE__ );

	$const = array(
		'SIMPLE_CALENDAR_FULLCALENDAR_VERSION'   => '1.0.1',
		'SIMPLE_CALENDAR_FULLCALENDAR_MAIN_FILE' => __FILE__,
		'SIMPLE_CALENDAR_FULLCALENDAR_ASSETS'    => $this_plugin_dir . 'assets/',
	);
	foreach ( $const as $k => $v ) {
		if ( ! defined( $k ) ) {
			define( $k, $v );
		}
	}
	include_once 'includes/add-on-fullcalendar.php';
}
