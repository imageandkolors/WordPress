<?php

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'Edutech_Environment' ) ) {
	/**
	 * Runtime compatibility and health checks for the Edutech foundation.
	 */
	final class Edutech_Environment {
		const MIN_PHP = '8.0.0';
		const MIN_WP  = '5.8';

		/**
		 * Return the current compatibility report.
		 *
		 * @return array<string, mixed>
		 */
		public static function report() {
			global $wpdb;

			$missing_extensions = array();
			foreach ( array( 'ctype', 'curl', 'json', 'mbstring', 'openssl' ) as $extension ) {
				if ( ! extension_loaded( $extension ) ) {
					$missing_extensions[] = $extension;
				}
			}

			$checks = array(
				'php'         => version_compare( PHP_VERSION, self::MIN_PHP, '>=' ),
				'wordpress'   => isset( $GLOBALS['wp_version'] ) ? version_compare( $GLOBALS['wp_version'], self::MIN_WP, '>=' ) : true,
				'database'    => is_object( $wpdb ) && ! empty( $wpdb->dbh ),
				'extensions'  => empty( $missing_extensions ),
				'filesystem'  => function_exists( 'wp_upload_dir' ),
			);

			return array(
				'ok'                 => ! in_array( false, $checks, true ),
				'checks'             => $checks,
				'missing_extensions' => $missing_extensions,
				'php_version'        => PHP_VERSION,
				'min_php'            => self::MIN_PHP,
				'min_wordpress'      => self::MIN_WP,
			);
		}

		/**
		 * Determine whether the plugin can safely boot.
		 *
		 * @return bool
		 */
		public static function is_compatible() {
			$report = self::report();
			return ! empty( $report['ok'] );
		}

		/**
		 * Render an actionable admin notice for an incompatible runtime.
		 *
		 * @return void
		 */
		public static function render_admin_notice() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$report = self::report();
			$issues = array();
			if ( empty( $report['checks']['php'] ) ) {
				$issues[] = sprintf(
					esc_html__( 'PHP %1$s or newer is required. Current version: %2$s.', 'school-management' ),
					self::MIN_PHP,
					PHP_VERSION
				);
			}
			if ( empty( $report['checks']['wordpress'] ) ) {
				$issues[] = sprintf(
					esc_html__( 'WordPress %1$s or newer is required.', 'school-management' ),
					self::MIN_WP
				);
			}
			if ( ! empty( $report['missing_extensions'] ) ) {
				$issues[] = sprintf(
					esc_html__( 'Missing PHP extensions: %s.', 'school-management' ),
					implode( ', ', array_map( 'sanitize_key', $report['missing_extensions'] ) )
				);
			}

			if ( empty( $issues ) ) {
				$issues[] = esc_html__( 'The database or WordPress runtime is not ready.', 'school-management' );
			}

			echo '<div class="notice notice-error"><p><strong>';
			echo esc_html__( 'Edutech cannot start on this server.', 'school-management' );
			echo '</strong> ' . esc_html( implode( ' ', $issues ) ) . '</p></div>';
		}
	}
}
