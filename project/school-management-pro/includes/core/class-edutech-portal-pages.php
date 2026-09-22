<?php

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'Edutech_Portal_Pages' ) ) {
	/**
	 * Metadata and safe regeneration helpers for portal system pages.
	 */
	final class Edutech_Portal_Pages {
		const OPTION = 'edutech_portal_pages';

		/**
		 * Return the supported system-page definitions.
		 *
		 * @return array<string, array<string, string>>
		 */
		public static function definitions() {
			$definitions = array(
				'login' => array(
					'slug'    => 'edutech-login',
					'title'   => __( 'Edutech Login', 'school-management' ),
					'content' => '[school_management_account mode="embedded"]',
				),
				'portal' => array(
					'slug'    => 'edutech-portal',
					'title'   => __( 'Edutech Portal', 'school-management' ),
					'content' => '[school_management_account mode="full-width"]',
				),
				'practice-cbt' => array(
					'slug'    => 'edutech-practice-cbt',
					'title'   => __( 'Edutech Practice CBT', 'school-management' ),
					'content' => '',
				),
				'official-cbt' => array(
					'slug'    => 'edutech-official-cbt',
					'title'   => __( 'Edutech Official CBT', 'school-management' ),
					'content' => '',
				),
			);

			/**
			 * Allow future modules to add page definitions without editing core.
			 *
			 * @param array<string, array<string, string>> $definitions Definitions.
			 */
			return apply_filters( 'edutech_portal_page_definitions', $definitions );
		}

		/**
		 * Return the stored page ID if it still points to a page.
		 *
		 * @param string $key Definition key.
		 * @return int
		 */
		public static function get_page_id( $key ) {
			$key   = sanitize_key( $key );
			$pages = get_option( self::OPTION, array() );
			$id    = isset( $pages[ $key ] ) ? absint( $pages[ $key ] ) : 0;
			return $id && 'page' === get_post_type( $id ) ? $id : 0;
		}

		/**
		 * Ensure one page exists without replacing an existing custom page.
		 *
		 * @param string $key Definition key.
		 * @return int|WP_Error
		 */
		public static function ensure( $key ) {
			$key         = sanitize_key( $key );
			$definitions = self::definitions();
			if ( empty( $definitions[ $key ] ) ) {
				return new WP_Error( 'edutech_page_not_found', __( 'Portal page definition not found.', 'school-management' ) );
			}
			$existing_id = self::get_page_id( $key );
			if ( $existing_id ) {
				return $existing_id;
			}

			$definition = $definitions[ $key ];
			$existing   = get_page_by_path( $definition['slug'], OBJECT, 'page' );
			$page_id    = $existing ? absint( $existing->ID ) : wp_insert_post(
				array(
					'post_type'    => 'page',
					'post_status'  => 'publish',
					'post_title'   => $definition['title'],
					'post_name'    => $definition['slug'],
					'post_content' => $definition['content'],
				),
				true
			);
			if ( is_wp_error( $page_id ) ) {
				return $page_id;
			}

			$pages         = get_option( self::OPTION, array() );
			$pages[ $key ] = absint( $page_id );
			update_option( self::OPTION, $pages, false );
			do_action( 'edutech_portal_page_registered', $key, absint( $page_id ), $definition );
			return absint( $page_id );
		}

		/**
		 * Ensure all defined pages, preserving existing custom content.
		 *
		 * @return array<string, int|WP_Error>
		 */
		public static function ensure_all() {
			$pages = array();
			foreach ( self::definitions() as $key => $definition ) {
				$pages[ $key ] = self::ensure( $key );
			}
			return $pages;
		}
	}
}
