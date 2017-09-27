<?php
/**
 * Google Calendar Pro - Admin
 *
 * @package    SimpleCalendar/Feeds
 * @subpackage GooglePro
 */
namespace SimpleCalendar\Feeds\Admin;

use SimpleCalendar\Admin\Metaboxes\Settings;
use SimpleCalendar\Feeds\Google_Pro;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Calendar Pro feed admin.
 */
class Google_Pro_Admin {

	/**
	 * Google Calendar Pro.
	 *
	 * @access private
	 * @var Google_Pro
	 */
	private $feed = null;

	/**
	 * Hook in tabs.
	 *
	 * @since 1.0.0
	 *
	 * @param Google_Pro $feed
	 */
	public function __construct( Google_Pro $feed ) {

		$this->feed = $feed;

		if ( 'calendar' == simcal_is_admin_screen() ) {
			add_filter( 'simcal_settings_meta_tabs_li', array( $this, 'add_settings_meta_tab_li' ), 10, 1 );
			add_action( 'simcal_settings_meta_panels', array( $this, 'add_settings_meta_panel' ), 10, 1 );
		}

		add_action( 'simcal_process_settings_meta', array( $this, 'process_meta' ), 10, 1 );
	}

	/**
	 * Settings page fields.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function settings_fields() {
		return array(
			'name' => $this->feed->name,
			'description' => __( 'Configure a Google OAuth client to read event details from both public and private Google Calendars.', 'simple-calendar-google-calendar-pro' ) .
			                 '<br/>' .
			                 '<p class="description" style="font-size: 14px">' .
			                 sprintf( __( '<a href="%s" target="_blank">Step-by-step instructions</a>', 'simple-calendar-google-calendar-pro' ),
				                 simcal_ga_campaign_url( simcal_get_url( 'docs' ) . '/google-calendar-pro-configure-google-oauth/', 'gcal-pro', 'settings-link' )
			                 ) .
			                 '<br/>' .
			                 sprintf( __( '<a href="%s" target="_blank">Google Developers Console</a> ', 'simple-calendar-google-calendar-pro' ),
				                 simcal_get_url( 'gdev-console' )
			                 ) .
			                 '</p>',
			'fields' => array(
				'client_id' => array(
					'type'       => 'standard',
					'subtype'    => 'text',
					'class'      => array( 'simcal-wide-text regular-text', 'ltr' ),
					'title'      => __( 'Google OAuth Client ID', 'simple-calendar-google-calendar-pro' ),
					'validation' => array( $this, 'check_client_id' ),
				),
				'client_secret' => array(
					'type'       => 'standard',
					'subtype'    => 'text',
					'class'      => array( 'simcal-wide-text regular-text', 'ltr' ),
					'title'      => __( 'Google OAuth Client Secret', 'simple-calendar-google-calendar-pro' ),
					'validation' => array( $this, 'check_client_secret' ),
				),
				'client_auth' => array(
					'type'       => 'standard',
					'subtype'    => 'text',
					'class'      => array( 'simcal-wide-text regular-text', 'ltr' ),
					'title'      => __( 'Authentication Code', 'simple-calendar-google-calendar-pro' ),
					//'tooltip'    => __( 'Enter your auth code here after you have pasted your client id and client secret and saved settings. If you want to authenticate again, delete this code and save settings first.', 'simple-calendar-google-calendar-pro' ),
					'validation' => array( $this, 'authenticate' ),
				),
			),
		);
	}

	/**
	 * Client id field callback.
	 *
	 * @since  1.0.0
	 * @internal
	 *
	 * @param  string $value
	 *
	 * @return bool|string
	 */
	public function check_client_id( $value ) {
		// TODO: There is probably a better way to do this
		return '<p class="description">' . __( 'Step 1: Enter your Google Client ID here.', 'simple-calendar-google-calendar-pro' ) . '</p>';
	}

