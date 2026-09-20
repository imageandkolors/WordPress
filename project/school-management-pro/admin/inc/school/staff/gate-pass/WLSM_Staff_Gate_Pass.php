<?php
defined( 'ABSPATH' ) || die();

require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_Gate_Pass.php';
require_once WLSM_PLUGIN_DIR_PATH . 'includes/helpers/staff/WLSM_M_Staff_General.php';

class WLSM_Staff_Gate_Pass {

	public static function fetch_gate_passes() {
		$current_user = WLSM_M_Role::can( 'view_gate_passes' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		global $wpdb;

		$page_url = WLSM_M_Staff_Gate_Pass::get_gate_passes_page_url();

		$query = WLSM_M_Staff_Gate_Pass::fetch_gate_pass_query( $school_id, $session_id );

		$query_filter = $query;

		$group_by = ' ' . WLSM_M_Staff_Gate_Pass::fetch_gate_pass_query_group_by();

		$query        .= $group_by;
		$query_filter .= $group_by;

		// Searching.
		$condition = '';
		if ( isset( $_POST['search']['value'] ) ) {
			$search_value = sanitize_text_field( $_POST['search']['value'] );
			if ( '' !== $search_value ) {
				$condition =
					'(gp.visitor_name LIKE "%' . $search_value . '%") OR ' .
					'(gp.visitor_mobile LIKE "%' . $search_value . '%") OR ' .
					'(gp.visitor_relation LIKE "%' . $search_value . '%") OR ' .
					'(gp.authorized_by LIKE "%' . $search_value . '%") OR ' .
					'(sr.name LIKE "%' . $search_value . '%") OR ' .
					'(c.label LIKE "%' . $search_value . '%") OR ' .
					'(se.label LIKE "%' . $search_value . '%")';

				$query_filter .= ( ' HAVING ' . $condition );
			}
		}

		// Ordering.
		$columns = array( 'gp.visitor_name', 'gp.visitor_mobile', 'gp.visitor_relation', 'sr.name', 'c.label', 'se.label', 'gp.visit_date', 'gp.in_time', 'gp.out_time', 'gp.authorized_by' );
		if ( isset( $_POST['order'] ) && isset( $columns[ $_POST['order']['0']['column'] ] ) ) {
			$order_by  = sanitize_text_field( $columns[ $_POST['order']['0']['column'] ] );
			$order_dir = sanitize_text_field( $_POST['order']['0']['dir'] );
			$query_filter .= ' ORDER BY ' . $order_by . ' ' . $order_dir;
		} else {
			$query_filter .= ' ORDER BY gp.ID DESC';
		}

		// Limiting.
		$limit = '';
		if ( -1 != $_POST['length'] ) {
			$start  = absint( $_POST['start'] );
			$length = absint( $_POST['length'] );
			$limit  = ' LIMIT ' . $start . ', ' . $length;
		}

		// Total query.
		$rows_query = WLSM_M_Staff_Gate_Pass::fetch_gate_pass_query_count( $school_id, $session_id );

		$total_rows_count = $wpdb->get_var( $rows_query );

		if ( $condition ) {
			$filter_rows_count = $wpdb->get_var( $rows_query . ' AND (' . $condition . ')' );
		} else {
			$filter_rows_count = $total_rows_count;
		}

		$filter_rows_limit = $wpdb->get_results( $query_filter . $limit );

		$data = array();

		if ( count( $filter_rows_limit ) ) {
			foreach ( $filter_rows_limit as $row ) {

				$edit_button = WLSM_M_Role::can( 'edit_gate_passes' ) ?
					'<a class="text-primary" href="' . esc_url( $page_url . '&action=save&id=' . $row->ID ) . '"><span class="dashicons dashicons-edit"></span></a>&nbsp;&nbsp;' :
					'<span class="text-muted"><span class="dashicons dashicons-edit"></span></span>&nbsp;&nbsp;';

				$delete_button = WLSM_M_Role::can( 'delete_gate_passes' ) ?
					'<a class="text-danger wlsm-delete-gate-pass" data-nonce="' . esc_attr( wp_create_nonce( 'delete-gate-pass-' . $row->ID ) ) . '" data-gate-pass="' . esc_attr( $row->ID ) . '" href="#" data-message-title="' . esc_attr__( 'Please Confirm!', 'school-management' ) . '" data-message-content="' . esc_attr__( 'This will delete the gate pass record.', 'school-management' ) . '" data-cancel="' . esc_attr__( 'Cancel', 'school-management' ) . '" data-submit="' . esc_attr__( 'Confirm', 'school-management' ) . '"><span class="dashicons dashicons-trash"></span></a>' :
					'<span class="text-muted"><span class="dashicons dashicons-trash"></span></span>';

				$print_button = WLSM_M_Role::can( 'view_gate_passes' ) ?
					'<a class="text-secondary wlsm-print-gate-pass" data-nonce="' . esc_attr( wp_create_nonce( 'print-gate-pass-' . $row->ID ) ) . '" data-gate-pass="' . esc_attr( $row->ID ) . '" data-message-title="' . esc_attr__( 'Gate Pass', 'school-management' ) . '" href="#"><span class="dashicons dashicons-printer"></span></a>&nbsp;&nbsp;' :
					'<span class="text-muted"><span class="dashicons dashicons-printer"></span></span>&nbsp;&nbsp;';

				$data[] = array(
					esc_html( WLSM_M_Staff_Gate_Pass::get_visitor_name_text( $row->visitor_name ) ),
					esc_html( $row->visitor_mobile ? $row->visitor_mobile : '-' ),
					esc_html( WLSM_M_Staff_Gate_Pass::get_relation_text( $row->visitor_relation ) ),
					esc_html( $row->student_name ? WLSM_M_Staff_Class::get_name_text( $row->student_name ) : '-' ),
					esc_html( $row->class_label ? WLSM_M_Class::get_label_text( $row->class_label ) : '-' ),
					esc_html( $row->section_label ? WLSM_M_Staff_Class::get_section_label_text( $row->section_label ) : '-' ),
					esc_html( WLSM_Config::get_date_text( $row->visit_date ) ),
					esc_html( WLSM_M_Staff_Gate_Pass::get_time_text( $row->in_time ) ),
					esc_html( WLSM_M_Staff_Gate_Pass::get_time_text( $row->out_time ) ),
					esc_html( $row->authorized_by ? $row->authorized_by : '-' ),
					$print_button . $edit_button . $delete_button,
				);
			}
		}

		$output = array(
			'draw'            => intval( $_POST['draw'] ),
			'recordsTotal'    => $total_rows_count,
			'recordsFiltered' => $filter_rows_count,
			'data'            => $data,
		);

		echo json_encode( $output );
		die();
	}

	public static function save_gate_pass() {
		$gate_pass_id = isset( $_POST['gate_pass_id'] ) ? absint( $_POST['gate_pass_id'] ) : 0;

		if ( $gate_pass_id ) {
			$current_user = WLSM_M_Role::can( 'edit_gate_passes' );
		} else {
			$current_user = WLSM_M_Role::can( 'add_gate_passes' );
		}

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			if ( $gate_pass_id ) {
				if ( ! wp_verify_nonce( $_POST[ 'edit-gate-pass-' . $gate_pass_id ], 'edit-gate-pass-' . $gate_pass_id ) ) {
					die();
				}
			} else {
				if ( ! wp_verify_nonce( $_POST['add-gate-pass'], 'add-gate-pass' ) ) {
					die();
				}
			}

			// Verify exists on edit.
			if ( $gate_pass_id ) {
				$gate_pass = WLSM_M_Staff_Gate_Pass::get_gate_pass( $school_id, $session_id, $gate_pass_id );
				if ( ! $gate_pass ) {
					throw new Exception( esc_html__( 'Gate pass record not found.', 'school-management' ) );
				}
			}

			$visitor_name     = isset( $_POST['visitor_name'] ) ? sanitize_text_field( $_POST['visitor_name'] ) : '';
			$visitor_mobile   = isset( $_POST['visitor_mobile'] ) ? sanitize_text_field( $_POST['visitor_mobile'] ) : '';
			$visitor_relation = isset( $_POST['visitor_relation'] ) ? sanitize_text_field( $_POST['visitor_relation'] ) : '';
			$visitor_photo_id = isset( $_POST['visitor_photo_id'] ) ? absint( $_POST['visitor_photo_id'] ) : 0;
			$reason_to_meet   = isset( $_POST['reason_to_meet'] ) ? sanitize_textarea_field( $_POST['reason_to_meet'] ) : '';
			$student_id       = isset( $_POST['student'] ) ? absint( $_POST['student'] ) : 0;
			$authorized_by    = isset( $_POST['authorized_by'] ) ? sanitize_text_field( $_POST['authorized_by'] ) : '';
			$visit_date       = isset( $_POST['visit_date'] ) ? DateTime::createFromFormat( WLSM_Config::date_format(), sanitize_text_field( $_POST['visit_date'] ) ) : null;
			$in_time          = isset( $_POST['in_time'] ) ? sanitize_text_field( $_POST['in_time'] ) : '';
			$out_time         = isset( $_POST['out_time'] ) ? sanitize_text_field( $_POST['out_time'] ) : '';

			// Validate.
			$errors = array();

			if ( empty( $visitor_name ) ) {
				$errors['visitor_name'] = esc_html__( 'Please enter visitor name.', 'school-management' );
			}
			if ( strlen( $visitor_name ) > 100 ) {
				$errors['visitor_name'] = esc_html__( 'Maximum length cannot exceed 100 characters.', 'school-management' );
			}
			if ( strlen( $authorized_by ) > 100 ) {
				$errors['authorized_by'] = esc_html__( 'Maximum length cannot exceed 100 characters.', 'school-management' );
			}
			if ( empty( $authorized_by ) ) {
				$errors['authorized_by'] = esc_html__( 'Please enter authorized by.', 'school-management' );
			}
			if ( empty( $visit_date ) ) {
				$errors['visit_date'] = esc_html__( 'Please provide visit date.', 'school-management' );
			} else {
				$visit_date = $visit_date->format( 'Y-m-d' );
			}

			// Validate student if provided.
			$student = null;
			if ( $student_id ) {
				$student = WLSM_M_Staff_General::get_student( $school_id, $session_id, $student_id, true, true );
				if ( ! $student ) {
					throw new Exception( esc_html__( 'Student not found.', 'school-management' ) );
				}
			}

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			wp_send_json_error( ! empty( $buffer ) ? $buffer : $exception->getMessage() );
		}

		if ( count( $errors ) < 1 ) {
			try {
				$wpdb->query( 'BEGIN;' );

				if ( $gate_pass_id ) {
					$message = esc_html__( 'Gate pass updated successfully.', 'school-management' );
					$reset   = false;
				} else {
					$message = esc_html__( 'Gate pass added successfully.', 'school-management' );
					$reset   = true;
				}

				$data = array(
					'visitor_name'      => $visitor_name,
					'visitor_mobile'    => $visitor_mobile,
					'visitor_relation'  => $visitor_relation,
					'visitor_photo_id'  => $visitor_photo_id ? $visitor_photo_id : null,
					'reason_to_meet'    => $reason_to_meet,
					'student_record_id' => $student_id ? $student_id : null,
					'authorized_by'     => $authorized_by,
					'visit_date'        => $visit_date,
					'in_time'           => $in_time ? $in_time : null,
					'out_time'          => $out_time ? $out_time : null,
				);

				if ( $gate_pass_id ) {
					$data['updated_at'] = current_time( 'Y-m-d H:i:s' );
					$success = $wpdb->update( WLSM_GATE_PASSES, $data, array( 'ID' => $gate_pass_id, 'school_id' => $school_id, 'session_id' => $session_id ) );
				} else {
					$data['school_id']  = $school_id;
					$data['session_id'] = $session_id;
					$data['created_at'] = current_time( 'Y-m-d H:i:s' );
					$success = $wpdb->insert( WLSM_GATE_PASSES, $data );
				}

				$buffer = ob_get_clean();
				if ( ! empty( $buffer ) ) {
					throw new Exception( $buffer );
				}
				if ( false === $success ) {
					throw new Exception( $wpdb->last_error );
				}

				$new_id = $gate_pass_id ? $gate_pass_id : $wpdb->insert_id;

				$wpdb->query( 'COMMIT;' );

				wp_send_json_success( array(
					'message'      => $message,
					'reset'        => $reset,
					'gate_pass_id' => $new_id,
					'print_nonce'  => wp_create_nonce( 'print-gate-pass-' . $new_id ),
				) );
			} catch ( Exception $exception ) {
				$wpdb->query( 'ROLLBACK;' );
				wp_send_json_error( $exception->getMessage() );
			}
		}
		wp_send_json_error( $errors );
	}

