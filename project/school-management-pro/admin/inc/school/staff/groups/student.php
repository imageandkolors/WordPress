<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_admissions       = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ADMISSIONS );
$page_url_students         = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_STUDENTS );
$page_url_id_cards         = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_ID_CARDS );
$page_url_promote          = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_PROMOTE );
$page_url_transfer_student = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_TRANSFER_STUDENT );
$page_url_certificates     = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_CERTIFICATES );
?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-users"></i>
					<?php esc_html_e( 'Student', 'school-management' ); ?>
				</span>
			</div>
		</div>
	</div>

		<?php
	global $wpdb;
	$school_id  = $current_school['id'];
	$session_id = $current_session['ID'];
	$_sess = WLSM_M_Session::get_label_text( $current_session['label'] );

	// Total Students.
	$total_students_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE ss.ID = %d AND cs.school_id = %d', $session_id, $school_id )
	);

	// Active Students.
	$active_students_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE ss.ID = %d AND cs.school_id = %d AND sr.is_active = 1', $session_id, $school_id )
	);

	// Inactive Students.
	$inactive_students_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE ss.ID = %d AND cs.school_id = %d AND sr.is_active = 0', $session_id, $school_id )
	);

	// Total Girls.
	$total_girls = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE ss.ID = %d AND cs.school_id = %d AND sr.gender = %s', $session_id, $school_id, 'female' )
	);

	// Total Boys.
	$total_boys = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE ss.ID = %d AND cs.school_id = %d AND sr.gender = %s', $session_id, $school_id, 'male' )
	);

	// Total Promoted Students.
	$promoted_students_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT sp.ID) FROM ' . WLSM_PROMOTIONS . ' as sp
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = sp.from_student_record
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE sr.session_id = %d AND cs.school_id = %d AND sr.is_active = 0', $session_id, $school_id )
	);

	// Total Inquiries.
	$total_inquiries_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(iq.ID) FROM " . WLSM_INQUIRIES . " as iq WHERE iq.school_id = %d", $school_id ) );

	// Active Inquiries.
	$active_inquiries_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(iq.ID) FROM " . WLSM_INQUIRIES . " as iq WHERE iq.school_id = %d AND iq.is_active = 1", $school_id ) );

	// Transferred Out.
	$students_transferred_to_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_TRANSFERS . ' as tf
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = tf.from_student_record
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d', $school_id, $session_id )
	);

	// Transferred In.
	$students_transferred_from_count = $wpdb->get_var(
		$wpdb->prepare( 'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_TRANSFERS . ' as tf
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = tf.to_student_record
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d', $school_id, $session_id )
	);

	// Total Pending Student Leaves.
	$total_pending_student_leaves_count = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(lv.ID) FROM ' . WLSM_LEAVES . ' as lv WHERE lv.school_id = %d AND lv.is_approved = 0 AND lv.student_record_id IS NOT NULL', $school_id ) );

	// Total ID Cards.
	$total_id_cards = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . WLSM_ID_CARDS . ' WHERE school_id = %d', $school_id ) );

	// Total Certificates.
	$total_certificates = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ID) FROM ' . WLSM_CERTIFICATES . ' WHERE school_id = %d', $school_id ) );
	?>

	<div class="row mt-3 mb-3 wlsm-stats-blocks">
		<?php if ( WLSM_M_Role::check_permission( array( 'view_students' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-users"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Total Students', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php echo esc_html( $_sess ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_students_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_students ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Students', 'school-management' ); ?>
					</a>
					<?php if ( WLSM_M_Role::check_permission( array( 'manage_admissions' ), $current_school['permissions'] ) ) : ?>
					<a href="<?php echo esc_url( $page_url_admissions ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add Admission', 'school-management' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-user-check"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Student Status', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php printf( esc_html__( 'Inactive: %s', 'school-management' ), $inactive_students_count ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $active_students_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<?php if ( WLSM_M_Role::check_permission( array( 'manage_admissions' ), $current_school['permissions'] ) ) : ?>
					<a href="<?php echo esc_url( $page_url_admissions . '&action=bulk_import' ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Bulk Admission', 'school-management' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-venus-mars"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Total Girls', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php printf( esc_html__( 'Total Boys: %s', 'school-management' ), $total_boys ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_girls ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_students ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Students', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'manage_promote' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-user-graduate"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Promoted', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Promoted Students', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $promoted_students_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_promote ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Promote Students', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_inquiries' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-envelope"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Inquiries', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php printf( esc_html__( 'Active: %s', 'school-management' ), $active_inquiries_count ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_inquiries_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wlsm-inquiries' ) ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Inquiries', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'manage_transfer_student' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-sign-out-alt"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Transferred Out', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php printf( esc_html__( 'Transferred In: %s', 'school-management' ), $students_transferred_from_count ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $students_transferred_to_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_transfer_student ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Transferred', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_transfer_student . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Transfer Student', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_student_leaves' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-calendar-times"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Leaves Pending', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Student Leaves', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_pending_student_leaves_count ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( admin_url('admin.php?page=wlsm-student-leaves') ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Leaves', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_students' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-id-card"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'ID Cards', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Custom ID Cards', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_id_cards ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_id_cards ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Print ID Cards', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_certificates' ), $current_school['permissions'] ) ) : ?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-certificate"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Certificates', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Manage Certificates', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_certificates ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_certificates ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Certificates', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url( $page_url_certificates . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add New', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

	</div>
</div>
