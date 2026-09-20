<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_exams                  = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_EXAMS );
$page_url_exam_admit_cards       = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_EXAM_ADMIT_CARDS );
$page_url_exam_results           = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_EXAM_RESULTS );
$page_url_results_assessment     = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_EXAM_ASSESSMENT );
?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-clock"></i>
					<?php esc_html_e( 'Examination', 'school-management' ); ?>
				</span>
			</div>
		</div>
	</div>

		<?php
	global $wpdb;
	$school_id  = $current_school['id'];
	$session_id = $current_session['ID'];
	$_sess = WLSM_M_Session::get_label_text( $current_session['label'] );
	?>
	<div class="row mt-3 mb-3 wlsm-stats-blocks">
		<?php if ( WLSM_M_Role::check_permission( array( 'view_exams' ), $current_school['permissions'] ) ) :
			$total_exams_with_published_timetables = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ex.ID) FROM ' . WLSM_EXAMS . ' as ex WHERE ex.school_id = %d AND ex.time_table_published = 1', $school_id ) );
		?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-calendar-alt"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Exams', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Published Timetables', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_exams_with_published_timetables ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_exams ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Exams', 'school-management' ); ?>
					</a>
					<?php if( WLSM_M_Role::can('add_exams')): ?>
						<a href="<?php echo esc_url( $page_url_exams . '&action=save' ); ?>" class="btn btn-sm btn-outline-primary">
							<?php esc_html_e( 'Add New Exam', 'school-management' ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_admit_cards' ), $current_school['permissions'] ) ) :
			$total_exams_with_published_admit_cards = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ex.ID) FROM ' . WLSM_EXAMS . ' as ex WHERE ex.school_id = %d AND ex.admit_cards_published = 1', $school_id ) );
		?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-id-card"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Admit Cards', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Published Admit Cards', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_exams_with_published_admit_cards ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_exam_admit_cards ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Admit Cards', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<?php if ( WLSM_M_Role::check_permission( array( 'view_exam_results' ), $current_school['permissions'] ) ) :
			$total_exams_with_published_results = $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(ex.ID) FROM ' . WLSM_EXAMS . ' as ex WHERE ex.school_id = %d AND ex.results_published = 1', $school_id ) );
		?>
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-table"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Exam Results', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Published Results', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_exams_with_published_results ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url( $page_url_exam_results ); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Exam Results', 'school-management' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
