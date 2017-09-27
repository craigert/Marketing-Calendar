<?php
/**
 * Google Calendar Pro
 *
 * @package    SimpleCalendar/Feeds
 * @subpackage GooglePro
 */
namespace SimpleCalendar\Feeds;

use Carbon\Carbon;
use SimpleCalendar\Abstracts\Calendar;
use SimpleCalendar\Feeds\Admin\Google_Pro_Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Calendar Pro feed.
 *
 * Connect to Google via OAuth and manage and display Google Calendars.
 */
class Google_Pro extends Google {

	/**
	 * Client auth redirect.
	 *
	 * @access private
	 * @var string
	 */
	protected $google_client_redirect = '';

	/**
	 * Client ID.
	 *
	 * @access private
	 * @var string
	 */
	protected $google_client_id = '';

	/**
	 * Client Secret.
	 *
	 * @access private
	 * @var string
	 */
	protected $google_client_secret = '';

	/**
	 * Client auth code.
	 *
	 * @access private
	 * @var string
	 */
	protected $google_client_auth = '';

	/**
	 * Client refresh token.
	 *
	 * @access private
	 * @var string
	 */
	protected $google_client_token = '';

	/**
	 * Use Google Calendars events colors.
	 *
	 * @access protected
	 * @var bool
	 */
	protected $google_events_colors = false;

	/**
	 * Current Google Calendar color.
	 *
	 * @access protected
	 * @var string
	 */
	protected $google_calendar_color = '';

	private $google_colors = array();

	/**
	 * Set properties.
	 *
	 * @since 1.0.0
	 *
	 * @param string|Calendar $calendar
	 */
	public function __construct( $calendar = '' ) {

		parent::__construct( $calendar, false );

		$this->set_colors();

		$this->type = 'google-pro';
		$this->name = __( 'Google Calendar Pro', 'simple-calendar-google-calendar-pro' );

		$this->set_client();

		if ( $this->connect() === true && $this->post_id > 0 ) {

			$this->google_calendar_id       = $this->esc_google_calendar_id( get_post_meta( $this->post_id, '_google_pro_calendar_id', true ) );
			$this->google_events_recurring  = esc_attr( get_post_meta( $this->post_id, '_google_pro_events_recurring', true ) );
			$this->google_search_query      = esc_attr( get_post_meta( $this->post_id, '_google_pro_events_search_query', true ) );
			$this->google_max_results       = max( absint( get_post_meta( $this->post_id, '_google_pro_events_max_results', true ) ), 1 );

			// Color settings.
			$this->google_calendar_color    = $this->get_calendar_color( $this->google_calendar_id );
			$colors = get_post_meta( $this->post_id, '_google_pro_events_colors', true );
			$this->google_events_colors = 'yes' == $colors ? true : false;

			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {
				$this->events = $this->get_events();
			}
		}

		if ( is_admin() ) {
			include_once 'admin/google-pro-admin.php';
			$admin = new Google_Pro_Admin( $this );
			$this->settings = $admin->settings_fields();
		}


	}

	/**
	 * Manually set the colors array so we don't need a curl request to get them.
	 *
	 * @since 1.0.2
	 *
	 */
	private function set_colors() {
		$this->google_colors = array(
				1  => '#a4bdfc',
				2  => '#7ae7bf',
				3  => '#dbadff',
				4  => '#ff887c',
				5  => '#fbd75b',
				6  => '#ffb878',
				7  => '#46d6db',
				8  => '#e1e1e1',
				9  =>  '#5484ed',
				10 => '#51b749',
				11 => '#dc2127',
		);
	}

	/**
	 * Set client.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @return void
	 */
	private function set_client() {

		$settings = get_option( 'simple-calendar_settings_feeds' );

		if ( ! empty( $settings['google-pro']['client_id'] ) ) {
			$this->google_client_id	= esc_attr( $settings['google-pro']['client_id'] );
		}
		if ( ! empty( $settings['google-pro']['client_secret'] ) ) {
			$this->google_client_secret = esc_attr(  $settings['google-pro']['client_secret'] );
		}
		if ( ! empty( $settings['google-pro']['client_auth'] ) ) {
			$this->google_client_auth = esc_attr( $settings['google-pro']['client_auth'] );
		}

		$this->google_client_redirect = 'urn:ietf:wg:oauth:2.0:oob';

		$this->google_client_scopes = array(
			\Google_Service_Calendar::CALENDAR, // Read, write and manage Google Calendar items.
			\Google_Service_Drive::DRIVE,       // Read and write attachments to Google Calendar events.
		);

		$this->google_client = $this->get_client();
	}

