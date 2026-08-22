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
 * v1.2.0：購物車元件可在頁首直接開啟**核心的**迷你購物車（drawer）。核心右下角浮動購物車
 * 關閉時，本外掛在 wp_footer 以核心同一份樣板輸出迷你購物車（同一套 ID／JS／REST），
 * 只換成右側抽屜的樣式——功能與核心浮動購物車完全相同，不複製任何購物車邏輯。
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
		// 20：在核心 YSMiniCart（wp_footer 預設 10）之後，才知道核心有沒有自己輸出迷你購物車。
		add_action( 'wp_footer', [ self::class, 'maybe_render_mini_cart_drawer' ], 20 );
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

		// v1.2.1：排在核心購物車樣式之後（抽屜與浮動面板的 override 都以核心為基底）。
		wp_enqueue_style(
			'ys-cart-blocksy',
			YS_CART_BLOCKSY_URL . 'assets/css/ys-cart-blocksy.css',
			wp_style_is( 'ys-ec-cart', 'registered' ) ? [ 'ys-ec-cart' ] : [],
			YS_CART_BLOCKSY_VERSION
		);

		// v1.2.0：頁首購物車 → 迷你購物車 drawer 的開關（委派點擊，不複製核心購物車邏輯；
		// 核心 ys-ec-cart.js 仍負責 body 重繪、移除、badge、加入購物車自動開啟）。
		wp_enqueue_script(
			'ys-cart-blocksy',
			YS_CART_BLOCKSY_URL . 'assets/js/ys-cart-blocksy.js',
			wp_script_is( 'ys-ec-cart', 'registered' ) ? [ 'ys-ec-cart' ] : [],
			YS_CART_BLOCKSY_VERSION,
			true
		);
	}

	/** 本次請求是否有「drawer 模式」的頁首購物車元件被渲染（由 view.php 宣告）。 */
	private static bool $drawer_requested = false;

	public static function request_mini_cart_drawer(): void {
		self::$drawer_requested = true;
	}

	/** 給契約／診斷用的唯讀旗標。 */
	public static function mini_cart_drawer_requested(): bool {
		return self::$drawer_requested;
	}

	/**
	 * 核心右下角浮動購物車關閉時，用核心同一份樣板在頁尾輸出迷你購物車（抽屜樣式）。
	 *
	 * 核心浮動購物車開著＝核心已輸出 #ys-ec-mini-cart，這裡什麼都不做（頁首 icon 直接開它）；
	 * 同一頁兩份同 ID 的迷你購物車會讓核心 JS 綁錯。
	 */
	public static function maybe_render_mini_cart_drawer(): void {
		if ( ! self::$drawer_requested || is_admin() || ! YSBlocksyDetector::ready() ) {
			return;
		}
		if ( ! class_exists( '\YangSheep\Ecommerce\YSEcommerce' )
			|| ! class_exists( '\YangSheep\Ecommerce\Handlers\YSCartHandler' )
			|| ! class_exists( '\YangSheep\Ecommerce\Services\Setup\YSPageResolver' )
			|| ! defined( 'YS_ECOMMERCE_PATH' ) ) {
			return;
		}
		if ( \YangSheep\Ecommerce\YSEcommerce::get_instance()->is_feature_enabled( 'floating_cart' ) ) {
			return; // 核心自己有輸出，頁首 icon 直接開核心的面板。
		}
		if ( ! (bool) apply_filters( 'ys_ec_render_standard_chrome', true, [ 'piece' => 'mini_cart' ] ) ) {
			return; // 與核心同一個 opt-out 契約（affiliate landing 等）。
		}

		$template_path = YS_ECOMMERCE_PATH . 'templates/cart/mini-cart.php';
		if ( ! file_exists( $template_path ) ) {
			return;
		}

		$cart_handler = \YangSheep\Ecommerce\Handlers\YSCartHandler::get_instance();
		$cart         = $cart_handler->get_cart();
		$items        = $cart['items'] ?? [];
		$totals       = $cart['totals'] ?? [];
		$checkout_url = esc_url( \YangSheep\Ecommerce\Services\Setup\YSPageResolver::url( 'checkout', 'checkout/' ) );
		$cart_url     = esc_url( \YangSheep\Ecommerce\Services\Setup\YSPageResolver::url( 'cart', 'cart/' ) );

		echo '<div class="ys-cart-blocksy-drawer-host" data-ys-cart-blocksy-drawer-host>';
		echo '<div class="ys-cart-blocksy-drawer-backdrop" data-ys-cart-blocksy-drawer-backdrop aria-hidden="true"></div>';
		include $template_path;
		echo '</div>';
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
