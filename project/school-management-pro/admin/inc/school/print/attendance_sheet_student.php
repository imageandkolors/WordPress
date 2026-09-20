<?php
defined( 'ABSPATH' ) || die();

$holiday_dates_in_month = array();
if ( isset( $start_date ) && isset( $end_date ) ) {
	global $wpdb;
	$holidays = $wpdb->get_results( $wpdb->prepare('SELECT start_date, end_date FROM ' . WLSM_HOLIDAYS . ' WHERE school_id = %d AND start_date <= %s AND end_date >= %s', $school_id, $end_date->format('Y-m-d'), $start_date->format('Y-m-d')) );
	foreach ($holidays as $holiday) {
		$h_start = new DateTime($holiday->start_date);
		$h_end = new DateTime($holiday->end_date);
		$h_end->modify('+1 day');
		$period = new DatePeriod($h_start, DateInterval::createFromDateString('1 day'), $h_end);
		foreach ($period as $dt) {
			$holiday_dates_in_month[] = $dt->format('Y-m-d');
		}
	}
}

if ( isset( $from_front ) ) {
	$print_button_classes = 'button btn-sm btn-success';
} else {
	$print_button_classes = 'btn btn-sm btn-success';
}
?>

<!-- Print attendance section. -->
<br>
<div class="wlsm-container wlsm-form-section" id="wlsm-print-attendance-sheet">
	<div class="wlsm-print-attendance-sheet-container">
		<div class="wlsm-font-bold">
			<span>
			<?php
				if( $attendance_by === 'subject' ) {
					printf(
						wp_kses(
							__('Attendance - Class: <span class="text-secondary">%1$s</span> | Section: <span class="text-secondary">%2$s</span> | Subject: <span class="text-secondary">%3$s</span>', 'school-management'),
							array('span' => array('class' => array()))
						),
						esc_html(WLSM_M_Class::get_label_text($class_school->label)),
						esc_html(WLSM_M_Staff_Class::get_section_label_text($section_label)),
						esc_html(WLSM_M_Staff_Class::get_subject_label_text($subject->subject_name))
					);
				 } else {
					 printf(
						 wp_kses(
							 /* translators: 1: class label, 2: section label */
							 __( 'Attendance - Class: <span class="text-secondary">%1$s</span> | Section: <span class="text-secondary">%2$s</span>', 'school-management' ),
							 array( 'span' => array( 'class' => array() ) )
						 ),
						 esc_html( WLSM_M_Class::get_label_text( $class_school->label ) ),
						 esc_html( WLSM_M_Staff_Class::get_section_label_text( $section_label ) )
					 );
				 }
			?>
			</span>
			<span class="float-md-right">
			<?php
			printf(
				wp_kses(
					/* translators: 1: month of attendance, 2: year of attendance */
					__( 'Month: <span class="text-dark wlsm-font-bold">%1$s %2$s</span>', 'school-management' ),
					array( 'span' => array( 'class' => array() ) )
				),
				esc_html( $month_format ),
				esc_html( $year_format )
			);
			?>
			</span>
		</div>




		<div class="table-responsive w-100">
			<table class="table table-bordered wlsm-view-students-attendance-table">
				<thead>
					<tr class="bg-primary text-white">
						<th class="text-nowrap">
							<?php esc_html_e( 'Date', 'school-management' ); ?>
						</th>
						<th class="text-nowrap">
							<?php esc_html_e( 'Attendance', 'school-management' ); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php
					foreach ( $students as $row ) {
						$attendance_records = array_filter( $saved_attendance, function ( $attendance ) use ( $row ) {
							return $attendance->student_record_id == $row->ID;
						});

						$attendance_by_dates = array();
						foreach ( $attendance_records as $attendance_record ) {
							$attendance_by_dates[ $attendance_record->attendance_date ] = $attendance_record->status;
						}

						$total_present = 0;
						$total_absent  = 0;
						$total_late  = 0;
						$total_holiday  = 0;
					?>
					<?php foreach ( $date_range as $date ) { ?>
						<tr>
							<td><?php echo esc_html( $date->format( 'd' ) ); ?></td>
							<td>

						<?php
							$status = '';
							$current_date_ymd = $date->format('Y-m-d');
							if ( isset( $attendance_by_dates[ $current_date_ymd ] ) ) {
								$status = $attendance_by_dates[ $current_date_ymd ];
							} else if ( in_array( $current_date_ymd, $holiday_dates_in_month ) ) {
								$status = 'h';
							}

							if ( $status ) {
						?>

							<?php
							if ( ! $status ) {
							}
							else if ( 'p' === $status ) {
								$total_present++;
							?>
							<span class="text-success wlsm-font-bold"><?php echo esc_html( ucwords($status) ); ?></span>
							<?php
							}
							else if('a' === $status) {
								$total_absent++;
							?>
							<span class="text-danger wlsm-font-bold"><?php echo esc_html( ucwords($status) ); ?></span>
							<?php
							}
							else if('h' === $status) {
								$total_holiday++;
							?>
							<span class="text-danger wlsm-font-bold"><?php echo esc_html( ucwords($status) ); ?></span>
							<?php
							}
							else if('l' === $status) {
								$total_present++;
								$total_late++;
							?>
							<span class="text-danger wlsm-font-bold"><?php echo esc_html( ucwords($status) ); ?></span>
							<?php
							}
							?>
						<?php
						}
						// p,a,h translated to Present, Absent, Holiday
						?>
							</td>
						</tr>
					<?php }} ?>


				</tbody>
			</table>
		</div>

	</div>
</div>
