<?php
defined( 'ABSPATH' ) || die();

$current_school_user = WLSM_M_Role::can( 'manage_id_cards' );
if ( ! $current_school_user ) {
	die();
}
$current_school = $current_school_user['school'];
$school_id      = $current_school['id'];

$search_fields     = WLSM_Helper::search_field_list();
$classes           = WLSM_M_Staff_Class::fetch_classes( $school_id );
$id_card_templates = WLSM_M_Staff_General::fetch_id_cards( $school_id );

$permissions         = $current_school['permissions'];
$can_delete_students = WLSM_M_Role::check_permission( array( 'delete_students' ), $permissions );

$gdpr_enable = get_option( 'wlsm_gdpr_enable' );
?>
<div class="wlsm container-fluid">
	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-id-card"></i>
					<?php esc_html_e( 'Print ID Cards', 'school-management' ); ?>
				</span>
				<span class="float-md-right">
				<button type="button" class="btn btn-sm btn-outline-light wlsm-print-bulk-id-cards-btn" id="wlsm-print-bulk-id-cards-btn">
						<i class="fas fa-print"></i>&nbsp;
						<?php esc_html_e( 'Print Selected Cards', 'school-management' ); ?>
					</button>
				</span>
			</div>
			<div class="wlsm-students-block wlsm-form-section">
				<div class="row">
					<div class="col-md-12">
						<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-get-students-print-form" class="mb-3">
							<?php
							$nonce_action = 'get-students';
							?>
							<?php $nonce = wp_create_nonce( $nonce_action ); ?>
							<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

							<input type="hidden" name="action" value="wlsm-get-students-printing">

							<div class="wlsm-filter-block p-1 border-bottom mb-3">
								<div class="form-row align-items-end">
									<div class="form-group col-md-3">
										<label for="wlsm_class" class="wlsm-font-bold">
											<?php esc_html_e( 'Class', 'school-management' ); ?>:
										</label>
										<select name="class_id" class="form-control selectpicker" data-nonce="<?php echo esc_attr( wp_create_nonce( 'get-class-sections' ) ); ?>" id="wlsm_class" data-live-search="true">
											<option value=""><?php esc_html_e( 'Select Class', 'school-management' ); ?></option>
											<?php foreach ( $classes as $class ) { ?>
											<option value="<?php echo esc_attr( $class->ID ); ?>">
												<?php echo esc_html( WLSM_M_Class::get_label_text( $class->label ) ); ?>
											</option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group col-md-3">
										<label for="wlsm_section" class="wlsm-font-bold">
											<?php esc_html_e( 'Section', 'school-management' ); ?>:
										</label>
										<select name="section_id" class="form-control selectpicker" id="wlsm_section" data-live-search="true" title="<?php esc_attr_e( 'All Sections', 'school-management' ); ?>" data-all-sections="1">
										</select>
									</div>
									<div class="form-group col-md-3">
										<label for="wlsm_id_card_template" class="wlsm-font-bold">
											<?php esc_html_e( 'ID Card Template', 'school-management' ); ?>:
										</label>
										<select name="id_card_template" class="form-control selectpicker" id="wlsm_id_card_template">
											<option value=""><?php esc_html_e( 'Default Layout', 'school-management' ); ?></option>
											<?php foreach ( $id_card_templates as $template ) { ?>
											<option value="<?php echo esc_attr( $template->ID ); ?>">
												<?php echo esc_html( $template->label ); ?>
											</option>
											<?php } ?>
										</select>
									</div>
									<div class="form-group col-md-3">
										<label for="wlsm_page_orientation" class="wlsm-font-bold">
											<?php esc_html_e( 'Page Orientation', 'school-management' ); ?>:
										</label>
										<select name="page_orientation" class="form-control selectpicker" id="wlsm_page_orientation">
											<option value="landscape"><?php esc_html_e( 'Landscape', 'school-management' ); ?></option>
											<option value="portrait"><?php esc_html_e( 'Portrait', 'school-management' ); ?></option>
										</select>
									</div>
									<div class="form-group col-md-3">
										<button type="button" class="btn btn-primary btn-block" id="wlsm-get-students-print-btn">
											<i class="fas fa-search"></i>&nbsp;
											<?php esc_html_e( 'Get Students', 'school-management' ); ?>
										</button>
									</div>
								</div>
							</div>
						</form>
					</div>
				</div>

				<div class="row mb-3">
					<div class="col-md-12">
						<button type="button" class="btn btn-sm btn-success wlsm-print-bulk-id-cards-btn" id="wlsm-print-bulk-id-cards-btn-linked">
							<i class="fas fa-print"></i>&nbsp;
							<?php esc_html_e( 'Print Selected Cards', 'school-management' ); ?>
						</button>
					</div>
				</div>

				<div class="wlsm-table-block wlsm-form-section">
					<div class="w-100">
						<table class="table table-hover table-bordered" id="wlsm-print-id-cards-table">
							<thead>
								<tr class="text-white bg-primary">
									<th class="text-center"><input type="checkbox" name="select_all" id="wlsm-select-all" value="1"></th>
									<th scope="col"><?php esc_html_e( 'Student Name', 'school-management' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Admission Number', 'school-management' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Class', 'school-management' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Section', 'school-management' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Roll Number', 'school-management' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Father\'s Name', 'school-management' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Phone', 'school-management' ); ?></th>
									<th scope="col"><?php esc_html_e( 'Action', 'school-management' ); ?></th>
								</tr>
							</thead>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Bulk Print Form (Hidden) -->
<form id="wlsm-print-selected-id-cards-form" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" style="display: none;">
	<input type="hidden" name="action" value="wlsm-print-selected-id-cards">
	<input type="hidden" name="id_card_template_id" id="wlsm-print-template-id">
	<input type="hidden" name="student_ids" id="wlsm-print-student-ids">
	<input type="hidden" name="page_orientation" id="wlsm-print-page-orientation">
	<?php wp_nonce_field( 'print-selected-id-cards', 'print-selected-id-cards-nonce' ); ?>
</form>
