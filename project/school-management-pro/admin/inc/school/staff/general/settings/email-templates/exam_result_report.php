<?php
defined( 'ABSPATH' ) || die();

// Email Exam Result Report settings.
$settings_email_exam_result_report = WLSM_M_Setting::get_settings_email_exam_result_report( $school_id );
$email_exam_result_report_enable   = $settings_email_exam_result_report['enable'];
$email_exam_result_report_subject  = $settings_email_exam_result_report['subject'];
$email_exam_result_report_body     = $settings_email_exam_result_report['body'];

$email_exam_result_report_placeholders = WLSM_SMS::exam_result_report_placeholders();
?>
<button type="button" class="mt-2 btn btn-block btn-primary" data-toggle="collapse" data-target="#wlsm_email_exam_result_report_fields" aria-expanded="true" aria-controls="wlsm_email_exam_result_report_fields">
	<?php esc_html_e( 'Exam Result Report Template', 'school-management' ); ?>
</button>

<div class="collapse border border-top-0 border-primary p-3" id="wlsm_email_exam_result_report_fields">

	<div class="wlsm_email_template wlsm_email_exam_result_report">
		<div class="row">
			<div class="col-md-3">
				<label for="wlsm_email_exam_result_report_enable" class="wlsm-font-bold">
					<?php esc_html_e( 'Exam Result Report Email', 'school-management' ); ?>:
				</label>
			</div>
			<div class="col-md-9">
				<div class="form-group">
					<label for="wlsm_email_exam_result_report_enable" class="wlsm-font-bold">
						<input <?php checked( $email_exam_result_report_enable, true, true ); ?> type="checkbox" name="email_exam_result_report_enable" id="wlsm_email_exam_result_report_enable" value="1">
						<?php esc_html_e( 'Enable', 'school-management' ); ?>
					</label>
				</div>
			</div>
		</div>
	</div>

	<div class="wlsm_email_template wlsm_email_exam_result_report mb-3">
		<div class="row">
			<div class="col-md-12">
				<span class="wlsm-font-bold wlsm-font-sm"><?php esc_html_e( 'You can use the following variables:', 'school-management' ); ?></span>
				<div class="row">
					<?php foreach ( $email_exam_result_report_placeholders as $key => $value ) { ?>
					<div class="col-sm-6 col-md-3 pb-1 pt-1 border">
						<span class="wlsm-font-bold text-secondary"><?php echo esc_html( $value ); ?></span>
						<br>
						<span><?php echo esc_html( $key ); ?></span>
					</div>
					<?php } ?>
				</div>
			</div>
		</div>
	</div>

	<div class="wlsm_email_template wlsm_email_exam_result_report">
		<div class="row">
			<div class="col-md-3">
				<label for="wlsm_email_exam_result_report_subject" class="wlsm-font-bold"><?php esc_html_e( 'Email Subject', 'school-management' ); ?>:</label>
			</div>
			<div class="col-md-9">
				<div class="form-group">
					<input name="email_exam_result_report_subject" type="text" id="wlsm_email_exam_result_report_subject" value="<?php echo esc_attr( $email_exam_result_report_subject ); ?>" class="form-control" placeholder="<?php esc_attr_e( 'Email Subject', 'school-management' ); ?>">
				</div>
			</div>
		</div>
	</div>

	<div class="wlsm_email_template wlsm_email_exam_result_report">
		<div class="row">
			<div class="col-md-3">
				<label for="wlsm_email_exam_result_report_body" class="wlsm-font-bold"><?php esc_html_e( 'Email Body', 'school-management' ); ?>:</label>
			</div>
			<div class="col-md-9">
				<div class="form-group">
					<?php
					$settings = array(
						'media_buttons' => false,
						'textarea_name' => 'email_exam_result_report_body',
						'textarea_rows' => 10,
						'wpautop'       => true,
					);
					wp_editor( wp_kses_post( stripslashes( $email_exam_result_report_body ?? '' ) ), 'wlsm_email_exam_result_report_body', $settings );
					?>
				</div>
			</div>
		</div>
	</div>

</div>
