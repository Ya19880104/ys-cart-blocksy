<?php
/**
 * Plugin Name: YS CART Blocksy 整合
 * Plugin URI: https://yangsheep.com.tw
 * Description: 在 Blocksy 佈景主題的頁首建構器（外觀 → 自訂 → 頁首）提供 YS CART 元件：帳號（含核心下拉）、搜尋 Icon、搜尋框，以及進階搜尋元件；尺寸顏色可調。
 * Version: 1.2.3
 * Author: YANGSHEEP DESIGN
 * Author URI: https://yangsheep.com.tw
 * Text Domain: ys-cart-blocksy
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * Requires Plugins: ys-cart
 * License: GPL v2 or later
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

define( 'YS_CART_BLOCKSY_VERSION', '1.2.3' );
// 迷你購物車 drawer 需要的最低核心版本（唯一宣告處；README／CHANGELOG 引用同一個數字）：
// 2.58.2 起核心才會在購物車變空／頁上無迷你購物車 body 時同步所有數量 badge。舊核心的購物車
// 元件自動退回「前往購物車頁」（YSBlocksyDetector::core_supports_mini_cart_drawer()）。
define( 'YS_CART_BLOCKSY_DRAWER_MIN_CORE', '2.58.2' );
define( 'YS_CART_BLOCKSY_FILE', __FILE__ );
define( 'YS_CART_BLOCKSY_PATH', plugin_dir_path( __FILE__ ) );
define( 'YS_CART_BLOCKSY_URL', plugin_dir_url( __FILE__ ) );

// PHP 版本檢查
if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
	add_action( 'admin_notices', function () {
		echo '<div class="notice notice-error"><p>';
		echo esc_html__( 'YS CART Blocksy 整合需要 PHP 8.2 以上版本。', 'ys-cart-blocksy' );
		echo '</p></div>';
	} );
	return;
}

// PSR-4 Autoloader
spl_autoload_register( function ( $class ) {
	$prefix   = 'YangSheep\\CartBlocksy\\';
	$base_dir = YS_CART_BLOCKSY_PATH . 'src/';

	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	$file = $base_dir . str_replace( '\\', '/', substr( $class, $len ) ) . '.php';
	if ( file_exists( $file ) ) {
		require $file;
	}
} );

// YS Plugin Hub Client（更新功能）— 防重複載入：站上其他 YS 外掛可能已載入。
if ( is_readable( YS_CART_BLOCKSY_PATH . 'vendor/autoload.php' ) ) {
	require_once YS_CART_BLOCKSY_PATH . 'vendor/autoload.php';
}

add_action( 'plugins_loaded', static function (): void {
	if ( class_exists( '\YangSheep\PluginHubClient\YSPluginHubClient' ) ) {
		\YangSheep\PluginHubClient\YSPluginHubClient::register( [
			'slug'        => 'ys-cart-blocksy',
			'version'     => YS_CART_BLOCKSY_VERSION,
			'plugin_file' => __FILE__,
			'name'        => 'YS CART Blocksy 整合',
		] );
	}
}, 30 );

add_action( 'plugins_loaded', [ \YangSheep\CartBlocksy\YSCartBlocksyPlugin::class, 'boot' ] );
