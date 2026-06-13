<?php
/**
 * YS 搜尋 Icon（v1.1.0 改名，自「YS 商品搜尋」）— 點擊開啟核心商品即時搜尋 overlay。
 * 注意：目錄名（item id）維持 ys-cart-search 不變，既有 header placements 不受影響。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$config = [
	'name'              => __( 'YS 搜尋 Icon', 'ys-cart-blocksy' ),
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
