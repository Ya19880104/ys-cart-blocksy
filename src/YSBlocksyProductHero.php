<?php
/**
 * YS CART 商品詳情頁不輸出 Blocksy 的頁面標題。
 *
 * DEMO SITE #5(2)：商品名已經有一個 H1，Blocksy hero 再輸出頁面標題就是第二個 H1。
 * 這裡讓 hero 不輸出，不用 CSS 藏（DOM 裡不能留兩個 H1）。
 *
 * Blocksy 2.1.52（DEV 實裝）兩種 hero 都看同一個 filter，回 false 就都不輸出：
 * - type-2：template-parts/single.php（page.php 用 get_template_part( 'single' ) 進來）
 * - type-1：inc/components/single/content-helpers.php（且不是 WooCommerce product）
 * blocksy_output_hero_section() 會再比對頁面標題型態；呼叫端傳入的 type 不合就回空字串。
 * 所以要在 filter 一次關掉，不能只擋其中一種。
 *
 * 不改這個 WordPress 頁面自己的標題開關。商店列表、分類頁與商品詳情是同一個頁面，
 * 關掉頁面標題會把商店的 H1 一起拿掉。只在商品詳情路由回 false，其他請求把原值送回。
 *
 * @package YangSheep\CartBlocksy
 */

namespace YangSheep\CartBlocksy;

defined( 'ABSPATH' ) || exit;

final class YSBlocksyProductHero {

	public static function register(): void {
		if ( ! YSBlocksyDetector::ready() ) {
			return;
		}

		add_filter( 'blocksy:single:has-default-hero', [ self::class, 'filter_has_default_hero' ] );
	}

	/**
	 * @param mixed $enabled Blocksy 原本要不要輸出 hero。
	 * @return mixed 商品詳情頁固定 false；其他頁面原樣送回。
	 */
	public static function filter_has_default_hero( $enabled ) {
		if ( self::is_product_detail_request() ) {
			return false;
		}

		return $enabled;
	}

	private static function is_product_detail_request(): bool {
		$resolver = \YangSheep\Ecommerce\Services\Setup\YSPageResolver::class;
		if ( method_exists( $resolver, 'is_product_detail_request' ) ) {
			return (bool) \YangSheep\Ecommerce\Services\Setup\YSPageResolver::is_product_detail_request();
		}

		return self::product_detail_request_before_26910();
	}

	/**
	 * 核心 2.69.10 以後改用核心判斷。
	 *
	 * 2.69.9 以前沒有 YSPageResolver::is_product_detail_request()。判斷與該方法的
	 * current_product_slug() 同源（YSShopSeo 原本的內部判斷）：rewrite 的
	 * ys_ec_product_slug、?product=、路徑 shop/<slug>（排除 category 開頭）。
	 * query var 不是字串時視為不是商品頁。
	 */
	private static function product_detail_request_before_26910(): bool {
		$slug = get_query_var( 'ys_ec_product_slug', '' );
		if ( empty( $slug ) && isset( $_GET['product'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- 只讀路由，與核心 current_product_slug() 相同。
			$slug = sanitize_title( wp_unslash( (string) $_GET['product'] ) );
		}
		if ( empty( $slug ) ) {
			$raw_path = parse_url( (string) ( $_SERVER['REQUEST_URI'] ?? '' ), PHP_URL_PATH );
			$path     = trim( is_string( $raw_path ) ? $raw_path : '', '/' );
			if ( preg_match( '#^shop/([a-z0-9][a-z0-9_-]*)$#i', $path, $m ) ) {
				if ( ! str_starts_with( $m[1], 'category' ) ) {
					$slug = sanitize_title( $m[1] );
				}
			}
		}

		return is_string( $slug ) && ! empty( $slug );
	}
}
