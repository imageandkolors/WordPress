<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Invoice.php';

if (isset($from_front)) {
	$print_button_classes = 'button btn-sm btn-success';
} else {
	$print_button_classes = 'btn btn-sm btn-success';
}

// $payments  — array of payment rows sharing the same receipt_number (set by caller)
// $school_id — set by caller

if (empty($payments)) {
	echo '<div class="alert alert-warning">' . esc_html__('No payment data found.', 'school-management') . '</div>';
	return;
}

$first       = $payments[0];
$receipt_no  = $first->receipt_number;
$pay_method  = WLSM_M_Invoice::get_payment_method_text($first->payment_method);
$pay_date    = WLSM_Config::get_date_text($first->created_at);
$student_name     = WLSM_M_Staff_Class::get_name_text($first->student_name);
$enrollment_no    = $first->enrollment_number;
$admission_no     = WLSM_M_Staff_Class::get_admission_no_text($first->admission_number);
$class_label      = WLSM_M_Class::get_label_text($first->class_label);
$section_label    = WLSM_M_Class::get_label_text($first->section_label);
$father_name      = WLSM_M_Staff_Class::get_name_text($first->father_name);
$father_phone     = WLSM_M_Staff_Class::get_phone_text($first->father_phone);
$phone            = WLSM_M_Staff_Class::get_phone_text($first->phone);
$bank_name        = $first->bank_name;
$cheque_number    = $first->cheque_number;
$cheque_date      = $first->cheque_date;
$authorized_by    = $first->authorized_by;
$transaction_id   = $first->transaction_id;
$note             = $first->note;

// Receiver name
$added_by_user = get_userdata($first->added_by);
$receiver_name = $added_by_user ? $added_by_user->display_name : '';

// Totals
$total_collected = 0;
$invoice_count   = count($payments);
foreach ($payments as $p) {
	$total_collected += (float) $p->amount;
}
?>

<!-- Print button -->
<div class="wlsm-container d-flex mb-2">
	<div class="col-md-12 wlsm-text-center">
		<br>
		<button type="button"
			class="<?php echo esc_attr($print_button_classes); ?>"
			id="wlsm-print-bulk-receipt-btn"
			data-styles='["<?php echo esc_url(WLSM_PLUGIN_URL . 'assets/css/bootstrap.min.css'); ?>","<?php echo esc_url(WLSM_PLUGIN_URL . 'assets/css/wlsm-school-header.css'); ?>","<?php echo esc_url(WLSM_PLUGIN_URL . 'assets/css/print/wlsm-payment.css'); ?>"]'
			data-title="<?php printf(esc_attr__('Bulk Payment Receipt - %s', 'school-management'), esc_attr($receipt_no)); ?>">
			<?php esc_html_e('Print Receipt', 'school-management'); ?>
		</button>
	</div>
</div>

