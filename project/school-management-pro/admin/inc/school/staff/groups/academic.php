<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_classes         = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_CLASSES );
$page_url_subjects        = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_SUBJECTS );
$page_url_attendance      = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ATTENDANCE );
$page_url_study_materials = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_STUDY_MATERIALS );
$page_url_homework        = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_HOMEWORK );
$page_url_notices         = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_NOTICES );
$page_url_events          = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_EVENTS );
?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-graduation-cap"></i>
					<?php esc_html_e( 'Academic', 'school-management' ); ?>
				</span>
			</div>
		</div>
	</div>

		<?php
	global $wpdb;
	$school_id  = $current_school['id'];

	// Total Classes.
	$total_classes_count  = $wpdb->get_var( WLSM_M_Staff_Class::fetch_classes_query_count( $school_id ) );

	// Total Sections.
	$total_sections_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT se.ID) FROM ' . WLSM_SECTIONS . ' as se JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = cs.school_id WHERE cs.school_id = %d', $school_id ) );
	?>

	<div class="row mt-3 mb-3 wlsm-stats-blocks">
		<?php if ( WLSM_M_Role::check_permission( array( 'manage_classes' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-layer-group"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Class Sections', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php printf( esc_html__( 'Total Sections: %s', 'school-management' ), $total_sections_count ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_classes_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_classes ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Add / Manage', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_subjects' ), $current_school['permissions'] ) ) : 
			$total_subjects = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(DISTINCT sj.ID) FROM ' . WLSM_SUBJECTS . ' as sj JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = sj.class_school_id WHERE cs.school_id = %d', $school_id ) );
		?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-book"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Subjects', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Manage Subjects', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_subjects ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_subjects ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Subjects', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_subjects ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_attendance' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-calendar-check"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Attendance', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Student Attendance', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_attendance ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Attendance', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_attendance . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Take Attendance', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_study_materials' ), $current_school['permissions'] ) ) : 
			$total_study_materials = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . WLSM_STUDY_MATERIALS . ' WHERE school_id = %d', $school_id ) );
		?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-file-pdf"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Study Materials', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Materials Shared', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_study_materials ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_study_materials ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Study Materials', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_study_materials . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_homework' ), $current_school['permissions'] ) ) : 
			$total_homeworks = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . WLSM_HOMEWORK . ' WHERE school_id = %d', $school_id ) );
		?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-edit"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Homework', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Assignments', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_homeworks ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_homework ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Homework', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_homework . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Assign', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_notices' ), $current_school['permissions'] ) ) : 
			$total_notices = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . WLSM_NOTICES . ' WHERE school_id = %d', $school_id ) );
		?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-bullhorn"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Noticeboard', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Announcements', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_notices ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_notices ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Noticeboard', 'school-management' ); ?>
					</a>
					<?php if( WLSM_M_Role::can('add_notices')): ?>
					<a href="<?php echo esc_url( $page_url_notices . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New', 'school-management' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_events' ), $current_school['permissions'] ) ) : 
			$total_events = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . WLSM_EVENTS . ' WHERE school_id = %d', $school_id ) );
		?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-calendar-day"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Events', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'School Events', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_events ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_events ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Events', 'school-management' ); ?>
					</a>
					<?php if( WLSM_M_Role::can('add_events')): ?>
					<a href="<?php echo esc_url( $page_url_events . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New Event', 'school-management' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
