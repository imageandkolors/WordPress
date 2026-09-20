<?php
defined('ABSPATH') || die();

// Offline-only payment methods allowed for bulk collect
$bulk_offline_methods = array(
	'cash'         => esc_html__('Cash', 'school-management'),
	'card'         => esc_html__('Card', 'school-management'),
	'check'        => esc_html__('Cheque', 'school-management'),
	'demand-draft' => esc_html__('Demand Draft', 'school-management'),
);

// Fetch school settings to honour which offline methods are enabled
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';

$enabled_bulk_methods = array();

$settings_cash = WLSM_M_Setting::get_settings_cash($school_id);
if (!empty($settings_cash['enable']) && isset($bulk_offline_methods['cash'])) {
	$enabled_bulk_methods['cash'] = $bulk_offline_methods['cash'];
}

$settings_card = WLSM_M_Setting::get_settings_card($school_id);
if (!empty($settings_card['enable']) && isset($bulk_offline_methods['card'])) {
	$enabled_bulk_methods['card'] = $bulk_offline_methods['card'];
}

$settings_check = WLSM_M_Setting::get_settings_check($school_id);
if (!empty($settings_check['enable']) && isset($bulk_offline_methods['check'])) {
	$enabled_bulk_methods['check'] = $bulk_offline_methods['check'];
}

$settings_demand_draft = WLSM_M_Setting::get_settings_demand_draft($school_id);
if (!empty($settings_demand_draft['enable']) && isset($bulk_offline_methods['demand-draft'])) {
	$enabled_bulk_methods['demand-draft'] = $bulk_offline_methods['demand-draft'];
}

// Fallback: if none are enabled in settings, show all offline methods
if (empty($enabled_bulk_methods)) {
	$enabled_bulk_methods = $bulk_offline_methods;
}

$bulk_collect_nonce = wp_create_nonce('wlsm-bulk-collect-fee');
$bulk_receipt_nonce = wp_create_nonce('wlsm-print-bulk-collect-receipt');
?>

<!-- Bulk Collect Overlay -->
<div id="wlsm-bulk-collect-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.55); z-index:99998;"></div>

