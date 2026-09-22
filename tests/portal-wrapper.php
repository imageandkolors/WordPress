<?php

declare(strict_types=1);

define( 'ABSPATH', __DIR__ . '/' );

function sanitize_key( $value ) {
	return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) );
}
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function shortcode_atts( $defaults, $attributes ) { return array_merge( $defaults, is_array( $attributes ) ? $attributes : array() ); }
function esc_attr( $value ) { return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' ); }
function __( $text ) { return $text; }

require dirname( __DIR__ ) . '/project/school-management-pro/includes/core/class-edutech-portal.php';

if ( 'embedded' !== Edutech_Portal::mode( 'invalid' ) ) {
	fwrite( STDERR, "Invalid mode did not fall back to embedded.\n" );
	exit( 1 );
}
$wrapped = Edutech_Portal::wrap( '<p>Legacy content</p>', array( 'mode' => 'full-width', 'label' => 'Student Portal' ) );
if ( false === strpos( $wrapped, 'edutech-portal-frame--full-width' ) || false === strpos( $wrapped, 'data-edutech-mode="full-width"' ) || false === strpos( $wrapped, 'Student Portal' ) ) {
	fwrite( STDERR, "Full-width wrapper contract failed.\n" );
	exit( 1 );
}
$standalone = Edutech_Portal::wrap( 'content', array( 'mode' => 'standalone', 'label' => '"unsafe"' ) );
if ( false === strpos( $standalone, 'edutech-portal-frame--standalone' ) || false === strpos( $standalone, '&quot;unsafe&quot;' ) ) {
	fwrite( STDERR, "Standalone wrapper escaping failed.\n" );
	exit( 1 );
}

echo "Portal wrapper compatibility test passed.\n";
