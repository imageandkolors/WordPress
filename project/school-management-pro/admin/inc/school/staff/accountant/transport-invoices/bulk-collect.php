<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Accountant.php';

if (!isset($_POST['invoice_ids']) || !is_array($_POST['invoice_ids']) || empty($_POST['invoice_ids'])) {
	die(esc_html__('No invoices selected for bulk collection.', 'school-management'));
}

if (!isset($_POST['wlsm-bulk-collect-nonce']) || !wp_verify_nonce($_POST['wlsm-bulk-collect-nonce'], 'wlsm-collect-transport-invoices')) {
	die(esc_html__('Security check failed.', 'school-management'));
}

$invoice_ids = array_map('absint', $_POST['invoice_ids']);

global $wpdb;
$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

// Fetch the invoices to make sure they belong to the current school and session and are UNPAID transport invoices
$invoices_placeholders = implode(',', array_fill(0, count($invoice_ids), '%d'));
$query = "
	SELECT i.ID, i.invoice_number, i.label, i.transport_month, i.amount, i.partial_payment, i.status, sr.ID as student_id, sr.name as student_name
	FROM " . WLSM_INVOICES . " as i
	JOIN " . WLSM_STUDENT_RECORDS . " as sr ON sr.ID = i.student_record_id
	WHERE i.ID IN ($invoices_placeholders)
	  AND i.invoice_type = 'transport'
	  AND i.status != 'paid'
	  AND sr.session_id = %d
";
$invoices = $wpdb->get_results($wpdb->prepare($query, array_merge($invoice_ids, array($session_id))));

if (empty($invoices)) {
	die(esc_html__('No valid unpaid transport invoices found for the specified student(s).', 'school-management'));
}

$student_id = $invoices[0]->student_id;
$student_name = $invoices[0]->student_name;

$total_amount = 0;
foreach ($invoices as $inv) {
	// Verify that all selected invoices belong to the SAME student
	if ($inv->student_id != $student_id) {
		die(esc_html__('Bulk collection can only be processed for a single student at a time.', 'school-management'));
	}
	$total_amount += $inv->amount;
}

$nonce_action = 'wlsm-submit-bulk-collect-transport';
?>

