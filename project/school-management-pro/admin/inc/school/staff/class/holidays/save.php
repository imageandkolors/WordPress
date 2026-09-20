<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Role.php';

global $wpdb;

$page_url = WLSM_M_Staff_Class::get_holidays_page_url();

$school_id = $current_school['id'];

$holiday = NULL;

$nonce_action = 'add-holiday';

$description  = '';
$start_date   = '';
$end_date     = '';

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id    = absint( $_GET['id'] );
	$holiday = WLSM_M_Staff_Class::get_holiday( $school_id, $id );

	if ( $holiday ) {
		$nonce_action = 'edit-holiday-' . $holiday->ID;

		$description  = $holiday->description;
		$start_date   = $holiday->start_date;
		$end_date     = $holiday->end_date;
	}
}
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php
					if ( $holiday ) {
						esc_html_e( 'Edit Holiday', 'school-management' );
					} else {
						esc_html_e( 'Add New Holiday', 'school-management' );
					}
					?>
				</span>
			</span>
			<span class="float-md-right">
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-calendar-alt"></i>&nbsp;
					<?php esc_html_e( 'View All', 'school-management' ); ?>
				</a>
			</span>
		</div>
		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-holiday-form">

			<?php $nonce = wp_create_nonce( $nonce_action ); ?>
			<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

			<input type="hidden" name="action" value="wlsm-save-holiday">

			<?php if ( $holiday ) { ?>
			<input type="hidden" name="holiday_id" value="<?php echo esc_attr( $holiday->ID ); ?>">
			<?php } ?>

			<div class="wlsm-form-section">
				<div class="form-row">
					<div class="form-group col-md-12">
						<label for="wlsm_description" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Description', 'school-management' ); ?>:
						</label>
						<textarea name="description" class="form-control" id="wlsm_description" placeholder="<?php esc_attr_e( 'Enter holiday description', 'school-management' ); ?>" rows="3"><?php echo esc_attr( stripcslashes( $description ) ); ?></textarea>
					</div>


					<div class="form-group col-md-4">
						<label for="wlsm_start_date" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Start Date', 'school-management' ); ?>:
						</label>
						<input type="text" name="start_date" class="form-control" id="wlsm_start_date" placeholder="<?php esc_attr_e( 'Enter start date', 'school-management' ); ?>" value="<?php echo esc_attr( WLSM_Config::get_date_text( $start_date ) ); ?>">
					</div>

					<div class="form-group col-md-4">
						<label for="wlsm_end_date" class="wlsm-font-bold">
							<?php esc_html_e( 'End Date', 'school-management' ); ?>:
						</label>
						<input type="text" name="end_date" class="form-control" id="wlsm_end_date" placeholder="<?php esc_attr_e( 'Enter end date', 'school-management' ); ?>" value="<?php echo esc_attr( WLSM_Config::get_date_text( $end_date ) ); ?>">
						<small class="text-secondary"><?php esc_html_e( 'Leave empty for single day holiday.', 'school-management' ); ?></small>
					</div>
				</div>
			</div>

			<div class="row mt-2">
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-primary" id="wlsm-save-holiday-btn">
						<?php
						if ( $holiday ) {
							?>
							<i class="fas fa-save"></i>&nbsp;
							<?php
							esc_html_e( 'Update Holiday', 'school-management' );
						} else {
							?>
							<i class="fas fa-plus-square"></i>&nbsp;
							<?php
							esc_html_e( 'Add New Holiday', 'school-management' );
						}
						?>
					</button>
				</div>
			</div>

		</form>
	</div>
</div>
