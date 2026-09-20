<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';

$current_school_user = WLSM_M_Role::can( 'manage_id_cards' );
if ( ! $current_school_user ) {
	die();
}
$current_school = $current_school_user['school'];

$page_url = WLSM_M_Staff_General::get_custom_id_cards_page_url();
$permissions = $current_school['permissions'];

if ( isset( $_GET['action'] ) && ! empty( $_GET['action'] ) && 'save' === $_GET['action'] ) {
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/general/id-cards/save.php';
} else {
?>

<div class="row">
	<div class="col-md-12">
		<div class="text-center wlsm-section-heading-block">
			<span class="wlsm-section-heading">
				<i class="fas fa-id-card"></i>
				<?php esc_html_e( 'ID Card Layouts', 'school-management' ); ?>
			</span>
			<span class="float-md-right">
			<?php if ( WLSM_M_Role::check_permission( array( 'manage_id_cards' ), $permissions ) ) : ?>
					<a href="<?php echo esc_url( $page_url . '&action=save' ); ?>" class="btn btn-sm btn-outline-light">
						<i class="fas fa-plus-square"></i>&nbsp;
						<?php echo esc_html__( 'Add New Layout', 'school-management' ); ?>
					</a>
				<?php endif; ?>
			</span>
		</div>
		<div class="wlsm-table-block">
			<table class="table table-hover table-bordered" id="wlsm-id-cards-table" width="100%">
				<thead>
					<tr class="text-white bg-primary">
						<th scope="col"><?php esc_html_e( 'Title', 'school-management' ); ?></th>
						<th scope="col" class="text-nowrap"><?php esc_html_e( 'Action', 'school-management' ); ?></th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>
<?php } ?>
