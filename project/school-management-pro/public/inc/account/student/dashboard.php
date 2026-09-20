<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Session.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Class.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';

global $wpdb;

$settings_dashboard = WLSM_M_Setting::get_settings_dashboard($school_id);
$school_enrollment_number = $settings_dashboard['school_enrollment_number'];
$school_admission_number = $settings_dashboard['school_admission_number'];

$student_name = WLSM_M_Staff_Class::get_name_text($student->student_name);
$class_school_id = $student->ID;
$class_id = $student->class_id;
$notices_per_page = WLSM_M::notices_per_page();
$notices_query = WLSM_M::notices_query($school_id);

$notices_total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM ({$notices_query}) AS combined_table", $school_id));
$notices_page = isset($_GET['notices_page']) ? absint($_GET['notices_page']) : 1;
$notices_page_offset = ($notices_page * $notices_per_page) - $notices_per_page;
$notices = $wpdb->get_results($wpdb->prepare($notices_query . ' ORDER BY n.ID DESC LIMIT %d, %d', $school_id, $notices_page_offset, $notices_per_page));

$filtered_notices = array_filter($notices, function ($notice) use ($student) {
    $notice_data = @unserialize($notice->notice_data);
    // If notice_data is not an array or empty, show notice to all
    if (!is_array($notice_data) || empty($notice_data)) {
        return true;
    }
    // If classes array doesn't exist or is empty, show notice to all
    if (!isset($notice_data['classes']) || empty($notice_data['classes'])) {
        return true;
    }
    // Check if notice is for student's class or for all classes
    return in_array($student->class_id, $notice_data['classes']) || in_array('all', $notice_data['classes']);
});

$section = WLSM_M_Staff_Class::get_school_section($school_id, $student->section_id);
$class_label = $section->class_label;
$section_label = $section->label;

$attendance = WLSM_M_Staff_General::get_student_attendance_stats($student->ID);
$invoices = WLSM_M_Staff_Accountant::get_student_pending_invoices($student->ID);

// Fetch upcoming holidays.
$upcoming_holidays = $wpdb->get_results($wpdb->prepare(
	'SELECT description, start_date, end_date FROM ' . WLSM_HOLIDAYS . ' WHERE school_id = %d AND end_date >= %s ORDER BY start_date ASC LIMIT 5',
	$school_id,
	current_time('Y-m-d')
));


$vehicle_id = $student->route_vehicle_id;
$transportation_details = [];
if ($vehicle_id) {
    $query = $wpdb->prepare(
        'SELECT ro.name, ro.fare, v.vehicle_number, v.driver_name, v.driver_phone
        FROM ' . WLSM_ROUTE_VEHICLE . ' as rov
        JOIN ' . WLSM_ROUTES . ' as ro ON ro.ID = rov.route_id
        JOIN ' . WLSM_VEHICLES . ' as v ON v.ID = rov.vehicle_id
        WHERE rov.ID = %d',
        $vehicle_id
    );
    $transportation_details = $wpdb->get_results($query);
}

$invoices = WLSM_M_Staff_Accountant::get_student_pending_invoices_paid($student->ID, 1);
$check_dashboard_display = WLSM_M_Setting::get_dash($invoices);

// Get leave stats.
$leave_stats = $wpdb->get_row($wpdb->prepare(
	'SELECT
		COUNT(lv.ID) as total,
		SUM(CASE WHEN lv.is_approved = 1 THEN 1 ELSE 0 END) as approved,
		SUM(CASE WHEN lv.is_approved = 0 THEN 1 ELSE 0 END) as pending,
		SUM(CASE WHEN lv.is_approved = 2 THEN 1 ELSE 0 END) as rejected
	FROM ' . WLSM_LEAVES . ' as lv
	WHERE lv.student_record_id = %d',
	$student->ID
));

$schools = $wpdb->get_results($wpdb->prepare('SELECT s.ID, s.label, s.phone, s.email, s.address, s.is_active FROM ' . WLSM_SCHOOLS . ' as s WHERE s.ID = %d', $school_id));

if ($schools[0]->is_active === '0') {
    echo '<span style="color: red;"> This School is not active </span>';
    die;
}

