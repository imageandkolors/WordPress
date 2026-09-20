<?php
defined('ABSPATH') || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';
global $wpdb;

$school_id  = $current_school['id'];
$session_id = $current_session['ID'];

// Fetch ticket statistics
$total_tickets = WLSM_M_Staff_Tickets::get_tickets_count($school_id);
$stats = WLSM_M_Staff_Tickets::get_tickets_stats($school_id);
$priority_stats = WLSM_M_Staff_Tickets::get_priority_stats($school_id);

// Prepare data for the Status chart
$chart_data = [
    'labels' => ['Open', 'In Progress', 'Resolved', 'Closed'],
    'datasets' => [
        [
            'label' => 'Tickets',
            'data' => [
                $stats['open'],
                $stats['in_progress'],
                $stats['resolved'],
                $stats['closed']
            ],
            'backgroundColor' => [
                '#FF6384',
                '#36A2EB',
                '#FFCE56',
                '#4BC0C0'
            ]
        ]
    ]
];

// Prepare data for the Priority chart
$priority_chart_data = [
    'labels' => ['Low', 'Medium', 'High', 'Urgent'],
    'datasets' => [
        [
            'label' => 'Priority',
            'data' => [
                $priority_stats['low'],
                $priority_stats['medium'],
                $priority_stats['high'],
                $priority_stats['urgent']
            ],
            'backgroundColor' => [
                '#9966FF',
                '#4BC0C0',
                '#FF9F40',
                '#FF6384'
            ]
        ]
    ]
];
?>

