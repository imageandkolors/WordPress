<?php

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'Edutech_Migrations' ) ) {
	/**
	 * Versioned, retryable migration coordinator for the Edutech foundation.
	 */
	final class Edutech_Migrations {
		const OPTION_VERSION = 'edutech_db_version';
		const OPTION_STATE   = 'edutech_migration_state';
		const TARGET_VERSION  = '1.0.0';

		/**
		 * Run pending migrations. Each migration must be safe to retry.
		 *
		 * @return bool
		 */
		public static function run() {
			$current = (string) get_option( self::OPTION_VERSION, '0.0.0' );
			if ( version_compare( $current, self::TARGET_VERSION, '>=' ) ) {
				return true;
			}

			$state = get_option(
				self::OPTION_STATE,
				array(
					'status'  => 'pending',
					'current' => $current,
					'target'  => self::TARGET_VERSION,
					'error'   => '',
				)
			);
			$state['status']  = 'running';
			$state['current'] = $current;
			$state['target']  = self::TARGET_VERSION;
			$state['started'] = current_time( 'mysql', true );
			$state['error']   = '';
			update_option( self::OPTION_STATE, $state, false );

			try {
				self::migrate_to_1_0_0();
				update_option( self::OPTION_VERSION, self::TARGET_VERSION, false );
				$state['status']     = 'completed';
				$state['completed']  = current_time( 'mysql', true );
				$state['error']      = '';
				update_option( self::OPTION_STATE, $state, false );
				return true;
			} catch ( Throwable $exception ) {
				$state['status'] = 'failed';
				$state['failed'] = current_time( 'mysql', true );
				$state['error']  = sanitize_text_field( $exception->getMessage() );
				update_option( self::OPTION_STATE, $state, false );
				do_action( 'edutech_migration_failed', $exception, $state );
				return false;
			}
		}

		/**
		 * Foundation migration. This only establishes state/options; legacy tables
		 * remain owned by WLSM_Database until a dedicated schema slice is approved.
		 *
		 * @return void
		 */
		private static function migrate_to_1_0_0() {
			if ( false === get_option( 'edutech_module_registry', false ) ) {
				add_option( 'edutech_module_registry', array(), '', false );
			}
			if ( false === get_option( 'edutech_feature_flags', false ) ) {
				add_option(
					'edutech_feature_flags',
					array(
						'portal' => false,
						'branding' => true,
						'cbt' => false,
						'mobile_api' => false,
					),
					'',
					false
				);
			}
		}

		/**
		 * Return migration health information for admin tools and diagnostics.
		 *
		 * @return array<string, mixed>
		 */
		public static function health() {
			return array(
				'current' => (string) get_option( self::OPTION_VERSION, '0.0.0' ),
				'target'  => self::TARGET_VERSION,
				'state'   => get_option( self::OPTION_STATE, array( 'status' => 'pending' ) ),
			);
		}
	}
}
