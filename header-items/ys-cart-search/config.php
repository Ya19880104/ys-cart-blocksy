<?php
/**
 * YS 商品搜尋（icon → 全屏即時搜尋 overlay）— Blocksy header 元件設定。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$config = [
	'name'              => __( 'YS 商品搜尋', 'ys-cart-blocksy' ),
	'description'       => __( '搜尋圖示，點擊開啟 YS CART 商品即時搜尋。', 'ys-cart-blocksy' ),
	'excluded_from'     => [ 'offcanvas' ],
	'selective_refresh' => [
		'ys_search_show_label',
		'ys_search_label',
	],
	'translation_keys'  => [
		[ 'key' => 'ys_search_label' ],
	],
];
