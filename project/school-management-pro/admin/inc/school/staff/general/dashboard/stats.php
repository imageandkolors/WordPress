<?php
defined( 'ABSPATH' ) || die();

/**
 * Helper: render one compact stat card.
 *
 * Layout: [ icon ] [ label / session ] [ counter ]
 *
 * @param string $icon      FontAwesome class, e.g. 'fas fa-users'
 * @param string $counter   The numeric / text value
 * @param string $label     Main label (already-translated string)
 * @param string $sub       Session sub-label – only pass when count is session-scoped
 */
function wlsm_stat_card( $icon, $counter, $label, $sub = '' ) {
	?>
	<div class="wlsm-stats-block">
		<div class="wlsm-stats-icon"><i class="<?php echo esc_attr( $icon ); ?>"></i></div>
		<div class="wlsm-stats-text">
			<div class="wlsm-stats-label"><?php echo esc_html( $label ); ?></div>
			<?php if ( $sub ) : ?>
			<div class="wlsm-stats-session"><?php echo esc_html( $sub ); ?></div>
			<?php endif; ?>
		</div>
		<div class="wlsm-stats-counter"><?php echo esc_html( $counter ); ?></div>
	</div>
	<?php
}

$_sess = WLSM_M_Session::get_label_text( $current_session['label'] );
?>
<div class="row mt-1 wlsm-stats-blocks">

<?php if ( WLSM_M_Role::check_permission( array( 'view_inquiries' ), $current_school['permissions'] ) ) : ?>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-envelope-open-text',
			number_format( intval( $active_inquiries_count ) ),
			__( 'Active Inquiries', 'school-management' )
		);
		?>
	</div>
	<?php endif; ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'view_students' ), $current_school['permissions'] ) ) : ?>

	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-user-graduate',
			number_format( intval( $active_students_count ) ),
			__( 'Active Students', 'school-management' ),
			$_sess
		);
		?>
	</div>
	<?php endif; ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'manage_classes' ), $current_school['permissions'] ) ) : ?>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-layer-group',
			number_format( intval( $total_classes_count ) ),
			__( 'Total Classes', 'school-management' )
		);
		?>
	</div>
	<?php endif; ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'manage_employees' ), $current_school['permissions'] ) ) : ?>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-chalkboard-teacher',
			number_format( intval( $total_staff_count ) ),
			__( 'Total Staff', 'school-management' )
		);
		?>
	</div>
	<?php endif; ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'stats_income' ), $current_school['permissions'] ) ) : ?>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-hand-holding-usd',
			WLSM_Config::get_money_text( floatval( $total_income_sum ), $school_id ),
			__( 'Total Income', 'school-management' ),
			$_sess
		);
		?>
	</div>
	<?php endif; ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'stats_payments' ), $current_school['permissions'] ) ) : ?>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-rupee-sign',
			WLSM_Config::get_money_text( floatval( $total_payment_received ), $school_id ),
			__( 'Fees Collected', 'school-management' ),
			$_sess
		);
		?>
	</div>
	<?php endif; ?>
	<?php if ( WLSM_M_Role::check_permission( array( 'view_invoices' ), $current_school['permissions'] ) ) : ?>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-exclamation-circle',
			WLSM_Config::get_money_text( floatval( $invoices_pending_amount ), $school_id ),
			__( 'Pending Dues', 'school-management' ),
			$_sess
		);
		?>
	</div>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-file-invoice-dollar',
			number_format( intval( $invoices_unpaid_count ) ),
			__( 'Unpaid Invoices', 'school-management' ),
			$_sess
		);
		?>
	</div>
	<?php endif; ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'stats_expense' ), $current_school['permissions'] ) ) : ?>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-file-invoice-dollar',
			WLSM_Config::get_money_text( floatval( $total_expenses_sum ), $school_id ),
			__( 'Total Expenses', 'school-management' ),
			$_sess
		);
		?>
	</div>
	<?php endif; ?>



	<?php if ( WLSM_M_Role::check_permission( array( 'view_library' ), $current_school['permissions'] ) ) : ?>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-book',
			number_format( intval( $total_books ) ),
			__( 'Total Books', 'school-management' )
		);
		?>
	</div>
	<?php endif; ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'view_student_leaves' ), $current_school['permissions'] ) ) : ?>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-user-clock',
			number_format( intval( $total_pending_student_leaves_count ) ),
			__( 'Pending Student Leaves', 'school-management' )
		);
		?>
	</div>
	<?php endif; ?>

	<?php if ( WLSM_M_Role::check_permission( array( 'view_staff_leaves' ), $current_school['permissions'] ) ) : ?>
	<div class="col-md-3 col-sm-6 mb-3">
		<?php
		wlsm_stat_card(
			'fas fa-user-times',
			number_format( intval( $total_pending_staff_leaves_count ) ),
			__( 'Pending Staff Leaves', 'school-management' )
		);
		?>
	</div>
	<?php endif; ?>

</div>
