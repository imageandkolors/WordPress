<?php
defined( 'ABSPATH' ) || die();

$page_url = WLSM_M_Staff_Class::get_attendance_page_url();

$school_id = $current_school['id'];

$restrict_to_section = WLSM_M_Role::restrict_to_section($current_school);
if ($restrict_to_section) {
	$restrict_to_section_detail = WLSM_M_Staff_Class::get_section_by_id($restrict_to_section);
}

$classes = WLSM_M_Staff_Class::fetch_classes( $school_id );
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-2 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-chart-bar"></i>
				<?php esc_html_e( 'Attendance Report', 'school-management' ); ?>
			</span>
			<span class="float-md-right">
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-arrow-left"></i>&nbsp;
					<?php echo esc_html__( 'Back', 'school-management' ); ?>
				</a>
			</span>
		</div>

		<div class="wlsm-form-section mt-3">
			<div class="form-row align-items-end">

				<?php if ( $restrict_to_section ) { ?>
				<div class="form-group col-md-3">
					<label class="wlsm-font-bold"><?php esc_html_e( 'Class', 'school-management' ); ?>:</label>
					<div class="ml-2">
						<?php echo esc_html( WLSM_M_Class::get_label_text( $restrict_to_section_detail->class_label ) ); ?>
					</div>
					<input type="hidden" name="class_id" id="wlsm_report_class" value="<?php echo esc_attr( $restrict_to_section_detail->class_id ); ?>">
				</div>
				<div class="form-group col-md-3">
					<label class="wlsm-font-bold"><?php esc_html_e( 'Section', 'school-management' ); ?>:</label>
					<div class="ml-2">
						<?php echo esc_html( WLSM_M_Staff_Class::get_section_label_text( $restrict_to_section_detail->section_label ) ); ?>
					</div>
					<input type="hidden" name="section_id" id="wlsm_report_section" value="<?php echo esc_attr( $restrict_to_section ); ?>">
				</div>
				<?php } else { ?>
				<div class="form-group col-md-3">
					<label for="wlsm_report_class" class="wlsm-font-bold">
						<?php esc_html_e( 'Class', 'school-management' ); ?>:
					</label>
					<select name="class_id[]" class="form-control selectpicker"
					        data-nonce="<?php echo esc_attr( wp_create_nonce( 'get-class-sections' ) ); ?>"
					        id="wlsm_report_class" data-live-search="true" multiple data-actions-box="true"
					        title="<?php esc_attr_e( 'Select Classes', 'school-management' ); ?>">
						<?php foreach ( $classes as $class ) { ?>
						<option value="<?php echo esc_attr( $class->ID ); ?>">
							<?php echo esc_html( WLSM_M_Class::get_label_text( $class->label ) ); ?>
						</option>
						<?php } ?>
					</select>
				</div>
				<div class="form-group col-md-3" id="wlsm_report_section_container" style="display: none;">
					<label for="wlsm_report_section" class="wlsm-font-bold">
						<?php esc_html_e( 'Section', 'school-management' ); ?>:
					</label>
					<select name="section_id" class="form-control selectpicker" id="wlsm_report_section"
					        data-live-search="true"
					        title="<?php esc_attr_e( 'All Sections', 'school-management' ); ?>">
					</select>
				</div>
				<?php } ?>

				<div class="form-group col-md-2">
					<label for="wlsm_report_date_from" class="wlsm-font-bold">
						<span class="wlsm-important">*</span> <?php esc_html_e( 'From Date', 'school-management' ); ?>:
					</label>
					<input type="text" class="form-control" id="wlsm_report_date_from"
					       placeholder="<?php esc_attr_e( 'From Date', 'school-management' ); ?>" autocomplete="off">
				</div>
				<div class="form-group col-md-2">
					<label for="wlsm_report_date_to" class="wlsm-font-bold">
						<span class="wlsm-important">*</span> <?php esc_html_e( 'To Date', 'school-management' ); ?>:
					</label>
					<input type="text" class="form-control" id="wlsm_report_date_to"
					       placeholder="<?php esc_attr_e( 'To Date', 'school-management' ); ?>" autocomplete="off">
				</div>
				<div class="form-group col-md-2">
					<label class="wlsm-font-bold d-block">&nbsp;</label>
					<button type="button" class="btn btn-primary btn-block" id="wlsm-attendance-report-btn"
					        data-nonce="<?php echo esc_attr( wp_create_nonce( 'attendance-class-report' ) ); ?>">
						<i class="fas fa-search"></i>&nbsp;
						<?php esc_html_e( 'Generate Report', 'school-management' ); ?>
					</button>
				</div>

			</div>
		</div>

		<div class="wlsm-attendance-report-result mt-3"></div>

	</div>
</div>
