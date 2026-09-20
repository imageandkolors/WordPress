<?php
defined( 'ABSPATH' ) || die();

class WLSM_M_Staff_Gate_Pass {

	public static function get_gate_passes_page_url() {
		return admin_url( 'admin.php?page=' . WLSM_MENU_STAFF_GATE_PASSES );
	}

	public static function fetch_gate_pass_query( $school_id, $session_id ) {
		$query = 'SELECT gp.ID, gp.visitor_name, gp.visitor_mobile, gp.visitor_relation, gp.reason_to_meet,
			gp.authorized_by, gp.visit_date, gp.in_time, gp.out_time,
			sr.name as student_name, sr.photo_id as student_photo,
			c.label as class_label, se.label as section_label
			FROM ' . WLSM_GATE_PASSES . ' as gp
			LEFT JOIN ' . WLSM_STUDENT_RECORDS . ' as sr ON sr.ID = gp.student_record_id
			LEFT JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
			LEFT JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
			LEFT JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
			WHERE gp.school_id = ' . absint( $school_id ) . ' AND gp.session_id = ' . absint( $session_id );
		return $query;
	}

	public static function fetch_gate_pass_query_group_by() {
		return 'GROUP BY gp.ID';
	}

	public static function fetch_gate_pass_query_count( $school_id, $session_id ) {
		$query = 'SELECT COUNT(DISTINCT gp.ID) FROM ' . WLSM_GATE_PASSES . ' as gp
			WHERE gp.school_id = ' . absint( $school_id ) . ' AND gp.session_id = ' . absint( $session_id );
		return $query;
	}

	public static function get_gate_pass( $school_id, $session_id, $id ) {
		global $wpdb;
		$gate_pass = $wpdb->get_row( $wpdb->prepare(
			'SELECT gp.ID, gp.visitor_name, gp.visitor_mobile, gp.visitor_relation, gp.reason_to_meet,
			gp.authorized_by, gp.visit_date, gp.in_time, gp.out_time, gp.student_record_id, gp.visitor_photo_id
			FROM ' . WLSM_GATE_PASSES . ' as gp
			WHERE gp.school_id = %d AND gp.session_id = %d AND gp.ID = %d',
			$school_id, $session_id, $id
		) );
		return $gate_pass;
	}

	public static function get_visitor_name_text( $name ) {
		if ( $name ) {
			return stripcslashes( $name );
		}
		return '';
	}

	public static function get_relation_text( $relation ) {
		if ( $relation ) {
			return stripcslashes( $relation );
		}
		return '-';
	}

	public static function get_time_text( $time ) {
		if ( $time ) {
			return date( 'h:i A', strtotime( $time ) );
		}
		return '-';
	}
}
