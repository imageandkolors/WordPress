<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$options = array(
	'edutech_feature_flags' => array(
		'portal'   => false,
		'branding' => true,
	),
);

class WP_Error {
	/** @var string */
	public $code;
	public function __construct( $code ) {
		$this->code = $code;
	}
}

function sanitize_key( $value ) {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) );
}
function absint( $value ) {
	return abs( (int) $value );
}
function get_option( $key, $default = false ) {
	global $options;
	return array_key_exists( $key, $options ) ? $options[ $key ] : $default;
}
function update_option( $key, $value ) {
	global $options;
	$options[ $key ] = $value;
	return true;
}
function apply_filters( $tag, $value ) {
	return $value;
}
function do_action() {}
function current_time() { return '2026-09-22 00:00:00'; }
function __( $text ) { return $text; }

require dirname( __DIR__ ) . '/project/school-management-pro/includes/core/class-edutech-features.php';

if ( Edutech_Features::enabled( 'portal' ) ) {
	fwrite( STDERR, "Legacy portal default should be disabled.\n" );
	exit( 1 );
}
if ( ! Edutech_Features::enabled( 'branding' ) ) {
	fwrite( STDERR, "Legacy branding default should be enabled.\n" );
	exit( 1 );
}
if ( true !== Edutech_Features::set( 'cbt', true, 17 ) ) {
	fwrite( STDERR, "School feature update failed.\n" );
	exit( 1 );
}
if ( ! Edutech_Features::enabled( 'cbt', 17 ) || Edutech_Features::enabled( 'cbt', 18 ) ) {
	fwrite( STDERR, "School feature scope was not isolated.\n" );
	exit( 1 );
}
if ( ! is_a( Edutech_Features::set( 'unknown-feature', true ), 'WP_Error' ) ) {
	fwrite( STDERR, "Unknown feature validation failed.\n" );
	exit( 1 );
}

echo "Feature flag compatibility test passed.\n";
