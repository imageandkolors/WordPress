<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_vehicles = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_HOSTELS );
$page_url_rooms    = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ROOMS );

global $wpdb;
$hostels_count       = WLSM_M_Staff_Transport::count_hostels($current_school['id']);
$rooms_count         = WLSM_M_Staff_Transport::count_rooms($current_school['id']);
$total_boys_hostels  = WLSM_M_Staff_Transport::count_boys_hostels($current_school['id']);
$total_girls_hostels = WLSM_M_Staff_Transport::count_girls_hostels($current_school['id']);

?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-home"></i>
					<?php esc_html_e( 'Hostels', 'school-management' ); ?>
				</span>
			</div>
		</div>
	</div>

	<!-- Hostel Stats Section -->
	<div class="row mt-3 mb-3 wlsm-stats-blocks">
		<!-- Total Hostels -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-hotel"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Hostels', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Building Facilities', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $hostels_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_vehicles ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View All', 'school-management' ); ?>
					</a>
					<?php if( WLSM_M_Role::can('add_hostel')): ?>
						<a href="<?php echo esc_url( $page_url_vehicles . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
							<?php esc_html_e( 'Add New', 'school-management' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Total Rooms -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-door-open"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Rooms', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Available Units', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $rooms_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_rooms ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Rooms', 'school-management' ); ?>
					</a>
					<?php if( WLSM_M_Role::can('add_hostel')): ?>
						<a href="<?php echo esc_url( $page_url_rooms . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
							<?php esc_html_e( 'Add Room', 'school-management' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Boys Hostels -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-male"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Boys Hostels', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Male Accommodations', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_boys_hostels ); ?></div>
				</div>
			</div>
		</div>

		<!-- Girls Hostels -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-female"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Girls Hostels', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Female Accommodations', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_girls_hostels ); ?></div>
				</div>
			</div>
		</div>
	</div>
</div>
