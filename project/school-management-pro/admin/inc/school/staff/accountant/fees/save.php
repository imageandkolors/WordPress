<?php
defined('ABSPATH') || die();

global $wpdb;
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';
$page_url = WLSM_M_Staff_Accountant::get_fees_page_url();

$school_id = $current_school['id'];

$fee = NULL;

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	if ( ! WLSM_M_Role::can('edit_fees') ) {
		die();
	}
} else {
	if ( ! WLSM_M_Role::can('add_fees') ) {
		die();
	}
}

$nonce_action         = 'add-fee';
$class_id             = '';
$label                = '';
$amount               = '';
$period               = '';
$active_on_admission  = 1;
$active_on_dashboard  = 0;
$assign_on_addmission = 0;
$student_types         = [];
$fee_type              = '';
$include_on_promotion  = 1;
$fee_session_id        = isset($current_session['ID']) ? $current_session['ID'] : 0;

if (isset($_GET['id']) && !empty($_GET['id'])) {
	$id  = absint($_GET['id']);
	$fee = WLSM_M_Staff_Accountant::fetch_fee($school_id, $id);
	if ($fee) {
		$nonce_action = 'edit-fee-' . $fee->ID;

		$label                = $fee->label;
		$amount               = $fee->amount;
		$period               = $fee->period;
		$class_id             = $fee->class_id;
		$active_on_admission  = $fee->active_on_admission;
		$active_on_dashboard  = $fee->active_on_dashboard;
		$fee_session_id       = isset($fee->session_id) ? $fee->session_id : $fee_session_id;
		if (!is_null($fee->student_type)) {
			$student_types = unserialize($fee->student_type);
		} else {
			$student_types = [];
		}

		$fee_type = WLSM_Helper::normalize_fee_type($fee->fee_type);
		$include_on_promotion = isset($fee->include_on_promotion) ? (bool) $fee->include_on_promotion : $include_on_promotion;
	}
}

if ( ! current_user_can( 'administrator' ) ) {
	$current_user 	= WLSM_M_Role::can('assigned_class');
	if ( $current_user ) {
		$role 			= $current_user['school']['role'];
	}
	if ( $current_user && $role !== 'admin' ) {
		$classes  = WLSM_M_Staff_Class::fetch_class_by_section_id( $school_id, absint($current_school['section_id']) );
	}else{
		$classes  = WLSM_M_Staff_Class::fetch_classes( $school_id );
	}
}else{
	$classes  = WLSM_M_Staff_Class::fetch_classes( $school_id );
}

// Load all sessions for the dropdown
$sessions = WLSM_M_Session::fetch_sessions();

