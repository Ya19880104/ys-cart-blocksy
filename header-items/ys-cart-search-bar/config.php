<?php
/**
 * YS 搜尋框（行內即時搜尋）— Blocksy header 元件設定。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$config = [
	'name'              => __( 'YS 搜尋框', 'ys-cart-blocksy' ),
	'description'       => __( '行內商品搜尋框，輸入即時顯示 YS CART 商品結果。', 'ys-cart-blocksy' ),
	'excluded_from'     => [ 'offcanvas' ],
	'selective_refresh' => [
		'ys_search_bar_placeholder',
		'ys_search_bar_width',
	],
	'translation_keys'  => [
		[ 'key' => 'ys_search_bar_placeholder' ],
	],
];
