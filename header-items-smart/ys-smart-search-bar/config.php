<?php
/**
 * YS 智慧搜尋框 — 行內智慧搜尋（熱門關鍵字建議＋分組即時結果）。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$config = [
	'name'              => __( 'YS 智慧搜尋框', 'ys-cart-blocksy' ),
	'description'       => __( '行內智慧搜尋框（focus 顯示熱門關鍵字、輸入即時分組結果）。需啟用 YS CART 智慧搜尋外掛。', 'ys-cart-blocksy' ),
	'excluded_from'     => [ 'offcanvas' ],
	'selective_refresh' => [
		'ys_smart_bar_placeholder',
		'ys_smart_bar_width',
	],
	'translation_keys'  => [
		[ 'key' => 'ys_smart_bar_placeholder' ],
	],
];