	/**
	 * Connect Google Client OAuth.
	 *
	 * @since  1.0.0
	 *
	 * @return bool Connection successful (true) or failed (false).
	 */
	public function connect() {

		$calendar = get_transient( '_simple-calendar_feed_id_' . strval( $this->post_id ) . '_' . $this->type );

		// If we are not in the admin section and we find that a transient is set for the calendar data then we can just return here
		// because we don't need to be authenticated to run through the cached data. This speeds up the page load quite a bit since we
		// are not authenticating each calendar on the page on every page load now.
		if ( ! empty( $calendar ) && ! is_admin() ) {
			return true;
		}

		if ( empty( $this->google_client_auth ) ) {
			return false;
		}

		$refresh_token = empty( $this->google_client_token ) ? get_option( 'simple-calendar_google-pro-token' ) : $this->google_client_token;

		if ( ! empty( $refresh_token ) ) {

			try {
				if ( $this->google_client->isAccessTokenExpired() ) {
					$this->google_client->refreshToken( $refresh_token );
				}
			} catch ( \Exception $e ) {
				return $e->getMessage();
			}

		} else {

			try {
				$this->google_client->authenticate( $this->google_client_auth );
				$access_token = $this->google_client->getAccessToken();
			}
			catch ( \Exception $e ) {
				// The auth code is invalid or empty or has been already used.
				return $e->getMessage();
			}

			if ( $access_token ) {
				$this->google_client->setAccessToken( $access_token );
				// Set a refresh token to maintain the connection.
				$refresh_token = $this->google_client->getRefreshToken();
				update_option( 'simple-calendar_google-pro-token', $refresh_token );
			} else {
				return false;
			}
		}

		$this->google_client_token = $refresh_token;

		return true;
	}

