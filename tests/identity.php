<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

class TestUser {
	public $ID;
	public $user_login;
	public $roles;
	public function __construct( $id, $login, $roles ) { $this->ID = $id; $this->user_login = $login; $this->roles = $roles; }
}
class WLSM_M_Role {
	public static function get_user_info( $user_id ) {
		if ( 9 === $user_id ) { return array( 'schools_assigned' => array( array( 'id' => 3 ) ), 'current_school' => array( 'id' => 3, 'school_id' => 3, 'role' => 'admin' ) ); }
		if ( 10 === $user_id ) { return array( 'schools_assigned' => array( array( 'id' => 3 ) ), 'current_school' => array( 'id' => 3, 'school_id' => 3, 'role' => 'employee' ) ); }
		return array();
	}
}
class WLSM_M {
	public static function get_student( $user_id ) { return 7 === $user_id ? (object) array( 'ID' => 77, 'school_id' => 3 ) : false; }
}
class WLSM_M_Parent {
	public static function get_parent_student_ids( $user_id ) { return 8 === $user_id ? array( 77, 78 ) : array(); }
}
function absint( $value ) { return abs( (int) $value ); }
function get_current_user_id() { return 0; }
function get_userdata( $user_id ) {
	$users = array(
		7  => new TestUser( 7, 'student', array( 'subscriber' ) ),
		8  => new TestUser( 8, 'parent', array( 'subscriber' ) ),
		9  => new TestUser( 9, 'admin', array( 'administrator' ) ),
		10 => new TestUser( 10, 'staff', array( 'employee' ) ),
		11 => new TestUser( 11, 'teacher', array( 'teacher' ) ),
		12 => new TestUser( 12, 'exam', array( 'exam_officer' ) ),
		13 => new TestUser( 13, 'designer', array( 'designer' ) ),
		14 => new TestUser( 14, 'unknown', array( 'subscriber' ) ),
	);
	return $users[ $user_id ] ?? false;
}
function get_user_meta( $user_id, $key, $single = false ) { return 2026 === $key ? 0 : 42; }
function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
function sanitize_user( $value ) { return (string) $value; }
function __( $text ) { return $text; }

require dirname( __DIR__ ) . '/project/school-management-pro/includes/core/class-edutech-identity.php';

$expected = array( 7 => 'student', 8 => 'parent', 9 => 'school_admin', 10 => 'staff', 11 => 'teacher', 12 => 'exam_officer', 13 => 'designer', 14 => 'unknown' );
foreach ( $expected as $user_id => $role ) {
	$context = Edutech_Identity::current( $user_id );
	if ( $role !== $context['role'] ) {
		fwrite( STDERR, "Role mapping failed for {$user_id}: expected {$role}, got {$context['role']}.\n" );
		exit( 1 );
	}
}
$frontend = Edutech_Identity::frontend( 7 );
if ( 'student' !== $frontend['role'] || 77 !== $frontend['studentId'] || 3 !== $frontend['schoolId'] ) {
	fwrite( STDERR, "Frontend identity contract failed.\n" );
	exit( 1 );
}

echo "Identity context compatibility test passed.\n";
