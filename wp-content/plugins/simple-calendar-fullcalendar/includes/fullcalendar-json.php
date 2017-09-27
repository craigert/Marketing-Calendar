<?php

/**
 * Simple Calendar - FullCalendar add-on JSON class
 *
 */
namespace FullCalendar;

use Carbon\Carbon;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_POST['get_json'] ) ) {
	FullCalendar_JSON::get_simcal_json();
}

/**
 * Class to take the Google events feeds and turn it into a JSON structure that FullCalendar can read.
 *
 * @since 1.0.0
 */
class FullCalendar_JSON {

	/**
	 * The calendar we need events from.
	 */
	public $calendar = '';

	/**
	 * Holds our JSON information.
	 */
	public $json = '';

	/**
	 * Class constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct( $calendar_id ) {
		$this->calendar = simcal_get_calendar( $calendar_id );

		$this->set_json();
	}

	/**
	 * Sets our JSON data so we ca use it when we need to.
	 */
	private function set_json() {

		$json_events   = array();
		$google_events = $this->calendar->events;
		$trim_titles   = $this->calendar->trim_titles;

		// These are the event properties we are looking for.
		// meta is an array from GCP that we need to grab the color from if they are using the "Use event colors" option
		$matches = array( 'timezone', 'title', 'start', 'end', 'uid', 'description', 'meta', 'whole_day' );

		// A counter to increment our JSON array
		$i = 0;

		foreach ( $google_events as $k => $v ) {
			foreach ( $v as $k2 => $v2 ) {
				foreach ( $v2 as $k3 => $v3 ) {

					if ( in_array( $k3, $matches ) ) {

						// Get title information
						if ( $k3 === 'title' ) {

							// Make sure we don't turn things like & into &amp;
							$v3 = html_entity_decode( $v3 );

							// Trim titles if needed.
							if ( $trim_titles > 0 ) {
								$v3 = html_entity_decode( strlen( $v3 ) > $trim_titles ? mb_substr( $v3, 0, $trim_titles ) . '&hellip;' : $v3 );
							}
						}

						// We store the UID of each event but FC uses it as 'id' so we just convert it here.
						if ( $k3 === 'uid' ) {
							$k3 = 'id';
						}

						// Set start and end time
						if ( $k3 === 'start' || $k3 === 'end' ) {
							// If the timezone exists then we know the event has it's own timezone so we use that, otherwise we use the calendar timezone.
							if ( isset( $v2->timezone ) ) {
								$v3 = Carbon::createFromTimestamp( $v3, $v2->timezone )->format( 'c' );
							} else {
								$v3 = Carbon::createFromTimestamp( $v3, $this->calendar->timezone )->format( 'c' );
							}
						}

						// This is to work in conjunction with the GCP "Use event colors" option.
						if ( $k3 === 'meta' ) {
							if ( isset( $v3['color'] ) ) {
								$k3 = 'color';
								$v3 = $v3['color'];
							}
						}

						// Convert whole day to be able to use in FC
						if ( $k3 === 'whole_day' ) {
							$k3 = 'allDay';
						}

						// Add all the event information we need to the JSON array
						$json_events[ $i ][ $k3 ] = $v3;
					}
				}

				// Setup a qTip ID so we can load the qTips
				$json_events[ $i ]['qtip_id'] = $i;

				$i++;
			}
		}

		// Save our JSON array as actual JSON
		$this->json = json_encode( $json_events );
	}

	/**
	 * Return the JSON data with option to output automatically.
	 *
	 * @param bool $output true by default since we are using this through AJAX mostly and it needs to be output.
	 *
	 * @return string
	 */
	public function get_json( $output = true ) {

		if ( $output ) {
			echo $this->json;
		} else {
			return $this->json;
		}

	}
}
