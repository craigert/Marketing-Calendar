<?php
/**
 * FullCalendar Grid View
 *
 * @package SimpleCalendar/Calendars
 */
namespace SimpleCalendar\Calendars\Views;

use Carbon\Carbon;
use Mexitek\PHPColors\Color;
use SimpleCalendar\Abstracts\Calendar;
use SimpleCalendar\Abstracts\Calendar_View;
use SimpleCalendar\Calendars\Default_FullCalendar;
use SimpleCalendar\Events\Event;
use FullCalendar;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FullCalendar Grid View.
 *
 * @since  1.0.0
 */
class FullCalendar_Grid implements Calendar_View {

	/**
	 * Calendar.
	 *
	 * @access public
	 * @var Default_Calendar
	 */
	public $calendar = null;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string|Calendar $calendar
	 */
	public function __construct( $calendar = '' ) {

		$this->calendar = $calendar;
	}

	/**
	 * Get the view parent calendar type.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_parent() {
		return 'fullcalendar';
	}

	/**
	 * Get the view type.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return 'fullcalendar-grid';
	}

	/**
	 * Get the view name.
	 *
	 * @since  1.0.0
	 *
	 * @return string
	 */
	public function get_name() {
		return __( 'FullCalendar', 'simple-calendar-fullcalendar' );
	}

	/**
	 * Filters to setup Ajax callback
	 *
	 * @since 1.0.0
	 */
	public function add_ajax_actions() {

		add_action( 'wp_ajax_simcal_fullcal_load_events', array( $this, 'load_events' ) );
		add_action( 'wp_ajax_nopriv_simcal_fullcal_load_events', array( $this, 'load_events' ) );
	}

	/**
	 * Ajax call to load JSON events for FullCalendar
	 *
	 * @since 1.0.0
	 */
	public function load_events() {

		$json = new FullCalendar\FullCalendar_JSON( intval( $_POST['calendar_id'] ) );

		$json->get_json();

		wp_die();
	}

	/**
	 * FullCalendar grid view scripts.
	 *
	 * Scripts to load when this view is displayed.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $min
	 *
	 * @return array
	 */
	public function scripts( $min = '' ) {

		return array(
			'simcal-fullcal-moment' => array(
				'src'       => SIMPLE_CALENDAR_FULLCALENDAR_ASSETS . 'js/vendor/moment' . $min . '.js',
				'deps'      => '',
				'ver'       => '2.11.2',
				'in_footer' => true,
			),
			'simcal-fullcal'        => array(
				'src'       => SIMPLE_CALENDAR_FULLCALENDAR_ASSETS . 'js/vendor/fullcalendar' . $min . '.js',
				'deps'      => array( 'jquery' ),
				'ver'       => '2.6.1',
				'in_footer' => true,
			),
			'simcal-fullcal-gcal'   => array(
				'src'       => SIMPLE_CALENDAR_FULLCALENDAR_ASSETS . 'js/vendor/gcal' . $min . '.js',
				'deps'      => array( 'jquery', 'simcal-fullcal', 'simcal-fullcal-moment' ),
				'ver'       => '2.6.1',
				'in_footer' => true,
			),
			'simcal-qtip'           => array(
				'src'       => SIMPLE_CALENDAR_ASSETS . 'js/vendor/jquery.qtip' . $min . '.js',
				'deps'      => array( 'jquery' ),
				'ver'       => '2.2.1',
				'in_footer' => true,
			),
			'simcal-fullcal-grid'   => array(
				'src'       => SIMPLE_CALENDAR_FULLCALENDAR_ASSETS . 'js/fullcalendar-grid' . $min . '.js',
				'deps'      => array(
					'jquery',
					'simcal-fullcal',
					'simcal-fullcal-moment',
					'simcal-fullcal-gcal',
					'simcal-qtip',
				),
				'ver'       => '1.0.0',
				'in_footer' => true,
				'localize'  => array(
					'simcal_fullcal' => array(
						'settings' => array(
							'ajax_url' => admin_url( 'admin-ajax.php', 'relative' ),
							'lang'     => \SimpleCalendar\plugin()->locale,
							'isRTL'    => is_rtl(),
						),
						'months'   => array(
							'full'  => simcal_get_calendar_names_i18n( 'month', 'full' ),
							'short' => simcal_get_calendar_names_i18n( 'month', 'short' ),
						),
						'days'     => array(
							'full'  => simcal_get_calendar_names_i18n( 'day', 'full' ),
							'short' => simcal_get_calendar_names_i18n( 'day', 'short' ),
						),
					),
				),
			),
		);
	}

