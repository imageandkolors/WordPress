<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Gate_Pass.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';

$page_url  = WLSM_M_Staff_Gate_Pass::get_gate_passes_page_url();
$school_id = $current_school['id'];
$session_id = $current_session['ID'];

$gate_pass    = null;
$nonce_action = 'add-gate-pass';

// Field defaults.
$visitor_name     = '';
$visitor_mobile   = '';
$visitor_relation = '';
$visitor_photo_id = 0;
$reason_to_meet   = '';
$authorized_by    = '';
$visit_date       = date( WLSM_Config::date_format() );
$in_time          = '';
$out_time         = '';
$student_id       = '';
$student_name     = '';
$student_photo    = '';
$class_label      = '';
$section_label    = '';

if ( isset( $_GET['id'] ) && ! empty( $_GET['id'] ) ) {
	$id        = absint( $_GET['id'] );
	$gate_pass = WLSM_M_Staff_Gate_Pass::get_gate_pass( $school_id, $session_id, $id );

	if ( $gate_pass ) {
		// Check edit permission.
		if ( ! WLSM_M_Role::can( 'edit_gate_passes' ) ) {
			?>
			<div class="alert alert-danger"><?php esc_html_e( 'You do not have permission to edit gate passes.', 'school-management' ); ?></div>
			<?php
			return;
		}

		$nonce_action     = 'edit-gate-pass-' . $gate_pass->ID;
		$visitor_name     = $gate_pass->visitor_name;
		$visitor_mobile   = $gate_pass->visitor_mobile;
		$visitor_relation = $gate_pass->visitor_relation;
		$visitor_photo_id = isset( $gate_pass->visitor_photo_id ) ? absint( $gate_pass->visitor_photo_id ) : 0;
		$reason_to_meet   = $gate_pass->reason_to_meet;
		$authorized_by    = $gate_pass->authorized_by;
		$visit_date       = $gate_pass->visit_date ? WLSM_Config::get_date_text( $gate_pass->visit_date ) : '';
		$in_time          = $gate_pass->in_time;
		$out_time         = $gate_pass->out_time;
		$student_id       = $gate_pass->student_record_id;

		// Load student info if linked.
		if ( $student_id ) {
			global $wpdb;
			$student = $wpdb->get_row( $wpdb->prepare(
				'SELECT sr.ID, sr.name, sr.photo_id as photo, c.label as class_label, se.label as section_label
				FROM ' . WLSM_STUDENT_RECORDS . ' as sr
				LEFT JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
				LEFT JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
				LEFT JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
				WHERE sr.ID = %d AND cs.school_id = %d',
				$student_id, $school_id
			) );
			if ( $student ) {
				$student_name  = $student->name;
				$student_photo = $student->photo;
				$class_label   = $student->class_label;
				$section_label = $student->section_label;
			}
		}
	}
}

// Add permission check.
if ( ! $gate_pass && ! WLSM_M_Role::can( 'add_gate_passes' ) ) {
	?>
	<div class="alert alert-danger"><?php esc_html_e( 'You do not have permission to add gate passes.', 'school-management' ); ?></div>
	<?php
	return;
}

// Fetch classes matching invoice form pattern.
if ( ! current_user_can( 'administrator' ) ) {
	$current_user_class = WLSM_M_Role::can('assigned_class');
	$role = '';
	if ( $current_user_class ) {
		$role = $current_user_class['school']['role'];
	}
	if ( $current_user_class && $role !== 'admin' ) {
		$classes = WLSM_M_Staff_Class::fetch_class_by_section_id( $school_id, absint($current_school['section_id']) );
	} else {
		$classes = WLSM_M_Staff_Class::fetch_classes( $school_id );
	}
} else {
	$classes = WLSM_M_Staff_Class::fetch_classes( $school_id );
}

// Load current admin name as default authorized_by.
if ( empty( $authorized_by ) ) {
	$current_user_obj = wp_get_current_user();
	$authorized_by    = $current_user_obj->display_name;
}
?>

