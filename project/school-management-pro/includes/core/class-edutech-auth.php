<?php

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'Edutech_Auth' ) ) {
	/**
	 * Frontend authentication controls around WordPress core authentication APIs.
	 */
	final class Edutech_Auth {
		const MAX_ATTEMPTS = 5;
		const WINDOW        = 900;
		const TRANSIENT    = 'edutech_login_attempt_';

		/**
		 * Register authentication filters and actions.
		 *
		 * @return void
		 */
		public static function boot() {
			add_filter( 'authenticate', array( __CLASS__, 'throttle' ), 30, 3 );
			add_action( 'wp_login_failed', array( __CLASS__, 'record_failure' ), 10, 1 );
			add_action( 'wp_login', array( __CLASS__, 'clear_attempts' ), 10, 2 );
		}

		/**
		 * Reject requests that exceed the login-attempt threshold.
		 *
		 * @param WP_User|WP_Error|null $user Existing authentication result.
		 * @param string                $username Submitted username.
		 * @param string                $password Submitted password.
		 * @return WP_User|WP_Error|null
		 */
		public static function throttle( $user, $username, $password ) {
			if ( '' === trim( (string) $username ) || '' === (string) $password ) {
				return $user;
			}
			if ( self::attempts( $username ) < self::MAX_ATTEMPTS ) {
				return $user;
			}

			return new WP_Error(
				'edutech_login_throttled',
				__( 'We could not sign you in at this time. Please wait and try again.', 'school-management' )
			);
		}

		/**
		 * Record a failed login using a hashed username/IP key.
		 *
		 * @param string $username Submitted username.
		 * @return void
		 */
		public static function record_failure( $username ) {
			$key      = self::attempt_key( $username );
			$attempts = (int) get_transient( $key );
			set_transient( $key, $attempts + 1, self::WINDOW );
		}

		/**
		 * Clear the attempt counter after a successful login.
		 *
		 * @param string  $user_login User login.
		 * @param WP_User $user       Authenticated user.
		 * @return void
		 */
		public static function clear_attempts( $user_login, $user ) {
			delete_transient( self::attempt_key( $user_login ) );
		}

		/**
		 * Validate a redirect as same-site and return a safe fallback otherwise.
		 *
		 * @param string $requested Requested URL.
		 * @param string $fallback Fallback URL.
		 * @return string
		 */
		public static function safe_redirect( $requested, $fallback = '' ) {
			$fallback = $fallback ? $fallback : home_url( '/' );
			$validated = wp_validate_redirect( (string) $requested, '' );
			if ( ! $validated ) {
				return $fallback;
			}

			$home   = wp_parse_url( home_url( '/' ) );
			$target = wp_parse_url( $validated );
			if ( ! empty( $target['host'] ) && ! empty( $home['host'] ) && strtolower( $target['host'] ) !== strtolower( $home['host'] ) ) {
				return $fallback;
			}
			return $validated;
		}

		/**
		 * Add a safe expiry marker to a frontend URL.
		 *
		 * @param string $url Destination URL.
		 * @return string
		 */
		public static function expiry_url( $url ) {
			return add_query_arg( 'edutech_session', 'expired', self::safe_redirect( $url ) );
		}

		/**
		 * Render a non-sensitive frontend authentication notice.
		 *
		 * @return void
		 */
		public static function render_notice() {
			if ( isset( $_GET['edutech_session'] ) && 'expired' === sanitize_key( wp_unslash( $_GET['edutech_session'] ) ) ) {
				echo '<div class="edutech-alert edutech-alert--warning" role="status">';
				esc_html_e( 'Your session has expired. Please sign in again.', 'school-management' );
				echo '</div>';
			}
		}

		/**
		 * Return arguments for a branded frontend WordPress login form.
		 *
		 * @param string $redirect Requested redirect.
		 * @return array<string, mixed>
		 */
		public static function login_form_args( $redirect ) {
			$redirect = self::safe_redirect( $redirect );
			return array(
				'redirect'       => $redirect,
				'form_id'        => 'edutech-login-form',
				'id_username'    => 'edutech-login-username',
				'id_password'    => 'edutech-login-password',
				'id_remember'    => 'edutech-login-remember',
				'id_submit'      => 'edutech-login-submit',
				'label_username' => __( 'Username or email', 'school-management' ),
				'label_password' => __( 'Password', 'school-management' ),
				'label_remember' => __( 'Remember me', 'school-management' ),
				'label_log_in'   => __( 'Sign in to Edutech', 'school-management' ),
				'value_username' => '',
			);
		}

		/** @param string $username Username. */
		private static function attempts( $username ) {
			return (int) get_transient( self::attempt_key( $username ) );
		}

		/** @param string $username Username. */
		private static function attempt_key( $username ) {
			$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';
			return self::TRANSIENT . md5( strtolower( trim( (string) $username ) ) . '|' . $ip );
		}
	}
}
