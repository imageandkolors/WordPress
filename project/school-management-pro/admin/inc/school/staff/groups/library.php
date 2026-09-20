<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url_books         = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_BOOKS );
$page_url_books_issued  = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_BOOKS_ISSUED );
$page_url_library_cards = admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_LIBRARY_CARDS );
?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading">
					<i class="fas fa-book"></i>
					<?php esc_html_e( 'Library', 'school-management' ); ?>
				</span>
			</div>
		</div>
	</div>

		<?php
	global $wpdb;
	$school_id  = $current_school['id'];
	$session_id = $current_session['ID'];
	$_sess = WLSM_M_Session::get_label_text( $current_session['label'] );

	if ( WLSM_M_Role::check_permission( array( 'view_library' ), $current_school['permissions'] ) ) {
		// Total Books.
		$total_books = $wpdb->get_var( WLSM_M_Staff_Library::fetch_book_query_count( $school_id ) );

		// Total Library Cards.
		$total_library_cards = $wpdb->get_var( WLSM_M_Staff_Library::fetch_library_card_query_count( $school_id, $session_id ) );

		// Total Books Issued.
		$total_books_issued = $wpdb->get_var( WLSM_M_Staff_Library::fetch_book_issued_query_count( $school_id, $session_id ) );

		// Total Books Return Pending.
		$total_books_return_pending = $wpdb->get_var( WLSM_M_Staff_Library::fetch_book_issued_query_count( $school_id, $session_id, true ) );
	?>
	<div class="row mt-3 mb-3 wlsm-stats-blocks">
		<!-- Books -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-book"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Books', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php esc_html_e( 'Library Inventory', 'school-management' ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_books ?: 0 ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url($page_url_books); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View', 'school-management' ); ?>
					</a>
					<?php if ( WLSM_M_Role::can('add_library') ) : ?>
					<a href="<?php echo esc_url($page_url_books . '&action=save'); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Add', 'school-management' ); ?>
					</a>
					<a href="<?php echo esc_url($page_url_books . '&action=save_bulk'); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Import', 'school-management' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Books Issued -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-book-open"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Books Issued', 'school-management' ); ?></div>
							<div class="wlsm-stats-session"><?php printf( esc_html__( 'Pending: %s', 'school-management' ), $total_books_return_pending ?: 0 ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_books_issued ?: 0 ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url($page_url_books_issued); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'Issued List', 'school-management' ); ?>
					</a>
					<?php if ( WLSM_M_Role::can('issue_books') ) : ?>
					<a href="<?php echo esc_url($page_url_books); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Issue Books', 'school-management' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<!-- Library Cards -->
		<div class="col-lg-3 col-md-6 col-sm-6 mb-4">
			<div class="wlsm-group h-100 d-flex flex-column">
				<div class="d-flex justify-content-between align-items-center mb-3">
					<div class="d-flex align-items-center">
						<div class="wlsm-stats-icon mr-3"><i class="fas fa-id-card"></i></div>
						<div class="wlsm-stats-text">
							<div class="wlsm-stats-label"><?php esc_html_e( 'Library Cards', 'school-management' ); ?></div>
							<div class="wlsm-stats-session">- <?php echo esc_html( $_sess ); ?></div>
						</div>
					</div>
					<div class="wlsm-stats-counter"><?php echo esc_html( $total_library_cards ?: 0 ); ?></div>
				</div>
				<div class="wlsm-group-actions mt-auto border-top pt-3">
					<a href="<?php echo esc_url($page_url_library_cards); ?>" class="btn btn-sm btn-primary">
						<?php esc_html_e( 'View Cards', 'school-management' ); ?>
					</a>
					<?php if ( WLSM_M_Role::can('issue_library_card') ) : ?>
					<a href="<?php echo esc_url($page_url_library_cards); ?>" class="btn btn-sm btn-outline-primary">
						<?php esc_html_e( 'Issue Cards', 'school-management' ); ?>
					</a>
					<?php endif; ?>
				</div>
			</div>
		</div>

	</div>
	<?php } ?>
</div>