	/**
	 * FullCalendar grid view styles.
	 *
	 * Stylesheets to load when this view is displayed.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $min = ''
	 *
	 * @return array
	 */
	public function styles( $min = '' ) {

		return array(
			'fullcalendar'        => array(
				'src'   => SIMPLE_CALENDAR_FULLCALENDAR_ASSETS . 'css/vendor/fullcalendar' . $min . '.css',
				'ver'   => SIMPLE_CALENDAR_FULLCALENDAR_VERSION,
				'media' => 'all',
			),
			'simcal-fullcal-grid' => array(
				'src'   => SIMPLE_CALENDAR_FULLCALENDAR_ASSETS . 'css/fullcalendar-grid' . $min . '.css',
				'ver'   => SIMPLE_CALENDAR_FULLCALENDAR_VERSION,
				'media' => 'all',
			),
			'simcal-qtip'         => array(
				'src'   => SIMPLE_CALENDAR_ASSETS . 'css/vendor/jquery.qtip' . $min . '.css',
				'ver'   => '2.2.1',
				'media' => 'all',
			),
		);
	}

	/**
	 * Default FullCalendar grid markup.
	 *
	 * @since 1.0.0
	 */
	public function html() {

		$calendar    = $this->calendar;
		$calendar_id = $calendar->id;

		$static_calendar = get_post_meta( $calendar_id, '_calendar_is_static', true );
		$today_button    = get_post_meta( $calendar_id, '_fullcalendar_today_button', true );
		$month_button    = get_post_meta( $calendar_id, '_fullcalendar_month_button', true );
		$week_button     = get_post_meta( $calendar_id, '_fullcalendar_week_button', true );
		$day_button      = get_post_meta( $calendar_id, '_fullcalendar_day_button', true );
		$default_view    = get_post_meta( $calendar_id, '_fullcalendar_default_view', true );
		$height_select   = get_post_meta( $calendar_id, '_fullcalendar_height_select', true );
		$height          = get_post_meta( $calendar_id, '_fullcalendar_height', true );


		switch ( $height_select ) {
			case 'auto':
				$height = 'auto';
				break;
			case 'use_custom':
				$height = ! empty( $height ) ? intval( $height ) : 0;
				break;
			default:
				//includes 'not_set'
				$height = -1;
		}

		edit_post_link( __( 'Edit Calendar', 'simple-calendar-fullcalendar' ), '<p class="simcal-align-right"><small>', '</small></p>', $calendar->id );

		echo '<div id="fullcalendar-' . esc_attr( intval( $calendar_id ) ) . '" class="simcal-fullcal simcal-fullcal-' . esc_attr( intval( $calendar_id ) ) . '" ' .
									'data-event-bubble-trigger="' . esc_attr( $calendar->event_bubble_trigger ) . '" ' .
									'data-calendar-id="' . esc_attr( intval( $calendar_id ) ) . '" ' .
									'data-timezone="' . esc_attr( $calendar->timezone ) . '" ' .
									'data-week-starts="' . esc_attr( $calendar->week_starts ) . '" ' .
									'data-start="' . esc_attr( Carbon::createFromTimestamp( $calendar->start )->toIso8601String() ) . '" ' .
									'data-event-limit="' . esc_attr( ( ( $calendar->events_limit > 0 ) ?  $calendar->events_limit : 'false' ) ) . '" ' .
									'data-event-color="' . esc_attr( $calendar->event_color ) . '" ' .
									'data-text-color="' . esc_attr( $calendar->text_color ) . '" ' .
									'data-paging-buttons="' . esc_attr( ( $static_calendar === 'yes' ? '" ' : 'prev,next ' ) ) . '" ' .
									'data-today-button="' . esc_attr( ( $today_button === 'yes' ? 'today' : '' ) ) . '" ' .
									'data-month-button="' . esc_attr( ( $month_button === 'yes' ? 'month,' : '' ) ) . '" ' .
									'data-week-button="' . esc_attr( ( $week_button === 'yes' ? 'agendaWeek,' : '' ) ) . '" ' .
									'data-day-button="' . esc_attr( ( $day_button === 'yes' ? 'agendaDay' : '' ) ) . '" ' .
									'data-calendar-height="' . esc_attr( $height ) . '" ' .
									'data-default-view="' . esc_attr( $default_view ) . '" ' .
			'>';

		$i = 0;

		foreach ( $calendar->events as $k => $v ) {
			foreach ( $v as $k2 => $v2 ) {
				echo '<div class="simcal-tooltip-content simcal-fullcal-tooltip-content simcal-fullcal-qtip-id-' . esc_attr( intval( $i ) ) . '" style="display: none;">' . $calendar->get_event_html( $v2 ) . '</div>' . "\n";
				$i++;
			}
		}

		echo '</div>';

		// Initially hide icon instead of container to help with fadeToggle animation.
		echo '<div class="simcal-ajax-loader simcal-spinner-top"><i class="simcal-icon-spinner simcal-icon-spin" style="display: none;"></i></div>';

	}
}
