<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Transport.php';

$page_url = WLSM_M_Staff_Accountant::get_transport_invoices_page_url();

$student_id = isset( $_GET['sr_id'] ) ? absint( $_GET['sr_id'] ) : 0;

if ( ! $student_id ) {
	die( esc_html__( 'Invalid student ID.', 'school-management' ) );
}

global $wpdb;
$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

// Fetch student details
$student = $wpdb->get_row( $wpdb->prepare(
	"SELECT sr.ID, sr.name, sr.enrollment_number, sr.admission_number, c.label as class_label, se.label as section_label, ro.name as route_name, v.vehicle_number, rv.ID as route_vehicle_id, ro.fare
	FROM " . WLSM_STUDENT_RECORDS . " as sr
	JOIN " . WLSM_SESSIONS . " as ss ON ss.ID = sr.session_id
	JOIN " . WLSM_SECTIONS . " as se ON se.ID = sr.section_id
	JOIN " . WLSM_CLASS_SCHOOL . " as cs ON cs.ID = se.class_school_id
	JOIN " . WLSM_CLASSES . " as c ON c.ID = cs.class_id
	LEFT JOIN " . WLSM_ROUTE_VEHICLE . " as rv ON rv.ID = sr.route_vehicle_id
	LEFT JOIN " . WLSM_ROUTES . " as ro ON ro.ID = rv.route_id
	LEFT JOIN " . WLSM_VEHICLES . " as v ON v.ID = rv.vehicle_id
	WHERE sr.ID = %d AND cs.school_id = %d AND ss.ID = %d",
	$student_id, $school_id, $session_id
) );

if ( ! $student ) {
	die( esc_html__( 'Student not found.', 'school-management' ) );
}

$routes = WLSM_M_Staff_Transport::fetch_routes( $school_id );
$nonce  = wp_create_nonce( 'get-transport-invoices' );

WLSM_Helper::enqueue_datatable_assets();
?>

<div class="row">
	<div class="col-md-12">

		<div class="wlsm-table-block">
			<!-- Student Info -->
			<div class="wlsm-form-section mb-4">
				<div class="row">
					<div class="col-md-12">
						<div class="wlsm-form-sub-heading wlsm-font-bold">
							<i class="fas fa-user-graduate"></i> <?php esc_html_e( 'Student Transport Detail', 'school-management' ); ?> :
							<?php echo esc_html( $student->name ); ?>
						</div>
					</div>
				</div>
				<div class="row mt-3">
					<div class="col-md-3 mb-2">
						<strong><?php esc_html_e( 'Enrollment No:', 'school-management' ); ?></strong><br>
						<span><?php echo esc_html( $student->enrollment_number ); ?></span>
					</div>
					<div class="col-md-3 mb-2">
						<strong><?php esc_html_e( 'Class / Section:', 'school-management' ); ?></strong><br>
						<span><?php echo esc_html( $student->class_label . ' / ' . $student->section_label ); ?></span>
					</div>
					<div class="col-md-3 mb-2">
						<strong><?php esc_html_e( 'Transport Route:', 'school-management' ); ?></strong><br>
						<?php if ( $student->route_vehicle_id ) : ?>
							<span><?php echo esc_html( $student->route_name . ' (' . $student->vehicle_number . ')' ); ?></span>
						<?php else : ?>
							<span class="badge badge-danger"><?php esc_html_e( 'Not Assigned', 'school-management' ); ?></span>
						<?php endif; ?>
					</div>
					<div class="col-md-3 mb-2">
						<strong><?php esc_html_e( 'Fare / Month:', 'school-management' ); ?></strong><br>
						<?php if ( $student->route_vehicle_id ) : ?>
							<span class="text-success font-weight-bold"><?php echo esc_html( WLSM_Config::get_money_text( $student->fare ) ); ?></span>
						<?php else : ?>
							<span>—</span>
						<?php endif; ?>
					</div>
				</div>
			</div>

			<!-- Hidden form to pass params to AJAX -->
			<form id="wlsm-get-transport-invoices-form" class="d-none">
				<input type="hidden" name="get-transport-invoices" value="<?php echo esc_attr( $nonce ); ?>">
				<input type="hidden" name="action" value="wlsm-get-transport-invoices">
				<input type="hidden" name="student_id" value="<?php echo esc_attr( $student_id ); ?>">
			</form>

			<div class="row mt-4">
				<div class="col-md-12">
					<div class="wlsm-form-sub-heading wlsm-font-bold mb-3">
						<span class="border-bottom"><?php esc_html_e( 'Monthly Transport Invoices', 'school-management' ); ?></span>
					</div>
				</div>
			</div>

			<table class="table table-hover table-bordered" id="wlsm-staff-transport-invoices-table" style="width:100%;">
				<thead>
					<tr class="text-white bg-primary">
						<th class="wlsm-checkbox-col"><input type="checkbox" id="wlsm-select-all-pending" title="<?php esc_attr_e( 'Select all pending', 'school-management' ); ?>"></th>
						<th scope="col" class="d-none"><?php esc_html_e( 'Student', 'school-management' ); ?></th>
						<th scope="col" class="d-none"><?php esc_html_e( 'Enrollment', 'school-management' ); ?></th>
						<th scope="col" class="d-none"><?php esc_html_e( 'Class', 'school-management' ); ?></th>
						<th scope="col" class="d-none"><?php esc_html_e( 'Route', 'school-management' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Month', 'school-management' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Amount', 'school-management' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Invoice No.', 'school-management' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Status', 'school-management' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management' ); ?></th>
					</tr>
				</thead>
			</table>

			<!-- Bulk Generate bar -->
			<div id="wlsm-generate-bar" style="display:none; padding:12px; margin-top:20px; text-align:center;">
				<input type="hidden" id="wlsm-generate-transport-invoices-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wlsm-generate-transport-invoices' ) ); ?>">
				<span class="mr-3 font-weight-bold"><span id="wlsm-selected-count">0</span> <?php esc_html_e( 'month(s) selected', 'school-management' ); ?></span>
				<button type="button" class="btn btn-primary" id="wlsm-generate-transport-invoices-btn">
					<i class="fas fa-file-invoice"></i> <?php esc_html_e( 'Generate Invoice(s)', 'school-management' ); ?>
				</button>
			</div>

			<!-- Bulk Collect bar -->
			<div id="wlsm-collect-bar" style="display:none; padding:12px; margin-top:20px; text-align:center;">
				<input type="hidden" id="wlsm-collect-transport-invoices-nonce" value="<?php echo esc_attr( wp_create_nonce( 'wlsm-collect-transport-invoices' ) ); ?>">
			<input type="hidden" id="wlsm-collect-transport-action-url" value="<?php echo esc_url( admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_TRANSPORT . '&action=bulk_collect' ) ); ?>">
				<span class="mr-3 font-weight-bold"><span id="wlsm-collect-count">0</span> <?php esc_html_e( 'invoice(s) selected', 'school-management' ); ?> - <?php esc_html_e( 'Total', 'school-management' ); ?>: <span id="wlsm-collect-total">0</span></span>
				<!-- <button type="button" class="btn btn-success" id="wlsm-collect-transport-invoices-btn">
					<?php esc_html_e( 'Bulk Collect Payment', 'school-management' ); ?>
				</button> -->
			</div>
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
						<select name="route_id" id="wlsm-assign-route-st" class="form-control selectpicker" data-live-search="true"
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