if ($invoices && 'paid' !== $check_dashboard_display) {
    require_once WLSM_PLUGIN_DIR_PATH . 'includes/partials/pending_fee_invoices.php';
} else {
    require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/student/partials/navigation.php'; ?>
    <div class="wlsm-content-area wlsm-section-dashboard wlsm-student-dashboard">
        <?php
        $total_days = (int) ($attendance['total_present'] + $attendance['total_absent'] + $attendance['total_late'] + $attendance['total_holiday']);
        $attendance_percent = $total_days ? (float) (($attendance['total_present'] / $total_days) * 100) : 0.0;
        $attendance_percent = max(0.0, min(100.0, $attendance_percent));
        $attendance_percent_display = number_format($attendance_percent, 1);
        ?>

        <!-- Student Overview Section -->

        <div class="">
            <div class="wlsm-col-main">
                <!-- Profile Card -->
                <div class="wlsm-card wlsm-profile-card">
                    <div class="wlsm-profile-header">
                        <div class="wlsm-student-photo-container">
                            <?php if ($student->photo_id) :
                                $photo_url = wp_get_attachment_url($student->photo_id);
                                if ($photo_url) : ?>
                                    <img src="<?php echo esc_url($photo_url); ?>"
                                        alt="<?php echo esc_attr($student_name); ?>"
                                        class="wlsm-student-photo">
                                <?php endif; ?>
                            <?php else : ?>
                                <div class="wlsm-student-photo-default">
                                    <?php echo esc_html(strtoupper(substr($student_name, 0, 1))); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="wlsm-student-meta">
                            <span class="wlsm-badge">
                                <h4><strong><?php echo esc_html(ucfirst($student_name)); ?></strong></h4>
                                <?php echo esc_html(WLSM_M_Class::get_label_text($student->class_label)); ?> -
                                <?php echo esc_html(WLSM_M_Class::get_label_text($student->section_label)); ?>
                            </span>
                        </div>
                    </div>

                    <!-- Student Details Grid -->
                    <div class="wlsm-profile-body">
                        <div class="wlsm-info-grid">
                            <ul class="wlsm-st-details-list">
                                <li>
                                    <span class="wlsm-st-details-list-key"><?php esc_html_e('Name'); ?>:</span>
                                    <span class="wlsm-st-details-list-value"><?php echo esc_html($student_name); ?></span>
                                </li>
                                <?php if ($school_enrollment_number) : ?>
                                    <li>
                                        <span class="wlsm-st-details-list-key"><?php esc_html_e('Enrollment Number', 'school-management'); ?>:</span>
                                        <span class="wlsm-st-details-list-value"><?php echo esc_html($student->enrollment_number); ?></span>
                                    </li>
                                <?php endif; ?>
                                <?php if ($school_admission_number) : ?>
                                    <li>
                                        <span class="wlsm-st-details-list-key"><?php esc_html_e('Admission Number', 'school-management'); ?>:</span>
                                        <span class="wlsm-st-details-list-value"><?php echo esc_html($student->admission_number); ?></span>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <span class="wlsm-st-details-list-key"><?php esc_html_e('Session', 'school-management'); ?>:</span>
                                    <span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Session::get_label_text($student->session_label)); ?></span>
                                </li>
                                <li>
                                    <span class="wlsm-st-details-list-key"><?php esc_html_e('Class', 'school-management'); ?>:</span>
                                    <span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Class::get_label_text($student->class_label)); ?></span>
                                </li>
                                <li>
                                    <span class="wlsm-st-details-list-key"><?php esc_html_e('Section', 'school-management'); ?>:</span>
                                    <span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Class::get_label_text($student->section_label)); ?></span>
                                </li>
                                <li>
                                    <span class="wlsm-st-details-list-key"><?php esc_html_e('Roll Number', 'school-management'); ?>:</span>
                                    <span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Staff_Class::get_roll_no_text($student->roll_number)); ?></span>
                                </li>
                                <li>
                                    <span class="wlsm-st-details-list-key"><?php esc_html_e('Father\'s Name', 'school-management'); ?>:</span>
                                    <span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Staff_Class::get_name_text($student->father_name)); ?></span>
                                </li>
                                <li>
                                    <span class="wlsm-st-details-list-key"><?php esc_html_e('Father\'s Phone', 'school-management'); ?>:</span>
                                    <span class="wlsm-st-details-list-value"><?php echo esc_html(WLSM_M_Staff_Class::get_name_text($student->father_phone)); ?></span>
                                </li>
                                <li>
                                    <span class="wlsm-st-details-list-key"><?php esc_html_e('ID Card', 'school-management'); ?>:</span>
                                    <span class="wlsm-st-details-list-value">
                                        <a class="wlsm-st-print-id-card" data-id-card="<?php echo esc_attr($user_id); ?>" data-user-id="<?php echo esc_attr($user_id); ?>"
                                            data-nonce="<?php echo esc_attr(wp_create_nonce('st-print-id-card-' . $user_id)); ?>"
                                            href="#"
                                            data-message-title="<?php echo esc_attr__('Print ID Card', 'school-management'); ?>"
                                            title="<?php esc_attr_e('Print ID Card', 'school-management'); ?>">
                                            <i class="fa fa-print"></i>
                                        </a>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Stats Section -->
                <!-- Removed left duplicate stats and moved to right column per Option B -->
                <!-- Fee Status Card -->
                <?php
                // Get payment data
                $payments_query = WLSM_M::payments_query();
                $payments = $wpdb->get_results($wpdb->prepare($payments_query . ' ORDER BY p.ID DESC', $student->ID));

                // Calculate total paid amount
                $total_paid = 0;
                if (!empty($payments)) {
                    foreach ($payments as $payment) {
                        $total_paid += $payment->amount;
                    }
                }

                // Calculate totals
                $student_id = $student->ID;
                $student = WLSM_M_Staff_General::fetch_student($school_id, $session_id, $student_id);
                $fees = WLSM_M_Staff_Accountant::fetch_student_fees($school_id, $student_id);

                // Calculate months in session
                $start_date = new DateTime($student->start_date);
                $end_date = new DateTime($student->end_date);
                $interval = $start_date->diff($end_date);

                // Calculate total months including years (years * 12 + months)
                $months_in_session = ($interval->y * 12) + $interval->m;

                // If you want to consider partial months (days)
                if ($interval->d > 0) {
                    ++$months_in_session; // Add one more month if there are remaining days
                }

                // Initialize fee totals
                $session_onetime_total = 0;
                $session_quarterly_total = 0;
                $session_quadrimester_total = 0;
                $session_half_yearly_total = 0;
                $session_monthly_total = 0;
                $session_yearly_total = 0;

                // Calculate fee totals by period
                foreach ($fees as $fee) {
                    switch ($fee->period) {
                        case 'monthly':
                            $session_monthly_total += intval($fee->amount) * $months_in_session;
                            break;
                        case 'one-time':
                            $session_onetime_total += intval($fee->amount);
                            break;
                        case 'quarterly':
                            $occurrences = ceil($months_in_session / 3);
                            $session_quarterly_total += intval($fee->amount) * $occurrences;
                            break;
                        case 'quadrimester':
                            $occurrences = ceil($months_in_session / 4);
                            $session_quadrimester_total += intval($fee->amount) * $occurrences;
                            break;
                        case 'half-yearly':
                            $occurrences = ceil($months_in_session / 6);
                            $session_half_yearly_total += intval($fee->amount) * $occurrences;
                            break;
                        case 'annually':
                            $occurrences = ceil($months_in_session / 12);
                            $session_yearly_total += intval($fee->amount) * $occurrences;
                            break;
                    }
                }

                // Calculate totals
                $total_payable = $session_monthly_total + $session_onetime_total +
                    $session_quarterly_total + $session_quadrimester_total +
                    $session_half_yearly_total + $session_yearly_total;

                $payment_percentage = $total_payable > 0 ? round(($total_paid / $total_payable) * 100) : 0;
                $remaining_amount = $total_payable - $total_paid;


                $session_period_totals = array(
                    'monthly'       => $session_monthly_total,
                    'quarterly'     => $session_quarterly_total,
                    'quadrimester'  => $session_quadrimester_total,
                    'half-yearly'   => $session_half_yearly_total,
                    'annually'      => $session_yearly_total,
                    'one-time'      => $session_onetime_total,
                );

                $student_concession = WLSM_M_Staff_General::fetch_student_concession($student->ID, $student->session_id, $school_id);
                $concession_amount  = 0;
                $payable_amount     = $total_payable;

                if ($student_concession && 'approved' === $student_concession->status) {
                    if ('percentage' === $student_concession->concession_type) {
                        $concession_amount = ($total_payable * $student_concession->percentage_value) / 100;
                    } elseif ('fixed_amount' === $student_concession->concession_type) {
                        $concession_amount = min($student_concession->fixed_amount, $total_payable);
                    }
                    $payable_amount = max($total_payable - $concession_amount, 0);
                }
                ?>

                <div class="wlsm-trio">
                    <?php
                    // Get Previous Session Due Amount
                    $previous_session = WLSM_M_Session::get_pre_session($session_id);
                    $prev_session_id = $previous_session ? absint($previous_session->ID) : 0;

                    $prev_session_due = 0;
                    if ($prev_session_id) {

                        // Get previous session's pending invoices with partial payments considered
                        $prev_invoices = $wpdb->get_results($wpdb->prepare(
                            "SELECT i.ID,
                                    i.amount,
                                    COALESCE(i.discount, 0) as discount,
                                    COALESCE(SUM(p.amount), 0) as paid_amount,
                                    (i.amount - COALESCE(i.discount, 0) - COALESCE(SUM(p.amount),0)) as due_amount
                            FROM " . WLSM_INVOICES . " as i
                            JOIN " . WLSM_STUDENT_RECORDS . " as sr ON sr.ID = i.student_record_id
                            LEFT JOIN " . WLSM_PAYMENTS . " as p ON p.invoice_id = i.ID
                            WHERE sr.admission_number = %s
                            AND sr.session_id = %d
                            AND (i.status IS NULL OR i.status != 'paid')
                            GROUP BY i.ID
                            HAVING due_amount > 0",
                            $student->admission_number,
                            $prev_session_id
                        ));

                        // Calculate total due amount considering discounts and partial payments
                        $prev_session_due = 0;
                        foreach ($prev_invoices as $invoice) {
                            $invoice_due = $invoice->amount - (float)$invoice->discount - (float)$invoice->paid_amount;
                            $prev_session_due += max(0, $invoice_due); // Ensure we don't add negative values
                        }
                    }
                    ?>

                    <?php if ($prev_session_due > 0) : ?>
                        <!-- Previous Session Due Card -->
                        <div class="wlsm-card wlsm-stats-card">
                            <div class="wlsm-stats-headerr">
                                <h4><?php esc_html_e('Previous Session Due', 'school-management'); ?></h4>
                            </div>
                            <div class="wlsm-stats-content">
                                <div class="wlsm-stats-value <?php echo $prev_session_due > 0 ? 'wlsm-text-danger' : 'wlsm-text-success'; ?>">
                                    <?php echo esc_html(WLSM_Config::get_money_text($prev_session_due, $school_id)); ?>
                                </div>
                                <?php if ($prev_session_due > 0): ?>
                                    <div class="wlsm-stats-sub">
                                        <?php esc_html_e('Please clear previous dues', 'school-management'); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Session Fee Summary -->
                    <div class="wlsm-card wlsm-stats-card">
                        <div class="wlsm-stats-headerr">
                            <h4><?php esc_html_e('Session Fee Summary', 'school-management'); ?></h4>
                        </div>
                        <div class="wlsm-stats-content">
                            <table class="wlsm-table wlsm-table-compact">
                                <thead>
                                    <tr>
                                        <th><?php esc_html_e('Period', 'school-management'); ?></th>
                                        <th><?php esc_html_e('Session', 'school-management'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($session_period_totals as $period_key => $period_total) : ?>
                                        <?php if ($period_total <= 0) {
                                            continue;
                                        } ?>
                                        <tr>
                                            <td style="padding: 8px;"><?php echo esc_html(WLSM_M_Staff_Accountant::get_fee_period_text($period_key)); ?></td>
                                            <td style="padding: 8px;"><?php echo esc_html(WLSM_Config::get_money_text($period_total, $school_id)); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr class="wlsm-font-bold">
                                        <td style="padding: 8px;"><?php esc_html_e('Total', 'school-management'); ?></td>
                                        <td style="padding: 8px;"><?php echo esc_html(WLSM_Config::get_money_text($total_payable, $school_id)); ?></td>
                                    </tr>
                                    <?php if ($concession_amount > 0) : ?>
                                        <tr class="wlsm-font-bold">
                                            <td style="padding: 8px;"><?php esc_html_e('Concession', 'school-management'); ?></td>
                                            <td style="padding: 8px;">- <?php echo esc_html(WLSM_Config::get_money_text($concession_amount, $school_id)); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr class="wlsm-font-bold">
                                        <td style="padding: 8px;"><?php esc_html_e('Balance Amount', 'school-management'); ?></td>
                                        <td style="padding: 8px;"><?php echo esc_html(WLSM_Config::get_money_text($payable_amount, $school_id)); ?></td>
                                    </tr>
                                    <tr class="wlsm-font-bold">
                                        <td style="padding: 8px;"><?php esc_html_e('Paid Amount', 'school-management'); ?></td>
                                        <td style="padding: 8px; color: green;"><?php echo esc_html(WLSM_Config::get_money_text($total_paid, $school_id)); ?></td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Attendance Card -->
                    <?php
                    $total_days = (int) ($attendance['total_present'] + $attendance['total_absent'] + $attendance['total_late'] + $attendance['total_holiday']);
                    $pct_present = $total_days ? round(($attendance['total_present'] / $total_days) * 100) : 0;
                    $pct_absent  = $total_days ? round(($attendance['total_absent'] / $total_days) * 100) : 0;
                    $pct_late    = $total_days ? round(($attendance['total_late'] / $total_days) * 100) : 0;
                    $pct_holiday = $total_days ? round(($attendance['total_holiday'] / $total_days) * 100) : 0;
                    ?>
                    <div class="wlsm-card wlsm-stats-card">
                        <div class="wlsm-stats-headerr">
                            <h4><?php esc_html_e('Attendance', 'school-management'); ?> <strong><?php echo esc_html($attendance_percent_display); ?>%</strong></h4>

                        </div>
                        <div class="wlsm-stats-content">
                            <div class="wlsm-progress wlsm-progress-segmented" role="progressbar" aria-valuemin="0" aria-valuemax="100">
                                <div class="wlsm-progress-bar wlsm-progress-success" style="width: <?php echo esc_attr($pct_present); ?>%" title="<?php esc_attr_e('Present', 'school-management'); ?>: <?php echo esc_attr($pct_present); ?>%"></div>
                                <div class="wlsm-progress-bar wlsm-progress-danger" style="width: <?php echo esc_attr($pct_absent); ?>%" title="<?php esc_attr_e('Absent', 'school-management'); ?>: <?php echo esc_attr($pct_absent); ?>%"></div>
                                <div class="wlsm-progress-bar wlsm-progress-info" style="width: <?php echo esc_attr($pct_late); ?>%" title="<?php esc_attr_e('Late', 'school-management'); ?>: <?php echo esc_attr($pct_late); ?>%"></div>
                                <div class="wlsm-progress-bar wlsm-progress-warning" style="width: <?php echo esc_attr($pct_holiday); ?>%" title="<?php esc_attr_e('Holiday', 'school-management'); ?>: <?php echo esc_attr($pct_holiday); ?>%"></div>
                            </div>
                            <div class="wlsm-attendance-stats">
                                <div class="wlsm-attendance-stat present"><i class="fas fa-check-circle"></i><span><?php esc_html_e('Present', 'school-management'); ?>: <?php echo esc_html($attendance['total_present']); ?> (<?php echo esc_html($pct_present); ?>%)</span></div>
                                <div class="wlsm-attendance-stat absent"><i class="fas fa-times-circle"></i><span><?php esc_html_e('Absent', 'school-management'); ?>: <?php echo esc_html($attendance['total_absent']); ?> (<?php echo esc_html($pct_absent); ?>%)</span></div>
                                <div class="wlsm-attendance-stat late"><i class="fas fa-clock"></i><span><?php esc_html_e('Late', 'school-management'); ?>: <?php echo esc_html($attendance['total_late']); ?> (<?php echo esc_html($pct_late); ?>%)</span></div>
                                <div class="wlsm-attendance-stat holiday"><i class="fas fa-umbrella-beach"></i><span><?php esc_html_e('Holiday', 'school-management'); ?>: <?php echo esc_html($attendance['total_holiday']); ?> (<?php echo esc_html($pct_holiday); ?>%)</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Transport Card -->
                    <?php if ($vehicle_id && !empty($transportation_details)) : $transport = $transportation_details[0]; ?>
                        <div class="wlsm-card wlsm-stats-card wlsm-transport-stats">
                            <div class="wlsm-stats-headerr">
                                <h4><?php esc_html_e('Transportation', 'school-management'); ?></h4>
                            </div>
                            <div class="wlsm-stats-content">
                                <div class=""><?php echo esc_html($transport->name); ?></div>
                                <div class="wlsm-transport-details">
                                    <div class="wlsm-transport-info"><i class="fas fa-bus"></i><span><?php echo esc_html($transport->vehicle_number); ?></span></div>
                                    <div class="wlsm-transport-info"><i class="fas fa-money-bill"></i><span><?php echo esc_html(WLSM_Config::get_money_text($transport->fare, $school_id)); ?></span></div>
                                    <?php if ($transport->driver_name) : ?>
                                        <div class="wlsm-transport-info"><i class="fas fa-user"></i><span><?php echo esc_html($transport->driver_name); ?><?php if ($transport->driver_phone) : ?><small>(<?php echo esc_html($transport->driver_phone); ?>)</small><?php endif; ?></span></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Full Width Sections -->
        <div class="wlsm-full-width-section">
            <!-- Notices Section -->
            <div class="wlsm-section-heading">
                <h2><?php esc_html_e('Latest Notices', 'school-management'); ?></h2>
            </div>

            <?php if ($filtered_notices) : ?>
                <div class="wlsm-notices-grid">
                    <?php foreach (array_slice($filtered_notices, 0, 4) as $notice) :
                        $link_to = $notice->link_to;
                        
                        // Determine the appropriate link and label
                        if ('url' === $link_to && !empty($notice->url)) {
                            $link = $notice->url;
                            $link_text = esc_html__('Open', 'school-management');
                            $link_icon = 'fas fa-external-link-alt';
                            $target = '_blank';
                        } elseif ('attachment' === $link_to && !empty($notice->attachment)) {
                            $link = wp_get_attachment_url($notice->attachment);
                            $link_text = esc_html__('Download', 'school-management');
                            $link_icon = 'fas fa-download';
                            $target = '_blank';
                        } else {
                            $link = esc_url(add_query_arg(array('action' => 'noticeboard'), $current_page_url));
                            $link_text = esc_html__('View', 'school-management');
                            $link_icon = 'fas fa-arrow-right';
                            $target = '_self';
                        }
                        
                        $notice_date = DateTime::createFromFormat('Y-m-d H:i:s', $notice->created_at);
                        $interval = (new DateTime())->diff($notice_date);
                    ?>
                        <div class="wlsm-notice-card">
                            <div class="wlsm-notice-card-header">
                                <span class="wlsm-notice-date"><?php echo esc_html($notice_date->format('M j, Y')); ?></span>
                                <?php if ($interval->days < 7) : ?>
                                    <span class="wlsm-badge-new"><?php esc_html_e('New', 'school-management'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="wlsm-notice-content">
                                <h3 class="wlsm-notice-title" title="<?php echo esc_attr(stripslashes($notice->title)); ?>"><?php echo esc_html(stripslashes($notice->title)); ?></h3>
                                <div class="wlsm-notice-desc" title="<?php echo esc_attr(stripslashes($notice->description)); ?>"><?php echo esc_html(stripslashes($notice->description)); ?></div>
                            </div>
                            <div class="wlsm-notice-footer">
                                <a target="<?php echo esc_attr($target); ?>" href="<?php echo esc_url($link); ?>" class="wlsm-btn-read-more">
                                    <?php echo esc_html($link_text); ?> <i class="<?php echo esc_attr($link_icon); ?>"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="wlsm-no-notices">
                    <i class="fas fa-bell-slash"></i>
                    <p><?php esc_html_e('No notices available', 'school-management'); ?></p>
                </div>
            <?php endif; ?>


            <!-- Calendar Widget Section -->
            <div class="wlsm-section-heading mt-4" style="margin-top: 3rem;">
                <h2><?php esc_html_e('School Calendar', 'school-management'); ?></h2>
            </div>

            <?php
            // Fetch current-month events and holidays for the calendar widget.
            $cal_today    = current_time('Y-m-d');
            $cal_year     = (int) date('Y', strtotime($cal_today));
            $cal_month    = (int) date('m', strtotime($cal_today));
            $cal_start    = sprintf('%04d-%02d-01', $cal_year, $cal_month);
                            $cal_end      = date('Y-m-t', strtotime($cal_start));
            $cal_days_in_month = (int) date('t', strtotime($cal_start));
            $cal_first_dow     = (int) date('w', strtotime($cal_start)); // 0=Sun

            $cal_events = $wpdb->get_results($wpdb->prepare(
                "SELECT ev.ID, ev.title, ev.description, ev.event_date AS date_start
                  FROM " . WLSM_EVENTS . " AS ev
                  WHERE ev.school_id = %d
                    AND ev.event_date BETWEEN %s AND %s
                    AND ev.is_active = 1",
                $school_id, $cal_start, $cal_end
            ));

            $cal_holidays = $wpdb->get_results($wpdb->prepare(
                "SELECT h.ID, h.description AS title, h.description, h.start_date, h.end_date
                  FROM " . WLSM_HOLIDAYS . " AS h
                  WHERE h.school_id = %d
                    AND h.start_date <= %s AND h.end_date >= %s",
                $school_id, $cal_end, $cal_start
            ));

            $cal_exams = $wpdb->get_results($wpdb->prepare(
                "SELECT ep.ID, CONCAT(ex.label, IFNULL(CONCAT(': ', s.label), '')) AS title, ep.paper_date AS date_start, ep.start_time, ep.end_time, ep.room_number
                 FROM " . WLSM_EXAM_PAPERS . " AS ep
                 JOIN " . WLSM_EXAMS . " AS ex ON ex.ID = ep.exam_id
                 JOIN " . WLSM_CLASS_SCHOOL_EXAM . " AS csex ON csex.exam_id = ex.ID
                 LEFT JOIN " . WLSM_SUBJECTS . " AS s ON s.ID = ep.subject_id
                 WHERE ex.school_id = %d
                   AND csex.class_school_id = %d
                   AND ep.paper_date BETWEEN %s AND %s
                   AND ex.is_active = 1
                   AND ex.time_table_published = 1",
                $school_id, $student->class_school_id, $cal_start, $cal_end
            ));

            // Build day-indexed maps.
            $cal_event_map   = array(); // 'Y-m-d' => [objects]
            $cal_holiday_map = array(); // 'Y-m-d' => [objects]
            $cal_exam_map    = array(); // 'Y-m-d' => [objects]

            foreach ($cal_events as $ev) {
                $ev->description = wp_strip_all_tags(stripslashes($ev->description));
                $cal_event_map[$ev->date_start][] = $ev;
            }
            foreach ($cal_holidays as $h) {
                $h->description = wp_strip_all_tags(stripslashes($h->description));
                $cursor = max($h->start_date, $cal_start);
                $last   = min($h->end_date,   $cal_end);
                while ($cursor <= $last) {
                    $cal_holiday_map[$cursor][] = $h;
                    $cursor = date('Y-m-d', strtotime($cursor . ' +1 day'));
                }
            }
            foreach ($cal_exams as $ex) {
                $cal_exam_map[$ex->date_start][] = $ex;
            }

            $day_names = array(
                esc_html__('Sun', 'school-management'),
                esc_html__('Mon', 'school-management'),
                esc_html__('Tue', 'school-management'),
                esc_html__('Wed', 'school-management'),
                esc_html__('Thu', 'school-management'),
                esc_html__('Fri', 'school-management'),
                esc_html__('Sat', 'school-management'),
            );
            $month_label = date_i18n('F Y', strtotime($cal_start));
            ?>
            <div class="wlsm-dashboard-calendar-widget">
                <div class="wlsm-cal-header">
                    <span class="wlsm-cal-month-title"><?php echo esc_html($month_label); ?></span>
                    <div class="wlsm-cal-legend">
                        <span class="wlsm-cal-legend-dot wlsm-cal-legend-holiday"></span>
                        <span class="wlsm-cal-legend-label"><?php esc_html_e('Holiday', 'school-management'); ?></span>
                        <span class="wlsm-cal-legend-dot wlsm-cal-legend-event"></span>
                        <span class="wlsm-cal-legend-label"><?php esc_html_e('Event', 'school-management'); ?></span>
                        <span class="wlsm-cal-legend-dot wlsm-cal-legend-exam"></span>
                        <span class="wlsm-cal-legend-label"><?php esc_html_e('Exam', 'school-management'); ?></span>
                    </div>
                </div>
                <table class="wlsm-cal-table">
                    <thead>
                        <tr>
                            <?php foreach ($day_names as $dn) : ?>
                                <th><?php echo esc_html($dn); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $cell = 0;
                    $day  = 1;
                    echo '<tr>';
                    // Leading empty cells.
                    for ($i = 0; $i < $cal_first_dow; $i++) {
                        echo '<td class="wlsm-cal-empty"></td>';
                        $cell++;
                    }
                    while ($day <= $cal_days_in_month) {
                        if ($cell % 7 === 0 && $cell > 0) {
                            echo '</tr><tr>';
                        }
                        $date_str   = sprintf('%04d-%02d-%02d', $cal_year, $cal_month, $day);
                        $is_today   = ($date_str === $cal_today);
                        $is_holiday = isset($cal_holiday_map[$date_str]);
                        $is_event   = isset($cal_event_map[$date_str]);
                        $is_exam    = isset($cal_exam_map[$date_str]);

                        $td_class = 'wlsm-cal-day';
                        if ($is_today)   $td_class .= ' wlsm-cal-today';
                        if ($is_holiday) $td_class .= ' wlsm-cal-has-holiday';
                        if ($is_event)   $td_class .= ' wlsm-cal-has-event';
                        if ($is_exam)    $td_class .= ' wlsm-cal-has-exam';

                        echo '<td class="' . esc_attr($td_class) . '">';
                        echo '<span class="wlsm-day-number">' . esc_html($day) . '</span>';

                        if ($is_holiday) {
                            foreach ($cal_holiday_map[$date_str] as $h) {
                                echo '<span class="wlsm-pill wlsm-pill-holiday wlsm-popover-trigger" data-type="holiday" data-title="' . esc_attr($h->title) . '" data-desc="' . esc_attr($h->description) . '" data-date="' . esc_attr(date_i18n(get_option('date_format'), strtotime($date_str))) . '">' . esc_html(mb_strimwidth($h->title, 0, 12, '…')) . '</span>';
                            }
                        }
                        if ($is_event) {
                            foreach ($cal_event_map[$date_str] as $ev) {
                                echo '<span class="wlsm-pill wlsm-pill-event wlsm-popover-trigger" data-type="event" data-title="' . esc_attr($ev->title) . '" data-desc="' . esc_attr($ev->description) . '" data-date="' . esc_attr(date_i18n(get_option('date_format'), strtotime($date_str))) . '">' . esc_html(mb_strimwidth($ev->title, 0, 12, '…')) . '</span>';
                            }
                        }
                        if ($is_exam) {
                            foreach ($cal_exam_map[$date_str] as $ex) {
                                $timeText = ($ex->start_time && $ex->end_time) ? substr($ex->start_time, 0, 5) . ' - ' . substr($ex->end_time, 0, 5) : '';
                                echo '<span class="wlsm-pill wlsm-pill-exam wlsm-popover-trigger" data-type="exam" data-title="' . esc_attr($ex->title) . '" data-time="' . esc_attr($timeText) . '" data-room="' . esc_attr($ex->room_number) . '" data-date="' . esc_attr(date_i18n(get_option('date_format'), strtotime($date_str))) . '">' . esc_html(mb_strimwidth($ex->title, 0, 12, '…')) . '</span>';
                            }
                        }

                        echo '</td>';
                        $day++;
                        $cell++;
                    }
                    // Trailing empty cells.
                    while ($cell % 7 !== 0) {
                        echo '<td class="wlsm-cal-empty"></td>';
                        $cell++;
                    }
                    echo '</tr>';
                    ?>
                    </tbody>
                </table>
                <?php
                $calendar_url = esc_url(add_query_arg(array('action' => 'calendar'), $current_page_url));
                ?>
                <div class="wlsm-cal-footer">
                    <a href="<?php echo $calendar_url; ?>" class="wlsm-btn-view-calendar">
                        <?php esc_html_e('View Full Calendar', 'school-management'); ?> <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
<?php } ?>
