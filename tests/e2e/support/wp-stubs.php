<?php
/**
 * e2e runner 渲染核心 `templates/cart/mini-cart.php` 所需的最小 WP 函式／核心類 stub。
 * 只給 tests/run-e2e.php 用；不進發布套件。
 */

declare(strict_types=1);

namespace {
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', __DIR__ . '/' );
	}
	if ( ! function_exists( 'esc_attr' ) ) {
		function esc_attr( $text ): string { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
	}
	if ( ! function_exists( 'esc_html' ) ) {
		function esc_html( $text ): string { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
	}
	if ( ! function_exists( 'esc_url' ) ) {
		function esc_url( $url ): string { return htmlspecialchars( (string) $url, ENT_QUOTES, 'UTF-8' ); }
	}
	if ( ! function_exists( 'wp_create_nonce' ) ) {
		function wp_create_nonce( $action = -1 ): string { return 'e2e-nonce'; }
	}
	if ( ! function_exists( 'remove_query_arg' ) ) {
		function remove_query_arg( $key, $url = '' ): string {
			unset( $key );
			return is_string( $url ) ? $url : '';
		}
	}
}

namespace YangSheep\Ecommerce\Services\Storefront {
	if ( ! class_exists( YSCartService::class, false ) ) {
		/** 分區導覽：e2e 快照沒有分區政策，回空字串，不載入核心類別。 */
		final class YSCartService {
			public static function render_partition_navigation( array $cart, string $surface, string $base_url, string $scope = 'default' ): string {
				unset( $cart, $surface, $base_url, $scope );
				return '';
			}
		}
	}
}

namespace YangSheep\Ecommerce\Handlers {
	if ( ! class_exists( YSDiscountEngine::class ) ) {
		/** 促銷提示：回一筆未達標提示，讓樣板的提示區塊（含 inline style 進度條）也渲染出來。 */
		final class YSDiscountEngine {
			public static function get_best_promotion_hint( float $subtotal, int $qty, string $surface = '' ): ?array {
				return [ 'message' => '再買 $220 即享免運', 'percent' => 85, 'met' => false ];
			}
		}
	}
}
