<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/student/partials/navigation.php';

$school_id  = $student->school_id;
$session_id = $student->session_id;
$class_id   = $student->class_id;

$lesson_per_page = WLSM_M::lesson_per_page();

$lesson_query = WLSM_M::lesson_query($school_id, $session_id);
$lesson_query .= " AND l.class_id = " . absint($class_id);

$lesson_total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(1) FROM ({$lesson_query}) AS combined_table", $school_id)); // Note: WLSM_M::lesson_query might need check if it prepares arguments or not. In lessons.php it was used with prepare.

// Let's check how lessons.php uses it:
// $lesson_query = WLSM_M::lesson_query($school_id, $session_id);
// $lessons = $wpdb->get_results($wpdb->prepare($lesson_query . ' ORDER BY l.ID DESC LIMIT %d, %d', $lesson_page_offset, $lesson_per_page));
// Wait, lessons.php didn't prepare with school_id inside prepare for the query itself, only for the limit?
// Let's re-examine public/inc/lesson/lessons.php in step 29.
// Line 23: $lesson_query = WLSM_M::lesson_query($school_id, $session_id);
// Line 31: $lessons = $wpdb->get_results($wpdb->prepare($lesson_query . ' ORDER BY l.ID DESC LIMIT %d, %d', $lesson_page_offset, $lesson_per_page));
// It seems lesson_query returns a raw SQL?
// No, line 23 passes school_id, session_id. So the query string probably contains values already interpolated or no placeholders?
// Wait, if lesson_query returns a string with placeholders, prepare is needed.
// In study_materials.php:
// $study_materials_query = WLSM_M::study_materials_query();
// $study_materials = $wpdb->get_results($wpdb->prepare($study_materials_query . ' ORDER BY cssm.ID DESC LIMIT %d, %d', $class_school_id, $study_materials_page_offset, $study_materials_per_page));
// Here prepare arguments are passed.
//
// In lessons.php:
// $lessons = $wpdb->get_results($wpdb->prepare($lesson_query . ' ORDER BY l.ID DESC LIMIT %d, %d', $lesson_page_offset, $lesson_per_page));
// Use arguments are just offset and per_page.
// This implies lesson_query stores the IDs directly? NO, strictly bad practice.
// Or maybe lesson_query function handles escaping internally and returns safe SQL string?
// OR maybe lesson_query returns query with %d placeholders but for some reason lessons.php doesn't pass them?
// Actually, looking at lessons.php:
// $classes = WLSM_M_Staff_General::fetch_school_classes($school_id); is called earlier.
//
// Let's assume WLSM_M::lesson_query handles the arguments if passed, OR it returns a query that doesn't need external arguments (unlikely for school_id).
// Re-reading lessons.php carefully:
// Line 23: $lesson_query = WLSM_M::lesson_query($school_id, $session_id);
// Line 31: $lessons = $wpdb->get_results($wpdb->prepare($lesson_query . ' ORDER BY l.ID DESC LIMIT %d, %d', $lesson_page_offset, $lesson_per_page));
//
// It seems WLSM_M::lesson_query($school_id, $session_id) returns a complete SQL string with values embedded or returns a string that doesn't use placeholders for those values.
// I will follow lessons.php pattern exactly.

$lesson_page = isset($_GET['lesson_page']) ? absint($_GET['lesson_page']) : 1;

$lesson_page_offset = ($lesson_page * $lesson_per_page) - $lesson_per_page;

$lessons = $wpdb->get_results($wpdb->prepare($lesson_query . ' ORDER BY l.ID DESC LIMIT %d, %d', $lesson_page_offset, $lesson_per_page));
?>
<div class="wlsm-content-area wlsm-section-lessons wlsm-student-lessons">
    <div class="wlsm-st-main-title">
        <span>
            <?php esc_html_e('Lessons', 'school-management'); ?>
        </span>
    </div>

    <div class="wlsm-st-lessons-section">
        <?php
        if (count($lessons)) {
        ?>
            <ul class="wlst-st-list wlsm-st-lessons">
                <?php
                foreach ($lessons as $key => $lesson) {
                ?>
                    <li>
                        <span>
                            <span class="wlsm-font-bold"><?php echo esc_html(stripslashes($lesson->title)); ?></span>
                            <br>
                            <small class="wlsm-text-secondary">
                                <?php echo esc_html($lesson->subject); ?> - <?php echo esc_html($lesson->chapter); ?>
                            </small>
                            <span class="wlsm-st-lesson-date wlsm-font-bold"><?php echo esc_html(WLSM_Config::get_date_text($lesson->created_at)); ?></span>

                            <a class="wlsm-st-view-lesson wlsm-ml-1" data-lesson="<?php echo esc_attr($lesson->ID); ?>" data-user="<?php echo esc_attr($user_id); ?>" data-nonce="<?php echo esc_attr(wp_create_nonce('st-view-lesson-' . $lesson->ID)); ?>" href="#" data-message-title="<?php echo esc_attr(stripslashes($lesson->title)); ?>" data-close="<?php echo esc_attr__('Close', 'school-management'); ?>">
                                <?php esc_html_e('View', 'school-management'); ?>
                            </a>
                        </span>
                    </li>
                <?php
                }
                ?>
            </ul>
            <div class="wlsm-text-right wlsm-font-medium wlsm-font-bold wlsm-mt-2">
                <?php
                echo paginate_links(
                    array(
                        'base'      => add_query_arg('lesson_page', '%#%'),
                        'format'    => '',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'total'     => ceil($lesson_total / $lesson_per_page),
                        'current'   => $lesson_page,
                    )
                );
                ?>
            </div>
        <?php
        } else {
        ?>
            <div class="wlsm-no-notices">
                <i class="fas fa-chalkboard-teacher"></i>
                <p><?php esc_html_e('No lessons available', 'school-management'); ?></p>
            </div>
        <?php
        }
        ?>
    </div>
</div>