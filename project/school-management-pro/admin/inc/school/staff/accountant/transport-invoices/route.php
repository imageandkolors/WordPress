<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/global.php';

$page_url = WLSM_M_Staff_Accountant::get_transport_invoices_page_url();

$action = '';
if ( isset( $_GET['action'] ) ) {
	$action = sanitize_text_field( $_GET['action'] );
}
?>
<div class="wlsm container-fluid">
	<?php
	require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/partials/header.php';
	?>

	<div class="row">
		<div class="col-md-12">
			<div class="mt-3 text-center wlsm-section-heading-block">
				<span class="wlsm-section-heading-box">
					<span class="wlsm-section-heading">
						<?php
						if ( 'view-student' === $action ) {
							esc_html_e( 'Student Transport Detail', 'school-management' );
						} else {
							esc_html_e( 'Transport Invoices', 'school-management' );
						}
						?>
					</span>
				</span>
			</div>
			<?php
			if ( 'view-student' === $action ) {
				require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/transport-invoices/student-transport.php';
			} elseif ( 'bulk_collect' === $action ) {
				require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/transport-invoices/bulk-collect.php';
			} elseif ( 'print_bulk_receipt' === $action ) {
				require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/transport-invoices/print-bulk-receipt.php';
			} else {
				require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/staff/accountant/transport-invoices/index.php';
			}
			?>
		</div>
	</div>
</div>
