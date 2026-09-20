<?php
defined('ABSPATH') || die();

$subjects           = WLSM_M_Staff_Class::get_class_subjects_students($school_id, $class_id, $student_id);
$current_session_id = get_option( 'wlsm_current_session' );
$student            = WLSM_M_Staff_General::get_student($school_id, $current_session_id, $student_id);
$student_section_id = $student->section_id;


if ($academic_report) {
	$exams = WLSM_M_Staff_Examination::get_class_school_exams_academic_report($school_id, $class_school_id,$report_id);

} else {
	$exams = WLSM_M_Staff_Examination::get_class_school_exams($school_id, $class_school_id);
}

// Filter exams by selected groups for multi-group reports
if ( ! empty( $is_multi_group_report ) && ! empty( $selected_group_ids ) ) {
	$filtered_exams = array();
	foreach ( $exams as $exam ) {
		if ( in_array( (int) $exam->exam_group, $selected_group_ids, true ) ) {
			$filtered_exams[] = $exam;
		}
	}
	$exams = $filtered_exams;
}

// Filter exams by selected exams for academic reports
if ( ! empty( $academic_report ) && ! empty( $academic_report->exams ) ) {
	$selected_exam_ids = json_decode( $academic_report->exams, true );
	if ( is_array( $selected_exam_ids ) ) {
		$selected_exam_ids = array_map( 'absint', $selected_exam_ids );
		$filtered_exams = array();
		foreach ( $exams as $exam ) {
			if ( in_array( (int) $exam->ID, $selected_exam_ids, true ) ) {
				$filtered_exams[] = $exam;
			}
		}
		$exams = $filtered_exams;
	}
}

// Filter subjects to only include those with exam results
if ( ! empty( $exams ) ) {
	$subjects_with_results = array();
	foreach ( $subjects as $subject ) {
		$has_result = false;
		foreach ( $exams as $exam ) {
			$exam_result = WLSM_M_Staff_Examination::get_exam_result_by_subject_code( $school_id, $exam->ID, $student_id, $subject->code );
			if ( $exam_result ) {
				$has_result = true;
				break;
			}
		}
		if ( $has_result ) {
			$subjects_with_results[] = $subject;
		}
	}
	$subjects = $subjects_with_results;
}

$exam_groups        = WLSM_M_Staff_Examination::get_class_school_exam_groups_assessment($school_id, $class_school_id);
$exam_without_group = WLSM_M_Staff_Examination::exam_without_group($school_id, $class_school_id);

// For academic reports with a selected group, filter exam_groups to only show that group
if ( ! empty( $academic_report ) && ! empty( $academic_report->exam_group ) ) {
	$selected_group_id = (int) $academic_report->exam_group;
	$group_details = WLSM_M_Staff_Examination::fetch_exams_group($school_id, $selected_group_id);
	$filtered_groups = array();
	if($group_details && in_array($group_details->label, $exam_groups)){
		$filtered_groups[] = $group_details->label;
	}
	$exam_groups = $filtered_groups;
}

$total_exam_groups = count($exam_groups);
$total_exams       = count($exams);
if (!$exam_without_group && ($total_exam_groups > 1) && ($total_exam_groups < $total_exams)) {
	$show_exam_groups = true;
} else {
	$show_exam_groups = false;
}
$show_remark = '0'; // Initialize globally

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
	<?php
	if ($show_exam_groups) {
	$exams = array();
	?>
		<tr class="wlsm-text-center text-center">
			<th></th>
			<?php
			foreach ($exam_groups as $exam_group) {
				$exam_group_exams = WLSM_M_Staff_Examination::get_exam_group_exams($school_id, $class_school_id, $exam_group);
				$exam_groups_colspan = count($exam_group_exams);

				$exams = array_merge($exams, $exam_group_exams);
			?>
				<th colspan="<?php echo esc_html($exam_groups_colspan); ?>"><?php echo esc_html(stripslashes($exam_group)); ?></th>
			<?php
			}
			?>
			<th></th>
		</tr>
	<?php
	}
	?>
	<tr>
		<th><?php esc_html_e('Subject', 'school-management'); ?></th>
		<?php

		$exam_ids           = array();
		$psychomotor_enable = array();
		$psychomotor        = array();
		$show_remark        = '0'; // Initialize show_remark


		foreach ($exams as $key => $exam) {
			$teacher_signature = $exam->teacher_signature;

			$show_rank   = $exam->show_rank;
			$rank_criteria = $exam->rank_criteria;
			$show_total_marks = $exam->enable_total_marks;
			if ($exam->show_remark === '1') {
				$show_remark = '1';
			}
		?>
			<th><?php echo esc_html(stripslashes($exam->exam_title)); ?></th>
		<?php
			array_push($exam_ids, $exam->ID);
			array_push($psychomotor_enable, $exam->psychomotor_analysis);
			$psych =  WLSM_Config::sanitize_psychomotor($exam->psychomotor);
			array_push($psychomotor, $psych);
		}
		?>
		<?php if ($show_total_marks) : ?>
			<th><?php esc_html_e('Total', 'school-management'); ?></th>
		<?php endif ?>
		<?php if ($show_total_marks) : ?>
		<th><?php esc_html_e('Grade', 'school-management'); ?></th>
		<?php endif ?>
		<?php if ($show_remark === '1') : ?>
			<th><?php esc_html_e('Remarks', 'school-management'); ?></th>
		<?php endif ?>

		<?php if ( $show_subject_wise_result_status && $final_exam->exam_type !== 'grade_only' ) { ?>
			<th><?php echo esc_html( $subject_result_status_text ); ?></th>
		<?php } ?>

	</tr>
