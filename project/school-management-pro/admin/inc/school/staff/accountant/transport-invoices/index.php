<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Transport.php';

$page_url   = WLSM_M_Staff_Accountant::get_transport_invoices_page_url();
$school_id  = $current_school['id'];
$session_id = $current_session['ID'];
$classes    = WLSM_M_Staff_Class::fetch_classes( $school_id );
$routes     = WLSM_M_Staff_Transport::fetch_routes( $school_id );
$nonce      = wp_create_nonce( 'get-transport-students' );

WLSM_Helper::enqueue_datatable_assets();

?>
<div class="row mt-3">
	<div class="col-md-12">
		<div class="wlsm-table-block">

			<!-- Filter form -->
			<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-get-transport-students-form" class="mb-3">
				<input type="hidden" name="get-transport-students" value="<?php echo esc_attr( $nonce ); ?>">
				<input type="hidden" name="action" value="wlsm-get-transport-students">

				<div class="row">
					<div class="col-md-12">
						<div class="h6 text-secondary wlsm-font-bold mb-2">
							<span class="border-bottom"><?php esc_html_e( 'Search Transport Students', 'school-management' ); ?></span>
						</div>
					</div>
				</div>

				<div class="form-row">
					<div class="form-group col-md-3">
						<label for="wlsm_class" class="wlsm-font-bold"><?php esc_html_e( 'Class', 'school-management' ); ?>:</label>
						<select name="class_id" class="form-control selectpicker" id="wlsm_class"
							data-live-search="true"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'get-class-sections' ) ); ?>">
							<option value=""><?php esc_html_e( 'Select Class', 'school-management' ); ?></option>
							<?php foreach ( $classes as $class ) : ?>
								<option value="<?php echo esc_attr( $class->ID ); ?>">
									<?php echo esc_html( WLSM_M_Class::get_label_text( $class->label ) ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="form-group col-md-3">
						<label for="wlsm_section" class="wlsm-font-bold"><?php esc_html_e( 'Section', 'school-management' ); ?>:</label>
						<select name="section_id" class="form-control selectpicker wlsm_section" id="wlsm_section"
							data-live-search="true"
							title="<?php esc_attr_e( 'All Sections', 'school-management' ); ?>"
							data-all-sections="1"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'get-section-students' ) ); ?>">
						</select>
					</div>
					<div class="form-group col-md-3">
						<label for="wlsm_transport_status" class="wlsm-font-bold"><?php esc_html_e( 'Status', 'school-management' ); ?>:</label>
						<select name="status" class="form-control selectpicker" id="wlsm_transport_status"
							data-live-search="false"
							title="<?php esc_attr_e( 'All Students', 'school-management' ); ?>">
							<option value=""><?php esc_html_e( 'All Students', 'school-management' ); ?></option>
							<option value="no_transport"><?php esc_html_e( 'No Transport Assigned', 'school-management' ); ?></option>
							<option value="none_generated"><?php esc_html_e( 'No Invoices Generated', 'school-management' ); ?></option>
							<option value="partial"><?php esc_html_e( 'Partially Generated', 'school-management' ); ?></option>
							<option value="all_generated"><?php esc_html_e( 'Fully Generated', 'school-management' ); ?></option>
						</select>
					</div>
					<div class="form-group col-md-3 d-flex align-items-end">
						<button type="button" class="btn btn-sm btn-primary" id="wlsm-get-transport-students-btn">
							<i class="fas fa-search"></i>&nbsp;<?php esc_html_e( 'Get Students', 'school-management' ); ?>
						</button>
					</div>
				</div>
			</form>

			<!-- Students DataTable -->
			<table class="table table-hover table-bordered" id="wlsm-transport-students-table" style="width:100%;">
				<thead>
					<tr class="text-white bg-primary">
						<th scope="col"><?php esc_html_e( 'Student Name', 'school-management' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Enrollment No.', 'school-management' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Class / Section', 'school-management' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Route / Vehicle', 'school-management' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Fare/Month', 'school-management' ); ?></th>
						<th scope="col" class="text-center"><?php esc_html_e( 'Paid', 'school-management' ); ?></th>
						<th scope="col" class="text-center"><?php esc_html_e( 'Unpaid', 'school-management' ); ?></th>
						<th scope="col" class="text-center"><?php esc_html_e( 'Not Generated', 'school-management' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management' ); ?></th>
					</tr>
				</thead>
			</table>

		</div>
	</div>
</div>

<!-- Assign Route Modal -->
<div class="modal fade" id="wlsm-assign-route-modal" tabindex="-1" role="dialog" aria-labelledby="assignRouteModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="assignRouteModalLabel"><?php esc_html_e( 'Assign Route to Student', 'school-management' ); ?></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<form id="wlsm-assign-route-form">
				<div class="modal-body">
					<input type="hidden" name="action" value="wlsm-assign-student-transport">
					<input type="hidden" name="wlsm-assign-student-transport" value="<?php echo esc_attr( wp_create_nonce( 'wlsm-assign-student-transport' ) ); ?>">
					<input type="hidden" name="student_record_id" id="wlsm-assign-student-id">

					<div class="form-group">
						<label class="font-weight-bold"><?php esc_html_e( 'Student', 'school-management' ); ?>:</label>
						<p id="wlsm-assign-student-name-display" class="form-control-plaintext font-weight-bold text-dark"></p>
					</div>

					<div class="form-group">
						<label for="wlsm-assign-route" class="font-weight-bold"><?php esc_html_e( 'Select Route', 'school-management' ); ?></label>
						<select name="route_id" id="wlsm-assign-route" class="form-control selectpicker" data-live-search="true"
							data-nonce="<?php echo esc_attr( wp_create_nonce( 'wlsm-generate-transport-invoices' ) ); ?>" required>
							<option value=""><?php esc_html_e( '— Select Route —', 'school-management' ); ?></option>
							<?php foreach ( $routes as $route ) : ?>
								<option value="<?php echo esc_attr( $route->ID ); ?>"><?php echo esc_html( $route->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="form-group">
						<label for="wlsm-assign-vehicle" class="font-weight-bold"><?php esc_html_e( 'Select Vehicle (Fare)', 'school-management' ); ?></label>
						<select name="route_vehicle_id" id="wlsm-assign-vehicle" class="form-control selectpicker" data-live-search="true" required disabled>
							<option value=""><?php esc_html_e( '— Select Route First —', 'school-management' ); ?></option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal"><?php esc_html_e( 'Cancel', 'school-management' ); ?></button>
					<button type="submit" class="btn btn-primary" id="wlsm-assign-route-submit"><?php esc_html_e( 'Assign Route', 'school-management' ); ?></button>
				</div>
			</form>
		</div>
	</div>
</div>

