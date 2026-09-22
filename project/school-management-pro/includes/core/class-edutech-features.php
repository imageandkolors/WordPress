<?php

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'Edutech_Features' ) ) {
	/**
	 * Feature flags and pilot-release controls for Edutech modules.
	 */
	final class Edutech_Features {
		const OPTION = 'edutech_feature_flags';
		const PLATFORM_SCOPE = 'platform';

		/** @var array<string, bool> */
		private static $defaults = array(
			'portal'          => false,
			'branding'        => true,
			'cbt'             => false,
			'mobile_api'      => false,
			'country_packs'   => false,
			'elementor'       => false,
			'gutenberg'       => false,
			'frontend_modules'=> false,
		);

		/**
		 * Return the supported feature keys and default values.
		 *
		 * @return array<string, bool>
		 */
		public static function defaults() {
			return self::$defaults;
		}

		/**
		 * Determine whether a feature is enabled for platform or school scope.
		 *
		 * @param string   $feature  Feature key.
		 * @param int|null $school_id School ID, or null for platform scope.
		 * @return bool
		 */
		public static function enabled( $feature, $school_id = null ) {
			$feature = sanitize_key( $feature );
			if ( ! array_key_exists( $feature, self::$defaults ) ) {
				return false;
			}

			$flags   = self::all();
			$value   = self::$defaults[ $feature ];
			$scope   = self::scope_key( $school_id );
			if ( isset( $flags[ $scope ][ $feature ] ) ) {
				$value = (bool) $flags[ $scope ][ $feature ];
			} elseif ( self::PLATFORM_SCOPE !== $scope && isset( $flags[ self::PLATFORM_SCOPE ][ $feature ] ) ) {
				$value = (bool) $flags[ self::PLATFORM_SCOPE ][ $feature ];
			}

			/**
			 * Allow a release controller to apply a temporary pilot rule.
			 *
			 * @param bool     $value    Current flag value.
			 * @param string   $feature  Feature key.
			 * @param int|null $school_id School ID.
			 */
			return (bool) apply_filters( 'edutech_feature_enabled', $value, $feature, $school_id );
		}

		/**
		 * Enable or disable a feature at platform or school scope.
		 *
		 * @param string   $feature  Feature key.
		 * @param bool     $enabled  Desired state.
		 * @param int|null $school_id School ID, or null for platform scope.
		 * @return true|WP_Error
		 */
		public static function set( $feature, $enabled, $school_id = null ) {
			$feature = sanitize_key( $feature );
			if ( ! array_key_exists( $feature, self::$defaults ) ) {
				return new WP_Error( 'edutech_feature_unknown', __( 'Unknown Edutech feature.', 'school-management' ) );
			}
			if ( null !== $school_id && absint( $school_id ) < 1 ) {
				return new WP_Error( 'edutech_feature_scope', __( 'A valid school is required for a school-scoped feature.', 'school-management' ) );
			}

			$flags = self::all();
			$scope = self::scope_key( $school_id );
			if ( ! isset( $flags[ $scope ] ) || ! is_array( $flags[ $scope ] ) ) {
				$flags[ $scope ] = array();
			}
			$flags[ $scope ][ $feature ] = (bool) $enabled;
			update_option( self::OPTION, $flags, false );
			do_action( 'edutech_feature_changed', $feature, (bool) $enabled, $school_id );
			return true;
		}

		/**
		 * Return normalized flags in scope-keyed format.
		 *
		 * @return array<string, array<string, bool>>
		 */
		public static function all() {
			$stored = get_option( self::OPTION, array() );
			if ( ! is_array( $stored ) ) {
				$stored = array();
			}

			// Migrate the original Slice 1.1 flat option shape in memory.
			if ( self::is_flat_flags( $stored ) ) {
				$stored = array( self::PLATFORM_SCOPE => $stored );
			}

			$flags = array( self::PLATFORM_SCOPE => array() );
			foreach ( self::$defaults as $feature => $default ) {
				$flags[ self::PLATFORM_SCOPE ][ $feature ] = $default;
			}
			foreach ( $stored as $scope => $scope_flags ) {
				if ( ! is_array( $scope_flags ) ) {
					continue;
				}
				$scope = self::normalize_scope( $scope );
				if ( ! isset( $flags[ $scope ] ) ) {
					$flags[ $scope ] = array();
				}
				foreach ( self::$defaults as $feature => $default ) {
					if ( array_key_exists( $feature, $scope_flags ) ) {
						$flags[ $scope ][ $feature ] = (bool) $scope_flags[ $feature ];
					}
				}
			}
			return $flags;
		}

		/**
		 * Return a safe release-control report.
		 *
		 * @param int|null $school_id School ID.
		 * @return array<string, bool>
		 */
		public static function release_report( $school_id = null ) {
			$report = array();
			foreach ( self::$defaults as $feature => $default ) {
				$report[ $feature ] = self::enabled( $feature, $school_id );
			}
			return $report;
		}

		/** @param int|null $school_id School ID. */
		private static function scope_key( $school_id ) {
			return null === $school_id ? self::PLATFORM_SCOPE : 'school_' . absint( $school_id );
		}

		/** @param string $scope Scope key. */
		private static function normalize_scope( $scope ) {
			return self::PLATFORM_SCOPE === $scope || 0 === strpos( $scope, 'school_' ) ? sanitize_key( $scope ) : self::PLATFORM_SCOPE;
		}

		/** @param array<string, mixed> $stored Stored values. */
		private static function is_flat_flags( $stored ) {
			foreach ( $stored as $key => $value ) {
				if ( array_key_exists( $key, self::$defaults ) ) {
					return true;
				}
			}
			return false;
		}
	}
}