<!-- Bulk Collect Slide-in Panel -->
<div id="wlsm-bulk-collect-panel" style="display:none; position:fixed; top:0; right:0; width:480px; max-width:96vw; height:100vh; background:#fff; z-index:99999; box-shadow:-4px 0 24px rgba(0,0,0,0.18); overflow-y:auto; transition:transform 0.3s ease;">

	<div style="background:#1a73e8; color:#fff; padding:16px 20px; display:flex; align-items:center; justify-content:space-between;">
		<div>
			<h5 style="margin:0; font-size:16px; font-weight:700;">
				<i class="fas fa-layer-group"></i>&nbsp;
				<?php esc_html_e('Bulk Collect Payment', 'school-management'); ?>
			</h5>
			<small id="wlsm-bulk-collect-count-label" style="opacity:0.85;">
				<?php esc_html_e('0 invoices selected', 'school-management'); ?>
			</small>
		</div>
		<button type="button" id="wlsm-bulk-collect-close" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer; padding:0 4px;" title="<?php esc_attr_e('Close', 'school-management'); ?>">
			<i class="fas fa-times"></i>
		</button>
	</div>

	<!-- Selected Invoices Summary -->
	<div style="padding:12px 20px; background:#f8f9fa; border-bottom:1px solid #e0e0e0;">
		<div id="wlsm-bulk-selected-invoices-list" style="font-size:13px; max-height:160px; overflow-y:auto;">
			<!-- Populated by JS -->
		</div>
		<div style="margin-top:8px; display:flex; justify-content:space-between; align-items:center;">
			<span style="font-size:13px; color:#555;"><?php esc_html_e('Total Due', 'school-management'); ?>:</span>
			<strong id="wlsm-bulk-total-due" style="font-size:15px; color:#1a73e8;">—</strong>
		</div>
	</div>

	<!-- Payment Form -->
	<form id="wlsm-bulk-collect-fee-form" style="padding:20px;">
		<input type="hidden" name="action" value="wlsm-submit-bulk-collect-fee">
		<input type="hidden" name="wlsm-bulk-collect-fee" value="<?php echo esc_attr($bulk_collect_nonce); ?>">
		<input type="hidden" id="wlsm-bulk-receipt-nonce" value="<?php echo esc_attr($bulk_receipt_nonce); ?>">
		<!-- invoice_ids[] populated dynamically by JS -->

		<div class="form-group">
			<label for="wlsm_bulk_payment_amount" class="wlsm-font-bold">
				<span class="wlsm-important">*</span> <?php esc_html_e('Payment Amount', 'school-management'); ?>:
			</label>
			<input type="number" step="any" min="0.01" name="payment_amount" class="form-control" id="wlsm_bulk_payment_amount"
				placeholder="<?php esc_attr_e('Enter amount', 'school-management'); ?>" required>
			<small class="text-muted"><?php esc_html_e('Amount will be distributed across selected invoices in order.', 'school-management'); ?></small>
		</div>

		<div class="form-group">
			<label for="wlsm_bulk_payment_method" class="wlsm-font-bold">
				<span class="wlsm-important">*</span> <?php esc_html_e('Payment Method', 'school-management'); ?>:
			</label>
			<select name="payment_method" class="form-control" id="wlsm_bulk_payment_method" required>
				<option value=""><?php esc_html_e('Select Payment Method', 'school-management'); ?></option>
				<?php foreach ($enabled_bulk_methods as $key => $label) : ?>
					<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="form-group">
			<label for="wlsm_bulk_payment_date" class="wlsm-font-bold">
				<span class="wlsm-important">*</span> <?php esc_html_e('Payment Date', 'school-management'); ?>:
			</label>
			<input type="text" name="payment_date" class="form-control wlsm_bulk_payment_date" id="wlsm_bulk_payment_date"
				placeholder="<?php esc_attr_e('Select date', 'school-management'); ?>"
				value="<?php echo esc_attr(WLSM_Config::get_date_text(current_time('Y-m-d H:i:s'))); ?>" required>
		</div>

		<!-- Cheque / DD extra fields -->
		<div id="wlsm_bulk_group_bank_name" class="form-group" style="display:none;">
			<label for="wlsm_bulk_bank_name" class="wlsm-font-bold">
				<?php esc_html_e('Bank Name', 'school-management'); ?>:
			</label>
			<input type="text" name="bank_name" class="form-control" id="wlsm_bulk_bank_name"
				placeholder="<?php esc_attr_e('Enter bank name', 'school-management'); ?>">
		</div>

		<div id="wlsm_bulk_group_cheque_number" class="form-group" style="display:none;">
			<label for="wlsm_bulk_cheque_number" class="wlsm-font-bold">
				<?php esc_html_e('Cheque Number', 'school-management'); ?>:
			</label>
			<input type="text" name="cheque_number" class="form-control" id="wlsm_bulk_cheque_number"
				placeholder="<?php esc_attr_e('Enter cheque number', 'school-management'); ?>">
		</div>

		<div id="wlsm_bulk_group_cheque_date" class="form-group" style="display:none;">
			<label for="wlsm_bulk_cheque_date" class="wlsm-font-bold">
				<?php esc_html_e('Cheque Date', 'school-management'); ?>:
			</label>
			<input type="text" name="cheque_date" class="form-control wlsm_bulk_cheque_date" id="wlsm_bulk_cheque_date"
				placeholder="<?php esc_attr_e('DD-MM-YYYY', 'school-management'); ?>">
		</div>

		<div class="form-group">
			<label for="wlsm_bulk_transaction_id" class="wlsm-font-bold">
				<?php esc_html_e('Transaction ID / Reference', 'school-management'); ?>:
			</label>
			<input type="text" name="transaction_id" class="form-control" id="wlsm_bulk_transaction_id"
				placeholder="<?php esc_attr_e('Optional', 'school-management'); ?>">
		</div>

		<div class="form-group">
			<label for="wlsm_bulk_authorized_by" class="wlsm-font-bold">
				<?php esc_html_e('Authorized By', 'school-management'); ?>:
			</label>
			<input type="text" name="authorized_by" class="form-control" id="wlsm_bulk_authorized_by"
				placeholder="<?php esc_attr_e('Optional', 'school-management'); ?>">
		</div>

		<div class="form-group">
			<label for="wlsm_bulk_payment_note" class="wlsm-font-bold">
				<?php esc_html_e('Note', 'school-management'); ?>:
			</label>
			<textarea name="payment_note" class="form-control" id="wlsm_bulk_payment_note" rows="2"
				placeholder="<?php esc_attr_e('Optional note', 'school-management'); ?>"></textarea>
		</div>

		<div id="wlsm-bulk-collect-error" class="alert alert-danger" style="display:none;"></div>

		<div style="display:flex; gap:10px; margin-top:8px;">
			<button type="button" id="wlsm-bulk-collect-cancel-btn" class="btn btn-secondary" style="flex:1;">
				<i class="fas fa-times"></i> <?php esc_html_e('Cancel', 'school-management'); ?>
			</button>
			<button type="submit" id="wlsm-bulk-collect-submit-btn" class="btn btn-success" style="flex:2;">
				<i class="fas fa-money-bill-wave"></i>
				<?php esc_html_e('Collect Payment', 'school-management'); ?>
			</button>
		</div>
	</form>
</div>

<!-- Bulk Receipt Modal (shown after success) -->
<div id="wlsm-bulk-receipt-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:100000; align-items:center; justify-content:center;">
	<div style="background:#fff; border-radius:8px; padding:24px; max-width:520px; width:94%; max-height:90vh; overflow-y:auto; box-shadow:0 8px 32px rgba(0,0,0,0.25);">
		<div style="text-align:center; margin-bottom:16px;">
			<div style="width:56px; height:56px; background:#e8f5e9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 12px;">
				<i class="fas fa-check-circle" style="color:#43a047; font-size:28px;"></i>
			</div>
			<h5 style="margin:0 0 4px; font-weight:700;"><?php esc_html_e('Payment Collected!', 'school-management'); ?></h5>
			<p id="wlsm-bulk-receipt-summary" style="margin:0; color:#555; font-size:14px;"></p>
		</div>
		<div style="display:flex; gap:10px; justify-content:center; margin-top:16px;">
			<button type="button" id="wlsm-bulk-receipt-print-btn" class="btn btn-primary">
				<i class="fas fa-print"></i> <?php esc_html_e('Print Receipt', 'school-management'); ?>
			</button>
			<button type="button" id="wlsm-bulk-receipt-close-btn" class="btn btn-secondary">
				<?php esc_html_e('Close', 'school-management'); ?>
			</button>
		</div>
	</div>
</div>