// $classes = WLSM_M_Staff_Class::fetch_classes($school_id);
$fee_periods         = WLSM_Helper::fee_period_list();
$available_fee_types = WLSM_Helper::fee_type_list();
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php
					if ($fee) {
						printf(
							wp_kses(
								/* translators: %s: fee type */
								__('Edit Fee Type: %s', 'school-management'),
								array(
									'span' => array('class' => array())
								)
							),
							esc_html(stripcslashes($label))
						);
					} else {
						esc_html_e('Add New Fee Type', 'school-management');
					}
					?>
				</span>
			</span>
			<span class="float-md-right">
				<a href="<?php echo esc_url($page_url); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-file-invoice"></i>&nbsp;
					<?php esc_html_e('View All', 'school-management'); ?>
				</a>
			</span>
		</div>
		<form action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" id="wlsm-save-fee-form">

			<?php $nonce = wp_create_nonce($nonce_action); ?>
			<input type="hidden" name="<?php echo esc_attr($nonce_action); ?>" value="<?php echo esc_attr($nonce); ?>">

			<input type="hidden" name="action" value="wlsm-save-fee">

			<?php if ($fee) { ?>
				<input type="hidden" name="fee_id" value="<?php echo esc_attr($fee->ID); ?>">
			<?php } ?>

			<div class="wlsm-form-section">
				<div class="form-row">
					<div class="form-group col-md-3">
						<label for="wlsm_label" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e('Fee Label', 'school-management'); ?>:
						</label>
						<input type="text" name="label" class="form-control" id="wlsm_label" placeholder="<?php esc_attr_e('Enter fee label', 'school-management'); ?>" value="<?php echo esc_attr(stripcslashes($label)); ?>">
					</div>
					<div class="form-group col-md-3">
						<label for="wlsm_fee_session" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e('Session', 'school-management'); ?>:
						</label>
						<select name="session_id" class="form-control selectpicker" id="wlsm_fee_session" data-live-search="true">
							<?php foreach ($sessions as $session) { ?>
								<option value="<?php echo esc_attr($session->ID); ?>" <?php selected($session->ID, $fee_session_id, true); ?>>
									<?php echo esc_html(WLSM_M_Session::get_label_text($session->label)); ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group col-md-3">
						<label for="wlsm_class" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e('Class', 'school-management'); ?>:
						</label>
						<select name="class_id[]" class="form-control selectpicker" data-nonce="<?php echo esc_attr(wp_create_nonce('get-class-sections')); ?>" id="wlsm_class" multiple data-live-search="true" data-actions-box="true">
							<?php foreach ($classes as $class) { ?>
								<option value="<?php echo esc_attr($class->ID); ?>" <?php selected($class->ID, $class_id, true); ?>>
									<?php echo esc_html(WLSM_M_Class::get_label_text($class->label)); ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group col-md-3">
						<label for="wlsm_student_type" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e('Student type', 'school-management'); ?>:
						</label>
						<select name="student_type[]" class="form-control selectpicker" data-nonce="<?php echo esc_attr(wp_create_nonce('get-class-sections')); ?>" id="wlsm_student_type" multiple data-live-search="true" data-actions-box="true">
							<?php
							$student_type_list = WLSM_Helper::student_type($school_id);
							foreach ($student_type_list as $type) { ?>
								<option value="<?php echo esc_attr($type->label); ?>" <?php echo in_array($type->label, $student_types) ? 'selected' : ''; ?>>
									<?php echo esc_html(WLSM_M_Class::get_label_text($type->label)); ?>
								</option>
							<?php } ?>
						</select>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-3">
						<label for="wlsm_period" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e('Period', 'school-management'); ?>:
						</label>
						<select name="period" class="form-control selectpicker" id="wlsm_period" data-live-search="true">
							<?php foreach ($fee_periods as $key => $value) { ?>
								<option value="<?php echo esc_attr($key); ?>" <?php selected($key, $period, true); ?>>
									<?php echo esc_html($value); ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group col-md-3">
						<label for="wlsm_amount" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e('Amount', 'school-management'); ?>:
						</label>
						<input type="number" step="any" min="0" name="amount" class="form-control" id="wlsm_amount" placeholder="<?php esc_attr_e('Enter amount', 'school-management'); ?>" value="<?php echo esc_attr($amount ? WLSM_Config::sanitize_money($amount) : ''); ?>">
					</div>
				</div>

				<div class="form-row mt-1">
					<div class="form-group col-md-6">
						<input <?php checked($active_on_admission, 1, true); ?> class="form-check-input mt-1" type="checkbox" name="active_on_admission" id="wlsm_active_on_admission" value="1">
						<label class="ml-4 mb-1 form-check-label wlsm-font-bold text-dark" for="wlsm_active_on_admission">
							<?php esc_html_e('Auto Generate Invoice On Admission', 'school-management'); ?>
						</label>
					</div>
					<div class="form-group col-md-6">
						<input <?php checked($include_on_promotion, 1, true); ?> class="form-check-input mt-1" type="checkbox" name="include_on_promotion" id="wlsm_include_on_promotion" value="1">
						<label class="ml-4 mb-1 form-check-label wlsm-font-bold text-dark" for="wlsm_include_on_promotion">
							<?php esc_html_e('Include On Promotion', 'school-management'); ?>
						</label>
						<label class="text-danger d-block mb-0" for="wlsm_include_on_promotion"> <?php esc_html_e('If disabled, this fee will not be assigned or invoiced when a student is promoted.', 'school-management'); ?></label>
					</div>
				</div>
				<div class="form-row mt-1">
					<div class="form-group col-md-6">
						<input <?php checked($active_on_dashboard, 1, true); ?> class="form-check-input mt-1" type="checkbox" name="active_on_dashboard" id="wlsm_active_on_dashboard" value="1">
						<label class="ml-4 mb-1 form-check-label wlsm-font-bold text-dark" for="wlsm_active_on_dashboard">
							<?php esc_html_e('Dashboard Disable? ', 'school-management'); ?>
						</label>
						<label class="text-danger" for=""> <?php esc_html_e('If Enabled: User Can Not Access Dashboard Until Fee is Paid Fully. ', 'school-management'); ?></label>
					</div>
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-primary" id="wlsm-save-fee-btn">
						<?php
						if ($fee) {
						?>
							<i class="fas fa-save"></i>&nbsp;
						<?php
							esc_html_e('Update Fee Type', 'school-management');
						} else {
						?>
							<i class="fas fa-plus-square"></i>&nbsp;
						<?php
							esc_html_e('Add New Fee Type', 'school-management');
						}
						?>
					</button>
				</div>
			</div>

		</form>
	</div>
</div>
