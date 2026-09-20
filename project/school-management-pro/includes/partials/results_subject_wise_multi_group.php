<?php
defined( 'ABSPATH' ) || die();

if ( ! isset( $selected_group_ids ) || ! is_array( $selected_group_ids ) ) {
	$selected_group_ids = array();
}

$selected_group_ids = array_values( array_unique( array_map( 'absint', $selected_group_ids ) ) );

$subjects = WLSM_M_Staff_Class::get_class_subjects_students( $school_id, $class_id, $student_id );

if ( empty( $selected_group_ids ) ) {
	require WLSM_PLUGIN_DIR_PATH . 'includes/partials/results_subject_wise.php';
	return;
}

$group_objects = WLSM_M_Staff_Examination::fetch_exam_groups_by_ids( $school_id, $selected_group_ids );
$group_labels  = array();

foreach ( $group_objects as $group_object ) {
	$group_labels[ (int) $group_object->ID ] = $group_object->label;
}

$group_order = $selected_group_ids;

$exams = WLSM_M_Staff_Examination::get_class_school_exams_academic_multi_group_report( $school_id, $class_school_id, $academic_report->ID );

if ( empty( $exams ) ) {
	require WLSM_PLUGIN_DIR_PATH . 'includes/partials/results_subject_wise.php';
	return;
}

$grouped_exams        = array();
$group_exam_counts    = array();
$group_summaries      = array();
$show_total_marks     = false;
$show_remark          = '0';
$psychomotor_enable   = array();
$psychomotor          = array();
$teacher_signature    = null;
// Note: $school_signature is already set from settings in school_header.php

foreach ( $group_order as $group_id ) {
	$grouped_exams[ $group_id ]     = array();
	$group_exam_counts[ $group_id ] = 0;
	$group_summaries[ $group_id ]   = array(
		'obtained' => 0,
		'maximum'  => 0,
		'exams'    => 0,
	);
}

foreach ( $exams as $exam ) {
	$group_id = (int) $exam->exam_group_id;

	if ( ! isset( $grouped_exams[ $group_id ] ) ) {
		$grouped_exams[ $group_id ]     = array();
		$group_exam_counts[ $group_id ] = 0;
		$group_summaries[ $group_id ]   = array(
			'obtained' => 0,
			'maximum'  => 0,
			'exams'    => 0,
		);
		$group_order[] = $group_id;
	}

	$grouped_exams[ $group_id ][] = $exam;
	$group_exam_counts[ $group_id ]++;

	if ( ! isset( $group_labels[ $group_id ] ) ) {
		$group_labels[ $group_id ] = isset( $exam->exam_group_label ) ? $exam->exam_group_label : '';
	}

	if ( $exam->enable_total_marks ) {
		$show_total_marks = true;
	}

	if ( '1' === $exam->show_remark ) {
		$show_remark = '1';
	}

	if ( null === $teacher_signature ) {
		$teacher_signature = $exam->teacher_signature;
	}

	$psychomotor_enable[] = $exam->psychomotor_analysis;
	$psychomotor[]        = WLSM_Config::sanitize_psychomotor( $exam->psychomotor );
	$group_summaries[ $group_id ]['exams']++;
}

$group_order = array_values( array_unique( $group_order ) );
$overall_obtained = 0;
$overall_maximum  = 0;
$marks_grades     = array();

// Extract Thresholds from Final Exam
$final_exam = end($exams);
$show_overall_result_status      = isset($final_exam->show_overall_result_status) ? $final_exam->show_overall_result_status : 0;
$show_subject_wise_result_status = isset($final_exam->show_subject_wise_result_status) ? $final_exam->show_subject_wise_result_status : 0;
$overall_result_status_text      = isset($final_exam->overall_result_status_text) && !empty($final_exam->overall_result_status_text) ? $final_exam->overall_result_status_text : 'Overall Result';
$subject_result_status_text      = isset($final_exam->subject_result_status_text) && !empty($final_exam->subject_result_status_text) ? $final_exam->subject_result_status_text : 'Status';
$subject_pass_threshold          = isset($final_exam->subject_pass_threshold) ? $final_exam->subject_pass_threshold : 0;
$overall_pass_threshold          = isset($final_exam->overall_pass_threshold) ? $final_exam->overall_pass_threshold : 0;
$overall_pass_text               = isset($final_exam->overall_pass_text) && !empty($final_exam->overall_pass_text) ? $final_exam->overall_pass_text : 'Pass';
$overall_fail_text               = isset($final_exam->overall_fail_text) && !empty($final_exam->overall_fail_text) ? $final_exam->overall_fail_text : 'Fail';
$subject_pass_text               = isset($final_exam->subject_pass_text) && !empty($final_exam->subject_pass_text) ? $final_exam->subject_pass_text : 'Pass';
$subject_fail_text               = isset($final_exam->subject_fail_text) && !empty($final_exam->subject_fail_text) ? $final_exam->subject_fail_text : 'Fail';

