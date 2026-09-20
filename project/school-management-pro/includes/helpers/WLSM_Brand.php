<?php
/**
 * Edutech v1.0 public brand service.
 *
 * Legacy WLSM identifiers remain intentionally unchanged during the migration
 * so existing options, integrations, shortcodes, routes, and database tables
 * continue to work.
 *
 * @package Edutech
 */

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'WLSM_Brand' ) ) {
	final class WLSM_Brand {
		/**
		 * Return the public Edutech brand configuration.
		 *
		 * @return array<string, string>
		 */
		public static function get() {
			$brand = array(
				'name'        => 'Edutech v1.0',
				'short_name'  => 'Edutech',
				'version'     => '1.0.0',
				'slug'        => 'edutech',
				'text_domain' => 'school-management',
			);

			/**
			 * Filter the public brand without changing compatibility identifiers.
			 *
			 * @param array<string, string> $brand Brand configuration.
			 */
			return apply_filters( 'edutech_brand', $brand );
		}

		/**
		 * Get one public brand value.
		 *
		 * @param string $key Brand key.
		 * @param string     $default Fallback value.
		 * @return string
		 */
		public static function value( $key, $default = '' ) {
			$brand = self::get();
			return isset( $brand[ $key ] ) ? (string) $brand[ $key ] : $default;
		}
	}
}
