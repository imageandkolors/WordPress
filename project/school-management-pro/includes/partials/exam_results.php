<?php
defined( 'ABSPATH' ) || die();

$show_marks_grades = count( $marks_grades );

$student_section_id = null;
if ( isset( $exam->rank_criteria ) && 'section_wise' === $exam->rank_criteria ) {
    $student_section_id = $admit_card->section_id;
}
$student_rank = WLSM_M_Staff_Examination::calculate_exam_ranks( $school_id, $exam_id, array(), $admit_card->ID, true, $student_section_id );

// Threshold Settings
$show_overall_result_status      = isset($exam->show_overall_result_status) ? $exam->show_overall_result_status : 0;
$show_subject_wise_result_status = isset($exam->show_subject_wise_result_status) ? $exam->show_subject_wise_result_status : 0;
$overall_result_status_text      = isset($exam->overall_result_status_text) && !empty($exam->overall_result_status_text) ? $exam->overall_result_status_text : 'Overall Result';
$subject_result_status_text      = isset($exam->subject_result_status_text) && !empty($exam->subject_result_status_text) ? $exam->subject_result_status_text : 'Status';
$subject_pass_threshold          = isset($exam->subject_pass_threshold) ? $exam->subject_pass_threshold : 0;
$overall_pass_threshold          = isset($exam->overall_pass_threshold) ? $exam->overall_pass_threshold : 0;
$overall_pass_text               = isset($exam->overall_pass_text) && !empty($exam->overall_pass_text) ? $exam->overall_pass_text : 'Pass';
$overall_fail_text               = isset($exam->overall_fail_text) && !empty($exam->overall_fail_text) ? $exam->overall_fail_text : 'Fail';
$subject_pass_text               = isset($exam->subject_pass_text) && !empty($exam->subject_pass_text) ? $exam->subject_pass_text : 'Pass';
$subject_fail_text               = isset($exam->subject_fail_text) && !empty($exam->subject_fail_text) ? $exam->subject_fail_text : 'Fail';

$maximum_failed_subjects        = isset($exam->maximum_failed_subjects) ? $exam->maximum_failed_subjects : 0;

