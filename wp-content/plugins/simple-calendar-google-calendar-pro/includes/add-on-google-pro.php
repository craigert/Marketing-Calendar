<?php
/**
 * Simple Calendar - Google Calendar Pro add-on
 *
 * @package     SimpleCalendar/Extensions
 * @subpackage  GooglePro
 */
namespace SimpleCalendar;

use SimpleCalendar\Feeds\Google_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A Blog Feed add on for Simple Calendar.
 */
class Add_On_Google_Pro {

	/**
	 * Plugin add-on name.
	 *
	 * @access public
	 * @var string
	 */
	public $name = 'Google Calendar Pro';

	/**
	 * Plugin add-on internal slug.
	 *
	 * @access public
	 * @var string
	 */
	public $slug = 'google-pro';

	/**
	 * Plugin add-on internal unique id.
	 *
	 * @access public
	 * @var string
	 */
	public $id = '';

	/**
	 * Plugin add-on version.
	 *
	 * @access public
	 * @var string
	 */
	public $version = SIMPLE_CALENDAR_GOOGLE_PRO_VERSION;

	/**
	 * Load plugin.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->id = defined( 'SIMPLE_CALENDAR_GOOGLE_PRO_ID' ) ? SIMPLE_CALENDAR_GOOGLE_PRO_ID : '610';

		register_activation_hook( SIMPLE_CALENDAR_GOOGLE_PRO_MAIN_FILE, array( $this, 'activate' ) );

		add_action( 'init', function () {
			load_plugin_textdomain( 'simple-calendar-google-calendar-pro', false, 'simple-calendar-google-calendar-pro' . '/languages' );
		} );

		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	public function license_notification() {

		if ( simcal_is_admin_screen() !== false ) {

			$license = $this->check_license();

			if ( $license === 'expired' ) {
				// TODO: Add link to admin message to purchase new license?
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php printf( __( 'Your Simple Calendar %1$s add-on license key has expired.', 'simple-calendar-google-calendar-pro' ), $this->name ); ?></p>
				</div>
				<?php
			} elseif ( $license !== 'valid' && ! empty( $license ) ) {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php printf( __( 'Your Simple Calendar %1$s add-on license key is invalid.', 'simple-calendar-google-calendar-pro' ), $this->name ); ?></p>
				</div>
				<?php
			}
		}
	}

	public function pre_wp_update_check( $array ) {

		$addon        = 'simcal_' . $this->id;
		$licenses     = get_option( 'simple-calendar_licenses_status', array() );
		$license_data = $this->check_license();

		if ( $license_data !== 'valid' ) {
			$licenses[ $addon ] = $license_data;
			update_option( 'simple-calendar_licenses_status', $licenses );
		} else {
			$licenses[ $addon ] = $license_data;
			update_option( 'simple-calendar_licenses_status', $licenses );
		}

		// This is a parameter from the hook itself so we want to make sure we return it
		return $array;
	}

	public function check_license() {

		$addon        = 'simcal_' . $this->id;
		$keys         = get_option( 'simple-calendar_settings_licenses', array() );
		$key          = isset( $keys['keys'][ $addon ] ) ? $keys['keys'][ $addon ] : '';
		$status       = get_option( 'simple-calendar_licenses_status' );
		$license_data = '';

		if ( ! empty( $status[ $addon ] ) ) {
			if ( ! empty( $key ) ) {

				$api_params = array(
					'edd_action' => 'check_license',
					'license'    => $key,
					'item_id'    => intval( $this->id ),
					'url'        => home_url(),
				);

				// Call the API.
				$response = wp_remote_post( defined( 'SIMPLE_CALENDAR_STORE_URL' ) ? SIMPLE_CALENDAR_STORE_URL : simcal_get_url( 'home' ), array(
					'timeout'   => 15,
					'sslverify' => false,
					'body'      => $api_params,
				) );

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			}

			if ( ! empty( $license_data->license ) ) {
				$status[ $addon ] = $license_data->license;
				update_option( 'simple-calendar_licenses_status', $status );

				return $license_data->license;
			}
		}

		return false;
	}

	/**
	 * Init.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {

		if ( class_exists( 'SimpleCalendar\Plugin' ) ) {

			include_once 'feeds/google-pro.php';

			// Add new feed type.
			add_filter( 'simcal_get_feed_types', function ( $feed_types ) {
				return array_merge( $feed_types, array(
					'google-pro',
				) );
			}, 10, 1 );
			add_action( 'simcal_load_objects ', function () {
				new Google_Pro();
			} );

			// License management and updates.
			if ( is_admin() ) {
				$this->admin_init();
			}

		} else {

			$name = $this->name;

			add_action( 'admin_notices', function () use ( $name ) {
				echo '<div class="error"><p>' . sprintf( __( 'The Simple Calendar %s add-on requires the <a href="%s" target="_blank">Simple Calendar core plugin</a> to be installed and activated.', 'simple-calendar-google-calendar-pro' ), $name, 'https://wordpress.org/plugins/google-calendar-events/' ) . '</p></div>';
			} );

		}

	}

	/**
	 * Hook in tabs.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public function admin_init() {

		$id   = $this->id;
		$name = $this->name;

		// Add license key field.
		add_filter( 'simcal_installed_addons', function ( $addons ) use ( $id, $name ) {
			if ( ! isset( $addons['google-pro'] ) ) {
				$addons = array_merge_recursive( (array) $addons, array( strval( 'simcal_' . $id ) => $name ) );
			}

			return $addons;
		}, 20, 1 );

		// Enable license settings page.
		add_filter( 'simcal_get_admin_pages', function ( $pages ) {
			if ( isset( $pages['settings'] ) && ! isset( $pages['settings']['licenses'] ) ) {
				$pages = array_merge_recursive( (array) $pages, array( 'settings' => array( 'licenses' ) ), $pages );
			}

			return $pages;
		}, 20, 1 );

		// TODO: Update filter name and function name for these
		add_filter( 'simcal_addon_status_simcal_' . $this->id, array( $this, 'check_license' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pre_wp_update_check' ), 0 );

		add_action( 'admin_notices', array( $this, 'license_notification' ) );

		// Init plugin updater.
		add_action( 'admin_init', array( $this, 'updater' ), 0 );

	}

	/**
	 * Plugin updater.
	 *
	 * @since 1.0.0
	 * @internal
	 *
	 * @return void
	 */
	public function updater() {

		$license    = simcal_get_license_key( strval( 'simcal_' . $this->id ) );
		$activation = simcal_get_license_status( strval( 'simcal_' . $this->id ) );

		if ( ! empty( $license ) && 'valid' == $activation ) {

			simcal_addon_updater( defined( 'SIMPLE_CALENDAR_STORE_URL' ) ? SIMPLE_CALENDAR_STORE_URL : simcal_get_url( 'home' ), SIMPLE_CALENDAR_GOOGLE_PRO_MAIN_FILE, array(
					'version' => $this->version,
					'license' => $license,
					'item_id' => intval( $this->id ),
					'author'  => 'Moonstone Media',
				) );

		}
	}

	/**
	 * Upon plugin activation registration hook.
	 *
	 * @since  1.0.0
	 *
	 * @return void
	 */
	public static function activate() {
		if ( ! get_term_by( 'slug', 'google-pro', 'calendar_feed' ) ) {
			wp_insert_term( 'google-pro', 'calendar_feed' );
		}
	}

}

new Add_On_Google_Pro();