	/**
	 * Client secret field callback.
	 *
	 * @since  1.0.0
	 * @internal
	 *
	 * @param  string $value
	 *
	 * @return bool|string
	 */
	public function check_client_secret( $value ) {
		// TODO: There is probably a better way to do this
		return '<p class="description">' . __( 'Step 2: Enter your Google Client secret here, then click <strong>"Save Changes"</strong> below.', 'simple-calendar-google-calendar-pro' ) . '</p>';
	}

	/**
	 * Client authentication code field callback.
	 *
	 * @since  1.0.0
	 * @internal
	 *
	 * @param  string $code
	 *
	 * @return bool|string
	 */
	public function authenticate( $code ) {

		$settings = get_option( 'simple-calendar_settings_feeds' );

		$client_id     = ( isset( $settings['google-pro']['client_id'] ) && ! empty( $settings['google-pro']['client_id'] ) ? true : false );
		$client_secret = ( isset( $settings['google-pro']['client_secret'] ) && ! empty( $settings['google-pro']['client_secret'] ) ? true : false );

		// Start paragraph tag and description text.
		$message = '<p class="description">' .
		           __( 'Step 3: After your Client ID and secret are saved, click the "Authenticate with Google" button that appears, then enter your issued Google Authentication Code here.', 'simple-calendar-google-calendar-pro' );

		// Show authenticate button only when client ID & secret values are saved.
		if ( $client_id && $client_secret ) {
			$url = "'" . $this->feed->get_auth_url() . "','activate','width=700,height=500,toolbar=0,menubar=0,location=0,status=1,scrollbars=1,resizable=1,left=0,top=0'";

			$message .= '<br/><br/>' .
			            '<a target="_blank" href="javascript:void(0);" onclick="window.open(' . $url . ');" class="button button-secondary" style="font-style: normal;">' .
			            __( 'Authenticate with Google', 'simple-calendar-google-calendar-pro' ) .
			            '</a>' .
			            '<br/><br/>' .
			            __( 'Step 4: With your Authentication Code filled in, click <strong>"Save Changes"</strong> below once again. That\'s it!', 'simple-calendar-google-calendar-pro' );
		}

		// TODO Fail and show error message gracefully.

		// TODO Old text links
		if ( empty( $code ) ) {
			//$message .= __( 'Click here to authenticate with your Google account and paste the authentication code in this field.', 'simple-calendar-google-calendar-pro' );
			// Wipes out the refresh token.
			delete_option( 'simple-calendar_google-pro-token' );
		} elseif ( ! empty( $this->google_client_id ) && ! empty( $this->google_client_secret ) && ! $this->feed->connect() ) {
			//$message .= __( 'An error occurred. Please check your client id and client secret, then click to authenticate again and enter a new code.', 'simple-calendar-google-calendar-pro' );
		}

		// End paragraph tag.
		$message .= '</p>';

		//return ! empty( $message ) ? $html_start . $message . $html_end : true;

		return $message;
	}

	/**
	 * Add a tab to the settings meta box.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $tabs
	 *
	 * @return array
	 */
	public function add_settings_meta_tab_li( $tabs ) {
		return array_merge( $tabs, array(
			'google-pro' => array(
				'label'   => $this->feed->name,
				'target'  => 'google-pro-settings-panel',
				'class'   => array( 'simcal-feed-type', 'simcal-feed-type-google-pro' ),
				'icon'    => 'simcal-icon-google',
			),
		) );
	}