<div class="wlsm container-fluid">
    <?php require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php'; ?>

    <div class="row">
        <div class="col-md-12">
            <div class="text-center wlsm-section-heading-block">
                <span class="wlsm-section-heading">
                    <i class="fas fa-ticket-alt"></i>
                    <?php esc_html_e('Tickets', 'school-management'); ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Ticket Stats Section -->
    <div class="row mt-3 mb-3 wlsm-stats-blocks">
        <?php
        $page_url = WLSM_M_Staff_Tickets::get_tickets_page_url();
        ?>
        <!-- Total Tickets -->
        <div class="col-lg col-md-6 col-sm-6 mb-4">
            <div class="wlsm-group h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="wlsm-stats-icon mr-3"><i class="fas fa-ticket-alt"></i></div>
                        <div class="wlsm-stats-text">
                            <div class="wlsm-stats-label"><?php esc_html_e('Total Tickets', 'school-management'); ?></div>
                            <div class="wlsm-stats-session"><?php esc_html_e('All Support Requests', 'school-management'); ?></div>
                        </div>
                    </div>
                    <div class="wlsm-stats-counter"><?php echo esc_html($total_tickets); ?></div>
                </div>
                <div class="wlsm-group-actions mt-auto border-top pt-3">
                    <a href="<?php echo esc_url($page_url); ?>" class="btn btn-sm btn-primary">
                        <?php esc_html_e('View All', 'school-management'); ?>
                    </a>
                    <?php if (WLSM_M_Role::can('add_tickets')) : ?>
                        <a href="<?php echo esc_url($page_url . '&action=save'); ?>" class="btn btn-sm btn-outline-primary">
                            <?php esc_html_e('Add New', 'school-management'); ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Open Tickets -->
        <div class="col-lg col-md-6 col-sm-6 mb-4">
            <div class="wlsm-group h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="wlsm-stats-icon mr-3"><i class="fas fa-folder-open"></i></div>
                        <div class="wlsm-stats-text">
                            <div class="wlsm-stats-label"><?php esc_html_e('Open', 'school-management'); ?></div>
                            <div class="wlsm-stats-session"><?php esc_html_e('Awaiting Action', 'school-management'); ?></div>
                        </div>
                    </div>
                    <div class="wlsm-stats-counter"><?php echo esc_html($stats['open']); ?></div>
                </div>
                <div class="wlsm-group-actions mt-auto border-top pt-3">
                    <a href="<?php echo esc_url($page_url); ?>" class="btn btn-sm btn-primary">
                        <?php esc_html_e('View Open', 'school-management'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- In Progress -->
        <div class="col-lg col-md-6 col-sm-6 mb-4">
            <div class="wlsm-group h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="wlsm-stats-icon mr-3"><i class="fas fa-spinner fa-spin"></i></div>
                        <div class="wlsm-stats-text">
                            <div class="wlsm-stats-label"><?php esc_html_e('In Progress', 'school-management'); ?></div>
                            <div class="wlsm-stats-session"><?php esc_html_e('Being Handled', 'school-management'); ?></div>
                        </div>
                    </div>
                    <div class="wlsm-stats-counter"><?php echo esc_html($stats['in_progress']); ?></div>
                </div>
                <div class="wlsm-group-actions mt-auto border-top pt-3">
                    <a href="<?php echo esc_url($page_url); ?>" class="btn btn-sm btn-primary">
                        <?php esc_html_e('View Pending', 'school-management'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Resolved -->
        <div class="col-lg col-md-6 col-sm-6 mb-4">
            <div class="wlsm-group h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="wlsm-stats-icon mr-3"><i class="fas fa-check-circle"></i></div>
                        <div class="wlsm-stats-text">
                            <div class="wlsm-stats-label"><?php esc_html_e('Resolved', 'school-management'); ?></div>
                            <div class="wlsm-stats-session"><?php esc_html_e('Completed', 'school-management'); ?></div>
                        </div>
                    </div>
                    <div class="wlsm-stats-counter"><?php echo esc_html($stats['resolved']); ?></div>
                </div>
                <div class="wlsm-group-actions mt-auto border-top pt-3">
                    <a href="<?php echo esc_url($page_url); ?>" class="btn btn-sm btn-primary">
                        <?php esc_html_e('View Resolved', 'school-management'); ?>
                    </a>
                </div>
            </div>
        </div>

        <!-- Closed -->
        <div class="col-lg col-md-6 col-sm-6 mb-4">
            <div class="wlsm-group h-100 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center">
                        <div class="wlsm-stats-icon mr-3"><i class="fas fa-times-circle"></i></div>
                        <div class="wlsm-stats-text">
                            <div class="wlsm-stats-label"><?php esc_html_e('Closed', 'school-management'); ?></div>
                            <div class="wlsm-stats-session"><?php esc_html_e('Archived', 'school-management'); ?></div>
                        </div>
                    </div>
                    <div class="wlsm-stats-counter"><?php echo esc_html($stats['closed']); ?></div>
                </div>
                <div class="wlsm-group-actions mt-auto border-top pt-3">
                    <a href="<?php echo esc_url($page_url); ?>" class="btn btn-sm btn-primary">
                        <?php esc_html_e('View Closed', 'school-management'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Ticket Chart Section -->
    <div class="row mt-3">
        <div class="col-md-6 mb-4">
            <div class="wlsm-group">
                <h6 class="wlsm-font-bold mb-3"><?php esc_html_e('Status Distribution (Pie Chart)', 'school-management'); ?></h6>
                <canvas id="ticketPieChart"></canvas>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="wlsm-group">
                <h6 class="wlsm-font-bold mb-3"><?php esc_html_e('Tickets by Priority (Bar Chart)', 'school-management'); ?></h6>
                <canvas id="ticketBarChart"></canvas>
            </div>
        </div>
    </div>


</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var pieCtx = document.getElementById('ticketPieChart').getContext('2d');
        var barCtx = document.getElementById('ticketBarChart').getContext('2d');
        var chartData = <?php echo json_encode($chart_data); ?>;
        var priorityChartData = <?php echo json_encode($priority_chart_data); ?>;

        new Chart(pieCtx, {
            type: 'pie',
            data: chartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                }
            }
        });

        new Chart(barCtx, {
            type: 'bar',
            data: priorityChartData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>
