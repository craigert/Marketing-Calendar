<?php
/**
 * FullCalendar - Admin
 *
 * @package    SimpleCalendar/Feeds
 */
namespace SimpleCalendar\Calendars\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FullCalendar view admin.
 *
 * @since 1.0.0
 */
class FullCalendar_Admin {

	/**
	 * Used to load minified assets
	 *
	 * @since 1.0.0
	 */
	public $min = '.min';

	/**
	 * Hook in tabs.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		$this->min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG == true ) ? '' : '.min';

		if ( simcal_is_admin_screen() !== false ) {
			add_action( 'simcal_settings_meta_calendar_panel', array(
				$this,
				'add_settings_meta_fullcalendar_panel',
			), 10, 1 );
		}
		add_action( 'simcal_process_settings_meta', array( $this, 'process_meta' ), 10, 1 );

		add_action( 'admin_enqueue_scripts', array( $this, 'load' ), 100 );
	}

	public function load() {

		$css_path = SIMPLE_CALENDAR_FULLCALENDAR_ASSETS . 'css/';

		wp_register_style( 'simcal-fullcal-admin', $css_path . 'admin' . $this->min . '.css', false, SIMPLE_CALENDAR_FULLCALENDAR_VERSION );

		if ( simcal_is_admin_screen() !== false ) {
			wp_enqueue_style( 'simcal-fullcal-admin' );
		}
	}

	/**
	 * Add FullCalendar specific settings to the appearance tab
	 *
	 * @since  1.0.0
	 *
	 * @param int $post_id
	 */
	public function add_settings_meta_fullcalendar_panel( $post_id ) {
		?>
		<table id="fullcalendar-settings">
			<thead>
			<tr>
				<th colspan="2"><?php _e( 'FullCalendar Settings', 'simple-calendar-fullcalendar' ); ?></th>
			</tr>
			</thead>

			<tbody class="simcal-panel-section">

			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_height_select"><?php _e( 'Calendar Height', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php
					$fullcalendar_height_select = get_post_meta( $post_id, '_fullcalendar_height_select', true );
					$fullcalendar_height        = get_post_meta( $post_id, '_fullcalendar_height', true );

					if ( false === $fullcalendar_height_select || empty( $fullcalendar_height_select ) ) {
						$fullcalendar_height_select = 'not_set';
					}
					?>

					<select name="_fullcalendar_height_select"
					        id="_fullcalendar_height_select"
					        class="simcal-field simcal-field-select simcal-field-show-other">
						<option value="not_set" <?php selected( 'not_set', $fullcalendar_height_select, true ); ?>><?php _e( 'Default (1.35 aspect ratio)', 'simple-calendar-fullcalendar' ); ?></option>
						<option value="auto" <?php selected( 'auto', $fullcalendar_height_select, true ); ?>><?php _e( 'Auto (no scrollbars)', 'simple-calendar-fullcalendar' ); ?></option>
						<option value="use_custom" data-show-field="_fullcalendar_height_wrap" <?php selected( 'use_custom', $fullcalendar_height_select, true ); ?>><?php _e( 'Custom', 'simple-calendar-fullcalendar' ); ?></option>
					</select>
					<i class="simcal-icon-help simcal-help-tip" data-tip="<?php _e( 'Adjust the height of the calendar. The default behavior is to render a width-to-height aspect ratio of 1.35. Select &quot;Custom&quot; to specify an exact height.', 'simple-calendar-fullcalendar' ) ?>"></i>
					<p id="_fullcalendar_height_wrap" style="<?php echo $fullcalendar_height_select != 'use_custom' ? 'display: none;' : ''; ?>">
						<label for="_fullcalendar_height">
							<input type="text"
							       name="_fullcalendar_height"
							       id="_fullcalendar_height"
							       class="simcal-field simcal-field-text simcal-field-tiny"
							       value="<?php echo esc_attr( $fullcalendar_height ); ?>" />
							<?php _e( 'px', 'simple-calendar-fullcalendar' ); ?>
						</label>
					</p>
				</td>
			</tr>

			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_today_button"><?php _e( 'Today Button', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php
					$today_button = get_post_meta( $post_id, '_fullcalendar_today_button', true );
					$today_button = ( ! empty( $today_button ) ? $today_button : 'yes' );

					simcal_print_field( array(
						'type'    => 'checkbox',
						'name'    => '_fullcalendar_today_button',
						'id'      => '_fullcalendar_today_button',
						'class'   => array(
							'',
						),
						'value'   => 'yes' == $today_button ? 'yes' : 'no',
						'text'    => __( 'Show', 'simple-calendar-fullcalendar' ),
						'tooltip' => __( "Display the today button at the top of the calendar to allow visitors to quickly jump to today's date.", 'simple-calendar-fullcalendar' ),
					) );
					?>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th><label><?php _e( 'View Buttons', 'simple-calendar-fullcalendar' ); ?></label></th>
				<td>
					<ul class="simcal-field-checkboxes-inline">
						<li>
							<?php
							$month_button = get_post_meta( $post_id, '_fullcalendar_month_button', true );
							$month_button = ( ! empty( $month_button ) ? $month_button : 'yes' );

							simcal_print_field( array(
								'type'  => 'checkbox',
								'name'  => '_fullcalendar_month_button',
								'id'    => '_fullcalendar_month_button',
								'class' => array(
									'',
								),
								'value' => 'yes' == $month_button ? 'yes' : 'no',
								'text'  => '<label for="_fullcalendar_month_button">' . __( 'Month', 'simple-calendar-fullcalendar' ) . '</label>',
							) );
							?>
						</li>

						<li>
							<?php
							$week_button = get_post_meta( $post_id, '_fullcalendar_week_button', true );
							$week_button = ( ! empty( $week_button ) ? $week_button : 'yes' );

							simcal_print_field( array(
								'type'  => 'checkbox',
								'name'  => '_fullcalendar_week_button',
								'id'    => '_fullcalendar_week_button',
								'class' => array(
									'',
								),
								'value' => 'yes' == $week_button ? 'yes' : 'no',
								'text'  => '<label for="_fullcalendar_week_button">' . __( 'Week', 'simple-calendar-fullcalendar' ) . '</label>',
							) );
							?>
						</li>

						<li>
							<?php

							$day_button = get_post_meta( $post_id, '_fullcalendar_day_button', true );
							$day_button = ( ! empty( $day_button ) ? $day_button : 'yes' );

							simcal_print_field( array(
								'type'    => 'checkbox',
								'name'    => '_fullcalendar_day_button',
								'id'      => '_fullcalendar_day_button',
								'class'   => array(
									'',
								),
								'value'   => 'yes' == $day_button ? 'yes' : 'no',
								'text'    => '<label for="_fullcalendar_day_button">' . __( 'Day', 'simple-calendar-fullcalendar' ) . '</label>',
								'tooltip' => __( 'Display buttons at the top of the calendar that allow the visitor to switch between month, week and day views.', 'simple-calendar-fullcalendar' ),
							) );
							?>
						</li>
					</ul>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_default_view"><?php _e( 'Default View', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$default_view = get_post_meta( $post_id, '_fullcalendar_default_view', true );

					simcal_print_field( array(
						'type'    => 'select',
						'name'    => '_fullcalendar_default_view',
						'id'      => '_fullcalendar_default_view',
						'title'   => __( 'Default view', 'simple-calendar-fullcalendar' ),
						'tooltip' => __( 'The view to show when the calendar is first loaded.', 'simple-calendar-fullcalendar' ),
						'options' => array(
							'month'      => __( 'Month', 'simple-calendar-fullcalendar' ),
							'agendaWeek' => __( 'Week', 'simple-calendar-fullcalendar' ),
							'agendaDay'  => __( 'Day', 'simple-calendar-fullcalendar' ),
						),
						'default' => 'month',
						'value'   => $default_view,
					) );
					?>
				</td>
			</tr>
			</tbody>

			<tbody class="simcal-panel-section">

			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_event_bubble_trigger"><?php _e( 'Event Bubbles', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$bubbles = get_post_meta( $post_id, '_fullcalendar_event_bubble_trigger', true );

					simcal_print_field( array(
						'type'    => 'radio',
						'inline'  => 'inline',
						'name'    => '_fullcalendar_event_bubble_trigger',
						'id'      => '_fullcalendar_event_bubble_trigger',
						'tooltip' => __( 'Open event bubbles in calendar grid by clicking or hovering on event titles. On mobile devices it will always default to tapping.', 'simple-calendar-fullcalendar' ),
						'value'   => $bubbles ? $bubbles : 'hover',
						'default' => 'hover',
						'options' => array(
							'click' => __( 'Click', 'simple-calendar-fullcalendar' ),
							'hover' => __( 'Hover', 'simple-calendar-fullcalendar' ),
						),
					) );

					?>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_trim_titles"><?php _e( 'Trim Event Titles', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$trim = get_post_meta( $post_id, '_fullcalendar_trim_titles', true );

					simcal_print_field( array(
						'type'       => 'checkbox',
						'name'       => '_fullcalendar_trim_titles',
						'id'         => '_fullcalendar_trim_titles',
						'class'      => array(
							'simcal-field-show-next',
						),
						'value'      => 'yes' == $trim ? 'yes' : 'no',
						'attributes' => array(
							'data-show-next-if-value' => 'yes',
						),
					) );

					simcal_print_field( array(
						'type'       => 'standard',
						'subtype'    => 'number',
						'name'       => '_fullcalendar_trim_titles_chars',
						'id'         => '_fullcalendar_trim_titles_chars',
						'tooltip'    => __( 'Shorten event titles in calendar grid to a specified length in characters.', 'simple-calendar-fullcalendar' ),
						'class'      => array(
							'simcal-field-tiny',
						),
						'value'      => 'yes' == $trim ? strval( max( absint( get_post_meta( $post_id, '_fullcalendar_trim_titles_chars', true ) ), 1 ) ) : '20',
						'attributes' => array(
							'min' => '1',
						),
					) );

					?>
				</td>
			</tr>
			<tr class="simcal-panel-field simcal-fullcalendar-grid simcal-default-calendar-list">
				<th>
					<label for="_fullcalendar_limit_visible_events"><?php _e( 'Limit Visible Events', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$limit = get_post_meta( $post_id, '_fullcalendar_limit_visible_events', true );

					simcal_print_field( array(
						'type'       => 'checkbox',
						'name'       => '_fullcalendar_limit_visible_events',
						'id'         => '_fullcalendar_limit_visible_events',
						'value'      => 'yes' == $limit ? 'yes' : 'no',
						'class'      => array(
							'simcal-field-show-next',
						),
						'attributes' => array(
							'data-show-next-if-value' => 'yes',
						),
					) );

					$visible_events = absint( get_post_meta( $post_id, '_fullcalendar_visible_events', true ) );
					$visible_events = $visible_events > 0 ? $visible_events : 3;

					simcal_print_field( array(
						'type'       => 'standard',
						'subtype'    => 'number',
						'name'       => '_fullcalendar_visible_events',
						'id'         => '_fullcalendar_visible_events',
						'tooltip'    => __( 'Limit the number of initial visible events on each day to a set maximum.', 'simple-calendar-fullcalendar' ),
						'class'      => array(
							'simcal-field-tiny',
						),
						'value'      => $visible_events,
						'attributes' => array(
							'min' => '1',
						),
					) );

					?>
				</td>
			</tr>
			</tbody>
			<?php
			$default_event_color      = '#1e73be';
			$default_text_color_color = '#ffffff';
			?>
			<tbody class="simcal-panel-section">
			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_style_event_color"><?php _e( 'Event Color', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$saved = get_post_meta( $post_id, '_fullcalendar_style_event_color', true );
					$value = ! $saved ? $default_event_color : $saved;

					simcal_print_field( array(
						'type'    => 'standard',
						'subtype' => 'color-picker',
						'name'    => '_fullcalendar_style_event_color',
						'id'      => '_fullcalendar_style_event_color',
						'value'   => $value,
						'tooltip' => __( 'Sets the background color of each event. If using a Google Calendar PRO event source and "Use event colors" is enabled, background colors will instead be set from each Google calendar event.', 'simple-calendar-fullcalendar' ),
					) );

					?>
				</td>
			</tr>

			<tr class="simcal-panel-field simcal-fullcalendar-grid">
				<th>
					<label for="_fullcalendar_style_text_color"><?php _e( 'Event Text Color', 'simple-calendar-fullcalendar' ); ?></label>
				</th>
				<td>
					<?php

					$saved = get_post_meta( $post_id, '_fullcalendar_style_text_color', true );
					$value = ! $saved ? $default_text_color_color : $saved;

					simcal_print_field( array(
						'type'    => 'standard',
						'subtype' => 'color-picker',
						'name'    => '_fullcalendar_style_text_color',
						'id'      => '_fullcalendar_style_text_color',
						'value'   => $value,
						'tooltip' => __( 'Sets the text color of each event.', 'simple-calendar-fullcalendar' ),
					) );

					?>
				</td>
			</tr>
			</tbody>
		</table>
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

		// Height
		$height_select = isset( $_POST['_fullcalendar_height_select'] ) ? esc_attr( $_POST['_fullcalendar_height_select'] ) : 'not_set';
		update_post_meta( $post_id, '_fullcalendar_height_select', $height_select );
		$height = isset( $_POST['_fullcalendar_height'] ) ? intval( sanitize_text_field( $_POST['_fullcalendar_height'] ) ) : '';
		update_post_meta( $post_id, '_fullcalendar_height', $height );

		// Today button
		$today_button = isset( $_POST['_fullcalendar_today_button'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_today_button', $today_button );

		// Month button
		$month_button = isset( $_POST['_fullcalendar_month_button'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_month_button', $month_button );

		// Week button
		$week_button = isset( $_POST['_fullcalendar_week_button'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_week_button', $week_button );

		// Day button
		$day_button = isset( $_POST['_fullcalendar_day_button'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_day_button', $day_button );

		// Default view
		$default_view = isset( $_POST['_fullcalendar_default_view'] ) ? esc_attr( $_POST['_fullcalendar_default_view'] ) : 'month';
		update_post_meta( $post_id, '_fullcalendar_default_view', $default_view );

		// Event color.
		$event_color = isset( $_POST['_fullcalendar_style_event_color'] ) ? sanitize_text_field( $_POST['_fullcalendar_style_event_color'] ) : '#1e73be';
		update_post_meta( $post_id, '_fullcalendar_style_event_color', $event_color );

		// Event text color.
		$text_color = isset( $_POST['_fullcalendar_style_text_color'] ) ? sanitize_text_field( $_POST['_fullcalendar_style_text_color'] ) : '#ffffff';
		update_post_meta( $post_id, '_fullcalendar_style_text_color', $text_color );

		// Limit number of initially visible daily events.
		$limit = isset( $_POST['_fullcalendar_limit_visible_events'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_limit_visible_events', $limit );
		$number = isset( $_POST['_fullcalendar_visible_events'] ) ? absint( $_POST['_fullcalendar_visible_events'] ) : 3;
		update_post_meta( $post_id, '_fullcalendar_visible_events', $number );

		// Grid event bubbles action.
		$bubbles = isset( $_POST['_fullcalendar_event_bubble_trigger'] ) ? esc_attr( $_POST['_fullcalendar_event_bubble_trigger'] ) : 'hover';
		update_post_meta( $post_id, '_fullcalendar_event_bubble_trigger', $bubbles );

		// Trim event titles characters length.
		$trim = isset( $_POST['_fullcalendar_trim_titles'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_fullcalendar_trim_titles', $trim );
		$chars = isset( $_POST['_fullcalendar_trim_titles_chars'] ) ? max( absint( $_POST['_fullcalendar_trim_titles_chars'] ), 1 ) : 20;
		update_post_meta( $post_id, '_fullcalendar_trim_titles_chars', $chars );
	}
}
