<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Class.php';

$events_page_url   = WLSM_M_Staff_Class::get_events_page_url();
$holidays_page_url = WLSM_M_Staff_Class::get_holidays_page_url();
?>

<div class="row">
	<div class="col-md-12">
		<div class="text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-calendar-alt"></i>
				<?php esc_html_e( 'Calendar', 'school-management' ); ?>
			</span>
			<span class="float-md-right">
				<?php if ( WLSM_M_Role::can( 'add_events' ) ) : ?>
				<a href="<?php echo esc_url( $events_page_url . '&action=save' ); ?>" class="btn btn-sm btn-outline-light mr-1">
					<i class="fas fa-plus-square"></i>&nbsp;<?php esc_html_e( 'Add Event', 'school-management' ); ?>
				</a>
				<?php endif; ?>
				<?php if ( WLSM_M_Role::can( 'view_attendance' ) ) : ?>
				<a href="<?php echo esc_url( $holidays_page_url . '&action=save' ); ?>" class="btn btn-sm btn-outline-light">
					<i class="fas fa-plus-square"></i>&nbsp;<?php esc_html_e( 'Add Holiday', 'school-management' ); ?>
				</a>
				<?php endif; ?>
			</span>
		</div>

		<div class="wlsm-table-block">
			<!-- Minimal Navigation Bar -->
			<div class="d-flex justify-content-between align-items-center mb-3">
				<div class="d-flex align-items-center">
					<button id="wlsm-cal-prev" class="btn btn-sm btn-light border mr-1">
						<i class="fas fa-chevron-left text-muted"></i>
					</button>
					<h5 id="wlsm-cal-title" class="mb-0 mx-3 font-weight-bold text-dark" style="min-width: 150px; text-align: center;"></h5>
					<button id="wlsm-cal-next" class="btn btn-sm btn-light border">
						<i class="fas fa-chevron-right text-muted"></i>
					</button>
					<button id="wlsm-cal-today" class="btn btn-sm btn-outline-primary ml-3">
						<?php esc_html_e( 'Month', 'school-management' ); ?>
					</button>
					<button id="wlsm-cal-year" class="btn btn-sm btn-outline-primary ml-1">
						<?php esc_html_e( 'Year', 'school-management' ); ?>
					</button>
				</div>
				<div class="d-flex align-items-center" style="font-size: 0.85rem;">
					<span class="wlsm-pill-holiday p-1 mr-1" style="width:12px;height:12px;display:inline-block;border-radius:2px;"></span>
					<span class="text-muted mr-3 font-weight-bold"><?php esc_html_e( 'Holiday', 'school-management' ); ?></span>
					<span class="wlsm-pill-event p-1 mr-1" style="width:12px;height:12px;display:inline-block;border-radius:2px;"></span>
					<span class="text-muted mr-3 font-weight-bold"><?php esc_html_e( 'Event', 'school-management' ); ?></span>
					<span class="wlsm-pill-exam p-1 mr-1" style="width:12px;height:12px;display:inline-block;border-radius:2px;"></span>
					<span class="text-muted font-weight-bold"><?php esc_html_e( 'Exam', 'school-management' ); ?></span>
				</div>
			</div>

			<div id="wlsm-calendar-loader" class="text-center py-5" style="display:none;">
				<div class="spinner-border text-primary" role="status"></div>
			</div>
			<div id="wlsm-calendar-grid"></div>
		</div>
	</div>
</div>
