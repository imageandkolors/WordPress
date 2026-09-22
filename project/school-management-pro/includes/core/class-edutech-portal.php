<?php

defined( 'ABSPATH' ) || die();

if ( ! class_exists( 'Edutech_Portal' ) ) {
	/**
	 * Theme-independent portal presentation wrapper.
	 */
	final class Edutech_Portal {
		const DEFAULT_MODE = 'embedded';

		/**
		 * Normalize a shortcode display mode.
		 *
		 * @param mixed $mode Requested mode.
		 * @return string
		 */
		public static function mode( $mode ) {
			$mode = sanitize_key( (string) $mode );
			return in_array( $mode, array( 'embedded', 'full-width', 'standalone' ), true ) ? $mode : self::DEFAULT_MODE;
		}

		/**
		 * Wrap legacy account output without changing its internal markup.
		 *
		 * @param string               $content Rendered legacy content.
		 * @param array<string, mixed> $attributes Shortcode attributes.
		 * @return string
		 */
		public static function wrap( $content, $attributes = array() ) {
			$attributes = shortcode_atts(
				array(
					'mode'  => self::DEFAULT_MODE,
					'label' => __( 'Edutech portal', 'school-management' ),
				),
				is_array( $attributes ) ? $attributes : array(),
				'school_management_account'
			);
			$mode  = self::mode( $attributes['mode'] );
			$label = sanitize_text_field( $attributes['label'] );
			if ( '' === $label ) {
				$label = __( 'Edutech portal', 'school-management' );
			}

			$classes = array( 'edutech-portal-frame', 'edutech-portal-frame--' . $mode );
			return sprintf(
				'<section class="%1$s" data-edutech-mode="%2$s" aria-label="%3$s"><div class="edutech-portal-frame__content">%4$s</div></section>',
				esc_attr( implode( ' ', $classes ) ),
				esc_attr( $mode ),
				esc_attr( $label ),
			$content
			);
		}
	}
}