	/**
	 * Add a panel to the settings meta box.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id
	 */
	public function add_settings_meta_panel( $post_id ) {
		$inputs = array(
			$this->feed->type => array(
				'_google_pro_calendar_id' => array(
					'type'       => 'select',
					'enhanced'   => 'enhanced',
					'name'       => '_google_pro_calendar_id',
					'id'         => '_google_pro_calendar_id',
					'title'      => __( 'Calendar', 'simple-calendar-google-calendar-pro' ),
					'tooltip'    => __( 'Choose the Google Calendar you want to get events from.', 'simple-calendar-google-calendar-pro' ),
					'options'    => array( '' => '' ) + $this->feed->get_calendars(),
					'value'      => base64_decode( get_post_meta( $post_id, '_google_pro_calendar_id', true ) ),
					'escaping'   => array( $this->feed, 'esc_google_calendar_id' ),
				),
				'_google_pro_events_colors' => array(
					'type'       => 'checkbox',
					'name'       => '_google_pro_events_colors',
					'id'         => '_google_pro_events_colors',
					'title'      => __( 'Use Event Colors', 'simple-calendar-google-calendar-pro' ),
					'tooltip'    => __( 'If the calendar type supports event colors, colors from the corresponding event in the Google Calendar will be used accordingly.', 'simple-calendar-google-calendar-pro' ),
				),
				'_google_pro_events_search_query' => array(
					'type'        => 'standard',
					'subtype'     => 'text',
					'name'        => '_google_pro_events_search_query',
					'id'          => '_google_pro_events_search_query',
					'title'       => __( 'Search Query', 'simple-calendar-google-calendar-pro' ),
					'tooltip'     => __( 'Type in keywords if you only want display events that match these terms. You can use basic boolean search operators too.', 'simple-calendar-google-calendar-pro' ),
					'placeholder' => __( 'Filter events to display by search terms...', 'simple-calendar-google-calendar-pro' )
				),
				'_google_pro_events_recurring' => array(
					'type'    => 'select',
					'name'    => '_google_pro_events_recurring',
					'id'      => '_google_pro_events_recurring',
					'title'   => __( 'Recurring Events', 'simple-calendar-google-calendar-pro' ),
					'tooltip' => __( 'Events that are programmed to repeat themselves periodically.', 'simple-calendar-google-calendar-pro' ),
					'options' => array(
						'show' => __( 'Show all', 'simple-calendar-google-calendar-pro' ),
						'first-only' => __( 'Only show first occurrence', 'simple-calendar-google-calendar-pro' ),
					),
				),
				'_google_pro_events_max_results' => array(
					'type'        => 'standard',
					'subtype'     => 'number',
					'name'        => '_google_pro_events_max_results',
					'id'          => '_google_pro_events_max_results',
					'title'       => __( 'Maximum Events', 'simple-calendar-google-calendar-pro' ),
					'tooltip'     => __( 'Google Calendar only allows to query for a maximum amount of 2500 events from a calendar each time.', 'simple-calendar-google-calendar-pro' ),
					'class'       => array(
						'simcal-field-small',
					),
					'default'     => '2500',
					'attributes' => array(
						'min'  => '0',
						'max'  => '2500'
					)
				),
			)
		);

		?>
		<div id="google-pro-settings-panel" class="simcal-panel">
			<table>
				<thead>
				<tr><th colspan="2"><?php _e( 'Google Calendar Pro Settings', 'simple-calendar-google-calendar-pro' ); ?></th></tr>
				</thead>
				<?php Settings::print_panel_fields( $inputs, $post_id ); ?>
			</table>
		</div>
		<?php

	}

	/**
	 * Process meta fields.
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id
	 */
	public function process_meta( $post_id ) {

		$calendar_id = isset( $_POST['_google_pro_calendar_id'] ) ? base64_encode( trim( $_POST['_google_pro_calendar_id'] ) ): '';
		update_post_meta( $post_id, '_google_pro_calendar_id', $calendar_id );

		$event_colors = isset( $_POST['_google_pro_events_colors'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_google_pro_events_colors', $event_colors );

		$search_query = isset( $_POST['_google_pro_events_search_query'] ) ? sanitize_text_field( $_POST['_google_pro_events_search_query'] ) : '';
		update_post_meta( $post_id, '_google_pro_events_search_query', $search_query );

		$recurring = isset( $_POST['_google_pro_events_recurring'] ) ? sanitize_key( $_POST['_google_pro_events_recurring'] ) : 'show';
		update_post_meta( $post_id, '_google_pro_events_recurring', $recurring );

		$max_results = isset( $_POST['_google_pro_events_max_results'] ) ? absint( $_POST['_google_pro_events_max_results'] ) : '2500';
		update_post_meta( $post_id, '_google_pro_events_max_results', $max_results );

	}

}
