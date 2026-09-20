<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_School.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Role.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Setting.php';

$current_school_id = NULL;

$user_info = WLSM_M_Role::get_user_info();

if ( $user_info['current_school'] ) {
	$current_school_id = $user_info['current_school']['id'];
}

global $wpdb;
$schools = $wpdb->get_results( 'SELECT s.ID, s.label, s.phone, s.email, s.address, s.is_active, COUNT(cs.ID) as classes_count FROM ' . WLSM_SCHOOLS . ' as s LEFT OUTER JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.school_id = s.ID GROUP BY s.ID' );
?>

<div class="wlsm container-fluid">
	<div class="wlsm-main-header card col">
		<div class="card-header">
			<h1 class="h3 text-center">
				<i class="fas fa-school text-primary"></i>
				<?php esc_html_e( 'Dashboard', 'school-management' ); ?>
			</h1>
		</div>
	</div>
	<?php
	if ( count( $schools ) ) {
	?>
	<div class="row">
		<?php
		foreach ( $schools as $school ) {
			$settings_general = WLSM_M_Setting::get_settings_general( $school->ID );
			$school_logo      = $settings_general['school_logo'];
		?>
		<div class="col-sm-6 col-md-4">
			<a href="javascript:void(0)" class="wlsm-school-card-link" data-school="<?php echo esc_attr( $school->ID ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'set-school-' . $school->ID ) ); ?>">
				<div class="card wlsm-school-card <?php if ( $school->ID === $current_school_id ) { echo 'wlsm-school-card-border'; } ?>">
					<div class="card-body">
						<div class="d-flex align-items-center mb-3">
							<?php if ( ! empty( $school_logo ) ) { ?>
								<img src="<?php echo esc_url( wp_get_attachment_url( $school_logo ) ); ?>" alt="<?php echo esc_attr( WLSM_M_School::get_label_text( $school->label ) ); ?>" class="wlsm-school-card-logo mr-3" style="max-width: 60px; height: auto;">
							<?php } else { ?>
								<div class="wlsm-school-card-logo-placeholder mr-3 d-flex align-items-center justify-content-center bg-light text-primary rounded" style="width: 60px; height: 60px; font-size: 24px;">
									<i class="fas fa-school"></i>
								</div>
							<?php } ?>
							<h6 class="card-title wlsm-school-card-title wlsm-school-card-dark mb-0"><?php echo esc_html( WLSM_M_School::get_label_text( $school->label ) ); ?></h6>
						</div>
						
						<ul class="list-unstyled mb-0">
							<li class="mb-1">
								<span class="wlsm-school-card-light wlsm-font-bold"><i class="fas fa-phone-alt mr-1"></i> <?php esc_html_e( 'Phone:', 'school-management' ); ?></span>
								<span class="wlsm-school-card-dark wlsm-font-bold"><?php echo esc_html( $school->phone ); ?></span>
							</li>
							<?php if ( ! empty( $school->email ) ) { ?>
							<li class="mb-1">
								<span class="wlsm-school-card-light wlsm-font-bold"><i class="fas fa-envelope mr-1"></i> <?php esc_html_e( 'Email:', 'school-management' ); ?></span>
								<span class="wlsm-school-card-dark wlsm-font-bold"><?php echo esc_html( $school->email ); ?></span>
							</li>
							<?php } ?>
							<?php if ( ! empty( $school->address ) ) { ?>
							<li class="mb-1">
								<span class="wlsm-school-card-light wlsm-font-bold"><i class="fas fa-map-marker-alt mr-1"></i> <?php esc_html_e( 'Address:', 'school-management' ); ?></span>
								<span class="wlsm-school-card-dark wlsm-font-bold"><?php echo esc_html( $school->address ); ?></span>
							</li>
							<?php } ?>
							<li class="mb-1">
								<span class="wlsm-school-card-light wlsm-font-bold"><i class="fas fa-layer-group mr-1"></i> <?php esc_html_e( 'Total Classes:', 'school-management' ); ?></span>
								<span class="wlsm-school-card-dark wlsm-font-bold">
									<?php
									if ( $school->classes_count ) {
										echo esc_html( $school->classes_count );
									} else {
										esc_html_e( 'Assign Classes', 'school-management');
									}
									?>
								</span>
							</li>
							<li>
								<span class="wlsm-school-card-light wlsm-font-bold"><i class="fas fa-info-circle mr-1"></i> <?php esc_html_e( 'Status:', 'school-management' ); ?></span>
								<span class="wlsm-school-card-dark wlsm-font-bold"><?php echo esc_html( WLSM_M_School::get_status_text( $school->is_active ) ); ?></span>
							</li>
						</ul>
					</div>
				</div>
			</a>
		</div>
		<?php
		}
		?>
	</div>
	<?php
	} else {
	?>
	<div class="row mt-2">
		<div class="col-md-12">
			<div class="alert alert-secondary text-center">
				<?php esc_html_e( 'There is no school.', 'school-management' ); ?>&nbsp;
				<a href="<?php echo esc_url( WLSM_M_School::get_page_url() . '&action=save' ); ?>" class="btn btn-sm btn-primary ">
					<?php esc_html_e( 'Add a New School', 'school-management' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
	}
	?>
</div>