	public static function delete_gate_pass() {
		$current_user = WLSM_M_Role::can( 'delete_gate_passes' );

		if ( ! $current_user ) {
			die();
		}
		WLSM_Helper::check_demo();

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$gate_pass_id = isset( $_POST['gate_pass_id'] ) ? absint( $_POST['gate_pass_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'delete-gate-pass-' . $gate_pass_id ], 'delete-gate-pass-' . $gate_pass_id ) ) {
				die();
			}

			$gate_pass = WLSM_M_Staff_Gate_Pass::get_gate_pass( $school_id, $session_id, $gate_pass_id );
			if ( ! $gate_pass ) {
				throw new Exception( esc_html__( 'Gate pass record not found.', 'school-management' ) );
			}

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			wp_send_json_error( ! empty( $buffer ) ? $buffer : $exception->getMessage() );
		}

		try {
			$wpdb->query( 'BEGIN;' );

			$success = $wpdb->delete( WLSM_GATE_PASSES, array( 'ID' => $gate_pass_id ) );
			$message = esc_html__( 'Gate pass deleted successfully.', 'school-management' );

			$exception = ob_get_clean();
			if ( ! empty( $exception ) ) {
				throw new Exception( $exception );
			}
			if ( false === $success ) {
				throw new Exception( $wpdb->last_error );
			}

			$wpdb->query( 'COMMIT;' );
			wp_send_json_success( array( 'message' => $message ) );
		} catch ( Exception $exception ) {
			$wpdb->query( 'ROLLBACK;' );
			wp_send_json_error( $exception->getMessage() );
		}
	}

