<?php
defined( 'ABSPATH' ) || die();

if ( ! count( $students ) ) {
	?>
	<div class="text-center">
		<span class="text-danger wlsm-font-bold">
			<?php esc_html_e( 'No student found.', 'school-management' ); ?>
		</span>
	</div>
	<?php
	return;
}

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Helper.php';

if ( isset( $from_front ) ) {
	$print_button_classes = 'button btn-sm btn-success';
} else {
	$print_button_classes = 'btn btn-sm btn-success';
}

$fields = unserialize( $id_card_template->fields );
$image_id = $id_card_template->image_id;
$image_url = wp_get_attachment_url( $image_id );

$card_orientation = isset($fields['orientation']) ? $fields['orientation'] : 'portrait';

$page_orientation = 'landscape';
// Override page orientation with POST variable if present
if ( isset( $_POST['page_orientation'] ) && ! empty( $_POST['page_orientation'] ) ) {
    $page_orientation = sanitize_text_field( $_POST['page_orientation'] );
}

// Dimensions
$css_width = '210mm'; // A4 width usually, but ID cards are smaller.
$css_height = '297mm';
// Actually standard CR80 is 85.6mm x 53.98mm.
// Certificate module uses px (800x600).
// If the user designed it in pixels in save.php (600x800 for portrait), we should use that.
// Editor uses 1013px x 638px (~300 DPI for CR80)
$width_px = 638;
$height_px = 1013;
if ($card_orientation == 'landscape') {
    $width_px = 1013;
    $height_px = 638;
}

// Map fields to student data
function wlsm_get_student_field_value($key, $student, $school_id) {
    switch ($key) {
        case 'name': return $student->student_name;
        case 'enrollment-number': return $student->enrollment_number;
        case 'admission-number': return $student->admission_number;
        case 'roll-number': return $student->roll_number;
        case 'class': return $student->class_label;
        case 'section': return $student->section_label;
        case 'dob': return $student->dob ? WLSM_Config::get_date_text($student->dob) : '';
        case 'caste': return isset($student->caste) ? $student->caste : '';
        case 'blood-group': return isset($student->blood_group) ? $student->blood_group : '';
        case 'father-name': return $student->father_name;
        case 'phone': return $student->phone;
        case 'email': return $student->email;
        case 'address': return $student->address;
        case 'session-label': return $student->session_label;
        case 'photo':
            if (!empty($student->photo_id)) {
                return wp_get_attachment_url($student->photo_id);
            }
            return WLSM_PLUGIN_URL . 'assets/images/student.jpg';
        // Add more as needed
        default: return '';
    }
}

// School settings for macros like school-name
$settings_general = WLSM_M_Setting::get_settings_general( $school_id );

// Fetch school details from schools table (name, phone, email, address are stored there, not in settings)
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_School.php';
$school_record = WLSM_M_School::fetch_school( $school_id );

$school_name = esc_html( WLSM_M_School::get_label_text( $school_record->label ?? '' ) );
$school_phone = esc_html( WLSM_M_School::get_phone_text( $school_record->phone ?? '' ) );
$school_email = esc_html( WLSM_M_School::get_email_text( $school_record->email ?? '' ) );
$school_address = esc_html( WLSM_M_School::get_address_text( $school_record->address ?? '' ) );
$school_logo_url = '';
$school_logo_url = '';
if ( ! empty ( $settings_general['school_logo'] ) ) {
    $school_logo_url = esc_url( wp_get_attachment_url( $settings_general['school_logo'] ) );
}

// CSS for Print (passed to JS)
$print_css = "@page { size: A4 " . esc_attr($page_orientation) . "; margin: 0; } body, html { margin: 0 !important; padding: 0 !important; } .wlsm-container, .wlsm-print-id-cards-container { width: 100% !important; margin: 0 !important; padding: 0 !important; }";
?>

<!-- Print ID cards. -->
<div class="wlsm-container d-flex mb-2">
	<div class="col-md-12 wlsm-text-center">
		<br>
		<!-- Pass empty styles array to prevent overriding inline styles with stylesheet if possible, or include bootstrap -->
		<button type="button" class="<?php echo esc_attr( $print_button_classes ); ?>" id="wlsm-print-custom-id-cards-btn" data-styles='["<?php echo esc_url( WLSM_PLUGIN_URL . 'assets/css/bootstrap.min.css' ); ?>"]' data-css="<?php echo esc_attr($print_css); ?>" data-title="<?php esc_attr_e( 'ID Cards', 'school-management' ); ?>"><?php esc_html_e( 'Print ID Cards', 'school-management' ); ?>
		</button>
	</div>