$maximum_failed_subjects        = isset($final_exam->maximum_failed_subjects) ? $final_exam->maximum_failed_subjects : 0;

$failed_subjects_count = 0;
reset($exams);


?>
<thead>
	<tr>
		<th><?php esc_html_e( 'Subject', 'school-management' ); ?></th>
		<?php foreach ( $group_order as $group_id ) : ?>
			<?php
			$group_label      = isset( $group_labels[ $group_id ] ) ? $group_labels[ $group_id ] : '';
			$group_exam_count = isset( $group_exam_counts[ $group_id ] ) ? $group_exam_counts[ $group_id ] : 0;
			?>
			<th class="text-center" colspan="<?php echo esc_attr( $group_exam_count + 1 ); ?>">
				<?php echo esc_html( stripslashes( $group_label ) ); ?>
			</th>
		<?php endforeach; ?>
		<?php if ( $show_total_marks ) : ?>
			<th class="text-center"><?php esc_html_e( 'Overall Total', 'school-management' ); ?></th>
		<?php endif; ?>
		<?php if ( $show_total_marks ) : ?>
		<th class="text-center"><?php esc_html_e( 'Grade', 'school-management' ); ?></th>
		<?php endif; ?>
		<?php if ( '1' === $show_remark ) : ?>
			<th class="text-center"><?php esc_html_e( 'Remarks', 'school-management' ); ?></th>
		<?php endif; ?>
		<?php if ( $show_subject_wise_result_status && $final_exam->exam_type !== 'grade_only' ) { ?>
			<th class="text-center"><?php echo esc_html( $subject_result_status_text ); ?></th>
		<?php } ?>
	</tr>
	<tr>
		<th></th>
		<?php foreach ( $group_order as $group_id ) : ?>
			<?php foreach ( $grouped_exams[ $group_id ] as $exam ) : ?>
				<th><?php echo esc_html( stripslashes( $exam->exam_title ) ); ?></th>
			<?php endforeach; ?>
			<?php if ( $show_total_marks ) : ?>
			<th class="text-center"><?php esc_html_e( 'Group Total', 'school-management' ); ?></th>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( $show_total_marks ) : ?>
			<th></th>
		<?php endif; ?>
		<?php if ( $show_total_marks ) : ?>
		<th></th>
		<?php endif; ?>
		<?php if ( '1' === $show_remark ) : ?>
			<th></th>
		<?php endif; ?>
		<?php if ( $show_subject_wise_result_status && $final_exam->exam_type !== 'grade_only' ) { ?>
			<th></th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php foreach ( $subjects as $subject ) : ?>
		<?php
		$subject_overall_obtained = 0;
		$subject_overall_maximum  = 0;
		$grade_subject_percentage = 0;
		$remark                   = '';
		$subject_has_results      = false;

		// Check if this subject has any exam results in any group
		foreach ( $group_order as $group_id ) : ?>
			<?php foreach ( $grouped_exams[ $group_id ] as $exam ) : ?>
				<?php
				$exam_result = WLSM_M_Staff_Examination::get_exam_result_by_subject_code( $school_id, $exam->ID, $student_id, $subject->code );
				if ( $exam_result ) {
					$subject_has_results = true;
				}
				?>
			<?php endforeach; ?>
		<?php endforeach; ?>

		<?php if ( ! $subject_has_results ) : ?>
			<?php continue; ?>
		<?php endif; ?>

		<tr>
			<td>
				<?php
				printf(
					wp_kses(
						/* translators: 1: subject label, 2: subject code */
						_x( '%1$s (%2$s)', 'Subject', 'school-management' ),
						array( 'span' => array( 'class' => array() ) )
					),
					esc_html( WLSM_M_Staff_Class::get_subject_label_text( $subject->label ) ),
					esc_html( $subject->code )
				);
				?>
			</td>
			<?php foreach ( $group_order as $group_id ) : ?>
				<?php
				$group_subject_obtained = 0;
				$group_subject_maximum  = 0;
				?>
				<?php foreach ( $grouped_exams[ $group_id ] as $exam ) : ?>
					<?php
					$exam_result = WLSM_M_Staff_Examination::get_exam_result_by_subject_code( $school_id, $exam->ID, $student_id, $subject->code );
					?>
					<td>
						<?php if ( $exam_result ) : ?>
							<?php
							$exam_type = $exam->exam_type ? $exam->exam_type : 'marks_grades';

							$group_subject_obtained += $exam_result->obtained_marks;
							$group_subject_maximum  += $exam_result->maximum_marks;
							$remark                  = $exam_result->remark;

							$grade_criteria = WLSM_Config::sanitize_grade_criteria( $exam->grade_criteria );
							$marks_grades   = $grade_criteria['marks_grades'];

							$subject_overall_obtained += $exam_result->obtained_marks;
							$subject_overall_maximum  += $exam_result->maximum_marks;

							$group_summaries[ $group_id ]['obtained'] += $exam_result->obtained_marks;
							$group_summaries[ $group_id ]['maximum']  += $exam_result->maximum_marks;
							?>
							<span class="wlsm-font-bold">
								<?php
								if ($exam_type === 'grade_only') {
									echo esc_html(strtoupper($exam_result->obtained_grade));
								} else {
									echo esc_html($exam_result->obtained_marks);
								}
								?>
							</span>
						<?php else : ?>
							-
						<?php endif; ?>
					</td>
				<?php endforeach; ?>
				<?php if ( $show_total_marks ) : ?>
				<td>
					<?php
					if ( $group_subject_obtained > 0 ) {
						echo '<span class="wlsm-font-bold">' . esc_html( $group_subject_obtained ) . '</span>';
					} else {
						echo '-';
					}
					?>
				</td>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php if ( $show_total_marks ) : ?>
				<td>
					<?php
					if ( $subject_overall_obtained > 0 ) {
						echo '<span class="wlsm-font-bold">' . esc_html( $subject_overall_obtained ) . '</span>';
					} else {
						echo '-';
					}
					?>
				</td>
			<?php endif; ?>
			<?php if ( $show_total_marks ) : ?>
			<td>
				<?php
				if ( $subject_overall_maximum > 0 ) {
					$grade_subject_percentage = WLSM_Config::sanitize_percentage( $subject_overall_maximum, WLSM_Config::sanitize_marks( $subject_overall_obtained ) );
					echo esc_html( WLSM_Helper::calculate_grade( $marks_grades, $grade_subject_percentage ) );
				}
				?>
			</td>
			<?php endif; ?>
			<?php if ( '1' === $show_remark ) : ?>
				<td><?php echo esc_html( $remark ); ?></td>
			<?php endif; ?>

			<?php
			$subject_status_text = '';
			if ( $show_subject_wise_result_status && $final_exam->exam_type !== 'grade_only' ) {
				if ( $subject_overall_maximum > 0 ) {
					$subject_percent = ($subject_overall_obtained / $subject_overall_maximum) * 100;
					if ( $subject_percent >= $subject_pass_threshold ) {
						$subject_status_text = $subject_pass_text;
					} else {
						$subject_status_text = $subject_fail_text;
						$failed_subjects_count++;
					}
				} else {
					$subject_status_text = $subject_fail_text;
					$failed_subjects_count++;
				}
				?>
				<td><span class="wlsm-font-bold"><?php echo esc_html( $subject_status_text ); ?></span></td>
			<?php } ?>

		</tr>
		<?php
		$overall_obtained += $subject_overall_obtained;
		$overall_maximum  += $subject_overall_maximum;
		?>
	<?php endforeach; ?>

	<tr>
		<th><?php esc_html_e( 'Group Totals', 'school-management' ); ?></th>
		<?php foreach ( $group_order as $group_id ) : ?>
			<?php foreach ( $grouped_exams[ $group_id ] as $unused ) : ?>
				<td></td>
			<?php endforeach; ?>
			<?php if ( $show_total_marks ) : ?>
			<td>
				<?php
				$group_total_obtained = $group_summaries[ $group_id ]['obtained'];
				if ( $group_total_obtained > 0 ) {
					echo '<span class="wlsm-font-bold">' . esc_html( $group_total_obtained ) . '</span>';
				} else {
					echo '-';
				}
				?>
			</td>
			<?php endif; ?>
		<?php endforeach; ?>
		<?php if ( $show_total_marks ) : ?>
			<td>
				<?php
				if ( $overall_obtained > 0 ) {
					echo '<span class="wlsm-font-bold">' . esc_html( $overall_obtained ) . '</span>';
				} else {
					echo '-';
				}
				?>
			</td>
		<?php endif; ?>
		<td></td>
		<?php if ( '1' === $show_remark ) : ?>
			<td></td>
		<?php endif; ?>
		<?php if ( $show_subject_wise_result_status && $final_exam->exam_type !== 'grade_only' ) { ?>
			<td></td>
		<?php } ?>
	</tr>

	<?php if ( $show_total_marks ) : // Hide Average row if total marks disabled ?>
	<?php
	$group_averages = array();
	foreach ( $group_order as $group_id ) {
		$exam_count = max( 1, $group_summaries[ $group_id ]['exams'] );
		$group_averages[ $group_id ] = $group_summaries[ $group_id ]['obtained'] / $exam_count;
	}
	$overall_average = ! empty( $group_averages ) ? array_sum( $group_averages ) / count( $group_averages ) : 0;
	?>
	<tr>
		<th><?php esc_html_e( 'Average of Groups', 'school-management' ); ?></th>
		<?php foreach ( $group_order as $group_id ) : ?>
			<?php foreach ( $grouped_exams[ $group_id ] as $unused ) : ?>
				<td></td>
			<?php endforeach; ?>
			<td>
				<?php
				$average_value = $group_averages[ $group_id ];
				echo '<span class="wlsm-font-bold">' . esc_html( number_format_i18n( $average_value, 2 ) ) . '</span>';
				?>
			</td>
		<?php endforeach; ?>
		<?php if ( $show_total_marks ) : ?>
			<td>
				<?php echo '<span class="wlsm-font-bold">' . esc_html( number_format_i18n( $overall_average, 2 ) ) . '</span>'; ?>
			</td>
		<?php endif; ?>
		<td></td>
		<?php if ( '1' === $show_remark ) : ?>
			<td></td>
		<?php endif; ?>
		<?php if ( $show_subject_wise_result_status && $final_exam->exam_type !== 'grade_only' ) { ?>
			<td></td>
		<?php } ?>
	</tr>
	<?php endif; ?>

	<?php
	$show_rank = '0';
	$rank_criteria = '';
	foreach ($exams as $exam) {
		if ($exam->show_rank === '1') {
			$show_rank = '1';
		}
		if ($exam->rank_criteria === 'section_wise') {
			$rank_criteria = 'section_wise';
		}
	}

	if ($show_rank === '1') {
		global $wpdb;
		$report_id = $academic_report->ID;
		$student_section_id = $student->section_id;

		if ($rank_criteria === 'section_wise' && isset($student_section_id)) {
			// Section-wise rank.
			$results = $wpdb->get_results($wpdb->prepare(
				"SELECT tm.student_id, tm.total_marks FROM " . WLSM_STUDENT_TOTAL_MARKS . " as tm
				JOIN " . WLSM_STUDENT_RECORDS . " as sr ON sr.ID = tm.student_id
				WHERE tm.report_id = %d AND sr.section_id = %d ORDER BY tm.total_marks DESC",
				$report_id,
				$student_section_id
			));
		} else {
			// Class-wise rank.
			$results = $wpdb->get_results($wpdb->prepare(
				"SELECT student_id, total_marks FROM " . WLSM_STUDENT_TOTAL_MARKS . " WHERE report_id = %d ORDER BY total_marks DESC",
				$report_id
			));
		}

		$rank = 1;
		$student_rank = '-';
		$total_students = count($results);
		foreach ($results as $row) {
			if ($row->student_id == $student_id) {
				$student_rank = $rank;
				break;
			}
			$rank++;
		}

		?>
		<tr>
			<th><?php esc_html_e('Position/Rank', 'school-management'); ?></th>
			<?php foreach ( $group_order as $group_id ) : ?>
				<?php foreach ( $grouped_exams[ $group_id ] as $unused ) : ?>
					<td></td>
				<?php endforeach; ?>
				<?php if ( $show_total_marks ) : ?>
					<?php
					$group_rank_output = '-';
					$group_exams_list = isset($grouped_exams[$group_id]) ? $grouped_exams[$group_id] : array();
					$group_exam_ids = array();
					$group_show_rank = false;
					$group_rank_criteria = '';

					foreach ($group_exams_list as $e) {
						$group_exam_ids[] = (int)$e->ID;
						if ($e->show_rank === '1') { $group_show_rank = true; }
						if ($e->rank_criteria === 'section_wise') { $group_rank_criteria = 'section_wise'; }
					}

					if ($group_show_rank && !empty($group_exam_ids)) {
						$placeholders = implode(',', array_fill(0, count($group_exam_ids), '%d'));
						$rank_args = array_merge($group_exam_ids, array($student->session_id));
						$rank_where = '';

						if ($group_rank_criteria === 'section_wise') {
							$rank_where = ' AND sr.section_id = %d';
							$rank_args[] = $student->section_id;
						}

						$rank_query = "SELECT sr.ID, SUM(er.obtained_marks) as total_marks FROM " . WLSM_EXAM_RESULTS . " as er
							JOIN " . WLSM_EXAM_PAPERS . " as ep ON ep.ID = er.exam_paper_id
							JOIN " . WLSM_ADMIT_CARDS . " as ac ON ac.ID = er.admit_card_id
							JOIN " . WLSM_STUDENT_RECORDS . " as sr ON sr.ID = ac.student_record_id
							WHERE ep.exam_id IN (" . $placeholders . ") AND sr.session_id = %d AND sr.is_active = 1" . $rank_where . "
							GROUP BY sr.ID
							ORDER BY total_marks DESC";

						$group_results = $wpdb->get_results($wpdb->prepare($rank_query, $rank_args));

						$g_rank = 1;
						$g_found = false;
						$g_total_students = count($group_results);

						foreach ($group_results as $g_row) {
							if ($g_row->ID == $student_id) {
								$group_rank_output = $g_rank . ' / ' . $g_total_students;
								$g_found = true;
								break;
							}
							$g_rank++;
						}
					}
					?>
					<td><span class="wlsm-font-bold"><?php echo esc_html($group_rank_output); ?></span></td>
				<?php endif; ?>
			<?php endforeach; ?>
			<?php if ( $show_total_marks ) : ?>
				<td><span class="wlsm-font-bold"><?php echo esc_html($student_rank . ' / ' . $total_students); ?></span></td>
			<?php endif; ?>
			<td></td>
			<?php if ( '1' === $show_remark ) : ?>
				<td></td>
			<?php endif; ?>
			<?php if ( $show_subject_wise_result_status && $final_exam->exam_type !== 'grade_only' ) { ?>
				<td></td>
			<?php } ?>
		</tr>
	<?php } ?>

	<?php if ($show_overall_result_status && $final_exam->exam_type !== 'grade_only'): ?>
	<?php
		$overall_status_text = '';
		if ( $failed_subjects_count > $maximum_failed_subjects ) {
			$overall_status_text = $overall_fail_text;
		} else {
			if ( $overall_maximum > 0 ) {
				$total_percent = ($overall_obtained / $overall_maximum) * 100;
				if ( $total_percent >= $overall_pass_threshold ) {
					$overall_status_text = $overall_pass_text;
				} else {
					$overall_status_text = $overall_fail_text;
				}
			} else {
				$overall_status_text = $overall_fail_text;
			}
		}

		$cols_before = 1;
		foreach ( $group_order as $group_id ) {
			$cols_before += count( $grouped_exams[ $group_id ] );
			if ( $show_total_marks ) { $cols_before += 1; }
		}

		$cols_after = ($show_total_marks ? 2 : 0) + ($show_remark === '1' ? 1 : 0) + ($show_subject_wise_result_status ? 1 : 0);
	?>
	<tr>
		<th colspan="<?php echo esc_html( $cols_before ); ?>"><?php echo esc_html( $overall_result_status_text ); ?></th>
		<th colspan="<?php echo esc_html( $cols_after ); ?>"><?php echo esc_html( $overall_status_text ); ?></th>
	</tr>
	<?php endif; ?>
</tbody>

<br>
<table></table>
	<div class="row">
			<?php if ($school_signature): ?>
			<div class="col">
				<div class="text-left">
					<?php if ( ! empty( $school_signature ) ) { ?>
						<img src="<?php echo esc_url( wp_get_attachment_url( $school_signature ) ); ?>" class="" width="30%" >
					<?php } ?>
					<br>
					<span><?php esc_html_e( 'Principal Signature', 'school-management' ); ?></span>
				</div>
			</div>
			<?php endif ?>


			<?php if ($teacher_signature): ?>
				<div class="col">
					<div class="text-right">
						<?php if ( ! empty( $teacher_signature ) ) { ?>
							<img src="<?php echo esc_url( wp_get_attachment_url( $teacher_signature ) ); ?>" class="" width="30%" >
						<?php } ?>
						<br>
						<span><?php esc_html_e( 'Class Teacher Signature', 'school-management' ); ?></span>
					</div>
				</div>
			<?php endif ?>

		</div>