</thead>
<tbody>
	<?php
	$overall_show_rank     = '0';
	$overall_rank_criteria = '';
	$overall_show_total_marks = false;

	foreach ( $exams as $exam ) {
		if ( '1' === $exam->show_rank ) {
			$overall_show_rank = '1';
		}
		if ( 'section_wise' === $exam->rank_criteria ) {
			$overall_rank_criteria = 'section_wise';
		}
		if ( $exam->enable_total_marks ) {
			$overall_show_total_marks = true;
		}
	}

	$grand_total_maximum = 0;
	$grand_total_obtained = 0;

	foreach ($subjects as $subject) {
	?>
		<tr>
			<td>
				<?php
				printf(
					wp_kses(
						/* translators: 1: subject label, 2: subject code */
						_x('%1$s (%2$s)', 'Subject', 'school-management'),
						array('span' => array('class' => array()))
					),
					esc_html(WLSM_M_Staff_Class::get_subject_label_text($subject->label)),
					esc_html($subject->code)
				);
				?>
			</td>
			<?php
			$total_maximum_marks_subject  = 0;
			$total_obtained_marks_subject = 0;
			$psychomotor_scale = array();
			$grade_subject_percentage = 0;
			$remark = '';
			foreach ($exams as $key => $exam) {
				// Get exam paper with this subject code.
				$exam_result = WLSM_M_Staff_Examination::get_exam_result_by_subject_code($school_id, $exam->ID, $student_id, $subject->code);


				// last exam setting
				$show_rank   = $exam->show_rank;
				$rank_criteria = $exam->rank_criteria;

				$grade_criteria = WLSM_Config::sanitize_grade_criteria( $exam->grade_criteria );
				$enable_overall_grade = $grade_criteria['enable_overall_grade'];
				$marks_grades         = $grade_criteria['marks_grades'];
			?>
				<td>
					<?php
					if ($exam_result) {
						// Check exam type
						$exam_type = $exam->exam_type ? $exam->exam_type : 'marks_grades';

						$maximum_marks  = $exam_result->maximum_marks;
						$obtained_marks = $exam_result->obtained_marks;
						$obtained_grade = $exam_result->obtained_grade; // Get grade
						$remark         = $exam_result->remark;
						$p_scale = $exam_result->scale;
						array_push($psychomotor_scale, $p_scale);

						$total_maximum_marks_subject  += $exam_result->maximum_marks;
						$total_obtained_marks_subject += $exam_result->obtained_marks;

						$grand_total_maximum += $exam_result->maximum_marks;
						$grand_total_obtained += $exam_result->obtained_marks;

						$grade_subject_percentage = WLSM_Config::sanitize_percentage($total_maximum_marks_subject, WLSM_Config::sanitize_marks($total_obtained_marks_subject));
					?>
						<span class="wlsm-font-bold">
							<?php
							if ($exam_type === 'grade_only') {
								echo esc_html(strtoupper($obtained_grade));
							} else {
								echo esc_html($obtained_marks);
							}
							?>
						</span>
					<?php
						// echo ' / ';
						// echo esc_html($maximum_marks);
					} else {
						echo '-';
					}
					?>
				</td>


			<?php
			}
			?>
			<?php if ($show_total_marks) : ?>
				<td>
					<?php
					if ($total_maximum_marks_subject) {
					?>
						<span class="wlsm-font-bold">
							<?php echo esc_html($total_obtained_marks_subject); ?>
						</span>
					<?php
						// echo ' / ';
						// echo esc_html($total_maximum_marks_subject);
					} else {
						echo '-';
					}
					?>
				</td>
			<?php endif ?>

			<?php if ($show_total_marks) : ?>
			<td>
				<?php if (!empty($grade_subject_percentage)) : ?>
					<?php echo esc_html(WLSM_Helper::calculate_grade($marks_grades, $grade_subject_percentage)); ?>
				<?php endif; ?>
			</td>
			<?php endif ?>

			<?php if ($show_remark === '1') : ?>
				<td> <?php echo esc_html($remark); ?></td>
			<?php endif ?>

			<?php
			$subject_status_text = '';
			if ( $show_subject_wise_result_status && $final_exam->exam_type !== 'grade_only' ) {
				if ( $total_maximum_marks_subject > 0 ) {
					$subject_percent = ($total_obtained_marks_subject / $total_maximum_marks_subject) * 100;
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
	}
	?>
	<?php
	$percentage_data = array(); // Initialize to avoid undefined variable warning.
	if ($show_total_marks) : ?>
		<tr>
			<th><?php esc_html_e('Total', 'school-management'); ?></th>
			<?php
			$total_percentage_obtained = 0;
			$total_percentage_maximum  = 0;
			foreach ($exams as $key => $exam) {
				$exam_result = WLSM_M_Staff_Examination::get_exam_results_total_by_student_id($school_id, $exam->ID, $student_id);
				$percentage_row = array();
			?>
				<td>
					<?php

					if ($exam_result->total_marks) {
						$total_marks    = $exam_result->total_marks;
						$obtained_marks = $exam_result->obtained_marks;

						$percentage_row['value'] = WLSM_Config::sanitize_percentage($total_marks, $obtained_marks);
						$percentage_row['text']  = WLSM_Config::get_percentage_text($total_marks, $obtained_marks);

						$total_percentage_obtained += $obtained_marks;
						$total_percentage_maximum  += $total_marks;
					?>
						<span class="wlsm-font-bold">
							<?php echo esc_html($exam_result->obtained_marks); ?>
						</span>
					<?php
						echo ' / ';
						echo esc_html($exam_result->total_marks);
					} else {
						$percentage_row['value'] = 0;
						$percentage_row['text']  = '-';
						echo '-';
					}

					array_push($percentage_data,  $percentage_row);
					?>
				</td>


			<?php
			}
			?>
			<th>
				<?php
				if ($total_percentage_maximum) {
					echo esc_html($total_percentage_obtained);
					echo ' / ';
					echo esc_html($total_percentage_maximum);
				} else {
					echo '-';
				}
				?>
			</th>
			<td></td>
			<?php if ($show_remark === '1') : ?>
			<td></td>
			<?php endif; ?>
			<?php if ( $show_subject_wise_result_status && $final_exam->exam_type !== 'grade_only' ) { ?>
			<td></td>
			<?php } ?>

		</tr>
	<?php endif ?>

	<?php if ($show_total_marks) : // Only show Percentage if Total Marks are enabled ?>
	<tr>
		<th><?php esc_html_e('Percentage', 'school-management'); ?></th>
		<?php
		foreach ($percentage_data as $percentage) {
		?>
			<td>
				<span class="wlsm-font-bold"><?php echo esc_html($percentage['text']); ?></span>
			</td>
		<?php
		}
		if ($total_percentage_maximum) {
			$total_percentage_value = WLSM_Config::sanitize_percentage($total_percentage_maximum, $total_percentage_obtained);
			$total_percentage_text  = WLSM_Config::get_percentage_text($total_percentage_maximum, $total_percentage_obtained);
		}

		$grade_percentage = WLSM_Config::sanitize_percentage( $total_maximum_marks_subject, WLSM_Config::sanitize_marks( $total_obtained_marks_subject ) );
		?>
		<th>
			<?php
			if ($total_percentage_value) {
				echo esc_html($total_percentage_text);
			} else {
				echo '-';
			}
			?>
		</th>
		<th><?php echo esc_html( WLSM_Helper::calculate_grade( $marks_grades, $grade_percentage ) ); ?></th>
		<!-- <td></td> -->

		<?php if ($show_remark === '1') : ?>
		<td></td>
		<?php endif; ?>
		<?php if ( $show_subject_wise_result_status && $final_exam->exam_type !== 'grade_only' ) { ?>
		<td></td>
		<?php } ?>

	</tr>
	<?php endif; ?>

	<?php if ($show_overall_result_status && $final_exam->exam_type !== 'grade_only'): ?>
	<?php
		$overall_status_text = '';
		if ( $failed_subjects_count > $maximum_failed_subjects ) {
			$overall_status_text = $overall_fail_text;
		} else {
			if ( $grand_total_maximum > 0 ) {
				$total_percent = ($grand_total_obtained / $grand_total_maximum) * 100;
				if ( $total_percent >= $overall_pass_threshold ) {
					$overall_status_text = $overall_pass_text;
				} else {
					$overall_status_text = $overall_fail_text;
				}
			} else {
				$overall_status_text = $overall_fail_text;
			}
		}
	?>
	<tr>
		<th colspan="<?php echo esc_html( count($exams) + ($show_total_marks ? 1 : 0) + 1 ); ?>"><?php echo esc_html( $overall_result_status_text ); ?></th>
		<th colspan="<?php echo esc_html( ($show_total_marks ? 1 : 0) + ($show_remark === '1' ? 1 : 0) + ($show_subject_wise_result_status ? 1 : 0) ); ?>"><?php echo esc_html( $overall_status_text ); ?></th>
	</tr>
	<?php endif; ?>
	<?php
	// Check if this is a grade only exam to force hide rank
	// $is_grade_only = (isset($exam) && $exam->exam_type === 'grade_only');
	if ($overall_show_rank === '1' && $overall_show_total_marks) { ?>
		<?php
		global $wpdb;
		if ($overall_rank_criteria === 'section_wise' && isset($student_section_id)) {
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

		$student_ranks = array();
		$rank = 1;

		foreach ($results as $row) {
			$student_ranks[$row->student_id] = $rank++;
		}
		?>
		<tr>
			<th><?php esc_html_e('Position/Rank', 'school-management'); ?></th>
			<?php

			$exams_total = $total_percentage_obtained;

			 $student_overall_rank = isset($overall_ranks[$student_id]) ? $overall_ranks[$student_id] : '-';
			 $exam_ids = array(); // Initialize an array to store exam IDs
			foreach ($exams as $key => $exam) {
				$exam_ids[] = $exam->ID;
				$student_rank = '-';
				$admit_card   = WLSM_M_Staff_Examination::get_admit_card_by_exam_student($school_id, $exam->ID, $student_id);

				if ($admit_card) {
					$section_id_param = null;
					if ($exam->rank_criteria === 'section_wise') {
						$section_id_param = $student_section_id;
					}
					$student_rank = WLSM_M_Staff_Examination::calculate_exam_ranks($school_id, $exam->ID, array(), $admit_card->ID, true, $section_id_param);
				}
			?>
				<td>
					<span class="wlsm-font-bold"><?php echo esc_html($student_rank); ?></span>
				</td>
			<?php
			}

			 // Fetch the overall rank from the student_ranks array
			 $student_overall_rank = isset($student_ranks[$student_id]) ? $student_ranks[$student_id] : '-';
			 if ($student_overall_rank !== '-') {
				 $student_overall_rank = $student_overall_rank . ' / ' . count($student_ranks);
			 }
			 ?>
			 <td>
				 <span class="wlsm-font-bold"><?php echo esc_html($student_overall_rank); ?></span>
			 </td>
			 <td></td>

		</tr>
	<?php } ?>
</tbody>

<?php if ($psychomotor_enable[0] === '1') : ?>

	<table class="table table-bordered wlsm-view-exam-results-table">
		<thead>
			<tr>
				<th colspan="10"> <?php esc_html_e('Psychomotor Analysis', 'school-management'); ?></th>
			</tr>

		</thead>

		<tbody>
			<tr>
				<?php foreach ($psychomotor[0]['psych'] as $key => $value) : ?>
					<td><?php echo $value; ?></td>
				<?php endforeach ?>
			</tr>
			<tr>
				<?php
				$psychomotor_scale = unserialize($psychomotor_scale[0]);
				foreach ($psychomotor_scale as $value) : ?>
					<td> <?php echo $value; ?> </td>
				<?php endforeach ?>
			</tr>
		</tbody>
	</table>

	<table class="table table-bordered wlsm-view-exam-results-table">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e('Scale', 'school-management'); ?></th>
				<th scope="col"><?php esc_html_e('Definition', 'school-management'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php $s = 1; ?>
			<?php foreach ($psychomotor[0]['def'] as $key => $value) : ?>
				<tr>
					<th scope="row"><?php echo $s++; ?></th>
					<td><?php echo $value; ?></td>
				</tr>
			<?php endforeach ?>

		</tbody>
	</table>

<?php endif ?>
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
