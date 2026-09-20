<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Gate_Pass.php';
?>

<!-- Print gate pass. -->
<div class="wlsm-container d-flex mb-2">
	<div class="col-md-12 wlsm-text-center">
		<br>
		<button type="button" class="btn btn-sm btn-success" id="wlsm-print-gate-pass-btn"
			data-styles='["<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/bootstrap.min.css' ); ?>","<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/wlsm-school-header.css' ); ?>","<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/print/wlsm-gate-pass.css' ); ?>"]'
			data-title="<?php echo esc_attr__( 'Gate Pass', 'school-management' ); ?> #<?php echo absint( $gate_pass->ID ); ?>">
			<?php esc_html_e( 'Print Gate Pass', 'school-management' ); ?>
		</button>
	</div>
</div>

<!-- Print gate pass section. -->
<div class="wlsm-container wlsm" id="wlsm-print-gate-pass">
	<div class="wlsm-print-gate-pass-container">
		<?php require WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/partials/school_header.php'; ?>

		<div class="row">
			<div class="col-md-12">
				<div class="wlsm-h5 wlsm-gate-pass-heading text-center mb-3">
					<?php esc_html_e( 'Visitor Gate Pass', 'school-management' ); ?>
				</div>
			</div>
		</div>

		<table class="table table-bordered wlsm-gate-pass-print-table">
			<tbody>
				<tr>
					<th style="width: 20%;"><?php esc_html_e( 'Pass No:', 'school-management' ); ?></th>
					<td style="width: 30%; font-weight: bold;"><?php echo absint( $gate_pass->ID ); ?></td>
					<th style="width: 20%;"><?php esc_html_e( 'Date:', 'school-management' ); ?></th>
					<td style="width: 30%; font-weight: bold;"><?php echo esc_html( WLSM_Config::get_date_text( $gate_pass->visit_date ) ); ?></td>
				</tr>

				<!-- Visitor Details Section -->
				<tr class="wlsm-gate-pass-section-row">
					<th colspan="4" class="text-center text-uppercase bg-light p-2"><?php esc_html_e( 'Visitor Details', 'school-management' ); ?></th>
				</tr>
				<tr>
					<?php if ( ! empty( $visitor_photo_url ) ) : ?>
					<td rowspan="4" class="text-center align-middle wlsm-gate-pass-photo-cell" style="width: 120px; padding: 10px;">
						<img src="<?php echo esc_url( $visitor_photo_url ); ?>" alt="<?php esc_attr_e( 'Visitor Photo', 'school-management' ); ?>" class="wlsm-gate-pass-print-photo" style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;">
					</td>
					<?php endif; ?>
					<th <?php echo empty( $visitor_photo_url ) ? 'style="width: 20%;"' : ''; ?>><?php esc_html_e( 'Name:', 'school-management' ); ?></th>
					<td <?php echo empty( $visitor_photo_url ) ? 'colspan="3"' : 'colspan="2"'; ?>><?php echo esc_html( WLSM_M_Staff_Gate_Pass::get_visitor_name_text( $gate_pass->visitor_name ) ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Mobile:', 'school-management' ); ?></th>
					<td <?php echo empty( $visitor_photo_url ) ? 'colspan="3"' : 'colspan="2"'; ?>><?php echo esc_html( $gate_pass->visitor_mobile ? $gate_pass->visitor_mobile : '-' ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Relation:', 'school-management' ); ?></th>
					<td <?php echo empty( $visitor_photo_url ) ? 'colspan="3"' : 'colspan="2"'; ?>><?php echo esc_html( WLSM_M_Staff_Gate_Pass::get_relation_text( $gate_pass->visitor_relation ) ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Reason to Meet:', 'school-management' ); ?></th>
					<td <?php echo empty( $visitor_photo_url ) ? 'colspan="3"' : 'colspan="2"'; ?>><?php echo esc_html( $gate_pass->reason_to_meet ? $gate_pass->reason_to_meet : '-' ); ?></td>
				</tr>

				<!-- Student Details Section -->
				<?php if ( $student ) : ?>
				<tr class="wlsm-gate-pass-section-row">
					<th colspan="4" class="text-center text-uppercase bg-light p-2"><?php esc_html_e( 'Student Details', 'school-management' ); ?></th>
				</tr>
				<tr>
					<?php if ( $student->photo ) : ?>
					<td rowspan="3" class="text-center align-middle wlsm-gate-pass-photo-cell" style="width: 120px; padding: 10px;">
						<img src="<?php echo esc_url( wp_get_attachment_image_url( $student->photo, 'thumbnail' ) ); ?>" alt="<?php esc_attr_e( 'Student Photo', 'school-management' ); ?>" class="wlsm-gate-pass-print-photo" style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc; border-radius: 4px;">
					</td>
					<?php endif; ?>
					<th <?php echo empty( $student->photo ) ? 'style="width: 20%;"' : ''; ?>><?php esc_html_e( 'Name:', 'school-management' ); ?></th>
					<td <?php echo empty( $student->photo ) ? 'colspan="3"' : 'colspan="2"'; ?>><?php echo esc_html( WLSM_M_Staff_Class::get_name_text( $student->name ) ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Class:', 'school-management' ); ?></th>
					<td <?php echo empty( $student->photo ) ? 'colspan="3"' : 'colspan="2"'; ?>><?php echo esc_html( $student->class_label ); ?> &mdash; <?php echo esc_html( $student->section_label ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Admission Number:', 'school-management' ); ?></th>
					<td <?php echo empty( $student->photo ) ? 'colspan="3"' : 'colspan="2"'; ?>><?php echo esc_html( $student->admission_number ? $student->admission_number : '-' ); ?></td>
				</tr>
				<?php endif; ?>

				<!-- Visit Details Section -->
				<tr class="wlsm-gate-pass-section-row">
					<th colspan="4" class="text-center text-uppercase bg-light p-2"><?php esc_html_e( 'Visit Information', 'school-management' ); ?></th>
				</tr>
				<tr>
					<th style="width: 20%;"><?php esc_html_e( 'In Time:', 'school-management' ); ?></th>
					<td style="width: 30%;"><?php echo esc_html( WLSM_M_Staff_Gate_Pass::get_time_text( $gate_pass->in_time ) ); ?></td>
					<th style="width: 20%;"><?php esc_html_e( 'Out Time:', 'school-management' ); ?></th>
					<td style="width: 30%;"><?php echo esc_html( WLSM_M_Staff_Gate_Pass::get_time_text( $gate_pass->out_time ) ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Authorized By:', 'school-management' ); ?></th>
					<td colspan="3"><?php echo esc_html( $gate_pass->authorized_by ? $gate_pass->authorized_by : '-' ); ?></td>
				</tr>
			</tbody>
		</table>

		<!-- Signature row -->
		<div class="row mt-5 mb-4 px-3" style="margin-top: 60px !important;">
			<div class="col-6 text-center">
				<div style="border-top: 1px solid #000; display: inline-block; padding-top: 8px; min-width: 180px;">
					<strong><?php esc_html_e( 'Security Signature', 'school-management' ); ?></strong>
				</div>
			</div>
			<div class="col-6 text-center">
				<div style="border-top: 1px solid #000; display: inline-block; padding-top: 8px; min-width: 180px;">
					<strong><?php esc_html_e( 'Authorized Signature', 'school-management' ); ?></strong>
				</div>
			</div>
		</div>
	</div>
</div>
