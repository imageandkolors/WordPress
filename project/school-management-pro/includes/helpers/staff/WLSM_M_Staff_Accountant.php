<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_Config.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/WLSM_M_Invoice.php';

class WLSM_M_Staff_Accountant {
	public static function get_invoices_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_INVOICES );
	}

	public static function get_transport_invoices_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_TRANSPORT_INVOICES );
	}

	public static function get_payment_method_statistics($school_id, $session_id) {
		global $wpdb;

		$payments_table = WLSM_PAYMENTS;
		$invoices_table = WLSM_INVOICES;
		$students_table = WLSM_STUDENT_RECORDS;
		$sessions_table = WLSM_SESSIONS;

		// Get payments grouped by payment method
		$payments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT p.payment_method, SUM(p.amount) as total_amount, COUNT(p.ID) as payment_count,
				COUNT(DISTINCT p.invoice_id) as invoice_count
				FROM {$payments_table} as p
				JOIN {$invoices_table} as i ON i.ID = p.invoice_id
				JOIN {$students_table} as sr ON sr.ID = p.student_record_id
				JOIN {$sessions_table} as ss ON ss.ID = sr.session_id
				WHERE p.school_id = %d
				AND ss.ID = %d
				GROUP BY p.payment_method",
				$school_id,
				$session_id
			)
		);

		$payment_statistics = array();
		foreach ($payments as $payment) {
			$payment_statistics[$payment->payment_method] = array(
				'amount' => $payment->total_amount,
				'count' => $payment->payment_count,
				'invoice_count' => $payment->invoice_count
			);
		}

		return $payment_statistics;
	}

	public static function fetch_transport_students_query( $school_id, $session_id, $filter ) {
		$where = 'cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . ' AND sr.is_active = 1';

		if ( ! empty( $filter['class_id'] ) ) {
			$where .= ' AND c.ID = ' . absint( $filter['class_id'] );
		}
		if ( ! empty( $filter['section_id'] ) ) {
			$where .= ' AND se.ID = ' . absint( $filter['section_id'] );
		}
		if ( ! empty( $filter['student_id'] ) && $filter['student_id'] !== 'all' ) {
			$where .= ' AND sr.ID = ' . absint( $filter['student_id'] );
		}

		$query = "SELECT sr.ID, sr.name, sr.enrollment_number, c.label as class_label, se.label as section_label, ro.name as route_name, v.vehicle_number, rv.ID as route_vehicle_id, ro.fare
			FROM " . WLSM_STUDENT_RECORDS . " as sr
			JOIN " . WLSM_SESSIONS . " as ss ON ss.ID = sr.session_id
			JOIN " . WLSM_SECTIONS . " as se ON se.ID = sr.section_id
			JOIN " . WLSM_CLASS_SCHOOL . " as cs ON cs.ID = se.class_school_id
			JOIN " . WLSM_CLASSES . " as c ON c.ID = cs.class_id
			LEFT JOIN " . WLSM_ROUTE_VEHICLE . " as rv ON rv.ID = sr.route_vehicle_id
			LEFT JOIN " . WLSM_ROUTES . " as ro ON ro.ID = rv.route_id
			LEFT JOIN " . WLSM_VEHICLES . " as v ON v.ID = rv.vehicle_id
			WHERE $where ORDER BY sr.name ASC";

		return $query;
	}

	public static function 	fetch_invoices_query( $school_id, $session_id, $filter ) {
		require WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/partials/fetch_invoices_query.php';

		$query = 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, i.amount, (i.amount) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount) - COALESCE(SUM(p.amount), 0)) as due, i.status, sr.name as student_name, sr.father_name,  sr.phone, sr.admission_number, sr.enrollment_number, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . $where;
		return $query;
	}

	public static function 	fetch_invoices_query_collect_payments( $school_id, $session_id, $filter ) {

		require WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/partials/fetch_invoices_query.php';

		$query = 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, i.amount, (i.amount) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount) - COALESCE(SUM(p.amount), 0)) as due, i.status, sr.name as student_name, sr.father_name,  sr.phone, sr.admission_number, sr.enrollment_number, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . $where;
		return $query;
	}

		public static function fetch_invoices_report($school_id, $session_id, $filter) {
		require WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/partials/fetch_invoices_query.php';

		// Check if payment method filter is applied
		$payment_method_condition = '';
		if (!empty($filter['payment_method'])) {
			$payment_method_condition = " AND payment_method = '" . esc_sql($filter['payment_method']) . "'";
		}

		$query = 'SELECT COALESCE(SUM(i.amount)) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount) - COALESCE(SUM(p.amount), 0)) as due , sr.name as student_name, sr.father_name, sr.phone, sr.admission_number, sr.enrollment_number, c.label as class_label, se.label as section_label FROM ' . WLSM_STUDENT_RECORDS . ' as sr
		JOIN ' . WLSM_INVOICES . ' as i ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN (SELECT invoice_id, SUM(amount) as amount FROM ' . WLSM_PAYMENTS . ' WHERE 1=1' . $payment_method_condition . ' GROUP BY invoice_id) as p ON p.invoice_id = i.ID
		WHERE cs.school_id = ' . absint($school_id) . ' AND ss.ID = ' . absint($session_id) . $where;

		return $query;
	}

	public static function 	get_invoices_report_total( $school_id, $session_id, $class_id, $section_id, $payment_method, $status = '' ) {
	global $wpdb;

	$where = 'WHERE cs.school_id = %d AND ss.ID = %d AND c.ID = %d';
	$params = array( $school_id, $session_id, $class_id );

	if ( $section_id ) {
		$where .= ' AND se.ID = %d';
		$params[] = $section_id;
	}

	// Payment method condition for JOIN
	$payment_join = '';
	if ( $payment_method ) {
		$payment_join = ' AND p.payment_method = %s';
		// We need to inject this param before the WHERE params, or handle param order carefully.
		// Since we are building a raw query string, we can't easily inject params in the middle of the array if we use one array.
		// Strategy: Use specific placeholders or rebuild params array.
	}

	if ( $status ) {
		$where .= ' AND i.status = %s';
		$params[] = $status;
	}

	$group_by = 'GROUP BY i.ID';

	// Construct the JOIN clause with payment method parameter if needed
	$join_payments = 'LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID';
	if ( $payment_method ) {
		$join_payments .= $wpdb->prepare( ' AND p.payment_method = %s', $payment_method );
	}

	$sql = 'SELECT SUM(due) as due, SUM(paid) as paid FROM (
		SELECT (i.amount - COALESCE(SUM(p.amount), 0)) as due, COALESCE(SUM(p.amount), 0) as paid FROM ' . WLSM_INVOICES . ' as i
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		' . $join_payments . ' ' . $where . ' ' . $group_by . '
	) as sub';

	$query = $wpdb->get_row( $wpdb->prepare( $sql, $params ) );
	return $query;
}

	public static function fetch_invoices_report_query_group_by() {
		$group_by = 'GROUP BY sr.ID';
		return $group_by;
	}

	public static function fetch_invoices( $school_id, $session_id ) {
		require WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/partials/fetch_invoices_query.php';

		$query = 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, i.amount, (i.amount ) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount ) - COALESCE(SUM(p.amount), 0)) as due, i.status, sr.name as student_name, sr.phone, sr.admission_number, sr.enrollment_number, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id );
		return $query;
	}

	public static function fetch_invoices_query_group_by() {
		$group_by = 'GROUP BY i.ID';
		return $group_by;
	}

	public static function fetch_invoices_query_count( $school_id, $session_id, $filter ) {
		require WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/partials/fetch_invoices_query.php';

		$query = 'SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . $where;
		return $query;
	}

	public static function get_invoice( $school_id, $session_id, $id ) {
		global $wpdb;
		$invoice = $wpdb->get_row( $wpdb->prepare( 'SELECT i.ID, i.status, p.ID as payment_id FROM ' . WLSM_INVOICES . ' as i
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
		WHERE cs.school_id = %d AND ss.ID = %d AND i.ID = %d', $school_id, $session_id, $id ) );
		return $invoice;
	}

	public static function fetch_invoice( $school_id, $session_id, $id ) {
		global $wpdb;
		$invoice = $wpdb->get_row( $wpdb->prepare( 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.fee_list, i.description as invoice_description, i.date_issued, i.due_date, i.amount, i.invoice_amount_total, i.discount, i.due_date_amount, (i.amount) as payable, COALESCE(SUM(p.amount), 0) as paid, i.partial_payment, i.status, sr.ID as student_id, sr.name as student_name, sr.phone, sr.email, sr.admission_number, sr.enrollment_number, sr.roll_number, sr.father_name, sr.father_phone, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
		WHERE cs.school_id = %d AND ss.ID = %d AND i.ID = %d', $school_id, $session_id, $id ) );
		return $invoice;
	}

	public static function get_discount_data($invoice_id) {
		global $wpdb;

		// Fetch discount data based on invoice_id
		$discount_data = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM " . WLSM_DISCOUNTS . " WHERE invoice_id = %d",
				$invoice_id
			)
		);

		return $discount_data;
	}

	public static function fetch_bulk_invoices( $school_id, $session_id, $class_id ) {
		global $wpdb;
		$invoice =  ( $wpdb->prepare( 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.fee_list, i.description as invoice_description, i.date_issued, i.due_date, i.amount, i.invoice_amount_total, i.discount, i.due_date_amount, (i.amount) as payable, p.amount as paid, i.partial_payment, i.status, sr.ID as student_id, sr.name as student_name, sr.phone, sr.email, sr.admission_number, sr.enrollment_number, sr.roll_number, sr.father_name, sr.father_phone, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
		WHERE cs.school_id = %d AND ss.ID = %d AND cs.class_id = %d', $school_id, $session_id, $class_id ) );
		return $invoice;
	}

	public static function get_invoice_payments( $invoice_id ) {
		global $wpdb;
		$payments = $wpdb->get_results( $wpdb->prepare( 'SELECT p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note FROM ' . WLSM_PAYMENTS . ' as p
		WHERE p.invoice_id = %d ORDER BY p.ID DESC', $invoice_id ) );
		return $payments;
	}

	public static function fetch_invoice_payments_query( $school_id, $session_id, $invoice_id ) {
		$query = 'SELECT p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.attachment, p.created_at, p.note FROM ' . WLSM_PAYMENTS . ' as p
		JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . ' AND p.invoice_id = ' . absint( $invoice_id );
		return $query;
	}

	public static function fetch_payments_query_group_by() {
		$group_by = 'GROUP BY p.ID';
		return $group_by;
	}

	public static function fetch_invoice_payments_query_count( $school_id, $session_id, $invoice_id ) {
		$query = 'SELECT COUNT(DISTINCT p.ID) FROM ' . WLSM_PAYMENTS . ' as p
		JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		WHERE cs.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ) . ' AND p.invoice_id = ' . absint( $invoice_id );
		return $query;
	}

	public static function get_invoice_payments_total( $invoice_id ) {
		global $wpdb;
		$total = $wpdb->get_var(
			$wpdb->prepare( 'SELECT COALESCE(SUM(p.amount), 0) as paid FROM ' . WLSM_PAYMENTS . ' as p
				JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
				WHERE i.ID = %d GROUP BY i.ID', $invoice_id )
		);
		return $total;
	}

	public static function get_invoice_payment( $invoice_id, $id ) {
		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT p.ID FROM ' . WLSM_PAYMENTS . ' as p
		WHERE p.invoice_id = %d AND p.ID = %d', $invoice_id, $id ) );
		return $payment;
	}

	public static function get_invoice_by_id( $invoice_id ) {
		global $wpdb;
		$invoice = $wpdb->get_row( $wpdb->prepare( 'SELECT i.ID, i.amount, i.discount FROM ' . WLSM_INVOICES . ' as i WHERE i.ID = %d', $invoice_id ) );
		return $invoice;
	}

	public static function fetch_payments_query( $school_id, $session_id, $start_date = null, $end_date = null ) {
		$query = 'SELECT sr.ID as student_id, sr.name as student_name, sr.admission_number, sr.enrollment_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.attachment, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PAYMENTS . ' as p
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		WHERE p.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id );

		if (!empty($start_date)) {
		$query = ('SELECT sr.name as student_name, sr.admission_number, sr.enrollment_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.attachment, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PAYMENTS . ' as p
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		WHERE p.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id ). ' AND p.created_at BETWEEN "'.  $start_date.'"
		AND "'.($end_date).'"');
		}
		return $query;
	}

	public static function fetch_payments_query_count( $school_id, $session_id ) {
		$query = 'SELECT COUNT(DISTINCT p.ID) FROM ' . WLSM_PAYMENTS . ' as p
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		WHERE p.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id );
		return $query;
	}

	public static function fetch_payment( $school_id, $session_id, $id ) {
		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.ID as student_record_id, sr.name as student_name, sr.admission_number, sr.enrollment_number, sr.roll_number, sr.phone, sr.email, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.amount as invoice_amount, i.label as invoice_title, c.label as class_label, se.label as section_label, p.added_by, p.bank_name, p.cheque_number, p.cheque_date, p.authorized_by FROM ' . WLSM_PAYMENTS . ' as p
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		WHERE p.school_id = %d AND ss.ID = %d AND p.ID = %d', $school_id, $session_id, $id ) );

			if ( $payment ) {
				$invoice_payable = (float) $payment->invoice_payable;
				if ( ! $invoice_payable && isset( $payment->invoice_amount ) ) {
					$invoice_payable = (float) $payment->invoice_amount;
				}

				$payment->invoice_payable = $invoice_payable;

				if ( $payment->invoice_id ) {
					$due = WLSM_M_Invoice::get_due_after_payment( $payment->invoice_id, $school_id, $payment->ID, $invoice_payable, isset( $payment->student_record_id ) ? $payment->student_record_id : 0 );
					if ( null !== $due ) {
						$payment->due = $due;
					} else {
						$payment->due = WLSM_Config::sanitize_money( $invoice_payable - (float) $payment->amount );
					}
				} else {
					$payment->due = WLSM_Config::sanitize_money( $invoice_payable - (float) $payment->amount );
				}
			}

		return $payment;
	}

	public static function get_payment( $school_id, $session_id, $id ) {
		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT p.ID, p.invoice_id FROM ' . WLSM_PAYMENTS . ' as p
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		WHERE p.school_id = %d AND ss.ID = %d AND p.ID = %d', $school_id, $session_id, $id ) );
		return $payment;
	}

	public static function get_payment_note( $school_id, $session_id, $id ) {
		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT p.ID, p.note FROM ' . WLSM_PAYMENTS . ' as p
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		WHERE p.school_id = %d AND ss.ID = %d AND p.ID = %d', $school_id, $session_id, $id ) );
		return $payment;
	}

	public static function fetch_pending_payments_query( $school_id, $session_id ) {
		$query = 'SELECT sr.ID as student_id, sr.name as student_name, sr.admission_number, sr.enrollment_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.attachment, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PENDING_PAYMENTS . ' as p
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		WHERE p.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id );
		return $query;
	}

	public static function fetch_pending_payments_query_count( $school_id, $session_id ) {
		$query = 'SELECT COUNT(DISTINCT p.ID) FROM ' . WLSM_PENDING_PAYMENTS . ' as p
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		WHERE p.school_id = ' . absint( $school_id ) . ' AND ss.ID = ' . absint( $session_id );
		return $query;
	}

	public static function fetch_pending_payment( $school_id, $session_id, $id ) {
		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.ID as student_record_id, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.attachment, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id FROM ' . WLSM_PENDING_PAYMENTS . ' as p
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		WHERE p.school_id = %d AND ss.ID = %d AND p.ID = %d', $school_id, $session_id, $id ) );
		return $payment;
	}

	public static function get_pending_payment( $school_id, $session_id, $id ) {
		global $wpdb;
		$payment = $wpdb->get_row( $wpdb->prepare( 'SELECT p.ID, p.invoice_id FROM ' . WLSM_PENDING_PAYMENTS . ' as p
		JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
		LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
		WHERE p.school_id = %d AND ss.ID = %d AND p.ID = %d', $school_id, $session_id, $id ) );
		return $payment;
	}

	public static function calculate_payable_amount( $invoice ) {
		return $invoice->amount - $invoice->discount;
	}

	public static function refresh_invoice_status( $invoice_id ) {
		global $wpdb;

		ob_start();

		$invoice = self::get_invoice_by_id( $invoice_id );

		$paid    = self::get_invoice_payments_total( $invoice_id );

		$payable = self::calculate_payable_amount( $invoice );

		$invoice_status = WLSM_M_Invoice::get_status_key( $payable, $paid );

		$data = array(
			'status'     => $invoice_status,
			'updated_at' => current_time( 'Y-m-d H:i:s' ),
		);

		$success = $wpdb->update( WLSM_INVOICES, $data, array( 'ID' => $invoice_id ) );

		$buffer = ob_get_clean();
		if ( ! empty( $buffer ) ) {
			throw new Exception( $buffer );
		}

		if ( false === $success ) {
			throw new Exception( $wpdb->last_error );
		}

		return $invoice_status;
	}

	public static function get_expenses_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_EXPENSES );
	}

	public static function get_income_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_INCOME );
	}

	public static function fetch_expense_category_query( $school_id ) {
		$query = 'SELECT ec.ID, ec.label FROM ' . WLSM_EXPENSE_CATEGORIES . ' as ec
		WHERE ec.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function fetch_expense_category_query_group_by() {
		$group_by = 'GROUP BY ec.ID';
		return $group_by;
	}

	public static function fetch_expense_category_query_count( $school_id ) {
		$query = 'SELECT COUNT(ec.ID) FROM ' . WLSM_EXPENSE_CATEGORIES . ' as ec
		WHERE ec.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function get_expense_category( $school_id, $id ) {
		global $wpdb;
		$expense_category = $wpdb->get_row( $wpdb->prepare( 'SELECT ec.ID FROM ' . WLSM_EXPENSE_CATEGORIES . ' as ec
		WHERE ec.school_id = %d AND ec.ID = %d', $school_id, $id ) );
		return $expense_category;
	}

	public static function fetch_expense_category( $school_id, $id ) {
		global $wpdb;
		$expense_category = $wpdb->get_row( $wpdb->prepare( 'SELECT ec.ID, ec.label FROM ' . WLSM_EXPENSE_CATEGORIES . ' as ec
		WHERE ec.school_id = %d AND ec.ID = %d', $school_id, $id ) );
		return $expense_category;
	}

	public static function fetch_income_category_query( $school_id ) {
		$query = 'SELECT ic.ID, ic.label FROM ' . WLSM_INCOME_CATEGORIES . ' as ic
		WHERE ic.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function fetch_income_category_query_group_by() {
		$group_by = 'GROUP BY ic.ID';
		return $group_by;
	}

	public static function fetch_income_category_query_count( $school_id ) {
		$query = 'SELECT COUNT(ic.ID) FROM ' . WLSM_INCOME_CATEGORIES . ' as ic
		WHERE ic.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function get_income_category( $school_id, $id ) {
		global $wpdb;
		$income_category = $wpdb->get_row( $wpdb->prepare( 'SELECT ic.ID FROM ' . WLSM_INCOME_CATEGORIES . ' as ic
		WHERE ic.school_id = %d AND ic.ID = %d', $school_id, $id ) );
		return $income_category;
	}

	public static function fetch_income_category( $school_id, $id ) {
		global $wpdb;
		$income_category = $wpdb->get_row( $wpdb->prepare( 'SELECT ic.ID, ic.label FROM ' . WLSM_INCOME_CATEGORIES . ' as ic
		WHERE ic.school_id = %d AND ic.ID = %d', $school_id, $id ) );
		return $income_category;
	}

	public static function fetch_expense_query( $school_id, $start_date, $end_date, $session_start_date = null, $session_end_date = null ) {
		$query = 'SELECT ep.ID, ep.label, ep.invoice_number, ep.amount, ep.expense_date, ep.note, ec.label as expense_category, ep.supplier_name FROM ' . WLSM_EXPENSES . ' as ep
		LEFT OUTER JOIN ' . WLSM_EXPENSE_CATEGORIES . ' as ec ON ec.ID = ep.expense_category_id
		WHERE ep.school_id = ' . absint( $school_id ) .' AND ep.expense_date between "' . $session_start_date . '" and "' . $session_end_date . '"';

		if ( ! empty( $start_date && $end_date ) ) {
			$query .= ' AND ep.expense_date BETWEEN "' . $start_date . '" AND "' . $end_date . '"';
		}
		return $query;
	}

	public static function fetch_expense_query_group_by() {
		$group_by = 'GROUP BY ep.ID';
		return $group_by;
	}

	public static function fetch_expense_query_count( $school_id, $start_date, $end_date, $session_start_date = null, $session_end_date = null ) {
		$query = 'SELECT COUNT(DISTINCT ep.ID) FROM ' . WLSM_EXPENSES . ' as ep
		LEFT OUTER JOIN ' . WLSM_EXPENSE_CATEGORIES . ' as ec ON ec.ID = ep.expense_category_id
		WHERE ep.school_id = ' . absint( $school_id ).' AND ep.expense_date between "' . $session_start_date . '" and "' . $session_end_date . '"';

		if ( ! empty( $start_date && $end_date ) ) {
			$query .= ' AND ep.expense_date BETWEEN "' . $start_date . '" AND "' . $end_date . '"';
		}
		return $query;
	}

	public static function get_expense( $school_id, $id ) {
		global $wpdb;
		$expense = $wpdb->get_row( $wpdb->prepare( 'SELECT ep.ID FROM ' . WLSM_EXPENSES . ' as ep
		WHERE ep.school_id = %d AND ep.ID = %d', $school_id, $id ) );
		return $expense;
	}

	public static function fetch_expense( $school_id, $id ) {
		global $wpdb;
		$expense = $wpdb->get_row( $wpdb->prepare( 'SELECT ep.ID, ep.label, ep.invoice_number, ep.amount, ep.expense_date, ep.note, ep.attachment, ep.expense_category_id, ep.supplier_name, ep.receiver_signature, ec.label as expense_category FROM ' . WLSM_EXPENSES . ' as ep
		LEFT JOIN ' . WLSM_EXPENSE_CATEGORIES . ' as ec ON ec.ID = ep.expense_category_id
		WHERE ep.school_id = %d AND ep.ID = %d', $school_id, $id ) );
		return $expense;
	}

	public static function fetch_expense_categories( $school_id ) {
		global $wpdb;
		$expense_categories = $wpdb->get_results( $wpdb->prepare( 'SELECT ec.ID, ec.label FROM ' . WLSM_EXPENSE_CATEGORIES . ' as ec WHERE ec.school_id = %d ORDER BY ec.ID ASC', $school_id ) );
		return $expense_categories;
	}

	public static function get_expense_note( $school_id, $id ) {
		global $wpdb;
		$expense = $wpdb->get_row( $wpdb->prepare( 'SELECT ep.ID, ep.note FROM ' . WLSM_EXPENSES . ' as ep
		WHERE ep.school_id = %d AND ep.ID = %d', $school_id, $id ) );
		return $expense;
	}

	public static function fetch_income_query( $school_id, $start_date, $end_date ) {
		$query = 'SELECT im.ID, im.label, im.invoice_number, im.amount, im.income_date, im.note, ic.label as income_category, im.doner_name FROM ' . WLSM_INCOME . ' as im
		LEFT OUTER JOIN ' . WLSM_INCOME_CATEGORIES . ' as ic ON ic.ID = im.income_category_id
		WHERE im.school_id = ' . absint( $school_id );

		if ($start_date && $end_date) {
			$query .= ' AND im.income_date BETWEEN "' . $start_date . '" AND "' . $end_date . '"';
		}

		return $query;
	}

	public static function fetch_income_query_group_by() {
		$group_by = 'GROUP BY im.ID';
		return $group_by;
	}

	public static function fetch_income_query_count( $school_id ) {
		$query = 'SELECT COUNT(DISTINCT im.ID) FROM ' . WLSM_INCOME . ' as im
		LEFT OUTER JOIN ' . WLSM_INCOME_CATEGORIES . ' as ic ON ic.ID = im.income_category_id
		WHERE im.school_id = ' . absint( $school_id );
		return $query;
	}

	public static function get_income( $school_id, $id ) {
		global $wpdb;
		$income = $wpdb->get_row( $wpdb->prepare( 'SELECT im.ID FROM ' . WLSM_INCOME . ' as im
		WHERE im.school_id = %d AND im.ID = %d', $school_id, $id ) );
		return $income;
	}

	public static function fetch_income( $school_id, $id ) {
		global $wpdb;
		$income = $wpdb->get_row( $wpdb->prepare( 'SELECT im.ID, im.label, im.invoice_number, im.amount, im.income_date, im.note, im.attachment, im.income_category_id, im.doner_name, im.receiver_signature, ic.label as income_category FROM ' . WLSM_INCOME . ' as im
		LEFT JOIN ' . WLSM_INCOME_CATEGORIES . ' as ic ON ic.ID = im.income_category_id
		WHERE im.school_id = %d AND im.ID = %d', $school_id, $id ) );
		return $income;
	}

	public static function fetch_income_categories( $school_id ) {
		global $wpdb;
		$income_categories = $wpdb->get_results( $wpdb->prepare( 'SELECT ic.ID, ic.label FROM ' . WLSM_INCOME_CATEGORIES . ' as ic WHERE ic.school_id = %d ORDER BY ic.ID ASC', $school_id ) );
		return $income_categories;
	}

	public static function get_income_note( $school_id, $id ) {
		global $wpdb;
		$income = $wpdb->get_row( $wpdb->prepare( 'SELECT im.ID, im.note FROM ' . WLSM_INCOME . ' as im
		WHERE im.school_id = %d AND im.ID = %d', $school_id, $id ) );
		return $income;
	}

	public static function get_student_pending_invoices( $student_id ) {
		global $wpdb;
		$invoices = $wpdb->get_results(
			$wpdb->prepare( 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, i.amount, (i.amount ) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount ) - COALESCE(SUM(p.amount), 0)) as due, i.status, sr.name as student_name, sr.enrollment_number, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
				LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
				WHERE sr.ID = %d AND (i.status = "%s" OR i.status = "%s") GROUP BY i.ID ORDER BY i.ID DESC', $student_id, WLSM_M_Invoice::get_unpaid_key(), WLSM_M_Invoice::get_partially_paid_key() )
		);
		return $invoices;
	}

	public static function get_student_pending_invoices_paid($student_id, $fee_paid)
	{
		global $wpdb;
		$invoices = $wpdb->get_results(
			$wpdb->prepare('SELECT i.ID, i.label as invoice_title, wif.label as fees_title, wif.active_on_dashboard, i.invoice_number, i.date_issued, i.due_date, i.amount, (i.amount ) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount ) - COALESCE(SUM(p.amount), 0)) as due, i.status, sr.name as student_name, sr.enrollment_number, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES .' as i

				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
				JOIN ' . WLSM_CLASSES .' as c ON c.ID = cs.class_id
				JOIN ' . WLSM_FEES . ' as wif ON wif.label = i.label AND wif.session_id = sr.session_id
				LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
				WHERE sr.ID = %d AND wif.active_on_dashboard = '.$fee_paid.' AND (i.status = "%s" OR i.status = "%s") GROUP BY i.ID ORDER BY i.ID DESC', $student_id, WLSM_M_Invoice::get_unpaid_key(), WLSM_M_Invoice::get_partially_paid_key())
		);
		return $invoices;
	}

	public static function get_student_pending_invoice( $invoice_id ) {
		global $wpdb;
		$invoice = $wpdb->get_row(
			$wpdb->prepare( 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, i.amount, i.invoice_amount_total, i.discount, COALESCE(d.amount, 0) as discount_amount, (i.amount ) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount ) - COALESCE(SUM(p.amount), 0)) as due, i.status, i.due_date_amount, i.partial_payment, i.student_record_id as student_id, sr.name as student_name, sr.phone, sr.email, sr.address, sr.admission_number, sr.enrollment_number, sr.admission_number, sr.session_id, c.label as class_label, se.label as section_label, cs.school_id, u.user_email as login_email FROM ' . WLSM_INVOICES . ' as i
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
				LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
				LEFT OUTER JOIN ' . WLSM_DISCOUNTS . ' as d ON d.invoice_id = i.ID
				LEFT OUTER JOIN ' . WLSM_USERS . ' as u ON u.ID = sr.user_id
				WHERE (i.status = "%s" OR i.status = "%s") AND i.ID = %d', WLSM_M_Invoice::get_unpaid_key(), WLSM_M_Invoice::get_partially_paid_key(), $invoice_id )
		);
		return $invoice;
	}

	public static function get_student_invoices( $student_id ) {
		global $wpdb;
		$invoices = $wpdb->get_results(
			$wpdb->prepare( 'SELECT i.ID, i.label as invoice_title, i.invoice_number, i.date_issued, i.due_date, i.amount, (i.amount ) as payable, COALESCE(SUM(p.amount), 0) as paid, ((i.amount ) - COALESCE(SUM(p.amount), 0)) as due, i.status, sr.name as student_name, sr.enrollment_number, c.label as class_label, se.label as section_label FROM ' . WLSM_INVOICES . ' as i
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
				LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
				WHERE sr.ID = %d GROUP BY i.ID ORDER BY i.ID DESC', $student_id )
		);
		return $invoices;
	}

	public static function get_student_payments( $student_id ) {
		global $wpdb;
		$payments = $wpdb->get_results(
			$wpdb->prepare( 'SELECT sr.name as student_name, sr.admission_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PAYMENTS . ' as p
				JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
				LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
				WHERE sr.ID = %d GROUP BY p.ID ORDER BY p.ID DESC', $student_id )
		);
		return $payments;
	}

	public static function get_student_payment( $student_id, $payment_id ) {
		global $wpdb;
		$payment = $wpdb->get_row(
			$wpdb->prepare( 'SELECT sr.name as student_name, sr.roll_number, sr.admission_number, sr.enrollment_number, sr.phone, sr.email, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.note, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label , p.school_id FROM ' . WLSM_PAYMENTS . ' as p
				JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
				LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
				WHERE sr.ID = %d AND p.ID = %d', $student_id, $payment_id )
		);
		return $payment;
	}

	public static function get_total_payments_received( $school_id, $session_id ) {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare( 'SELECT COALESCE(SUM(p.amount), 0) as sum FROM ' . WLSM_PAYMENTS . ' as p
				JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
				JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
				WHERE p.school_id = %d AND ss.ID = %d', $school_id, $session_id )
		);
	}

	public static function get_total_fees_structure_amount( $school_id, $session_id ) {
		global $wpdb;

		return $wpdb->get_var(
			$wpdb->prepare( 'SELECT COALESCE(SUM(sft.amount), 0) as sum FROM ' . WLSM_STUDENT_FEES . ' as sft
				JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = sft.student_record_id
				JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
				JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
				JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
				WHERE cs.school_id = %d AND ss.ID = %d', $school_id, $session_id )
		);
	}

	public static function get_fees_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_FEES );
	}

	public static function get_concession_types_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_CONCESSION );
	}

	public static function get_students_concession_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STUDENTS_CONCESSION );
	}

	public static function fetch_fee_query( $school_id, $restrict_to_section = null, $session_id = null )
	{
		if ( $restrict_to_section ) {
			$section_where = ' AND cs.default_section_id = ' . absint( $restrict_to_section );
		} else {
			$section_where = '';
		}

		$session_where = '';
		if ( $session_id ) {
			$session_where = ' AND ft.session_id = ' . absint( $session_id );
		}

		$query = 'SELECT ft.ID, ft.session_id, c.label, ft.label as fee_label, ft.amount, ft.period, ft.class_id, ft.fee_type, ft.include_on_promotion FROM ' . WLSM_FEES . ' as ft
		LEFT JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.class_id = ft.class_id AND cs.school_id = ' . absint( $school_id ) . '
        LEFT JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = cs.default_section_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = ft.class_id
		WHERE ft.school_id = ' . absint( $school_id ) . $section_where . $session_where;
		return $query;
	}

	public static function fetch_fee_query_group_by() {
		$group_by = 'GROUP BY ft.ID';
		return $group_by;
	}

	public static function fetch_fee_query_count( $school_id, $restrict_to_section = null, $session_id = null )
	{
		if ( $restrict_to_section ) {
			$section_where = ' AND cs.default_section_id = ' . absint( $restrict_to_section );
		} else {
			$section_where = '';
		}

		$session_where = '';
		if ( $session_id ) {
			$session_where = ' AND ft.session_id = ' . absint( $session_id );
		}

		$query = 'SELECT COUNT(DISTINCT ft.ID) FROM ' . WLSM_FEES . ' as ft
		LEFT JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.class_id = ft.class_id AND cs.school_id = ' . absint( $school_id ) . '
        LEFT JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = cs.default_section_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = ft.class_id
		WHERE ft.school_id = ' . absint( $school_id ) . $section_where . $session_where;
		return $query;
	}

	public static function get_fee( $school_id, $id ) {
		global $wpdb;
		$fee = $wpdb->get_row( $wpdb->prepare( 'SELECT ft.ID FROM ' . WLSM_FEES . ' as ft
		WHERE ft.school_id = %d AND ft.ID = %d', $school_id, $id ) );
		return $fee;
	}

	public static function fetch_fee( $school_id, $id ) {
		global $wpdb;
		$fee = $wpdb->get_row( $wpdb->prepare( 'SELECT ft.ID, ft.label, ft.amount, ft.period, ft.active_on_admission, ft.active_on_dashboard, ft.class_id, ft.student_type, ft.fee_type, ft.include_on_promotion, ft.session_id FROM ' . WLSM_FEES . ' as ft
		WHERE ft.school_id = %d AND ft.ID = %d', $school_id, $id ) );
		return $fee;
	}

	public static function fetch_fees( $school_id, $active_on_admission = true, $session_id = '' ) {
		global $wpdb;

		$where = '';
		if ( $active_on_admission ) {
			$where .= ' AND ft.active_on_admission = 1';
		}

		if ( ! empty( $session_id ) ) {
			$where .= ' AND ft.session_id = ' . absint( $session_id );
		}

		$fees = $wpdb->get_results( $wpdb->prepare('SELECT ft.ID, ft.label, ft.amount, ft.period, ft.active_on_dashboard, ft.active_on_admission, ft.class_id, ft.fee_type, ft.include_on_promotion FROM ' . WLSM_FEES . ' as ft
		WHERE ft.school_id = %d' . $where, $school_id ) );
		return $fees;
	}

	public static function fetch_fees_by_class( $school_id, $class_id, $active_on_admission = true, $session_id = '' ) {
		global $wpdb;

		$where = '';
		if ( $active_on_admission ) {
			$where .= ' AND ft.active_on_admission = 1';
		}

		if ( ! empty( $session_id ) ) {
			$where .= ' AND ft.session_id = ' . absint( $session_id );
		}

		$fees = $wpdb->get_results( $wpdb->prepare('SELECT ft.ID, ft.label, ft.amount, ft.period, ft.active_on_dashboard, ft.active_on_admission, ft.class_id, ft.fee_type, ft.include_on_promotion FROM ' . WLSM_FEES . ' as ft
		WHERE ft.school_id = %d AND ft.class_id = %d' . $where, $school_id, $class_id ) );
		return $fees;
	}

	public static function fetch_fees_by_class_student_type( $school_id, $class_id, $student_type = NULL, $session_id = '' ) {
		global $wpdb;

		$where = '';
		if ( ! empty( $session_id ) ) {
			$where .= ' AND ft.session_id = ' . absint( $session_id );
		}

		$fees = $wpdb->get_results( $wpdb->prepare('SELECT ft.ID, ft.label, ft.amount, ft.period, ft.active_on_dashboard, ft.active_on_admission, ft.class_id, ft.student_type, ft.fee_type, ft.include_on_promotion FROM ' . WLSM_FEES . ' as ft
		WHERE ft.school_id = %d AND ft.class_id = %d' . $where, $school_id, $class_id ) );

		if ($student_type !== NULL) {
			$filtered_fees = array_filter($fees, function($fee) use ($student_type) {
				$student_types = unserialize($fee->student_type);
				return is_array($student_types) && in_array($student_type, $student_types);
			});
			return array_values($filtered_fees);
		}

		return $fees;
	}

	public static function fetch_fees_paid_dashboard($school_id, $active_on_dashboard = true, $session_id = '')
	{
		global $wpdb;

		$where = '';
		if ($active_on_dashboard) {
			$where .= ' AND ft.active_on_dashboard = 1';
		}

		if (!empty($session_id)) {
			$where .= ' AND ft.session_id = ' . absint($session_id);
		}

		$fees = $wpdb->get_results($wpdb->prepare('SELECT ft.ID, ft.label, ft.amount, ft.period FROM ' . WLSM_FEES . ' as ft
		WHERE ft.school_id = %d' . $where, $school_id));
		return $fees;
	}

	public static function fetch_student_assigned_fees( $school_id, $student_id ) {
		global $wpdb;
		$fees = $wpdb->get_results( $wpdb->prepare( 'SELECT sft.ID, sft.label, sft.amount, sft.period, sft.fee_type, sft.fee_order, sft.student_record_id FROM ' . WLSM_STUDENT_FEES . ' as sft
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = sft.student_record_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		WHERE cs.school_id = %d AND sft.student_record_id = %d ORDER BY sft.fee_order ASC', $school_id, $student_id ) );
		return $fees;
	}

	public static function fetch_student_fees( $school_id, $student_id ) {
		global $wpdb;
		$fees = $wpdb->get_results( $wpdb->prepare( 'SELECT sft.ID, sft.fee_id, sft.label, sft.amount, sft.period, sft.fee_type, sft.fee_order, sft.student_record_id FROM ' . WLSM_STUDENT_FEES . ' as sft
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = sft.student_record_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		WHERE cs.school_id = %d AND sft.student_record_id = %d ORDER BY sft.fee_order ASC', $school_id, $student_id ) );
		return $fees;
	}
	public static function fetch_student_fees_invoices( $school_id, $student_id ) {
		global $wpdb;
		$fees = $wpdb->get_results( $wpdb->prepare( 'SELECT i.ID, i.label, i.amount, i.student_record_id FROM ' . WLSM_INVOICES . ' as i
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id

		WHERE  sr.ID = ' . absint( $student_id )));

		return $fees;
	}

	public static function get_invoice_title_text( $invoice_title ) {
		if ( $invoice_title ) {
			return $invoice_title;
		}
		return '-';
	}

	public static function get_label_text( $label ) {
		if ( $label ) {
			return stripcslashes( $label );
		}
		return '';
	}

	public static function get_category_label_text( $label ) {
		if ( $label ) {
			return stripcslashes( $label );
		}
		return '-';
	}

	public static function get_partial_payments_allowed_text( $invoice_partial_payment ) {
		if ( $invoice_partial_payment ) {
			return esc_html__( 'Yes', 'school-management' );
		}
		return esc_html__( 'No', 'school-management' );
	}

	public static function get_fee_period_text( $period ) {
		if ( isset( WLSM_Helper::fee_period_list()[ $period ] ) ) {
			return WLSM_Helper::fee_period_list()[ $period ];
		}
		return '-';
	}

	public static function fetch_concession_types_query( $school_id, $session_id = 0 ) {
		$query = 'SELECT ct.ID, ct.concession_name, ct.concession_type, ct.percentage_value, ct.fixed_amount, ct.eligibility_criteria, ct.is_active, ct.session_id, c.label as class_label, ss.label as session_label FROM ' . WLSM_CONCESSION_TYPES . ' as ct
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = ct.class_id
		LEFT JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = ct.session_id
		WHERE ct.school_id = ' . absint( $school_id );
		if ( $session_id ) {
			$query .= ' AND ct.session_id = ' . absint( $session_id );
		}
		return $query;
	}

	public static function fetch_concession_types_query_group_by() {
		$group_by = 'GROUP BY ct.ID';
		return $group_by;
	}

	public static function fetch_concession_types_query_count( $school_id, $session_id = 0 ) {
		$query = 'SELECT COUNT(DISTINCT ct.ID) FROM ' . WLSM_CONCESSION_TYPES . ' as ct
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = ct.class_id
		WHERE ct.school_id = ' . absint( $school_id );
		if ( $session_id ) {
			$query .= ' AND ct.session_id = ' . absint( $session_id );
		}
		return $query;
	}

	public static function fetch_concession_types( $school_id, $id ) {
		global $wpdb;
		$concession_types = $wpdb->get_row( $wpdb->prepare( 'SELECT ct.ID, ct.concession_name, ct.concession_type, ct.percentage_value, ct.fixed_amount, ct.eligibility_criteria, ct.is_active, ct.class_id, ct.session_id FROM ' . WLSM_CONCESSION_TYPES . ' as ct
		WHERE ct.school_id = %d AND ct.ID = %d', $school_id, $id ) );
		return $concession_types;
	}

	public static function get_concession_types( $school_id, $id ) {
		global $wpdb;
		$concession_types = $wpdb->get_row( $wpdb->prepare( 'SELECT ct.ID FROM ' . WLSM_CONCESSION_TYPES . ' as ct
		WHERE ct.school_id = %d AND ct.ID = %d', $school_id, $id ) );
		return $concession_types;
	}

	// Concession-Fee Mapping Methods
	public static function get_concession_fee_mappings( $concession_id ) {
		global $wpdb;
		$mappings = $wpdb->get_results( $wpdb->prepare(
			'SELECT cfm.fee_type_id, ft.label as fee_label, ft.amount, ft.period, ft.session_id FROM ' . WLSM_CONCESSION_FEE_MAPPINGS . ' as cfm
			JOIN ' . WLSM_FEES . ' as ft ON ft.ID = cfm.fee_type_id
			WHERE cfm.concession_type_id = %d ORDER BY ft.label ASC',
			$concession_id
		) );
		return $mappings;
	}

	public static function get_fee_concessions( $fee_id ) {
		global $wpdb;
		$concessions = $wpdb->get_results( $wpdb->prepare(
			'SELECT cfm.concession_type_id, ct.concession_name, ct.concession_type, ct.percentage_value, ct.fixed_amount FROM ' . WLSM_CONCESSION_FEE_MAPPINGS . ' as cfm
			JOIN ' . WLSM_CONCESSION_TYPES . ' as ct ON ct.ID = cfm.concession_type_id
			WHERE cfm.fee_type_id = %d AND ct.is_active = 1 ORDER BY ct.concession_name ASC',
			$fee_id
		) );
		return $concessions;
	}

	public static function save_concession_fee_mappings( $concession_id, $fee_type_ids ) {
		global $wpdb;

		// Delete existing mappings
		$wpdb->delete( WLSM_CONCESSION_FEE_MAPPINGS, array( 'concession_type_id' => $concession_id ) );

		// Insert new mappings
		if ( !empty( $fee_type_ids ) && is_array( $fee_type_ids ) ) {
			foreach ( $fee_type_ids as $fee_id ) {
				$fee_id = absint( $fee_id );
				if ( $fee_id > 0 ) {
					$wpdb->insert(
						WLSM_CONCESSION_FEE_MAPPINGS,
						array(
							'concession_type_id' => $concession_id,
							'fee_type_id' => $fee_id,
							'created_at' => current_time( 'Y-m-d H:i:s' )
						)
					);
				}
			}
		}
	}

	public static function delete_concession_fee_mappings( $concession_id ) {
		global $wpdb;
		return $wpdb->delete( WLSM_CONCESSION_FEE_MAPPINGS, array( 'concession_type_id' => $concession_id ) );
	}

	public static function calculate_concession_amount( $base_amount, $concession_type, $percentage_value, $fixed_amount ) {
		if ( $concession_type === 'percentage' && $percentage_value > 0 ) {
			return ( $base_amount * $percentage_value ) / 100;
		} else if ( $concession_type === 'fixed_amount' && $fixed_amount > 0 ) {
			return min( $fixed_amount, $base_amount ); // Don't exceed base amount
		}
		return 0;
	}

	public static function get_concession_mapped_fee_ids( $concession_type_id ) {
		global $wpdb;
		if ( empty( $concession_type_id ) ) {
			return array();
		}
		return $wpdb->get_col( $wpdb->prepare(
			"SELECT fee_type_id FROM " . WLSM_CONCESSION_FEE_MAPPINGS . "
			WHERE concession_type_id = %d",
			$concession_type_id
		) );
	}

	public static function get_concession_type_by_id( $concession_id ) {
		global $wpdb;

		if ( empty( $concession_id ) ) {
			return null;
		}

		$concession = $wpdb->get_row( $wpdb->prepare(
			'SELECT ct.ID, ct.concession_name, ct.concession_type, ct.percentage_value, ct.fixed_amount, ct.class_id
			FROM ' . WLSM_CONCESSION_TYPES . ' as ct
			WHERE ct.ID = %d AND ct.is_active = 1',
			$concession_id
		) );

		return $concession;
	}

	public static function get_concessions_by_class( $school_id, $class_id ) {
		global $wpdb;

		if ( empty( $class_id ) ) {
			return array();
		}

		$concessions = $wpdb->get_results( $wpdb->prepare(
			'SELECT ct.ID, ct.concession_name, ct.concession_type, ct.percentage_value, ct.fixed_amount
			FROM ' . WLSM_CONCESSION_TYPES . ' as ct
			WHERE ct.school_id = %d AND ct.class_id = %d AND ct.is_active = 1 AND ct.concession_type = "fixed_amount"
			ORDER BY ct.concession_name ASC',
			$school_id,
			$class_id
		) );

		return $concessions;
	}

	public static function fetch_students_concession_query( $school_id, $session_id ) {
		$query = 'SELECT sc.ID, sr.name as student_name, sr.admission_number,
		c.label as class_label, se.label as section_label, ct.concession_name,
		sc.status, sc.approved_by, sc.created_at
		FROM ' . WLSM_STUDENT_CONCESSION . ' as sc
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = sc.student_record_id
		JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sc.session_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		JOIN ' . WLSM_CONCESSION_TYPES . ' as ct ON ct.ID = sc.concession_type_id
		WHERE sc.school_id = ' . absint( $school_id ) . ' AND sc.session_id = ' . absint( $session_id );
		return $query;
	}

	public static function fetch_students_concession_query_group_by() {
		$group_by = 'GROUP BY sc.ID';
		return $group_by;
	}

	public static function fetch_students_concession_query_count( $school_id, $session_id ) {
		$query = 'SELECT COUNT(DISTINCT sc.ID) FROM ' . WLSM_STUDENT_CONCESSION . ' as sc
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = sc.student_record_id
		JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
		JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
		JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
		JOIN ' . WLSM_CONCESSION_TYPES . ' as ct ON ct.ID = sc.concession_type_id
		WHERE sc.school_id = ' . absint( $school_id ) . ' AND sc.session_id = ' . absint( $session_id );
		return $query;
	}

	public static function get_concession_value_text( $concession_type, $percentage_value, $fixed_amount ) {
		if ( $concession_type === 'percentage' && $percentage_value > 0 ) {
			return $percentage_value . '%';
		} else if ( $concession_type === 'fixed_amount' && $fixed_amount > 0 ) {
			return WLSM_Config::get_money_text( $fixed_amount );
		}
		return '-';
	}

	public static function get_concession_status_text( $status ) {
		$status_list = array(
			'pending'  => esc_html__( 'Pending', 'school-management' ),
			'approved' => esc_html__( 'Approved', 'school-management' ),
			'rejected' => esc_html__( 'Rejected', 'school-management' ),
			'expired'  => esc_html__( 'Expired', 'school-management' )
		);

		if ( isset( $status_list[ $status ] ) ) {
			return $status_list[ $status ];
		}
		return '-';
	}

	public static function get_student_concession( $school_id, $session_id, $id ) {
		global $wpdb;
		$student_concession = $wpdb->get_row( $wpdb->prepare(
			'SELECT sc.*, sr.name as student_name, sr.admission_number, c.label as class_label, se.label as section_label
			FROM ' . WLSM_STUDENT_CONCESSION . ' as sc
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = sc.student_record_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
			WHERE sc.school_id = %d AND sc.session_id = %d AND sc.ID = %d',
			$school_id, $session_id, $id
		) );
		return $student_concession;
	}

	public static function get_total_invoices_count( $school_id, $session_id ) {
		global $wpdb;
		// Total Invoices.
		$total_invoices_count = $wpdb->get_var(
			$wpdb->prepare('SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d', $school_id, $session_id)
		);
		return $total_invoices_count;
	}

	public static function get_invoices_paid_count( $school_id, $session_id ) {
		global $wpdb;
		$invoices_paid_count = $wpdb->get_var(
			$wpdb->prepare('SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d AND i.status = "%s"', $school_id, $session_id, WLSM_M_Invoice::get_paid_key())
		);
		return $invoices_paid_count;
	}

	public static function get_invoices_unpaid_count( $school_id, $session_id ) {
		global $wpdb;
		$invoices_unpaid_count = $wpdb->get_var(
			$wpdb->prepare('SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d AND i.status = "%s"', $school_id, $session_id, WLSM_M_Invoice::get_unpaid_key())
		);
		return $invoices_unpaid_count;
	}

	public static function get_invoices_partially_paid_count( $school_id, $session_id ) {
		global $wpdb;
		$invoices_partially_paid_count = $wpdb->get_var(
			$wpdb->prepare('SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND ss.ID = %d AND i.status = "%s"', $school_id, $session_id, WLSM_M_Invoice::get_partially_paid_key())
		);
		return $invoices_partially_paid_count;
	}

	public static function get_invoices_pending_amount( $school_id, $session_id ) {
		global $wpdb;
		$invoices_pending_amount = $wpdb->get_col(
			$wpdb->prepare( 'SELECT ((i.amount) - COALESCE(SUM(p.amount), 0)) as due FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
			LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
			WHERE cs.school_id = %d AND ss.ID = %d AND (i.status = "%s" OR i.status = "%s") GROUP BY i.ID', $school_id, $session_id, WLSM_M_Invoice::get_unpaid_key(), WLSM_M_Invoice::get_partially_paid_key() )
		);
		return $invoices_pending_amount;
	}

	public static function get_total_expenses_sum( $school_id, $session_start_date, $session_end_date ) {
		global $wpdb;
		$total_expenses_sum = $wpdb->get_var($wpdb->prepare('SELECT COALESCE(SUM(ep.amount), 0) as sum FROM ' . WLSM_EXPENSES . ' as ep WHERE ep.school_id = %d AND ep.expense_date BETWEEN %s AND %s', $school_id, $session_start_date, $session_end_date));
		return $total_expenses_sum;
	}

	public static function get_total_income_sum( $school_id, $session_start_date, $session_end_date ) {
		global $wpdb;
		$total_income_sum = $wpdb->get_var($wpdb->prepare('SELECT COALESCE(SUM(im.amount), 0) as sum FROM ' . WLSM_INCOME . ' as im WHERE im.school_id = %d AND im.income_date BETWEEN %s AND %s', $school_id, $session_start_date, $session_end_date));
		return $total_income_sum;
	}

	public static function get_total_dailypaymentstotal_sum( $school_id, $session_id, $date_now ) {
		global $wpdb;
		$total_dailypaymentstotal_sum = $wpdb->get_var($wpdb->prepare('SELECT COALESCE(SUM(im.amount), 0) as sum FROM ' . WLSM_PAYMENTS . ' as im
		JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = im.student_record_id
		WHERE im.school_id = %d AND sr.session_id=%d AND im.created_at BETWEEN %s AND %s', $school_id, $session_id, $date_now, $date_now));
		return $total_dailypaymentstotal_sum;
	}

	public static function get_total_dailyexpencestotal_sum( $school_id, $date_now ) {
		global $wpdb;
		$total_dailyexpencestotal_sum = $wpdb->get_var($wpdb->prepare('SELECT COALESCE(SUM(we.amount), 0) as sum FROM ' . WLSM_EXPENSES . ' as we WHERE we.school_id = %d AND we.expense_date = %s', $school_id, $date_now));
		return $total_dailyexpencestotal_sum;
	}

	public static function get_previous_session_invoices_pending_amount( $school_id, $previous_session_id ) {
		global $wpdb;
		$previous_session_invoices_pending_amount = $wpdb->get_col(
			$wpdb->prepare( 'SELECT ((i.amount) - COALESCE(SUM(p.amount), 0)) as due FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
			LEFT OUTER JOIN ' . WLSM_PAYMENTS . ' as p ON p.invoice_id = i.ID
			WHERE cs.school_id = %d AND ss.ID = %d AND (i.status = "%s" OR i.status = "%s") GROUP BY i.ID', $school_id, $previous_session_id, WLSM_M_Invoice::get_unpaid_key(), WLSM_M_Invoice::get_partially_paid_key() )
		);
		return $previous_session_invoices_pending_amount;
	}

	public static function get_transport_students_count( $school_id, $session_id ) {
		global $wpdb;
		$transport_students_count = $wpdb->get_var($wpdb->prepare(
			'SELECT COUNT(DISTINCT sr.ID) FROM ' . WLSM_STUDENT_RECORDS . ' as sr
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			WHERE cs.school_id = %d AND sr.session_id = %d AND sr.route_vehicle_id IS NOT NULL AND sr.route_vehicle_id > 0 AND sr.is_active = 1',
			$school_id, $session_id
		));
		return $transport_students_count;
	}

	public static function get_transport_invoices_total( $session_id, $school_id ) {
		global $wpdb;
		$transport_invoices_total = $wpdb->get_var($wpdb->prepare(
			'SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			WHERE i.invoice_type = %s AND sr.session_id = %d AND sr.section_id IN
				(SELECT se.ID FROM ' . WLSM_SECTIONS . ' as se JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id WHERE cs.school_id = %d)',
			'transport', $session_id, $school_id
		));
		return $transport_invoices_total;
	}

	public static function get_transport_invoices_paid( $session_id, $school_id ) {
		global $wpdb;
		$transport_invoices_paid = $wpdb->get_var($wpdb->prepare(
			'SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			WHERE i.invoice_type = %s AND i.status = %s AND sr.session_id = %d AND sr.section_id IN
				(SELECT se.ID FROM ' . WLSM_SECTIONS . ' as se JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id WHERE cs.school_id = %d)',
			'transport', WLSM_M_Invoice::get_paid_key(), $session_id, $school_id
		));
		return $transport_invoices_paid;
	}

	public static function get_transport_invoices_unpaid( $session_id, $school_id ) {
		global $wpdb;
		$transport_invoices_unpaid = $wpdb->get_var($wpdb->prepare(
			'SELECT COUNT(DISTINCT i.ID) FROM ' . WLSM_INVOICES . ' as i
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			WHERE i.invoice_type = %s AND i.status = %s AND sr.session_id = %d AND sr.section_id IN
				(SELECT se.ID FROM ' . WLSM_SECTIONS . ' as se JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id WHERE cs.school_id = %d)',
			'transport', WLSM_M_Invoice::get_unpaid_key(), $session_id, $school_id
		));
		return $transport_invoices_unpaid;
	}

	public static function get_transport_amount_collected( $session_id, $school_id ) {
		global $wpdb;
		$transport_amount_collected = $wpdb->get_var($wpdb->prepare(
			'SELECT COALESCE(SUM(p.amount), 0) FROM ' . WLSM_PAYMENTS . ' as p
			JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = i.student_record_id
			WHERE i.invoice_type = %s AND sr.session_id = %d AND sr.section_id IN
				(SELECT se.ID FROM ' . WLSM_SECTIONS . ' as se JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id WHERE cs.school_id = %d)',
			'transport', $session_id, $school_id
		));
		return $transport_amount_collected;
	}

	public static function fetch_payments( $session_id, $school_id, $now ) {
		global $wpdb;
		$payments = $wpdb->get_results(
			$wpdb->prepare('SELECT sr.name as student_name, sr.admission_number, sr.phone, sr.father_name, sr.father_phone, p.ID, p.receipt_number, p.amount, p.payment_method, p.transaction_id, p.created_at, p.invoice_label, p.invoice_payable, p.invoice_id, i.label as invoice_title, c.label as class_label, se.label as section_label FROM ' . WLSM_PAYMENTS . ' as p
			JOIN ' . WLSM_SCHOOLS . ' as s ON s.ID = p.school_id
			JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = p.student_record_id
			JOIN ' . WLSM_SESSIONS . ' as ss ON ss.ID = sr.session_id
			JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
			LEFT OUTER JOIN ' . WLSM_INVOICES . ' as i ON i.ID = p.invoice_id
			WHERE p.school_id = %d AND ss.ID = %d AND p.created_at = %s GROUP BY p.ID ORDER BY p.ID DESC', $school_id, $session_id, $now)
		);
		return $payments;
	}

	public static function fetch_route_vehicles( $school_id, $route_id ) {
		global $wpdb;
		$vehicles = $wpdb->get_results( $wpdb->prepare(
			"SELECT rv.ID as id, ro.fare, v.vehicle_number as vehicle_name
			FROM " . WLSM_ROUTE_VEHICLE . " as rv
			JOIN " . WLSM_ROUTES . " as ro ON ro.ID = rv.route_id
			JOIN " . WLSM_VEHICLES . " as v ON v.ID = rv.vehicle_id
			WHERE ro.ID = %d AND ro.school_id = %d",
			$route_id, $school_id
		) );
		return $vehicles;
	}

	public static function fetch_transport_invoices( $session_id ) {
		global $wpdb;
		$invoices = $wpdb->get_results( $wpdb->prepare(
			"SELECT ID, student_record_id, invoice_number, date_issued, transport_month, amount, status
			FROM " . WLSM_INVOICES . "
			WHERE invoice_type = 'transport' AND student_record_id IN (SELECT ID FROM " . WLSM_STUDENT_RECORDS . " WHERE session_id = %d)",
			$session_id
		) );
		return $invoices;
	}

	public static function fetch_discount_changes( $invoice_id ) {
		global $wpdb;
		$discount_changes = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT dc.*, u.user_nicename as staff_name FROM " . WLSM_INVOICE_DISCOUNT_CHANGES . " dc
				LEFT JOIN " . WLSM_STAFF . " s ON s.ID = dc.staff_id
				LEFT JOIN " . $wpdb->users . " u ON u.ID = s.user_id
				WHERE dc.invoice_id = %d ORDER BY dc.change_date DESC",
				$invoice_id
			)
		);
		return $discount_changes;
	}
}