<!-- Printable receipt -->
<div class="wlsm-container wlsm" id="wlsm-print-invoice-payment">
	<div class="wlsm-print-invoice-payment-container">

		<?php require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/partials/school_header.php'; ?>

		<div class="row">
			<div class="col-md-12">
				<div class="wlsm-h5 wlsm-invoice-payment-heading wlsm-font-bold text-center">
					<?php esc_html_e('Bulk Payment Receipt', 'school-management'); ?>
					<small class="float-md-right">
						<?php
						printf(
							wp_kses(
								/* translators: %s: receipt number */
								__('<span class="wlsm-font-bold">Receipt No.</span> %s', 'school-management'),
								array('span' => array('class' => array()))
							),
							esc_html(WLSM_M_Invoice::get_receipt_number_text($receipt_no))
						);
						?>
					</small>
				</div>
			</div>
		</div>

		<!-- Student Info -->
		<div class="row mt-2">
			<div class="col-12">
				<div class="table-responsive w-100">
					<table class="table table-bordered">
						<tr>
							<th><?php esc_html_e('Student Name', 'school-management'); ?></th>
							<td><?php echo esc_html($student_name); ?></td>
							<th><?php esc_html_e('Admission Number', 'school-management'); ?></th>
							<td><?php echo esc_html($admission_no); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e('Class', 'school-management'); ?></th>
							<td><?php echo esc_html($class_label); ?></td>
							<th><?php esc_html_e('Section', 'school-management'); ?></th>
							<td><?php echo esc_html($section_label); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e('Enrollment Number', 'school-management'); ?></th>
							<td><?php echo esc_html($enrollment_no); ?></td>
							<th><?php esc_html_e('Phone', 'school-management'); ?></th>
							<td><?php echo esc_html($phone); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e("Father's Name", 'school-management'); ?></th>
							<td><?php echo esc_html($father_name); ?></td>
							<th><?php esc_html_e("Father's Phone", 'school-management'); ?></th>
							<td><?php echo esc_html($father_phone); ?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<!-- Invoices Covered -->
		<div class="row mt-1">
			<div class="col-12">
				<h6 class="wlsm-font-bold" style="border-bottom:2px solid #1a73e8; padding-bottom:4px; color:#1a73e8;">
					<?php esc_html_e('Invoices Covered', 'school-management'); ?>
				</h6>
				<div class="table-responsive w-100">
					<table class="table table-bordered table-sm">
						<thead class="bg-primary text-white">
							<tr>
								<th>#</th>
								<th><?php esc_html_e('Invoice Title', 'school-management'); ?></th>
								<th><?php esc_html_e('Invoice No.', 'school-management'); ?></th>
								<th class="text-right"><?php esc_html_e('Invoice Amount', 'school-management'); ?></th>
								<th class="text-right"><?php esc_html_e('Amount Paid', 'school-management'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php $row_num = 1; foreach ($payments as $p) : ?>
								<tr>
									<td><?php echo esc_html($row_num++); ?></td>
									<td><?php echo esc_html(WLSM_M_Staff_Accountant::get_invoice_title_text($p->invoice_title ?: $p->invoice_label)); ?></td>
									<td><?php echo esc_html($p->invoice_number ?? '—'); ?></td>
									<td class="text-right"><?php echo esc_html(WLSM_Config::get_money_text($p->invoice_payable, $school_id)); ?></td>
									<td class="text-right text-success wlsm-font-bold"><?php echo esc_html(WLSM_Config::get_money_text($p->amount, $school_id)); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr class="wlsm-font-bold">
								<td colspan="3" class="text-right"><?php esc_html_e('Total Invoices:', 'school-management'); ?> <strong><?php echo esc_html($invoice_count); ?></strong></td>
								<td colspan="2" class="text-right text-success"><?php echo esc_html(WLSM_Config::get_money_text($total_collected, $school_id)); ?></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		<!-- Payment Details -->
		<div class="row mt-1">
			<div class="col-12">
				<h6 class="wlsm-font-bold" style="border-bottom:2px solid #1a73e8; padding-bottom:4px; color:#1a73e8;">
					<?php esc_html_e('Payment Details', 'school-management'); ?>
				</h6>
				<div class="table-responsive w-100">
					<table class="table table-bordered">
						<tr>
							<th><?php esc_html_e('Receipt Number', 'school-management'); ?></th>
							<td><?php echo esc_html(WLSM_M_Invoice::get_receipt_number_text($receipt_no)); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e('Total Amount Collected', 'school-management'); ?></th>
							<td><strong class="text-success"><?php echo esc_html(WLSM_Config::get_money_text($total_collected, $school_id)); ?></strong></td>
						</tr>
						<tr>
							<th><?php esc_html_e('No. of Invoices', 'school-management'); ?></th>
							<td><?php echo esc_html($invoice_count); ?></td>
						</tr>
						<tr>
							<th><?php esc_html_e('Payment Method', 'school-management'); ?></th>
							<td><?php echo esc_html($pay_method); ?></td>
						</tr>
						<?php if ($bank_name) : ?>
							<tr>
								<th><?php esc_html_e('Bank Name', 'school-management'); ?></th>
								<td><?php echo esc_html($bank_name); ?></td>
							</tr>
						<?php endif; ?>
						<?php if ($cheque_number) : ?>
							<tr>
								<th><?php esc_html_e('Cheque Number', 'school-management'); ?></th>
								<td><?php echo esc_html($cheque_number); ?></td>
							</tr>
						<?php endif; ?>
						<?php if ($cheque_date) : ?>
							<tr>
								<th><?php esc_html_e('Cheque Date', 'school-management'); ?></th>
								<td><?php echo esc_html(WLSM_Config::get_date_text($cheque_date)); ?></td>
							</tr>
						<?php endif; ?>
						<?php if ($authorized_by) : ?>
							<tr>
								<th><?php esc_html_e('Authorized By', 'school-management'); ?></th>
								<td><?php echo esc_html($authorized_by); ?></td>
							</tr>
						<?php endif; ?>
						<?php if ($transaction_id) : ?>
							<tr>
								<th><?php esc_html_e('Transaction ID', 'school-management'); ?></th>
								<td><?php echo esc_html(WLSM_M_Invoice::get_transaction_id_text($transaction_id)); ?></td>
							</tr>
						<?php endif; ?>
						<tr>
							<th><?php esc_html_e('Date', 'school-management'); ?></th>
							<td><?php echo esc_html($pay_date); ?></td>
						</tr>
						<?php if ($note) : ?>
							<tr>
								<th><?php esc_html_e('Note', 'school-management'); ?></th>
								<td><?php echo esc_html($note); ?></td>
							</tr>
						<?php endif; ?>
						<tr>
							<th><?php esc_html_e('Receiver Name', 'school-management'); ?></th>
							<td><?php echo esc_html($receiver_name); ?></td>
						</tr>
					</table>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-6 pl-5 mt-4"></div>
			<div class="col-6 text-right pr-5 mt-4">
				<span class="wlsm-font-bold"><strong><?php esc_html_e("Receiver's Signature", 'school-management'); ?></strong></span>
			</div>
		</div>

	</div>
</div>
