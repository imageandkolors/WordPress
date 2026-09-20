<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Gate_Pass.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';

$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

$gate_pass = null;

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id        = absint( $_GET['id'] );
	$gate_pass = WLSM_M_Staff_Gate_Pass::get_gate_pass( $school_id, $session_id, $id );
}

if ( ! $gate_pass ) {
	?>
	<div class="alert alert-danger"><?php esc_html_e( 'Gate pass not found.', 'school-management' ); ?></div>
	<?php
	return;
}

// Load school info.
global $wpdb;
$school = $wpdb->get_row( $wpdb->prepare( 'SELECT * FROM ' . WLSM_SCHOOLS . ' WHERE ID = %d', $school_id ) );

// Load student info.
$student = null;
if ( $gate_pass->student_record_id ) {
	$student = $wpdb->get_row( $wpdb->prepare(
		'SELECT sr.name, sr.photo, sr.enrollment_number, c.label as class_label, se.label as section_label
		FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		LEFT JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		LEFT JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		LEFT JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		WHERE sr.ID = %d',
		$gate_pass->student_record_id
	) );
}

$school_name   = $school ? stripcslashes( $school->name ) : get_bloginfo( 'name' );
$school_logo   = $school && $school->logo ? wp_get_attachment_image_url( $school->logo, 'medium' ) : '';
$school_address= $school && $school->address ? stripcslashes( $school->address ) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php esc_html_e( 'Gate Pass', 'school-management' ); ?> #<?php echo absint( $gate_pass->ID ); ?></title>
<style>
	* { box-sizing: border-box; margin: 0; padding: 0; }
	body { font-family: Arial, sans-serif; font-size: 13px; color: #333; background: #fff; }
	.gp-wrap { max-width: 600px; margin: 20px auto; border: 2px solid #333; padding: 0; }
	.gp-header { background: #1a237e; color: #fff; padding: 16px 20px; display: flex; align-items: center; }
	.gp-header img { max-height: 60px; max-width: 80px; margin-right: 14px; background: #fff; padding: 2px; border-radius: 4px; }
	.gp-header .gp-school-info h2 { font-size: 18px; margin-bottom: 3px; }
	.gp-header .gp-school-info p { font-size: 11px; opacity: .85; }
	.gp-title { text-align: center; background: #e8eaf6; border-top: 1px solid #9fa8da; border-bottom: 1px solid #9fa8da; padding: 8px; font-size: 15px; font-weight: bold; letter-spacing: 1px; color: #1a237e; text-transform: uppercase; }
	.gp-body { padding: 16px 20px; }
	.gp-row { display: flex; margin-bottom: 10px; gap: 12px; }
	.gp-student-block { display: flex; align-items: flex-start; gap: 14px; background: #f5f5f5; border: 1px solid #ddd; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; }
	.gp-student-photo { width: 70px; height: 70px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc; }
	.gp-student-photo-placeholder { width: 70px; height: 70px; background: #e0e0e0; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #aaa; font-size: 11px; text-align: center; }
	.gp-student-info label { font-size: 11px; color: #888; }
	.gp-student-info .gp-student-name { font-size: 15px; font-weight: bold; }
	.gp-student-info .gp-student-sub { font-size: 12px; color: #555; }
	.gp-field { flex: 1; }
	.gp-field label { display: block; font-size: 10px; text-transform: uppercase; color: #888; margin-bottom: 2px; }
	.gp-field .gp-value { font-size: 13px; font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 3px; min-height: 20px; }
	.gp-reason { margin-bottom: 14px; }
	.gp-reason label { font-size: 10px; text-transform: uppercase; color: #888; display: block; margin-bottom: 3px; }
	.gp-reason .gp-value { font-size: 13px; border: 1px solid #ccc; padding: 6px 8px; border-radius: 4px; min-height: 40px; }
	.gp-footer { padding: 14px 20px; border-top: 1px solid #ddd; }
	.gp-sig-row { display: flex; gap: 20px; margin-top: 30px; }
	.gp-sig { flex: 1; border-top: 1px solid #333; padding-top: 4px; font-size: 11px; text-align: center; color: #555; }
	.gp-passno { text-align: right; font-size: 11px; color: #888; margin-bottom: 8px; }
	@media print {
		html, body { margin: 0; }
		.gp-wrap { max-width: 100%; margin: 0; border: 1px solid #333; }
		.wlsm-no-print { display: none !important; }
	}
</style>
</head>
<body>

<div style="text-align:center; margin: 12px 0;" class="wlsm-no-print">
	<button onclick="window.print();" style="padding:8px 20px; background:#1a237e; color:#fff; border:none; border-radius:4px; cursor:pointer; font-size:14px;">
		&#128438; <?php esc_html_e( 'Print Gate Pass', 'school-management' ); ?>
	</button>
</div>

<div class="gp-wrap">

	<!-- Header -->
	<div class="gp-header">
		<?php if ( $school_logo ) : ?>
		<img src="<?php echo esc_url( $school_logo ); ?>" alt="<?php esc_attr_e( 'School Logo', 'school-management' ); ?>">
		<?php endif; ?>
		<div class="gp-school-info">
			<h2><?php echo esc_html( $school_name ); ?></h2>
			<?php if ( $school_address ) : ?>
			<p><?php echo esc_html( $school_address ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Title -->
	<div class="gp-title"><?php esc_html_e( 'Visitor Gate Pass', 'school-management' ); ?></div>

	<div class="gp-body">

		<div class="gp-passno">
			<?php esc_html_e( 'Pass #', 'school-management' ); ?><?php echo absint( $gate_pass->ID ); ?> &nbsp;|&nbsp;
			<?php esc_html_e( 'Date:', 'school-management' ); ?> <?php echo esc_html( WLSM_Config::get_date_text( $gate_pass->visit_date ) ); ?>
		</div>

		<!-- Student Block -->
		<?php if ( $student ) : ?>
		<div class="gp-student-block">
			<?php if ( $student->photo ) : ?>
			<img class="gp-student-photo"
				src="<?php echo esc_url( wp_get_attachment_image_url( $student->photo, 'thumbnail' ) ); ?>"
				alt="<?php esc_attr_e( 'Student Photo', 'school-management' ); ?>">
			<?php else : ?>
			<div class="gp-student-photo-placeholder"><?php esc_html_e( 'No Photo', 'school-management' ); ?></div>
			<?php endif; ?>
			<div class="gp-student-info">
				<label><?php esc_html_e( 'Student', 'school-management' ); ?></label>
				<div class="gp-student-name"><?php echo esc_html( stripcslashes( $student->name ) ); ?></div>
				<div class="gp-student-sub">
					<?php echo esc_html( $student->class_label ); ?> &mdash; <?php echo esc_html( $student->section_label ); ?>
					<?php if ( $student->enrollment_number ) : ?>
					| <?php esc_html_e( 'Enroll:', 'school-management' ); ?> <?php echo esc_html( $student->enrollment_number ); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endif; ?>

		<!-- Visitor Row -->
		<div class="gp-row">
			<div class="gp-field">
				<label><?php esc_html_e( 'Visitor Name', 'school-management' ); ?></label>
				<div class="gp-value"><?php echo esc_html( stripcslashes( $gate_pass->visitor_name ) ); ?></div>
			</div>
			<div class="gp-field">
				<label><?php esc_html_e( 'Mobile', 'school-management' ); ?></label>
				<div class="gp-value"><?php echo esc_html( $gate_pass->visitor_mobile ? $gate_pass->visitor_mobile : '-' ); ?></div>
			</div>
			<div class="gp-field">
				<label><?php esc_html_e( 'Relation', 'school-management' ); ?></label>
				<div class="gp-value"><?php echo esc_html( $gate_pass->visitor_relation ? stripcslashes( $gate_pass->visitor_relation ) : '-' ); ?></div>
			</div>
		</div>

		<!-- Time Row -->
		<div class="gp-row">
			<div class="gp-field">
				<label><?php esc_html_e( 'In Time', 'school-management' ); ?></label>
				<div class="gp-value"><?php echo esc_html( $gate_pass->in_time ? $gate_pass->in_time : '-' ); ?></div>
			</div>
			<div class="gp-field">
				<label><?php esc_html_e( 'Out Time', 'school-management' ); ?></label>
				<div class="gp-value"><?php echo esc_html( $gate_pass->out_time ? $gate_pass->out_time : '-' ); ?></div>
			</div>
			<div class="gp-field">
				<label><?php esc_html_e( 'Authorized By', 'school-management' ); ?></label>
				<div class="gp-value"><?php echo esc_html( $gate_pass->authorized_by ? $gate_pass->authorized_by : '-' ); ?></div>
			</div>
		</div>

		<!-- Reason -->
		<?php if ( $gate_pass->reason_to_meet ) : ?>
		<div class="gp-reason">
			<label><?php esc_html_e( 'Reason to Meet', 'school-management' ); ?></label>
			<div class="gp-value"><?php echo esc_html( $gate_pass->reason_to_meet ); ?></div>
		</div>
		<?php endif; ?>

	</div>

	<!-- Footer / Signatures -->
	<div class="gp-footer">
		<div class="gp-sig-row">
			<div class="gp-sig"><?php esc_html_e( 'Visitor Signature', 'school-management' ); ?></div>
			<div class="gp-sig"><?php esc_html_e( 'Security', 'school-management' ); ?></div>
			<div class="gp-sig"><?php esc_html_e( 'Authorized By', 'school-management' ); ?></div>
		</div>
	</div>

</div>

</body>
</html>
