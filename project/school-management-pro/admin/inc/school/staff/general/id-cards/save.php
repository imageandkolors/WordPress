<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';

global $wpdb;

$current_school_user = WLSM_M_Role::can( 'manage_id_cards' );
if ( ! $current_school_user ) {
	die();
}
$current_school = $current_school_user['school'];

$page_url = WLSM_M_Staff_General::get_custom_id_cards_page_url();

$school_id = $current_school['id'];

$id_card = NULL;

$nonce_action = 'add-id-card';

$label    = '';
$image_id = '';
$orientation = 'landscape';

$fields = WLSM_Helper::get_id_card_dynamic_fields();

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id      = absint( $_GET['id'] );
	$id_card = WLSM_M_Staff_General::fetch_id_card( $school_id, $id );

	if ( $id_card ) {
		$nonce_action = 'edit-id-card-' . $id_card->ID;

		$label    = $id_card->label;
		$image_id = $id_card->image_id;

		if ( $id_card->fields ) {
			$saved_fields = unserialize( $id_card->fields );

			if ( is_array( $saved_fields ) && count( $saved_fields ) ) {
				foreach ( $fields as $field_key => $field_value ) {
					if ( array_key_exists( $field_key, $saved_fields ) ) {
						$fields[ $field_key ]['enable'] = isset( $saved_fields[ $field_key ]['enable'] ) ? $saved_fields[ $field_key ]['enable'] : 0;
						if ( isset( $saved_fields[ $field_key ]['props'] ) && is_array( $saved_fields[ $field_key ]['props'] ) ) {
							foreach ( $fields[ $field_key ]['props'] as $prop_key => $prop_value ) {
								if ( isset( $saved_fields[ $field_key ]['props'][ $prop_key ]['value'] ) ) {
									$fields[ $field_key ]['props'][ $prop_key ]['value'] = $saved_fields[ $field_key ]['props'][ $prop_key ]['value'];
								}
								// If saved data has unit, use it; otherwise keep default unit from helper
								if ( isset( $saved_fields[ $field_key ]['props'][ $prop_key ]['unit'] ) ) {
									$fields[ $field_key ]['props'][ $prop_key ]['unit'] = $saved_fields[ $field_key ]['props'][ $prop_key ]['unit'];
								}
							}
						}
					}
				}
				if (isset($saved_fields['orientation'])) {
					$orientation = $saved_fields['orientation'];
				}
			}
		}
	}
} else {
	$orientation = 'landscape';
}

	$js = '(function($) { "use strict";';

	foreach ( $fields as $field_key => $field_value ) {
		foreach ( $field_value['props'] as $key => $prop ) {
			// Skip width property for image to avoid validation error manually or add type checking if needed.
			// Assuming certificate.php styles are similar enough for now.

						$unit = isset( $prop['unit'] ) ? $prop['unit'] : '';
						$js .= "$(document).on('keyup', '#ctf-" . esc_attr( $field_key . '-' . $key ) . "', function() {
						var pos = $(this).val();
						$('.ctf-data-" . esc_attr( $field_key ) . "').css({'" . esc_attr( $key ) . "': pos + '" . esc_attr( $unit ) . "'});
					});";
		}

		$js .= "$(document).on('change', '#ctf-enable-" .  esc_attr( $field_key ) . "', function() {
					if($(this).is(':checked')) {
						$('.ctf-data-" . esc_attr( $field_key ) . "').css({'visibility': 'visible'});
					} else {
						$('.ctf-data-" . esc_attr( $field_key ) . "').css({'visibility': 'hidden'});
					}
				});";
	}

	$js .= "$(document).on('change', '#wlsm_orientation', function() {
		var orientation = $(this).val();
		if (orientation === 'landscape') {
			$('.wlsm-certificate-box').css({'width': '1013px', 'height': '638px'});
		} else {
			$('.wlsm-certificate-box').css({'width': '638px', 'height': '1013px'});
		}
	});

	// Update custom file input label
	$(document).on('change', '.custom-file-input', function() {
		var fileName = $(this).val().split('\\\\').pop();
		$(this).next('.custom-file-label').addClass('selected').html(fileName);
	});";

	$js .= '})(jQuery);';

	wp_register_script( 'wlsm-id-card-custom', false );
	wp_enqueue_script( 'wlsm-id-card-custom' );
	wp_add_inline_script( 'wlsm-id-card-custom', $js );
