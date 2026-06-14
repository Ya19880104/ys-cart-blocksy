<?php
/**
 * 外掛 bootstrap。
 *
 * 職責僅三件事：
 *   1. 把 `header-items/` 目錄註冊給 Blocksy 的 `blocksy:header:items-paths`
 *      filter（官方第三方元件機制；Blocksy Companion 也用同一機制）。
 *   2. 前台載入少量對齊 CSS（僅 Blocksy 主題時）。
 *   3. 缺相依（Blocksy / YS CART）時在外掛頁顯示提示（fail-soft，不 fatal）。
 *
 * 刻意不做的事：無後台頁、無 REST namespace、無 admin-ajax、無資料表 ——
 * 所有元件設定都在「外觀 → 自訂 → 頁首」走 Blocksy 原生選項系統。
 *
 * @package YangSheep\CartBlocksy
 */

namespace YangSheep\CartBlocksy;

defined( 'ABSPATH' ) || exit;

final class YSCartBlocksyPlugin {

	private static bool $booted = false;

	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}
		self::$booted = true;

		// 元件目錄註冊：無條件掛上（filter 只在 Blocksy builder 執行時才會被
		// 呼叫，主題不是 Blocksy 時等於 no-op），避免載入順序問題。
		add_filter( 'blocksy:header:items-paths', [ self::class, 'register_item_paths' ] );

		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_front_assets' ] );
		add_action( 'admin_notices', [ self::class, 'maybe_render_dependency_notice' ] );
		add_action( 'init', [ self::class, 'load_textdomain' ] );
	}

	/**
	 * @param array<int, string> $paths Blocksy 掃描元件的目錄清單。
	 * @return array<int, string>
	 */
	public static function register_item_paths( $paths ): array {
		$paths   = is_array( $paths ) ? $paths : [];
		$paths[] = YS_CART_BLOCKSY_PATH . 'header-items';

		// v1.1.0：進階搜尋元件 — 只在 ys-cart-smart-search 啟用時註冊
		//（未啟用＝建構器完全不顯示這兩個元件）。
		if ( defined( 'YS_SMART_SEARCH_VERSION' ) ) {
			$paths[] = YS_CART_BLOCKSY_PATH . 'header-items-smart';
		}

		return $paths;
	}

	public static function enqueue_front_assets(): void {
		if ( ! YSBlocksyDetector::is_blocksy() ) {
			return;
		}

		wp_enqueue_style(
			'ys-cart-blocksy',
			YS_CART_BLOCKSY_URL . 'assets/css/ys-cart-blocksy.css',
			[],
			YS_CART_BLOCKSY_VERSION
		);
	}

	/**
	 * 缺相依提示：只在外掛列表頁顯示一次性 warning，不全後台洗版。
	 */
	public static function maybe_render_dependency_notice(): void {
		if ( ! current_user_can( 'manage_options' ) || YSBlocksyDetector::ready() ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'plugins' !== $screen->id ) {
			return;
		}

		$missing = [];
		if ( ! YSBlocksyDetector::is_blocksy() ) {
			$missing[] = __( 'Blocksy 佈景主題（免費版即可）', 'ys-cart-blocksy' );
		}
		if ( ! YSBlocksyDetector::has_ys_cart() ) {
			$missing[] = __( 'YS CART 外掛', 'ys-cart-blocksy' );
		}

		echo '<div class="notice notice-warning"><p>';
		printf(
			/* translators: %s = 缺少的相依清單 */
			esc_html__( 'YS CART Blocksy 整合：尚未偵測到 %s，頁首元件暫不會生效。', 'ys-cart-blocksy' ),
			esc_html( implode( '、', $missing ) )
		);
		echo '</p></div>';
	}

	public static function load_textdomain(): void {
		load_plugin_textdomain(
			'ys-cart-blocksy',
			false,
			dirname( plugin_basename( YS_CART_BLOCKSY_FILE ) ) . '/languages'
		);
	}
}