	/**
	 * Get events feed.
	 *
	 * Normalizes Google data into a standard array object to list events.
	 *
	 * @since  1.0.0
	 *
	 * @return string|array
	 */
	public function get_events() {

		$calendar = get_transient( '_simple-calendar_feed_id_' . strval( $this->post_id ) . '_' . $this->type );

		if ( empty( $calendar ) && ! empty( $this->google_calendar_id ) ) {

			$error = '';

			try {
				$response = $this->make_request( $this->google_calendar_id );
			} catch ( \Exception $e ) {
				$error .= $e->getMessage();
			}

			if ( empty( $error ) && isset( $response['events'] ) && isset( $response['timezone'] ) ) {

				$calendar = array_merge( $response, array( 'events' => array() ) );

				// If no timezone has been set, use calendar feed.
				if ( 'use_calendar' == $this->timezone_setting ) {
					$this->timezone = $calendar['timezone'];
				}

				$source = isset( $response['title'] ) ? sanitize_text_field( $response['title'] ) : '';

				if ( ! empty( $response['events'] ) && is_array( $response['events'] ) ) {
					foreach ( $response['events'] as $event ) {
						if ( $event instanceof \Google_Service_Calendar_Event ) {

							// Event title & description.
							$title = strip_tags( $event->getSummary() );
							$title = sanitize_text_field( iconv( mb_detect_encoding( $title, mb_detect_order(), true ), 'UTF-8', $title ) );
							$description = wp_kses_post( iconv( mb_detect_encoding( $event->getDescription(), mb_detect_order(), true ), 'UTF-8', $event->getDescription() ) );

							$whole_day = false;

							// Event start properties.
							$start_timezone = ! $event->getStart()->timeZone ? $calendar['timezone'] : $event->getStart()->timeZone;
							if ( is_null( $event->getStart()->dateTime ) ) {
								// Whole day event.
								$date = Carbon::parse( $event->getStart()->date );
								$google_start = Carbon::createFromDate( $date->year, $date->month, $date->day, $start_timezone )->startOfDay()->addSeconds(59);
								$google_start_utc = Carbon::createFromDate( $date->year, $date->month, $date->day, 'UTC' )->startOfDay()->addSeconds(59);
								$whole_day = true;
							} else {
								$date = Carbon::parse( $event->getStart()->dateTime );
								$google_start     = Carbon::create( $date->year, $date->month, $date->day, $date->hour, $date->minute, $date->second, $start_timezone );
								$google_start_utc = Carbon::create( $date->year, $date->month, $date->day, $date->hour, $date->minute, $date->second, 'UTC' );
							}
							// Start.
							$start = $google_start->getTimestamp();
							// Start UTC.
							$start_utc = $google_start_utc->getTimestamp();

							$end = $end_utc = $end_timezone = '';
							$span = 0;
							if ( false == $event->getEndTimeUnspecified() ) {

								// Event end properties.
								$end_timezone = ! $event->getEnd()->timeZone ? $calendar['timezone'] : $event->getEnd()->timeZone;
								if ( is_null( $event->getEnd()->dateTime ) ) {
									// Whole day event.
									$date           = Carbon::parse( $event->getEnd()->date );
									$google_end     = Carbon::createFromDate( $date->year, $date->month, $date->day, $end_timezone )->startOfDay()->subSeconds( 59 );
									$google_end_utc = Carbon::createFromDate( $date->year, $date->month, $date->day, 'UTC' )->startOfDay()->subSeconds( 59 );
								} else {
									$date           = Carbon::parse( $event->getEnd()->dateTime );
									$google_end     = Carbon::create( $date->year, $date->month, $date->day, $date->hour, $date->minute, $date->second, $end_timezone );
									$google_end_utc = Carbon::create( $date->year, $date->month, $date->day, $date->hour, $date->minute, $date->second, 'UTC' );
								}
								// End.
								$end = $google_end->getTimestamp();
								// End UTC.
								$end_utc = $google_end_utc->getTimestamp();

								// Count multiple days.
								$span = $google_start->setTimezone( $calendar['timezone'] )->diffInDays( $google_end->setTimezone( $calendar['timezone'] ) );
							}

							// Multiple days.
							$multiple_days = $span > 0 ? $span : false;

							// Google cannot have two different locations for start and end time.
							$start_location = $end_location = $event->getLocation();

							// Recurring event.
							$recurrence = $event->getRecurrence();
							$recurring_id = $event->getRecurringEventId();
							if ( ! $recurrence && $recurring_id ) {
								$recurrence = true;
							}

							// Event color.
							$color = '';
							if ( true === $this->google_events_colors ) {
								$color = $this->get_color( $event->colorId );
							}

							// Event link.
							if ( 'use_calendar' == $this->timezone_setting ) {
								$link = add_query_arg( array( 'ctz' => $this->timezone ), $event->getHtmlLink() );
							} else {
								$link = $event->getHtmlLink();
							}

							// Build the event.
							$calendar['events'][ $start ][] = array(
								'type'           => 'google-calendar',
								'source'         => $source,
								'title'          => $title,
								'description'    => $description,
								'link'           => $link,
								'visibility'     => $event->getVisibility(),
								'uid'            => $event->getICalUID(),
								'calendar'       => $this->post_id,
								'timezone'       => $this->timezone,
								'start'          => $start,
								'start_utc'      => $start_utc,
								'start_timezone' => $start_timezone,
								'start_location' => $start_location,
								'end'            => $end,
								'end_utc'        => $end_utc,
								'end_timezone'   => $end_timezone,
								'end_location'   => $end_location,
								'whole_day'      => $whole_day,
								'multiple_days'  => $multiple_days,
								'recurrence'     => $recurrence,
								'template'       => $this->events_template,
								'meta'           => array(
									'attachments'   => $this->get_attachments( $event->getAttachments() ),
									'attendees'     => $this->get_attendees( $event->getAttendees() ),
									'color'         => $color,
									'organizer'     => $this->get_organizer( $event->getCreator() ),
									'rsvp'          => $event->getAnyoneCanAddSelf(),
								),
							);

						}
					}

					if ( ! empty( $calendar['events'] ) ) {

						ksort( $calendar['events'], SORT_NUMERIC );

						set_transient(
							'_simple-calendar_feed_id_' . strval( $this->post_id ) . '_' . $this->type,
							$calendar,
							max( absint( $this->cache ), 60 )
						);
					}
				}

			} else {

				$message = __( 'While trying to retrieve events, Google returned an error:', 'simple-calendar-google-calendar-pro' );
				$message .= '<br><br>' . $error . '<br><br>';
				$message .= __( 'Only you can see this notice.', 'simple-calendar-google-calendar-pro' );

				return $message;
			}

		}

		// If no timezone has been set, use calendar feed.
		if ( 'use_calendar' == $this->timezone_setting && isset( $calendar['timezone'] ) ) {
			$this->timezone = $calendar['timezone'];
		}

		return isset( $calendar['events'] ) ? $calendar['events'] : array();
	}

	/**
	 * Get event attachments.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param  array $attachments Array of \Google_Service_Calendar_EventAttachment
	 *
	 * @return array
	 */
	private function get_attachments( $attachments ) {

		$list = array();

		if ( ! empty( $attachments ) && is_array( $attachments ) )  {

			foreach ( $attachments as $attachment ) {

				if ( $attachment instanceof \Google_Service_Calendar_EventAttachment ) {

					$list[] = array(
						'icon'  => $attachment->getIconLink(),
						'name'  => $attachment->getTitle(),
						'mime'  => $attachment->getMimeType(),
						'url'   => $attachment->getFileUrl(),
					);
				}
			}
		}

		return $list;
	}