?>
<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php
					if ( $id_card ) {
						printf(
							wp_kses(
								/* translators: %s: ID card title */
								__( 'Edit ID Card: %s', 'school-management' ),
								array(
									'span' => array( 'class' => array() )
								)
							),
							esc_html( $label )
						);
					} else {
						esc_html_e( 'Add New ID Card Layout', 'school-management' );
					}
					?>
				</span>
			</span>
			<span class="float-md-right">
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-id-card"></i>&nbsp;
					<?php esc_html_e( 'View All', 'school-management' ); ?>
				</a>
			</span>
		</div>
		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-id-card-form" enctype="multipart/form-data">
			<?php
			$nonce_action = 'add-id-card';
			if ( $id_card ) {
				$nonce_action = 'edit-id-card-' . $id_card->ID;
			}
			?>
			<?php wp_nonce_field( $nonce_action, $nonce_action ); ?>

			<input type="hidden" name="action" value="wlsm-save-id-card">

			<?php if ( $id_card ) { ?>
			<input type="hidden" name="id_card_id" value="<?php echo esc_attr( $id_card->ID ); ?>">
			<?php } ?>

			<div class="row">
				<!-- Left Sidebar: Field Controls -->
				<div class="col-md-3 px-2">
					<div class="h-100">
						<div class="h6 font-weight-bold text-uppercase text-secondary mb-2 pl-1 small" style="letter-spacing: 0.5px;">
							<?php esc_html_e( 'Fields', 'school-management' ); ?>
						</div>

						<div class="accordion" id="wlsm-id-card-fields-accordion" style="max-height: 80vh; overflow-y: auto; overflow-x: hidden;">
							<?php
							foreach ( $fields as $field_key => $field_value ) {
							?>
							<div class="card mb-1 border rounded-sm shadow-none">
								<div class="card-header p-0 bg-white border-0" id="heading-<?php echo esc_attr( $field_key ); ?>">
									<h2 class="mb-0">
										<button class="btn btn-link btn-block text-left collapsed text-dark font-weight-bold py-2 px-3 d-flex justify-content-between align-items-center text-decoration-none" type="button" data-toggle="collapse" data-target="#collapse-<?php echo esc_attr( $field_key ); ?>" aria-expanded="false" aria-controls="collapse-<?php echo esc_attr( $field_key ); ?>" style="font-size: 0.85rem;">
											<span><?php echo esc_html( WLSM_Helper::get_certificate_field_label( $field_key ) ); ?></span>
											<i class="fas fa-chevron-down small text-muted"></i>
										</button>
									</h2>
								</div>

								<div id="collapse-<?php echo esc_attr( $field_key ); ?>" class="collapse" aria-labelledby="heading-<?php echo esc_attr( $field_key ); ?>" data-parent="#wlsm-id-card-fields-accordion">
									<div class="card-body p-2 bg-light border-top">
										<div class="form-group pb-0 mb-0">
											<div class="d-flex align-items-center justify-content-between mb-2 px-1">
												<label class="mb-0 small font-weight-bold text-muted" for="ctf-enable-<?php echo esc_attr( $field_key ); ?>"><?php esc_html_e( 'Visible', 'school-management' ); ?></label>
												<div class="custom-control custom-switch">
													<input <?php checked( $field_value['enable'], 1, true ); ?> class="custom-control-input" type="checkbox" name="enable-<?php echo esc_attr( $field_key ); ?>" id="ctf-enable-<?php echo esc_attr( $field_key ); ?>" value="1">
													<label class="custom-control-label" for="ctf-enable-<?php echo esc_attr( $field_key ); ?>"></label>
												</div>
											</div>

											<?php foreach ( $field_value['props'] as $key => $prop ) { ?>
											<div class="input-group mb-2">
												<div class="input-group-prepend">
													<span class="input-group-text">
														<?php echo esc_html( WLSM_Helper::get_certificate_property( $key ) ); ?>
													</span>
												</div>
												<input type="<?php echo esc_html( WLSM_Helper::get_certificate_field_type( $key ) ); ?>" name="<?php echo esc_attr( $field_key . '-' . $key ); ?>" class="form-control" id="ctf-<?php echo esc_attr( $field_key . '-' . $key ); ?>" value="<?php echo esc_attr( $prop['value'] ); ?>">
											</div>
											<?php } ?>
										</div>
									</div>
								</div>
							</div>
							<?php
							}
							?>
						</div>
					</div>
				</div>

				<!-- Right Main: Toolbar & Canvas -->
				<div class="col-md-9 px-2">
					<!-- Top Toolbar -->
					<div class="mb-3 border shadow-sm rounded">
						<div class="card-body p-2">
							<div class="form-row align-items-end">
								<div class="form-group col-md-4 mb-0">
									<label for="wlsm_label" class="font-weight-bold mb-1">
										<?php esc_html_e( 'Layout Name', 'school-management' ); ?>
									</label>
									<input type="text" name="label" class="form-control" id="wlsm_label" placeholder="<?php esc_attr_e( 'Enter ID card title', 'school-management' ); ?>" value="<?php echo esc_attr( $label ); ?>">
								</div>

								<div class="form-group col-md-3 mb-0">
									<label for="wlsm_orientation" class="font-weight-bold mb-1">
										<?php esc_html_e( 'Orientation', 'school-management' ); ?>
									</label>
									<select name="orientation" class="form-control selectpicker" id="wlsm_orientation">
										<option <?php selected( $orientation, 'landscape', true ); ?> value="landscape"><?php esc_html_e( 'Landscape', 'school-management' ); ?></option>
										<option <?php selected( $orientation, 'portrait', true ); ?> value="portrait"><?php esc_html_e( 'Portrait', 'school-management' ); ?></option>
									</select>
								</div>

								<div class="form-group col-md-3 mb-0">
									<label for="wlsm_image" class="font-weight-bold mb-1">
										<?php esc_html_e( 'Background Image', 'school-management' ); ?>
									</label>
									<div class="custom-file">
										<input type="file" class="custom-file-input" id="wlsm_image" name="image">
										<label class="custom-file-label text-truncate" for="wlsm_image"><?php esc_html_e( 'Choose File', 'school-management' ); ?></label>
									</div>
								</div>

								<div class="form-group col-md-2 mb-0 text-right">
									<button type="submit" class="btn btn-primary btn-block" id="wlsm-save-id-card-btn">
										<i class="fas fa-save mr-1"></i> <?php esc_html_e( 'Save', 'school-management' ); ?>
									</button>
								</div>
							</div>
						</div>
					</div>

					<!-- Canvas Area -->
					<div class="wlsm-id-card-preview-sticky bg-light d-flex justify-content-center align-items-center rounded border border-secondary border-dashed" style="position: sticky; top: 20px; overflow: auto; height: calc(100vh - 140px);">
						<?php
						$image_url = wp_get_attachment_url( $image_id );
						$fields['orientation'] = $orientation;
						?>
						<div class="wlsm-certificate-preview">
							<style>
								.wlsm-certificate-box {
									position: relative;
									background-repeat: no-repeat;
									background-size: cover;
									box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15); /* BS4 shadow-lg equivalent */
									transition: all 0.3s ease;
                                    zoom: 0.6;
                                    -moz-transform: scale(0.6);
                                    -moz-transform-origin: top center;
                                    transform: scale(0.6);
                                    transform-origin: top center;
								}
								.wlsm-certificate-field {
									position: absolute;
									border: 1px dashed transparent;
								}
								.wlsm-certificate-field:hover {
									border: 1px dashed #007bff; /* BS4 primary */
									cursor: move;
									background: rgba(0,123,255,0.05);
								}
							</style>
							<?php
							$css = 'width: 1013px; height: 638px;';
							if($orientation == 'portrait') {
								$css = 'width: 638px; height: 1013px;';
							}

							if ($image_url) {
								$css .= 'background-image: url(' . esc_url($image_url) . ');';
							} else {
								$css .= 'background-color: #fff; background-image: linear-gradient(45deg, #f3f3f3 25%, transparent 25%), linear-gradient(-45deg, #f3f3f3 25%, transparent 25%), linear-gradient(45deg, transparent 75%, #f3f3f3 75%), linear-gradient(-45deg, transparent 75%, #f3f3f3 75%); background-size: 20px 20px; background-position: 0 0, 0 10px, 10px -10px, -10px 0px;';
							}
							?>
							<div class="wlsm-certificate-box" style="<?php echo esc_attr($css); ?>">
								<?php foreach ($fields as $key => $field) {
									if ($key == 'orientation') continue;

									$style = '';
									foreach ($field['props'] as $prop_key => $prop) {
										$unit = isset( $prop['unit'] ) ? $prop['unit'] : '';
										$style .= $prop_key . ': ' . $prop['value'] . $unit . ';';
									}

									if (!$field['enable']) {
										$style .= 'visibility: hidden;';
									}
									?>
									<div class="wlsm-certificate-field ctf-data-<?php echo esc_attr($key); ?>" style="<?php echo esc_attr($style); ?>">
										<?php
										// Placeholder text
										echo esc_html(WLSM_Helper::get_certificate_field_label($key));
										?>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
