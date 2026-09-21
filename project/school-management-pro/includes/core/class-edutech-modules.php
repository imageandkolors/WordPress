<?php

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'Edutech_Modules' ) ) {
	/**
	 * Registry for core and optional Edutech modules.
	 */
	final class Edutech_Modules {
		const OPTION = 'edutech_module_registry';

		/** @var array<string, array<string, mixed>> */
		private static $definitions = array();
		private static $booted       = false;

		/**
		 * Boot the registry and allow modules to register themselves.
		 *
		 * @return void
		 */
		public static function boot() {
			if ( self::$booted ) {
				return;
			}
			self::$booted = true;
			self::register(
				'core-platform',
				array(
					'name'         => 'Edutech Core Platform',
					'version'      => defined( 'EDUTECH_VERSION' ) ? EDUTECH_VERSION : '1.0.0',
					'dependencies' => array(),
					'core'         => true,
				)
			);
			do_action( 'edutech_register_modules', __CLASS__ );
		}

		/**
		 * Register a module definition.
		 *
		 * @param string               $key  Stable module key.
		 * @param array<string, mixed> $data Module metadata.
		 * @return bool
		 */
		public static function register( $key, $data ) {
			$key = sanitize_key( $key );
			if ( empty( $key ) || ! is_array( $data ) ) {
				return false;
			}

			self::$definitions[ $key ] = wp_parse_args(
				$data,
				array(
					'name'         => $key,
					'version'      => '1.0.0',
					'dependencies' => array(),
					'core'         => false,
					'health_check' => null,
				)
			);
			return true;
		}

		/**
		 * Return registered modules with persisted status.
		 *
		 * @return array<string, array<string, mixed>>
		 */
		public static function all() {
			self::boot();
			$persisted = get_option( self::OPTION, array() );
			$modules   = array();
			foreach ( self::$definitions as $key => $definition ) {
				$status = isset( $persisted[ $key ]['status'] ) ? $persisted[ $key ]['status'] : ( ! empty( $definition['core'] ) ? 'active' : 'inactive' );
				$modules[ $key ] = array_merge(
					$definition,
					array(
						'key'    => $key,
						'status' => in_array( $status, array( 'active', 'inactive', 'failed' ), true ) ? $status : 'inactive',
					)
				);
			}
			return $modules;
		}

		/**
		 * Activate a module after dependency checks.
		 *
		 * @param string $key Module key.
		 * @return true|WP_Error
		 */
		public static function activate( $key ) {
			$key     = sanitize_key( $key );
			$modules = self::all();
			if ( empty( $modules[ $key ] ) ) {
				return new WP_Error( 'edutech_module_not_found', __( 'Module not found.', 'school-management' ) );
			}
			foreach ( (array) $modules[ $key ]['dependencies'] as $dependency ) {
				if ( empty( $modules[ $dependency ] ) || 'active' !== $modules[ $dependency ]['status'] ) {
					return new WP_Error( 'edutech_dependency_missing', sprintf( __( 'Module dependency is not active: %s.', 'school-management' ), $dependency ) );
				}
			}
			$persisted = get_option( self::OPTION, array() );
			$persisted[ $key ] = array( 'status' => 'active', 'updated' => current_time( 'mysql', true ) );
			update_option( self::OPTION, $persisted, false );
			do_action( 'edutech_module_activated', $key, $modules[ $key ] );
			return true;
		}

		/**
		 * Deactivate a module without deleting its data.
		 *
		 * @param string $key Module key.
		 * @return true|WP_Error
		 */
		public static function deactivate( $key ) {
			$key     = sanitize_key( $key );
			$modules = self::all();
			if ( empty( $modules[ $key ] ) ) {
				return new WP_Error( 'edutech_module_not_found', __( 'Module not found.', 'school-management' ) );
			}
			if ( ! empty( $modules[ $key ]['core'] ) ) {
				return new WP_Error( 'edutech_core_module', __( 'The Edutech core module cannot be deactivated.', 'school-management' ) );
			}
			foreach ( $modules as $dependent_key => $module ) {
				if ( 'active' === $module['status'] && in_array( $key, (array) $module['dependencies'], true ) ) {
					return new WP_Error( 'edutech_module_dependency', sprintf( __( 'Deactivate dependent module first: %s.', 'school-management' ), $dependent_key ) );
				}
			}
			$persisted = get_option( self::OPTION, array() );
			$persisted[ $key ] = array( 'status' => 'inactive', 'updated' => current_time( 'mysql', true ) );
			update_option( self::OPTION, $persisted, false );
			do_action( 'edutech_module_deactivated', $key, $modules[ $key ] );
			return true;
		}

		/**
		 * Return module health data.
		 *
		 * @return array<string, array<string, mixed>>
		 */
		public static function health() {
			$health = array();
			foreach ( self::all() as $key => $module ) {
				$healthy = true;
				if ( is_callable( $module['health_check'] ) ) {
					$healthy = (bool) call_user_func( $module['health_check'], $module );
				}
				$health[ $key ] = array(
					'status'  => $module['status'],
					'healthy' => $healthy,
					'version' => $module['version'],
				);
			}
			return $health;
		}
	}
}