	/**
	 * Get event attendees.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param  array $attendees Array of \Google_Service_Calendar_EventAttendee
	 *
	 * @return array
	 */
	private function get_attendees( $attendees ) {

		$list = array();

		if ( ! empty( $attendees ) && is_array( $attendees ) ) {

			foreach ( $attendees as $attendee ) {

				if ( $attendee instanceof \Google_Service_Calendar_EventAttendee ) {

					$list[] = array(
						'name'      => sanitize_text_field( $attendee->getDisplayName() ),
						'email'     => sanitize_text_field( $attendee->getEmail() ),
						'photo'     => get_avatar_url( $attendee->getEmail(), array( 'size' => 128 ) ),
						'response'  => $this->get_rsvp_status( $attendee->getResponseStatus() ),
						'comment'   => sanitize_text_field( wp_strip_all_tags( $attendee->getComment() ) ),
					);
				}
			}
		}

		return $list;
	}

	/**
	 * Normalize the RSVP status string from a Google event.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $response RSVP status response.
	 *
	 * @return string
	 */
	private function get_rsvp_status( $response ) {
		switch ( $response ) {
			case 'accepted' :
				return 'yes';
			case 'declined' :
				return 'no';
			case 'tentative' :
				return 'maybe';
			case 'needsAction' :
				return 'pending';
			default :
				return '';
		}
	}

	/**
	 * Get event color.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param  null|string $color_id
	 *
	 * @return string
	 */
	private function get_color( $color_id ) {

		$color = $this->google_calendar_color;

		if ( ! empty( $color_id ) && ( is_int( $color_id ) || is_string( $color_id ) ) ) {

			if ( isset( $this->google_colors[ $color_id ] ) ) {

				return $this->google_colors[ $color_id ];
			}
		}

		return $color;
	}

	/**
	 * Get calendar color.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $id
	 *
	 * @return string
	 */
	private function get_calendar_color( $id ) {

		try {

			$google = $this->get_service();
			$calendar = $google->calendarList->get( $id );

			if ( $calendar instanceof \Google_Service_Calendar_CalendarListEntry ) {
				return $calendar->getBackgroundColor();
			}

		} catch ( \Exception $e ) {}


		return '';
	}

	/**
	 * Get event organizer info.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @param  \Google_Service_Calendar_EventCreator $organizer
	 *
	 * @return array
	 */
	private function get_organizer( $organizer ) {

		$info = array();

		if ( $organizer instanceof \Google_Service_Calendar_EventCreator ) {

			$name  = sanitize_text_field( $organizer->getDisplayName() );
			$email = sanitize_text_field( $organizer->getEmail() );

			$info = array(
				'name'  => empty( $name ) ? $email : $name,
				'email' => $email,
				'photo' => get_avatar_url( $organizer->getEmail(), array( 'size' => 128 ) ),
			);

		}

		return $info;
	}

	/**
	 * Get Google calendars.
	 *
	 * @since  1.0.0
	 *
	 * @return array An associative array of Google calendars with id in keys and title in values.
	 */
	public function get_calendars() {

		$this->connect();

		$calendars = array();
		$google    = $this->get_service();
		$list_options = array(
						'showHidden' => true,
					);

		if ( $google instanceof \Google_Service_Calendar ) {

			try {
				$list = $google->calendarList->listCalendarList( $list_options );
			} catch( \Exception $e ) {
				return $calendars;
			}

			while ( true ) {
				foreach ( $list->getItems() as $calendar ) {
					if ( $calendar instanceof \Google_Service_Calendar_CalendarListEntry ) {
						$calendars[ $calendar->getId() ] = $calendar->getSummary();
					}
				}
				if ( $page_token = $list->getNextPageToken() ) {
					$list = $google->calendarList->listCalendarList( array(
						'pageToken' => $page_token,
					) );
				} else {
					break;
				}
			}

			asort( $calendars );
		}

		return $calendars;
	}

	/**
	 * Google API Client.
	 *
	 * @since  1.0.0
	 * @access private
	 *
	 * @return null|\Google_Client
	 */
	private function get_client() {

		$client = new \Google_Client();

		$client->setApplicationName( 'Simple Calendar' );
		$client->setScopes( $this->google_client_scopes );
		$client->setClientId( $this->google_client_id );
		$client->setClientSecret( $this->google_client_secret );
		$client->setRedirectUri( $this->google_client_redirect );
		$client->setApprovalPrompt( 'force' );
		$client->setAccessType( 'offline' );

		return $client;
	}

	/**
	 * Get Google auth redirection url fragment.
	 *
	 * @since  1.0.0
	 * @internal
	 *
	 * @return string
	 */
	public function get_auth_url() {
		return $this->google_client instanceof \Google_Client ? $this->google_client->createAuthUrl() : '';
	}

}
