<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

$transients = array();

class WP_Error {
	/** @var string */
	public $code;
	public function __construct( $code ) { $this->code = $code; }
}
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
function wp_unslash( $value ) { return $value; }
function home_url( $path = '/' ) { return 'https://edutech.test' . ( '/' === $path ? '/' : '/' . ltrim( $path, '/' ) ); }
function wp_parse_url( $url ) { return parse_url( $url ); }
function wp_validate_redirect( $location, $fallback ) { return filter_var( $location, FILTER_VALIDATE_URL ) ? $location : $fallback; }
function add_query_arg( $key, $value, $url ) { return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . rawurlencode( $key ) . '=' . rawurlencode( $value ); }
function __( $text ) { return $text; }
function esc_html_e( $text ) { echo $text; }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function get_transient( $key ) { global $transients; return $transients[ $key ] ?? false; }
function set_transient( $key, $value, $expiration ) { global $transients; $transients[ $key ] = $value; return true; }
function delete_transient( $key ) { global $transients; unset( $transients[ $key ] ); return true; }
function add_filter() {}
function add_action() {}

require dirname( __DIR__ ) . '/project/school-management-pro/includes/core/class-edutech-auth.php';

if ( 'https://edutech.test/dashboard' !== Edutech_Auth::safe_redirect( 'https://edutech.test/dashboard', home_url( '/' ) ) ) {
	fwrite( STDERR, "Same-site redirect was rejected.\n" );
	exit( 1 );
}
if ( home_url( '/' ) !== Edutech_Auth::safe_redirect( 'https://attacker.test/phishing', home_url( '/' ) ) ) {
	fwrite( STDERR, "External redirect was accepted.\n" );
	exit( 1 );
}
$args = Edutech_Auth::login_form_args( 'https://edutech.test/portal' );
if ( 'edutech-login-form' !== $args['form_id'] || 'edutech-login-submit' !== $args['id_submit'] || 'https://edutech.test/portal' !== $args['redirect'] ) {
	fwrite( STDERR, "Branded login form contract failed.\n" );
	exit( 1 );
}
for ( $i = 0; $i < 5; $i++ ) {
	Edutech_Auth::record_failure( 'student@example.test' );
}
if ( ! is_a( Edutech_Auth::throttle( null, 'student@example.test', 'password' ), 'WP_Error' ) ) {
	fwrite( STDERR, "Login throttle did not engage.\n" );
	exit( 1 );
}
Edutech_Auth::clear_attempts( 'student@example.test', null );
if ( is_a( Edutech_Auth::throttle( null, 'student@example.test', 'password' ), 'WP_Error' ) ) {
	fwrite( STDERR, "Successful-login reset did not clear throttle.\n" );
	exit( 1 );
}

echo "Authentication compatibility test passed.\n";
