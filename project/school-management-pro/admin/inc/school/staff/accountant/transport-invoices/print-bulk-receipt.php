<?php
defined( 'ABSPATH' ) || die();

$receipt_number = isset( $_GET['receipt'] ) ? sanitize_text_field( $_GET['receipt'] ) : '';

if ( empty( $receipt_number ) ) {
	die( esc_html__( 'Invalid receipt number.', 'school-management' ) );
}

global $wpdb;
$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

// Fetch all payments for this receipt
$payments = $wpdb->get_results( $wpdb->prepare(
	"SELECT p.*, sr.name as student_name, sr.enrollment_number, sr.admission_number,
			c.label as class_label, se.label as section_label,
			i.invoice_number, i.transport_month
	 FROM " . WLSM_PAYMENTS . " as p
	 JOIN " . WLSM_STUDENT_RECORDS . " as sr ON sr.ID = p.student_record_id
	 JOIN " . WLSM_SECTIONS . " as se ON se.ID = sr.section_id
	 JOIN " . WLSM_CLASS_SCHOOL . " as cs ON cs.ID = se.class_school_id
	 JOIN " . WLSM_CLASSES . " as c ON c.ID = cs.class_id
	 JOIN " . WLSM_INVOICES . " as i ON i.ID = p.invoice_id
	 WHERE p.receipt_number = %s AND p.school_id = %d",
	$receipt_number, $school_id
) );

if ( empty( $payments ) ) {
	die( esc_html__( 'Receipt not found.', 'school-management' ) );
}

$first_payment = $payments[0];
$total_paid    = 0;
foreach ( $payments as $payment ) {
	$total_paid += $payment->amount;
}
?>

<!-- Print Styling -->
<style>
	@media print {
		body { background: #fff; margin: 0; padding: 0; font-family: Arial, sans-serif; }
		.wlsm-bulk-receipt-container { padding: 20px; }
		.wlsm-no-print { display: none !important; }
		@page { margin: 1cm; }
	}
	.wlsm-bulk-receipt-container { font-family: Arial, sans-serif; color: #333; max-width: 800px; margin: 0 auto; padding: 30px; border: 1px solid #ddd; background: #fff; }
	.wlsm-receipt-header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
	.wlsm-receipt-header h2 { margin: 0 0 5px 0; font-size: 24px; text-transform: uppercase; }
	.wlsm-receipt-header p { margin: 0; font-size: 14px; color: #666; }
	.wlsm-receipt-info { display: flex; justify-content: space-between; margin-bottom: 20px; }
	.wlsm-receipt-info-block { width: 48%; }
	.wlsm-receipt-info-block p { margin: 5px 0; font-size: 14px; }
	.wlsm-receipt-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
	.wlsm-receipt-table th, .wlsm-receipt-table td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
	.wlsm-receipt-table th { background-color: #f9f9f9; font-weight: bold; }
	.wlsm-receipt-table .text-right { text-align: right; }
	.wlsm-receipt-totals { text-align: right; font-size: 16px; font-weight: bold; }
	.wlsm-receipt-footer { margin-top: 40px; border-top: 1px solid #ddd; padding-top: 10px; text-align: center; font-size: 12px; color: #777; }
</style>

<div class="row wlsm-no-print mb-3">
	<div class="col-md-12 text-center">
		<button class="btn btn-primary btn-sm" onclick="window.print();">
			<i class="fas fa-print"></i> <?php esc_html_e( 'Print Receipt', 'school-management' ); ?>
		</button>
		<button class="btn btn-secondary btn-sm ml-2" onclick="window.history.back();">
			<i class="fas fa-arrow-left"></i> <?php esc_html_e( 'Go Back', 'school-management' ); ?>
		</button>
	</div>
</div>

<div class="wlsm-bulk-receipt-container">
	<div class="wlsm-receipt-header">
		<h2><?php esc_html_e( 'Transport Fee Receipt', 'school-management' ); ?></h2>
		<p><?php esc_html_e( 'Bulk Collection', 'school-management' ); ?></p>
	</div>

	<div class="wlsm-receipt-info">
		<div class="wlsm-receipt-info-block">
			<p><strong><?php esc_html_e( 'Student Name:', 'school-management' ); ?></strong> <?php echo esc_html( $first_payment->student_name ); ?></p>
			<p><strong><?php esc_html_e( 'Enrollment No:', 'school-management' ); ?></strong> <?php echo esc_html( $first_payment->enrollment_number ); ?></p>
			<p><strong><?php esc_html_e( 'Class/Section:', 'school-management' ); ?></strong> <?php echo esc_html( $first_payment->class_label . ' / ' . $first_payment->section_label ); ?></p>
		</div>
		<div class="wlsm-receipt-info-block" style="text-align: right;">
			<p><strong><?php esc_html_e( 'Receipt No:', 'school-management' ); ?></strong> <?php echo esc_html( $receipt_number ); ?></p>
			<p><strong><?php esc_html_e( 'Payment Date:', 'school-management' ); ?></strong> <?php echo esc_html( WLSM_Config::get_date_text( $first_payment->created_at ) ); ?></p>
			<p><strong><?php esc_html_e( 'Payment Method:', 'school-management' ); ?></strong> <?php echo esc_html( WLSM_Helper::get_payment_method_text( $first_payment->payment_method ) ); ?></p>
			<?php if ( ! empty( $first_payment->transaction_id ) ) : ?>
				<p><strong><?php esc_html_e( 'Transaction ID:', 'school-management' ); ?></strong> <?php echo esc_html( $first_payment->transaction_id ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<table class="wlsm-receipt-table">
		<thead>
			<tr>
				<th><?php esc_html_e( 'S.No', 'school-management' ); ?></th>
				<th><?php esc_html_e( 'Description / Month', 'school-management' ); ?></th>
				<th><?php esc_html_e( 'Invoice No.', 'school-management' ); ?></th>
				<th class="text-right"><?php esc_html_e( 'Amount Paid', 'school-management' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $payments as $index => $payment ) : ?>
				<tr>
					<td><?php echo esc_html( $index + 1 ); ?></td>
					<td>
						<?php 
						$month_desc = $payment->transport_month ? date( 'F Y', strtotime( $payment->transport_month . '-01' ) ) : '';
						echo esc_html( $payment->invoice_label . ( $month_desc ? ' (' . $month_desc . ')' : '' ) ); 
						?>
					</td>
					<td><?php echo esc_html( $payment->invoice_number ); ?></td>
					<td class="text-right"><?php echo esc_html( WLSM_Config::get_money_text( $payment->amount ) ); ?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="wlsm-receipt-totals">
		<?php esc_html_e( 'Total Amount Paid:', 'school-management' ); ?> 
		<span style="font-size: 1.2em; color: #333;"><?php echo esc_html( WLSM_Config::get_money_text( $total_paid ) ); ?></span>
	</div>

	<?php if ( ! empty( $first_payment->note ) ) : ?>
		<div class="wlsm-receipt-note" style="margin-top:20px; font-size: 13px; font-style: italic;">
			<strong><?php esc_html_e( 'Note:', 'school-management' ); ?></strong> <?php echo esc_html( $first_payment->note ); ?>
		</div>
	<?php endif; ?>

	<div class="wlsm-receipt-footer">
		<?php esc_html_e( 'Thank you for your payment.', 'school-management' ); ?>
		<br>
		<?php echo esc_html( WLSM_M_School::get_label_text( $current_school['name'] ) ); ?>
	</div>
</div>