<div class="row">
	<div class="col-md-12">
		<div class="mt-3 text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading-box">
				<span class="wlsm-section-heading">
					<?php if ( $gate_pass ) : ?>
						<?php esc_html_e( 'Edit Gate Pass', 'school-management' ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Add Gate Pass', 'school-management' ); ?>
					<?php endif; ?>
				</span>
			</span>
			<span class="float-md-right">
				<a href="<?php echo esc_url( $page_url ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-id-card"></i>&nbsp;
					<?php esc_html_e( 'All Gate Passes', 'school-management' ); ?>
				</a>
			</span>
		</div>

		<form action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" method="post" id="wlsm-save-gate-pass-form" enctype="multipart/form-data">

			<?php $nonce = wp_create_nonce( $nonce_action ); ?>
			<input type="hidden" name="<?php echo esc_attr( $nonce_action ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
			<input type="hidden" name="action" value="wlsm-save-gate-pass">

			<?php if ( $gate_pass ) : ?>
			<input type="hidden" name="gate_pass_id" value="<?php echo esc_attr( $gate_pass->ID ); ?>">
			<?php endif; ?>

			<!-- Visitor Information -->
			<div class="wlsm-form-section">
				<div class="wlsm-form-section-heading">
					<?php esc_html_e( 'Visitor Information', 'school-management' ); ?>
				</div>
				<div class="form-row">
					<div class="form-group col-md-4">
						<label for="wlsm_visitor_name" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Visitor Name', 'school-management' ); ?>:
						</label>
						<input type="text" name="visitor_name" class="form-control" id="wlsm_visitor_name"
							placeholder="<?php esc_attr_e( 'Enter visitor name', 'school-management' ); ?>"
							value="<?php echo esc_attr( stripcslashes( $visitor_name ) ); ?>">
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_visitor_mobile" class="wlsm-font-bold">
							<?php esc_html_e( 'Mobile Number', 'school-management' ); ?>:
						</label>
						<input type="text" name="visitor_mobile" class="form-control" id="wlsm_visitor_mobile"
							placeholder="<?php esc_attr_e( 'Enter mobile number', 'school-management' ); ?>"
							value="<?php echo esc_attr( $visitor_mobile ); ?>">
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_visitor_relation" class="wlsm-font-bold">
							<?php esc_html_e( 'Relation to Student', 'school-management' ); ?>:
						</label>
						<input type="text" name="visitor_relation" class="form-control" id="wlsm_visitor_relation"
							placeholder="<?php esc_attr_e( 'e.g. Father, Mother, Guardian', 'school-management' ); ?>"
							value="<?php echo esc_attr( stripcslashes( $visitor_relation ) ); ?>">
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-12">
						<label class="wlsm-font-bold"><?php esc_html_e( 'Visitor Photo', 'school-management' ); ?>:</label>
						<div class="d-flex align-items-center">
							<input type="hidden" name="visitor_photo_id" id="wlsm_visitor_photo_id" value="<?php echo esc_attr( $visitor_photo_id ); ?>">
							<?php
							$visitor_photo_url = $visitor_photo_id ? wp_get_attachment_image_url( $visitor_photo_id, 'thumbnail' ) : '';
							?>
							<img id="wlsm_visitor_photo_preview"
								src="<?php echo esc_url( $visitor_photo_url ); ?>"
								alt="<?php esc_attr_e( 'Visitor Photo', 'school-management' ); ?>"
								style="width:70px;height:70px;object-fit:cover;border-radius:4px;margin-right:12px;<?php echo $visitor_photo_url ? '' : 'display:none;'; ?>">
							<div>
								<label class="btn btn-sm btn-outline-primary mb-0" for="wlsm_visitor_photo_file" style="cursor:pointer;">
									<i class="fas fa-upload"></i>&nbsp;<?php esc_html_e( 'Upload Photo', 'school-management' ); ?>
								</label>
								<input type="file" id="wlsm_visitor_photo_file" accept="image/*" style="display:none;">
								<span id="wlsm-visitor-photo-uploading" style="display:none; font-size:12px; color:#555;">
									<i class="fas fa-circle-notch fa-spin"></i>&nbsp;<?php esc_html_e( 'Uploading…', 'school-management' ); ?>
								</span>
								<button type="button" class="btn btn-sm btn-outline-secondary ml-2" id="wlsm-visitor-webcam-btn">
									<i class="fas fa-camera"></i>&nbsp;<?php esc_html_e( 'Capture Webcam', 'school-management' ); ?>
								</button>
								<button type="button" class="btn btn-sm btn-outline-danger ml-2" id="wlsm-visitor-photo-remove-btn" style="<?php echo $visitor_photo_url ? '' : 'display:none;'; ?>">
									<i class="fas fa-times"></i>&nbsp;<?php esc_html_e( 'Remove', 'school-management' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Student Information -->
			<div class="wlsm-form-section">
				<div class="wlsm-form-section-heading">
					<?php esc_html_e( 'Student Information', 'school-management' ); ?>
				</div>
				<div class="form-row">
					<div class="form-group col-md-4">
						<label for="wlsm_class" class="wlsm-font-bold">
							<?php esc_html_e('Class', 'school-management'); ?>:
						</label>
						<select name="class_id" class="form-control selectpicker" data-nonce="<?php echo esc_attr(wp_create_nonce('get-class-sections')); ?>" id="wlsm_class" data-live-search="true">
							<option value=""><?php esc_html_e('Select Class', 'school-management'); ?></option>
							<?php foreach ($classes as $class) { ?>
								<option value="<?php echo esc_attr($class->ID); ?>">
									<?php echo esc_html(WLSM_M_Class::get_label_text($class->label)); ?>
								</option>
							<?php } ?>
						</select>
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_section" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e('Section', 'school-management'); ?>:
						</label>
						<select name="section_id" class="form-control selectpicker wlsm_section" id="wlsm_section" data-nonce="<?php echo esc_attr(wp_create_nonce('get-section-students')); ?>" data-live-search="true" title="<?php esc_attr_e('Select Section', 'school-management'); ?>" data-all-sections="1" data-fetch-students="1">
						</select>
					</div>
					<div class="form-group col-md-4">
						<label for="wlsm_student" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e('Student', 'school-management'); ?>:
						</label>
						<select name="student" class="form-control selectpicker" id="wlsm_student" data-live-search="true" title="<?php esc_attr_e('Select Student', 'school-management'); ?>" data-actions-box="true">
							<?php if ( $student_id ) : ?>
							<option value="<?php echo esc_attr( $student_id ); ?>" selected>
								<?php echo esc_html( stripcslashes( $student_name ) ); ?>
							</option>
							<?php endif; ?>
						</select>
						<div class="mt-2">
							<button type="button" class="btn btn-sm btn-outline-secondary" id="wlsm-view-gate-pass-student-btn" data-nonce="<?php echo esc_attr( wp_create_nonce( 'view-gate-pass-student' ) ); ?>">
								<i class="fas fa-eye"></i> <?php esc_html_e('View Student Details', 'school-management'); ?>
							</button>
						</div>
					</div>
				</div>

				<div class="form-row mt-2" id="wlsm-student-info-block" <?php echo $student_id ? '' : 'style="display:none;"'; ?>>
					<div class="col-md-12">
						<div class="card p-2">
							<div class="d-flex align-items-center">
								<div class="mr-3">
									<?php if ( $student_photo ) : ?>
									<img id="wlsm_student_photo"
										src="<?php echo esc_url( wp_get_attachment_image_url( $student_photo, 'thumbnail' ) ); ?>"
										alt="<?php esc_attr_e( 'Student Photo', 'school-management' ); ?>"
										style="width:60px; height:60px; object-fit:cover; border-radius:50%;">
									<?php else : ?>
									<img id="wlsm_student_photo" src="" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:50%; display:none;">
									<?php endif; ?>
								</div>
								<div>
									<div class="wlsm-font-bold" id="wlsm_student_name_display"><?php echo esc_html( stripcslashes( $student_name ) ); ?></div>
									<div class="text-muted small" id="wlsm_class_section_display">
										<?php echo esc_html( $class_label ? $class_label . ' - ' . $section_label : '' ); ?>
									</div>
									<div class="text-muted small mt-1">
										<strong><?php esc_html_e( 'Adm No:', 'school-management' ); ?></strong> <span id="wlsm_student_admission_number_display"><?php echo esc_html( $student_id ? stripcslashes( $invoice->admission_number ?? '' ) : '' ); ?></span>
										&nbsp;|&nbsp;
										<strong><?php esc_html_e( 'Phone:', 'school-management' ); ?></strong> <span id="wlsm_student_phone_display"><?php echo esc_html( $student_id ? stripcslashes( $invoice->phone ?? '' ) : '' ); ?></span>
									</div>
									<div class="text-muted small">
										<strong><?php esc_html_e( 'Father:', 'school-management' ); ?></strong> <span id="wlsm_student_father_display"><?php echo esc_html( $student_id ? stripcslashes( $invoice->father_name ?? '' ) : '' ); ?></span>
										&nbsp;|&nbsp;
										<strong><?php esc_html_e( 'Mother:', 'school-management' ); ?></strong> <span id="wlsm_student_mother_display"><?php echo esc_html( $student_id ? stripcslashes( $invoice->mother_name ?? '' ) : '' ); ?></span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Visit Details -->
			<div class="wlsm-form-section">
				<div class="wlsm-form-section-heading">
					<?php esc_html_e( 'Visit Details', 'school-management' ); ?>
				</div>
				<div class="form-row">
					<div class="form-group col-md-3">
						<label for="wlsm_visit_date" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Visit Date', 'school-management' ); ?>:
						</label>
						<input type="text" name="visit_date" class="form-control wlsm-datepicker" id="wlsm_visit_date"
							placeholder="<?php esc_attr_e( 'Select date', 'school-management' ); ?>"
							value="<?php echo esc_attr( $visit_date ); ?>" readonly>
					</div>
					<div class="form-group col-md-3">
						<label for="wlsm_in_time" class="wlsm-font-bold">
							<?php esc_html_e( 'In Time', 'school-management' ); ?>:
						</label>
						<input type="time" name="in_time" class="form-control" id="wlsm_in_time"
							value="<?php echo esc_attr( $in_time ); ?>">
					</div>
					<div class="form-group col-md-3">
						<label for="wlsm_out_time" class="wlsm-font-bold">
							<?php esc_html_e( 'Out Time', 'school-management' ); ?>:
						</label>
						<input type="time" name="out_time" class="form-control" id="wlsm_out_time"
							value="<?php echo esc_attr( $out_time ); ?>">
					</div>
					<div class="form-group col-md-3">
						<label for="wlsm_authorized_by" class="wlsm-font-bold">
							<span class="wlsm-important">*</span> <?php esc_html_e( 'Authorized By', 'school-management' ); ?>:
						</label>
						<input type="text" name="authorized_by" class="form-control" id="wlsm_authorized_by"
							placeholder="<?php esc_attr_e( 'Enter authorized by', 'school-management' ); ?>"
							value="<?php echo esc_attr( stripcslashes( $authorized_by ) ); ?>" readonly>
					</div>
				</div>
				<div class="form-row">
					<div class="form-group col-md-12">
						<label for="wlsm_reason_to_meet" class="wlsm-font-bold">
							<?php esc_html_e( 'Reason to Meet', 'school-management' ); ?>
							<small class="text-muted">(<?php esc_html_e( 'Optional', 'school-management' ); ?>)</small>:
						</label>
						<textarea name="reason_to_meet" class="form-control" id="wlsm_reason_to_meet"
							rows="3"
							placeholder="<?php esc_attr_e( 'Enter reason for visit', 'school-management' ); ?>"><?php echo esc_html( $reason_to_meet ); ?></textarea>
					</div>
				</div>
			</div>

			<div class="row mt-2 mb-3">
				<div class="col-md-12 text-center">
					<button type="submit" class="btn btn-primary" id="wlsm-save-gate-pass-btn">
						<?php if ( $gate_pass ) : ?>
							<i class="fas fa-save"></i>&nbsp;<?php esc_html_e( 'Update Gate Pass', 'school-management' ); ?>
						<?php else : ?>
							<i class="fas fa-plus-square"></i>&nbsp;<?php esc_html_e( 'Add Gate Pass', 'school-management' ); ?>
						<?php endif; ?>
					</button>
				</div>
			</div>

		</form>

		<!-- Webcam Capture Modal -->
		<div id="wlsm-webcam-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.75); z-index:999999; align-items:center; justify-content:center;">
			<div style="background:#fff; border-radius:8px; padding:24px; max-width:480px; width:100%; margin:auto; position:relative; top:50%; transform:translateY(-50%);">
				<h5 style="margin-top:0; margin-bottom:16px; font-weight:600;">
					<i class="fas fa-camera"></i>&nbsp;<?php esc_html_e( 'Capture Visitor Photo', 'school-management' ); ?>
				</h5>
				<!-- Live video feed -->
				<div id="wlsm-webcam-live" style="text-align:center;">
					<video id="wlsm-webcam-video" autoplay playsinline style="width:100%; max-height:320px; border-radius:4px; background:#000;"></video>
					<div style="margin-top:12px;">
						<button type="button" class="btn btn-success" id="wlsm-webcam-capture-btn">
							<i class="fas fa-circle"></i>&nbsp;<?php esc_html_e( 'Take Photo', 'school-management' ); ?>
						</button>
						<button type="button" class="btn btn-secondary ml-2" id="wlsm-webcam-cancel-btn">
							<?php esc_html_e( 'Cancel', 'school-management' ); ?>
						</button>
					</div>
				</div>
				<!-- Snapshot preview -->
				<div id="wlsm-webcam-preview" style="display:none; text-align:center;">
					<canvas id="wlsm-webcam-canvas" style="width:100%; max-height:320px; border-radius:4px;"></canvas>
					<p id="wlsm-webcam-uploading" style="display:none; color:#555; margin-top:8px;">
						<i class="fas fa-circle-notch fa-spin"></i>&nbsp;<?php esc_html_e( 'Uploading…', 'school-management' ); ?>
					</p>
					<div style="margin-top:12px;">
						<button type="button" class="btn btn-primary" id="wlsm-webcam-use-btn">
							<i class="fas fa-check"></i>&nbsp;<?php esc_html_e( 'Use This Photo', 'school-management' ); ?>
						</button>
						<button type="button" class="btn btn-secondary ml-2" id="wlsm-webcam-retake-btn">
							<i class="fas fa-redo"></i>&nbsp;<?php esc_html_e( 'Retake', 'school-management' ); ?>
						</button>
					</div>
				</div>
				<canvas id="wlsm-webcam-canvas-hidden" style="display:none;"></canvas>
			</div>
		</div>
		<script>
		var wlsmWebcamNonce = '<?php echo esc_js( wp_create_nonce( 'media-form' ) ); ?>';
		var wlsmAjaxUrl    = '<?php echo esc_js( admin_url( 'async-upload.php' ) ); ?>';
		</script>
	</div>
</div>