	public static function print_gate_pass() {
		$current_user = WLSM_M_Role::can( 'view_gate_passes' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		try {
			ob_start();
			global $wpdb;

			$gate_pass_id = isset( $_POST['gate_pass_id'] ) ? absint( $_POST['gate_pass_id'] ) : 0;

			if ( ! wp_verify_nonce( $_POST[ 'print-gate-pass-' . $gate_pass_id ], 'print-gate-pass-' . $gate_pass_id ) ) {
				die();
			}

			$gate_pass = WLSM_M_Staff_Gate_Pass::get_gate_pass( $school_id, $session_id, $gate_pass_id );

			if ( ! $gate_pass ) {
				throw new Exception( esc_html__( 'Gate pass not found.', 'school-management' ) );
			}

			// Load visitor photo URL.
			$visitor_photo_url = '';
			if ( ! empty( $gate_pass->visitor_photo_id ) ) {
				$visitor_photo_url = wp_get_attachment_image_url( absint( $gate_pass->visitor_photo_id ), 'thumbnail' );
			}

			// Load student info if linked.
			$student = null;
			if ( $gate_pass->student_record_id ) {
				$student = $wpdb->get_row( $wpdb->prepare(
					'SELECT sr.name, sr.photo_id as photo, sr.admission_number, c.label as class_label, se.label as section_label
					FROM ' . WLSM_STUDENT_RECORDS . ' as sr
					LEFT JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id
					LEFT JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id
					LEFT JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id
					WHERE sr.ID = %d',
					$gate_pass->student_record_id
				) );
			}

		} catch ( Exception $exception ) {
			$buffer = ob_get_clean();
			wp_send_json_error( ! empty( $buffer ) ? $buffer : $exception->getMessage() );
		}

		ob_start();
		require_once WLSM_PLUGIN_DIR_PATH . 'admin/inc/school/print/gate-pass.php';
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}

	public static function view_gate_pass_student() {
		$current_user = WLSM_M_Role::can( 'add_gate_passes' );

		if ( ! $current_user ) {
			die();
		}

		$school_id  = $current_user['school']['id'];
		$session_id = $current_user['session']['ID'];

		if ( ! wp_verify_nonce( $_POST['view-gate-pass-student'], 'view-gate-pass-student' ) ) {
			die();
		}

		$student_id = isset( $_POST['student_id'] ) ? absint( $_POST['student_id'] ) : 0;

		if ( ! $student_id ) {
			wp_send_json_error( esc_html__( 'Please select a student.', 'school-management' ) );
		}

		global $wpdb;
		$student = $wpdb->get_row( $wpdb->prepare( 'SELECT sr.ID, sr.name, sr.photo_id, sr.enrollment_number, sr.admission_number, sr.phone, sr.father_name, sr.mother_name, c.label as class_label, se.label as section_label FROM ' . WLSM_STUDENT_RECORDS . ' as sr JOIN ' . WLSM_SECTIONS . ' as se ON se.ID = sr.section_id JOIN ' . WLSM_CLASS_SCHOOL . ' as cs ON cs.ID = se.class_school_id JOIN ' . WLSM_CLASSES . ' as c ON c.ID = cs.class_id WHERE cs.school_id = %d AND sr.session_id = %d AND sr.ID = %d', $school_id, $session_id, $student_id ) );

		if ( ! $student ) {
			wp_send_json_error( esc_html__( 'Student not found.', 'school-management' ) );
		}

		wp_send_json_success( array(
			'name'              => stripcslashes( $student->name ),
			'photo_url'         => $student->photo_id ? esc_url( wp_get_attachment_image_url( $student->photo_id, 'thumbnail' ) ) : '',
			'class_label'       => stripcslashes( $student->class_label ),
			'section_label'     => stripcslashes( $student->section_label ),
			'admission_number'  => stripcslashes( $student->admission_number ),
			'phone'             => stripcslashes( $student->phone ),
			'father_name'       => stripcslashes( $student->father_name ),
			'mother_name'       => stripcslashes( $student->mother_name ),
		) );
	}

	public static function upload_visitor_photo() {
		$current_user = WLSM_M_Role::can( 'add_gate_passes' );

		if ( ! $current_user ) {
			$user_id = get_current_user_id();
			$user_info = WLSM_M_Role::get_user_info();
			$debug_msg = 'You do not have permission to upload photos. Debug: UID=' . $user_id . ', Info=' . json_encode($user_info);
			wp_send_json_error( $debug_msg );
		}

		if ( ! isset( $_FILES['visitor_photo_file'] ) ) {
			wp_send_json_error( esc_html__( 'No file provided.', 'school-management' ) );
		}

		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );

		$attachment_id = media_handle_upload( 'visitor_photo_file', 0 );

		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( $attachment_id->get_error_message() );
		}

		wp_send_json_success( array(
			'id'  => $attachment_id,
			'url' => wp_get_attachment_image_url( $attachment_id, 'thumbnail' )
		) );
	}
}