</div>

<!-- Print ID cards section. -->
<div class="wlsm-container wlsm" id="wlsm-print-custom-id-cards">
	<div class="wlsm-print-id-cards-container">
        <style>
			@page {
				size: A4 <?php echo esc_html($page_orientation); ?>;
				margin: 0;
			}
            body, html {
                margin: 0 !important;
                padding: 0 !important;
            }
            .wlsm-container, .wlsm-print-id-cards-container {
                max-width: none !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .wlsm-id-card-wrapper {
                position: relative;
                margin: 5px;
                display: inline-block;
                vertical-align: top;
                page-break-inside: avoid;
                overflow: hidden;
                border: 1px solid #000;
                box-sizing: border-box;
            }
            .wlsm-custom-id-card {
                position: absolute;
                top: 0;
                left: 0;
                background-repeat: no-repeat;
                background-size: cover;
                transform-origin: top left;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                border: none; /* Border handled by wrapper */
                margin: 0;
            }
            .wlsm-custom-field {
                position: absolute;
                line-height: 1.2;
            }
            .wlsm-custom-field img {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
        </style>

		<?php
        // Standard CR80 Dimensions
        // We use high-res internal pixels (approx 300 DPI) for layout to match the editor defaults.
        // 1013px / 300 dpi * 25.4 = 85.7mm (close enough to 85.6mm)
        $width_px = 1013;
        $height_px = 638;

        // Physical dimensions for the wrapper
        $width_mm = '85.6mm';
        $height_mm = '53.98mm';

        // Scale factor: Browser pixels (96 DPI) vs Internal pixels (300 DPI)
        // 85.6mm is approx 323.5px at 96 DPI.
        // 323.5 / 1013 = 0.3193
        $scale_factor = 0.3193;

        if ( $card_orientation == 'landscape' ) {
            $wrapper_width_css = $width_mm;
            $wrapper_height_css = $height_mm;

            $card_width_px = $width_px;
            $card_height_px = $height_px;
        } else {
            // Portrait
            $wrapper_width_css = $height_mm;
            $wrapper_height_css = $width_mm;

            $card_width_px = $height_px;
            $card_height_px = $width_px;
        }

		foreach ( $students as $student ) {
		?>
        <div class="wlsm-id-card-wrapper" style="width: <?php echo esc_attr($wrapper_width_css); ?>; height: <?php echo esc_attr($wrapper_height_css); ?>;">
    		<div class="wlsm-custom-id-card" style="width: <?php echo esc_attr($card_width_px); ?>px; height: <?php echo esc_attr($card_height_px); ?>px; background-image: url('<?php echo esc_url($image_url); ?>'); transform: scale(<?php echo esc_attr($scale_factor); ?>); transform-origin: top left;">
                <?php foreach ($fields as $key => $field) {
                    if ($key == 'orientation') continue;
                    if (!$field['enable']) continue;

                    $style = '';
                    foreach ($field['props'] as $prop_key => $prop) {
                        $style .= $prop_key . ': ' . $prop['value'] . $prop['unit'] . ';';
                    }

                    $value = wlsm_get_student_field_value($key, $student, $school_id);
                    // Handle School Macros override
                    if ($key == 'school-name') $value = $school_name;
                    if ($key == 'school-phone') $value = $school_phone;
                    if ($key == 'school-email') $value = $school_email;
                    if ($key == 'school-address') $value = $school_address;
                    if ($key == 'school-logo') $value = $school_logo_url;

                    // Render
                    ?>
                    <div class="wlsm-custom-field" style="<?php echo esc_attr($style); ?>">
                        <?php
                        $type = WLSM_Helper::get_certificate_field_type($key);
                        if ($type == 'image') {
                            if ($value) {
                                echo '<img src="' . esc_url($value) . '">';
                            }
                        } else {
                            echo esc_html($value);
                        }
                        ?>
                    </div>
                <?php } ?>
    		</div>
        </div>
		<?php
		}
		?>
	</div>
</div>
