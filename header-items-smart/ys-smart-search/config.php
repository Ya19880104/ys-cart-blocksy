<?php
/**
 * YS 進階搜尋 Icon — 點擊開啟進階搜尋彈窗（熱門關鍵字建議＋分組即時結果）。
 * 本目錄只在 ys-cart-smart-search 啟用時被註冊（YSCartBlocksyPlugin::register_item_paths）。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$config = [
	'name'              => __( 'YS 進階搜尋 Icon', 'ys-cart-blocksy' ),
	'description'       => __( '搜尋圖示 → 進階搜尋彈窗（熱門關鍵字＋商品/分類/文章分組即時結果）。需啟用 YS CART 進階搜尋外掛。', 'ys-cart-blocksy' ),
	'excluded_from'     => [ 'offcanvas' ],
	'selective_refresh' => [],
];
