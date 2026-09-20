<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';

global $wpdb;

$house = NULL;

$nonce_action = 'add-house';

$house_id = NULL;

$label = '';


if ( isset( $_GET['house_id'] ) && ! empty( $_GET['house_id'] ) ) {
	$house_id = absint( $_GET['house_id'] );
	$house    = WLSM_M_Staff_Class::get_house( $school_id, $house_id );

	if ( $house ) {
		$nonce_action = 'edit-house-' . $house->ID;

		$house_id = $house->ID;

		$label = $house->label;
	}
}
?>
<div class="row justify-content-md-center ">
	<div class="col-md-12">
		<div class="card col">
				<div class="row<?php if ( $house ) { echo ' justify-content-md-center'; } ?>">
					<?php if ( ! $house ) { ?>
					<div class="col-md-7">
						<h2 class="h4 border-bottom pb-2">
							<i class="fas fa-home text-primary"></i>
							<?php esc_html_e( 'Houses', 'school-management' ); ?>
						</h2>
						<table class="table table-hover table-bordered" id="wlsm-class-house-table">
							<thead>
								<tr class="text-white bg-primary">
									<th scope="col"><?php esc_html_e( '#', 'school-management' ); ?></th>
									<th scope="col"><?php esc_html_e( 'House', 'school-management' ); ?></th>
									<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management' ); ?></th>
								</tr>
							</thead>
						</table>
					</div>
					<?php } ?>
					<div class="col-md-5">
						<div class="wlsm-page-heading-box">
							<h2 class="h4 border-bottom pb-2 wlsm-page-heading">
								<i class="fas fa-plus-square text-primary"></i>
								<?php esc_html_e( 'Add New House', 'school-management' ); ?>
							</h2>
						</div>
						<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-house-form">

							<?php $nonce = wp_create_nonce( $nonce_action ); ?>
							<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">

							<input type="hidden" name="action" value="wlsm-save-house">

							<?php if ( $house ) { ?>
							<input type="hidden" name="house_id" value="<?php echo esc_attr( $house->ID ); ?>">
							<?php } ?>

							<div class="form-group">
								<label for="wlsm_house_label" class="font-weight-bold"><?php esc_html_e( 'House Name', 'school-management' ); ?>:</label>
								<input type="text" name="label" class="form-control" id="wlsm_house_label" placeholder="<?php esc_attr_e( 'Enter house name', 'school-management' ); ?>" value="<?php echo esc_attr( $label ); ?>">
							</div>

							<div>
								<span class="float-md-right">
									<button type="submit" class="btn btn-sm btn-primary" id="wlsm-save-house-btn">
										<?php
										if ( $house ) {
											?>
											<i class="fas fa-save"></i>&nbsp;
											<?php
											esc_html_e( 'Update House', 'school-management' );
										} else {
											?>
											<i class="fas fa-plus-square"></i>&nbsp;
											<?php
											esc_html_e( 'Add New House', 'school-management' );
										}
										?>
									</button>
								</span>
							</div>

						</form>
					</div>
				</div>

		</div>
	</div>
</div>
