<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'public/inc/account/student/partials/navigation.php';
?>
<div class="wlsm-content-area wlsm-section-calendar wlsm-student-calendar">

	<div class="wlsm-st-main-title">
		<span>
			<i class="fas fa-calendar-alt"></i>
			<?php esc_html_e( 'Calendar', 'school-management' ); ?>
		</span>
	</div>

	<!-- Calendar toolbar -->
	<div class="wlsm-cal-toolbar">
		<div class="wlsm-cal-nav">
			<button id="wlsm-cal-prev" class="wlsm-cal-btn wlsm-cal-btn-icon" aria-label="<?php esc_attr_e('Previous', 'school-management'); ?>">
				<i class="fas fa-chevron-left"></i>
			</button>
			<span id="wlsm-cal-title" class="wlsm-cal-title-text"></span>
			<button id="wlsm-cal-next" class="wlsm-cal-btn wlsm-cal-btn-icon" aria-label="<?php esc_attr_e('Next', 'school-management'); ?>">
				<i class="fas fa-chevron-right"></i>
			</button>
		</div>

		<div class="wlsm-cal-view-switcher">
			<button id="wlsm-cal-today" class="wlsm-cal-btn wlsm-cal-btn-view active">
				<?php esc_html_e( 'Month', 'school-management' ); ?>
			</button>
			<button id="wlsm-cal-year" class="wlsm-cal-btn wlsm-cal-btn-view">
				<?php esc_html_e( 'Year', 'school-management' ); ?>
			</button>
		</div>

		<div class="wlsm-cal-legend">
			<span class="wlsm-cal-legend-dot wlsm-cal-legend-holiday"></span>
			<span class="wlsm-cal-legend-label"><?php esc_html_e( 'Holiday', 'school-management' ); ?></span>
			<span class="wlsm-cal-legend-dot wlsm-cal-legend-event"></span>
			<span class="wlsm-cal-legend-label"><?php esc_html_e( 'Event', 'school-management' ); ?></span>
			<span class="wlsm-cal-legend-dot wlsm-cal-legend-exam"></span>
			<span class="wlsm-cal-legend-label"><?php esc_html_e( 'Exam', 'school-management' ); ?></span>
		</div>
	</div>

	<!-- Loader -->
	<div id="wlsm-calendar-loader" style="display:none;">
		<div class="wlsm-cal-loading">
			<div class="wlsm-cal-spinner"></div>
			<span><?php esc_html_e( 'Loading…', 'school-management' ); ?></span>
		</div>
	</div>

	<!-- Grid injected by JS -->
	<div id="wlsm-calendar-grid"></div>

</div>
