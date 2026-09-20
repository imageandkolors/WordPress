<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/student/partials/navigation.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';

$student_id = $student->ID;
$session_id = $student->session_id;

if ( ! $settings_dashboard['school_tickets'] ) {
	die();
}

// Fetch student's enrolled subjects
$subjects = WLSM_M_Staff_General::fetch_subjects($student_id);
?>

<div class="wlsm-content-area wlsm-section-ticket-history wlsm-student-ticket-history">
    <div class="wlsm-registration-section">
        <div class="wlsm-registration-section-header">
            <h3 class="wlsm-registration-section-title">
                <?php esc_html_e('Add Ticket', 'school-management'); ?>
            </h3>
        </div>

        <div class="wlsm-registration-section-content">
            <form id="addTicketForm" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post">
                <div class="wlsm-form-group">
                    <label for="ticketTitle" class="wlsm-form-label wlsm-font-medium"><?php esc_html_e('Title', 'school-management'); ?>:</label>
                    <input type="text" class="wlsm-form-control-select wlsm-w-100" id="ticketTitle" name="title" required>
                </div>

                <div class="wlsm-form-group">
                    <label for="ticketDescription" class="wlsm-form-label wlsm-font-medium"><?php esc_html_e('Description', 'school-management'); ?>:</label>
                    <textarea class="wlsm-form-control-select wlsm-w-100" id="ticketDescription" name="description" rows="5" required style="min-height: 120px; resize: vertical;"></textarea>
                </div>

                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <div class="wlsm-form-group" style="flex: 1; min-width: 200px;">
                        <label for="ticketSubject" class="wlsm-form-label wlsm-font-medium"><?php esc_html_e('Subject', 'school-management'); ?>:</label>
                        <select class="wlsm-form-control-select wlsm-w-100" id="ticketSubject" name="subject_id">
                            <option value=""><?php esc_html_e('Select Subject (Optional)', 'school-management'); ?></option>
                            <?php foreach ($subjects as $subject) : ?>
                                <option value="<?php echo esc_attr($subject->ID); ?>"><?php echo esc_html($subject->label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="wlsm-form-group" style="flex: 1; min-width: 200px;">
                        <label for="ticketPriority" class="wlsm-form-label wlsm-font-medium"><?php esc_html_e('Priority', 'school-management'); ?>:</label>
                        <select class="wlsm-form-control-select wlsm-w-100" id="ticketPriority" name="priority">
                            <option value="low"><?php esc_html_e('Low', 'school-management'); ?></option>
                            <option value="normal" selected><?php esc_html_e('Normal', 'school-management'); ?></option>
                            <option value="high"><?php esc_html_e('High', 'school-management'); ?></option>
                            <option value="urgent"><?php esc_html_e('Urgent', 'school-management'); ?></option>
                        </select>
                    </div>
                </div>

                <input type="hidden" name="action" value="wlsm_save_ticket">
                <input type="hidden" name="student_id" value="<?php echo esc_attr($student_id); ?>">
                <?php wp_nonce_field('add-ticket', 'add_ticket_nonce'); ?>

                <div class="wlsm-form-group" style="text-align: center; margin-top: 1.5rem;">
                    <button type="submit" id="wlsm-submit-ticket-btn" class="wlsm-st-submit-btn" data-confirm="<?php esc_attr_e('Are you sure you want to submit this ticket?', 'school-management'); ?>">
                        <i class="fas fa-ticket-alt"></i>
                        <?php esc_html_e('Submit Ticket', 'school-management'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
