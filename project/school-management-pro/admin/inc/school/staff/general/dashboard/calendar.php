<?php
defined( 'ABSPATH' ) || die();
?>
<div class="wlsm-table-block wlsm-dashboard-calendar-widget">
	<div class="d-flex justify-content-between align-items-center mb-3">
		<div class="d-flex align-items-center">
			<button id="wlsm-cal-prev" class="btn btn-sm btn-light border mr-1">
				<i class="fas fa-chevron-left text-muted"></i>
			</button>
			<h6 id="wlsm-cal-title" class="mb-0 mx-2 font-weight-bold text-dark" style="min-width: 120px; text-align: center;"></h6>
			<button id="wlsm-cal-next" class="btn btn-sm btn-light border">
				<i class="fas fa-chevron-right text-muted"></i>
			</button>
			<button id="wlsm-cal-today" class="btn btn-sm btn-outline-primary ml-2">
				<?php esc_html_e( 'Today', 'school-management' ); ?>
			</button>
		</div>
		<div class="d-flex align-items-center d-none d-sm-flex" style="font-size: 0.75rem;">
			<span class="wlsm-pill-exam p-1 mr-1" style="width:10px;height:10px;display:inline-block;border-radius:2px;"></span>
			<span class="text-muted mr-2 font-weight-bold"><?php esc_html_e( 'Exam', 'school-management' ); ?></span>
			<span class="wlsm-pill-event p-1 mr-1" style="width:10px;height:10px;display:inline-block;border-radius:2px;"></span>
			<span class="text-muted mr-2 font-weight-bold"><?php esc_html_e( 'Event', 'school-management' ); ?></span>
			<span class="wlsm-pill-holiday p-1 mr-1" style="width:10px;height:10px;display:inline-block;border-radius:2px;"></span>
			<span class="text-muted font-weight-bold"><?php esc_html_e( 'Holiday', 'school-management' ); ?></span>
		</div>
	</div>

	<div id="wlsm-calendar-loader" class="text-center py-5" style="display:none;">
		<div class="spinner-border text-primary" role="status"></div>
	</div>
	<div id="wlsm-calendar-grid" class="wlsm-dashboard-calendar-grid"></div>
</div>
