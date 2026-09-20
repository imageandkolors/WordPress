<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_vehicles = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_VEHICLES );
$page_url_routes   = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ROUTES );

global $wpdb;
$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

// Total Vehicles.
$total_vehicles_count = $wpdb->get_var( WLSM_M_Staff_Transport::fetch_vehicle_query_count( $school_id ) );

// Total Routes.
$total_routes_count = $wpdb->get_var( WLSM_M_Staff_Transport::fetch_route_query_count( $school_id ) );

// Students assigned to transport in this session.
$transport_students_count = WLSM_M_Staff_Accountant::get_transport_students_count( $school_id, $session_id );

// Transport invoices.
$transport_invoices_total  = WLSM_M_Staff_Accountant::get_transport_invoices_total( $session_id, $school_id );
$transport_invoices_unpaid = WLSM_M_Staff_Accountant::get_transport_invoices_unpaid( $session_id, $school_id );
?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-bus-alt"></i>
					<?php esc_html_e( 'Transport', 'school-management' ); ?>
				</span>
			</div>
		</div>
	</div>

	<div class="row mt-3 mb-3 wlsm-stats-blocks">
		<?php if ( WLSM_M_Role::check_permission( array( 'view_transport' ), $current_school['permissions'] ) ) : ?>
		<!-- Vehicles -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-truck"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Vehicles', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Fleet Size', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_vehicles_count ?: 0 ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_vehicles ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Vehicles', 'school-management' ); ?>
					</a>
					<?php if ( WLSM_M_Role::can( 'add_transport' ) ) : ?>
					<a href="<?php echo esc_url( $page_url_vehicles . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New', 'school-management' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Routes -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-route"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Routes', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Active Routes', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_routes_count ?: 0 ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_routes ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Routes', 'school-management' ); ?>
					</a>
					<?php if ( WLSM_M_Role::can( 'add_transport' ) ) : ?>
					<a href="<?php echo esc_url( $page_url_routes . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New', 'school-management' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Students on Transport -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-users"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Students', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php printf( esc_html__( 'Unpaid: %s', 'school-management' ), $transport_invoices_unpaid ?: 0 ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $transport_students_count ?: 0 ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_TRANSPORT_INVOICES ) ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Transport Invoices', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_routes ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Assign Transport', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>

		<!-- Transport Invoices -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-file-invoice"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Invoices', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Transport Billing', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $transport_invoices_total ?: 0 ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_TRANSPORT_INVOICES ) ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Invoices', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