$failed_subjects_count = 0;
?>
<thead>
	<tr>
		<?php if ($show_subject_code): ?>
		<th><?php esc_html_e( 'Paper Code', 'school-management' ); ?></th>
		<?php endif ?>
		<th><?php esc_html_e( 'Subject Name', 'school-management' ); ?></th>
		<?php if ($show_subject_type): ?>
		<th><?php esc_html_e( 'Subject Type', 'school-management' ); ?></th>
		<?php endif ?>

		<?php if ($enable_max_marks): ?>
			<th><?php esc_html_e( 'Maximum Marks', 'school-management' ); ?></th>
		<?php endif ?>

		<?php if ($enable_obtained): ?>
			<th><?php esc_html_e( 'Obtained Marks', 'school-management' ); ?></th>
		<?php endif ?>

		<?php if ( $show_marks_grades ) { ?>
		<th><?php esc_html_e( 'Grade', 'school-management' ); ?></th>
		<?php } ?>
        
		<?php if ( $show_subject_wise_result_status && $exam->exam_type !== 'grade_only' ) { ?>
		<th><?php echo esc_html( $subject_result_status_text ); ?></th>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php
	$total_maximum_marks  = 0;
	$total_obtained_marks = 0;

	foreach ( $exam_papers as $key => $exam_paper ) {
		if ( $admit_card && isset( $exam_results[ $exam_paper->ID ] ) ) {
			$exam_result    = $exam_results[ $exam_paper->ID ];
			$obtained_marks = $exam_result->obtained_marks;
			$obtained_grade = $exam_result->obtained_grade;
		} else {
			$obtained_marks = '';
			$obtained_grade = '';
		}

		$percentage = WLSM_Config::sanitize_percentage( $exam_paper->maximum_marks, WLSM_Config::sanitize_marks( $obtained_marks ) );
		$teacher_remark = $exam_result->teacher_remark;
		$school_remark = $exam_result->school_remark;
		$p_scale = $exam_result->scale;

		$total_maximum_marks  += $exam_paper->maximum_marks;
		$total_obtained_marks += WLSM_Config::sanitize_marks( $obtained_marks );

		$subject_status_text = '';
		if ( $show_subject_wise_result_status && $exam->exam_type !== 'grade_only' ) {
			if ( is_numeric($obtained_marks) && $exam_paper->maximum_marks > 0 ) {
				$subject_percent = ($obtained_marks / $exam_paper->maximum_marks) * 100;
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
		}
	?>
	<tr>
		<?php if ($show_subject_code): ?>
		<td><?php echo esc_html( $exam_paper->paper_code ); ?></td>
		<?php endif ?>
		<td><?php echo esc_html( stripcslashes( $exam_paper->subject_label ) ); ?></td>
		<?php if ($show_subject_type): ?>
		<td><?php echo esc_html( WLSM_Helper::get_subject_type_text( strtolower($exam_paper->subject_type) ) ); ?></td>
		<?php endif ?>
		<?php if ($enable_max_marks): ?>
			<td><?php echo esc_html( $exam_paper->maximum_marks ); ?></td>
		<?php endif ?>
		<?php if ($enable_obtained): ?>
			<td><?php echo esc_html( $obtained_marks ); ?></td>
		<?php endif ?>
		<?php if ( $show_marks_grades ) { ?>
		<td>
			<?php
			if ( $exam->exam_type === 'grade_only' ) {
				echo esc_html( strtoupper( $obtained_grade ) );
			} else {
				echo esc_html( WLSM_Helper::calculate_grade( $marks_grades, $percentage ) );
			}
			?>
		</td>
		<?php } ?>

		<?php if ( $show_subject_wise_result_status && $exam->exam_type !== 'grade_only' ) { ?>
		<td><strong><?php echo esc_html( $subject_status_text ); ?></strong></td>
		<?php } ?>
	</tr>
	<?php
	}

	$total_percentage = WLSM_Config::sanitize_percentage( $total_maximum_marks, $total_obtained_marks );
	$p_scale = unserialize($p_scale);

	// Calculate total columns
	$cols = 1; // Subject Name
	if ($show_subject_code) { $cols++; }
	if ($show_subject_type) { $cols++; }
	if ($enable_max_marks) { $cols++; }
	if ($enable_obtained) { $cols++; }
	if ($show_marks_grades) { $cols++; }
	if ($show_subject_wise_result_status && $exam->exam_type !== 'grade_only') { $cols++; }
	?>
	<?php if ($enable_max_marks): ?>
		<tr>
		<th colspan="<?php echo esc_html( 1 + ($show_subject_code ? 1 : 0) + ($show_subject_type ? 1 : 0) ); ?>"><?php esc_html_e( 'Total', 'school-management' ); ?></th>
		<th><?php echo esc_html( $total_maximum_marks ); ?></th>
		<th><?php echo esc_html( $total_obtained_marks ); ?></th>
		<?php if ( $show_marks_grades ) { ?>
		<th></th>
		<?php } ?>
		<?php if ( $show_subject_wise_result_status && $exam->exam_type !== 'grade_only' ) { ?>
		<th></th>
		<?php } ?>
	</tr>
		<?php endif ?>

	<?php if ($enable_max_marks): // Only show percentage if max marks enabled ?>
	<tr>
		<th colspan="<?php echo esc_html( $cols - ($show_marks_grades ? 2 : 1) ); ?>"><?php esc_html_e( 'Percentage', 'school-management' ); ?></th>
		<th><?php echo esc_html( WLSM_Config::get_percentage_text( $total_maximum_marks, $total_obtained_marks ) ); ?></th>
		<?php if ( $show_marks_grades ) { ?>
		<th>
		<?php
		if ( $enable_overall_grade ) {
			echo esc_html( WLSM_Helper::calculate_grade( $marks_grades, $total_percentage ) );
		}
		?>
		</th>
		<?php } ?>
		<?php if ( $show_subject_wise_result_status && $exam->exam_type !== 'grade_only' ) { ?>
		<th></th>
		<?php } ?>
	</tr>
	<?php endif; ?>

	<?php if ($show_overall_result_status && $exam->exam_type !== 'grade_only'): ?>
	<?php
		$overall_status_text = '';
		if ( $failed_subjects_count > $maximum_failed_subjects ) {
			$overall_status_text = $overall_fail_text;
		} else {
			if ( $total_maximum_marks > 0 ) {
				$total_percent = ($total_obtained_marks / $total_maximum_marks) * 100;
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
		<th colspan="<?php echo esc_html( $cols - ($show_marks_grades ? 2 : 1) - ($show_subject_wise_result_status ? 1 : 0) ); ?>"><?php echo esc_html( $overall_result_status_text ); ?></th>
		<th colspan="<?php echo esc_html( 1 + ($show_subject_wise_result_status ? 1 : 0) + ($show_marks_grades ? 1 : 0) ); ?>"><?php echo esc_html( $overall_status_text ); ?></th>
	</tr>
	<?php endif; ?>

    <?php if ($show_rank === '1' && ($enable_max_marks || !$show_marks_grades)){ // Hide rank if grade only basically, or fix colspan ?>
	<tr>
		<th colspan="<?php echo esc_html( $cols - ($show_marks_grades ? 2 : 1) ); ?>"><?php esc_html_e( 'Rank', 'school-management' ); ?></th>
		<th colspan="<?php echo esc_html( $show_marks_grades ? '2' : '1' ); ?>"><?php echo esc_html( $student_rank ); ?></th>
	</tr>
    <?php }?>

	<?php if ($show_eremark=== '1'){ ?>
	<tr>
		<td colspan="<?php echo esc_html( $cols/2 ); ?>"><strong><?php esc_html_e( 'Teacher Remark :', 'school-management' ); ?></strong>  <?php  echo $teacher_remark; ?></td>
		<td colspan="<?php echo esc_html( $cols/2 + ($cols%2) ); ?>"><strong><?php esc_html_e( 'School Remark :', 'school-management' ); ?></strong>  <?php  echo $school_remark; ?></td>
	</tr>
	<?php }?>

	<?php if ($psychomotor_enable === '1'): ?>

	<table class="table table-bordered wlsm-view-exam-results-table">
		<thead>
			<tr>
			<th colspan="10"> <?php esc_html_e( 'Psychomotor Analysis', 'school-management' ); ?></th>
			</tr>

		</thead>

		<tbody>
		<tr>
				<?php foreach ($psychomotor['psych'] as $key => $value): ?>
				<td><?php echo $value; ?></td>
				<?php endforeach ?>
			</tr>
		<tr>
			<?php foreach ($p_scale as $value): ?>
			<td> <?php echo $value; ?> </td>
			<?php endforeach ?>
		</tr>
		</tbody>
	</table>

	<?php endif ?>
</tbody>
