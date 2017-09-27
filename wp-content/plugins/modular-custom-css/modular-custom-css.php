<?php
/**
 * Plugin Name: Modular Custom CSS
 * Plugin URI: http://celloexpressions.com/plugins/modular-custom-css
 * Description: Manage and live-preview theme-specific and plugin-specific Custom CSS in the Customizer.
 * Version: 1.0
 * Author: Nick Halsey
 * Author URI: http://celloexpressions.com/
 * Tags: Custom CSS, Customizer, Custom Design
 * License: GPL

=====================================================================================
Copyright (C) 2014 Nick Halsey

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WordPress; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
=====================================================================================
*/

/**
 * Register the custom CSS section, settings, and controls.
 *
 * @since Modular Custom CSS 1.0
 */
function cxnh_custom_css_customize_register( $wp_customize ) {
	// It really doesn't matter if another plugin or the theme adds the same section; they will merge.
	$wp_customize->add_section( 'custom_css', array(
		'title'    => 'Custom CSS',
		'priority' => 150, // After all core sections.
	) );

	// 'theme_mod's are stored with the theme, so different themes can have unique custom css rules with basically no extra effort.
	$wp_customize->add_setting( 'custom_theme_css' , array(
		'type'      => 'theme_mod',
		'transport' => 'postMessage',
	) );

	// 'option's are used regardless of the current theme, so they apply well to plugin-specific styles.
	$wp_customize->add_setting( 'custom_plugin_css' , array(
		'type'      => 'option',
		'transport' => 'postMessage',
	) );

	// Uses the `textarea` type added in WordPress 4.0.
	$wp_customize->add_control( 'custom_theme_css', array(
		'label'       => 'Custom Theme CSS',
		'description' => 'Theme-specific; safely sticks with each theme when switching themes.',
		'type'        => 'textarea',
		'section'     => 'custom_css',
	) );
	$wp_customize->add_control( 'custom_plugin_css', array(
		'label'       => 'Custom Plugin CSS',
		'description' => 'Theme-agnostic; persists across theme changes.',
		'type'        => 'textarea',
		'section'     => 'custom_css',
	) );
}
add_action('customize_register','cxnh_custom_css_customize_register');

/**
 * Bind JS handlers to make the Customizer preview load changes asynchronously.
 *
 * @since Modular Custom CSS 1.0
 */
function cxnh_custom_css_preview_js() {
	wp_enqueue_script( 'custom_css_preview', plugin_dir_url( __FILE__ ) . 'modular-custom-css-preview.js', array( 'customize-preview' ), '20140804', true );
}
add_action( 'customize_preview_init', 'cxnh_custom_css_preview_js' );

/**
 * Render the custom CSS.
 *
 * @since Modular Custom CSS 1.0
 */
function cxnh_custom_css_output() {
	echo '<style type="text/css" id="custom-theme-css">' . get_theme_mod( 'custom_theme_css', '' ) . '</style>';
	echo '<style type="text/css" id="custom-plugin-css">' . get_option( 'custom_plugin_css', '' ) . '</style>';
}
add_action( 'wp_head', 'cxnh_custom_css_output');