<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<i class="fas fa-rupee-sign"></i> <?php esc_html_e('Bulk Collect Transport Invoices', 'school-management'); ?>
				</span>
			</span>
			<div class="mt-2 text-muted">
				<?php esc_html_e('Student:', 'school-management'); ?> <strong><?php echo esc_html($student_name); ?></strong>
			</div>
		</div>

		<div class="wlsm-table-block">
			<form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" id="wlsm-bulk-collect-form">
				<?php $nonce = wp_create_nonce($nonce_action); ?>
				<input type="hidden" name="<?php echo esc_attr($nonce_action); ?>" value="<?php echo esc_attr($nonce); ?>">
				<input type="hidden" name="action" value="wlsm-submit-bulk-collect-transport">

				<div class="table-responsive mb-4">
					<table class="table table-bordered table-striped">
						<thead class="bg-primary text-white">
							<tr>
								<th><?php esc_html_e('Invoice No.', 'school-management'); ?></th>
								<th><?php esc_html_e('Label', 'school-management'); ?></th>
								<th><?php esc_html_e('Month', 'school-management'); ?></th>
								<th class="text-right"><?php esc_html_e('Amount', 'school-management'); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($invoices as $inv): ?>
								<tr>
									<td><?php echo esc_html($inv->invoice_number); ?></td>
									<td><?php echo esc_html($inv->label); ?></td>
									<td><?php echo esc_html($inv->transport_month ? date('F Y', strtotime($inv->transport_month . '-01')) : '-'); ?></td>
									<td class="text-right"><?php echo esc_html(WLSM_Config::get_money_text($inv->amount)); ?></td>
									<input type="hidden" name="invoice_ids[]" value="<?php echo esc_attr($inv->ID); ?>">
								</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr class="font-weight-bold">
								<td colspan="3" class="text-right"><?php esc_html_e('Total Payable Amount:', 'school-management'); ?></td>
								<td class="text-right text-success"><?php echo esc_html(WLSM_Config::get_money_text($total_amount)); ?></td>
								<input type="hidden" name="total_amount" value="<?php echo esc_attr($total_amount); ?>">
							</tr>
						</tfoot>
					</table>
				</div>

				<div class="wlsm-form-section">
					<div class="row">
						<div class="col-md-4">
							<div class="form-group">
								<label for="wlsm_payment_amount" class="wlsm-font-bold">
									<span class="wlsm-important">*</span> <?php esc_html_e('Payment Amount', 'school-management'); ?>:
								</label>
								<input type="number" step="any" min="0" name="payment_amount" class="form-control" id="wlsm_payment_amount" placeholder="<?php esc_attr_e('Enter amount', 'school-management'); ?>" value="<?php echo esc_attr($total_amount); ?>" required>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="wlsm_payment_method" class="wlsm-font-bold">
									<span class="wlsm-important">*</span> <?php esc_html_e('Payment Method', 'school-management'); ?>:
								</label>
								<select name="payment_method" class="form-control" id="wlsm_payment_method" required>
									<?php foreach (WLSM_M_Invoice::collect_payment_methods() as $key => $value) : ?>
										<option value="<?php echo esc_attr($key); ?>">
											<?php echo esc_html($value); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="wlsm_transaction_id" class="wlsm-font-bold">
									<?php esc_html_e('Transaction ID', 'school-management'); ?>:
								</label>
								<input type="text" name="transaction_id" class="form-control" id="wlsm_transaction_id" placeholder="<?php esc_attr_e('Enter transaction ID', 'school-management'); ?>">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-md-8">
							<div class="form-group">
								<label for="wlsm_payment_note" class="wlsm-font-bold">
									<?php esc_html_e('Note', 'school-management'); ?>:
								</label>
								<textarea name="payment_note" class="form-control" id="wlsm_payment_note" rows="2" placeholder="<?php esc_attr_e('Enter payment note', 'school-management'); ?>"></textarea>
							</div>
						</div>
						<div class="col-md-4">
							<div class="form-group">
								<label for="wlsm_payment_date" class="wlsm-font-bold">
									<span class="wlsm-important">*</span> <?php esc_html_e('Date', 'school-management'); ?>:
								</label>
								<input type="text" name="payment_date" class="form-control wlsm-date" id="wlsm_payment_date" placeholder="<?php esc_attr_e('Payment date', 'school-management'); ?>" value="<?php echo esc_attr(WLSM_Config::get_date_text(date('Y-m-d'))); ?>" required>
							</div>
						</div>
					</div>
				</div>

				<div class="row mt-4">
					<div class="col-md-12 text-center">
						<button type="button" class="btn btn-secondary mr-2" onclick="window.history.back();">
							<i class="fas fa-times"></i> <?php esc_html_e('Cancel', 'school-management'); ?>
						</button>
						<button type="submit" class="btn btn-success" id="wlsm-bulk-collect-submit-btn">
							<i class="fas fa-rupee-sign"></i> <?php esc_html_e('Confirm Bulk Collection', 'school-management'); ?>
						</button>
					</div>
				</div>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function ($) {
		'use strict';
		$('#wlsm-bulk-collect-form').on('submit', function (e) {
			e.preventDefault();
			var $btn = $('#wlsm-bulk-collect-submit-btn');
			$btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <?php esc_html_e('Processing...', 'school-management'); ?>');

			$.ajax({
				url: $(this).attr('action'),
				type: 'POST',
				data: $(this).serialize(),
				success: function (resp) {
					if (resp.success) {
						var receiptUrl = resp.data.receipt_url;
						$('body').append('<div class="alert alert-success mt-3" style="position:fixed;top:70px;right:20px;z-index:99999;"><i class="fas fa-check-circle"></i> ' + resp.data.message + '</div>');

						// Optionally open receipt immediately in new tab
						if (receiptUrl) {
							window.open(receiptUrl, '_blank');
						}

						// Redirect back to student invoice list
						setTimeout(function() {
							window.location.href = '<?php echo esc_url(admin_url('admin.php?page=' . WLSM_MENU_STAFF_TRANSPORT . '&action=view-student&sr_id=' . $student_id)); ?>';
						}, 1500);
					} else {
						alert(resp.data || '<?php esc_html_e('An error occurred. Please try again.', 'school-management'); ?>');
						$btn.prop('disabled', false).html('<i class="fas fa-rupee-sign"></i> <?php esc_html_e('Confirm Bulk Collection', 'school-management'); ?>');
					}
				},
				error: function () {
					alert('<?php esc_html_e('Server error. Please try again.', 'school-management'); ?>');
					$btn.prop('disabled', false).html('<i class="fas fa-rupee-sign"></i> <?php esc_html_e('Confirm Bulk Collection', 'school-management'); ?>');
				}
			});
		});
	});
</script>
