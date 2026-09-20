<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_admins         = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ADMINS );
$page_url_roles          = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ROLES );
$page_url_employees      = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_EMPLOYEES );
$page_url_staff_id_cards = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_STAFF_ID_CARDS );
?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-user-shield"></i>
					<?php esc_html_e( 'Administrator', 'school-management' ); ?>
				</span>
			</div>
		</div>
	</div>

		<?php
	global $wpdb;
	$school_id  = $current_school['id'];

	// Total Admins.
	$total_admins_count  = $wpdb->get_var( WLSM_M_Staff_General::fetch_staff_query_count( $school_id, WLSM_M_Role::get_admin_key() ) );

	// Total Roles.
	$total_roles_count = $wpdb->get_var( WLSM_M_Staff_General::fetch_role_query_count( $school_id ) );

	// Total Staff.
	$total_staff_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT a.ID) FROM ' . WLSM_ADMINS . ' as a
			JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
			WHERE sf.role = "%s" AND sf.school_id = %d', WLSM_M_Role::get_employee_key(), $school_id )
	);

	// Active Staff.
	$active_staff_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT a.ID) FROM ' . WLSM_ADMINS . ' as a
			JOIN ' . WLSM_STAFF . ' as sf ON sf.ID = a.staff_id
			WHERE sf.role = "%s" AND sf.school_id = %d AND a.is_active = 1', WLSM_M_Role::get_employee_key(), $school_id )
	);

	// Total Pending Staff Leaves.
	$total_pending_staff_leaves_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(lv.ID) FROM ' . WLSM_LEAVES . ' as lv WHERE lv.school_id = %d AND lv.is_approved = 0 AND lv.admin_id IS NOT NULL', $school_id ) );
	?>
	<div class="row mt-3 mb-3 wlsm-stats-blocks">
		<?php if ( WLSM_M_Role::check_permission( array( 'manage_admins' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-user-shield"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Admins', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Manage Admins', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_admins_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_admins ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Admins', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_admins . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'manage_roles' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-user-tag"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Roles', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Manage Roles', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_roles_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_roles ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Roles', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_roles . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'manage_employees' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-user-tie"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Staff', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php printf( esc_html__( 'Active Staff: %s', 'school-management' ), $active_staff_count ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_staff_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_employees ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Staff', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_employees . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_employees . '&action=save_bulk' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Bulk Import', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-id-card-alt"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Staff ID Cards', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Print ID Cards', 'school-management' ); ?></div>
						</div>
					</div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_staff_id_cards ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Print Cards', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_staff_leaves' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-calendar-times"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Staff Leaves', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Leaves Pending', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_pending_staff_leaves_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( admin_url('admin.php?page=wlsm-staff-leaves') ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Leaves', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
