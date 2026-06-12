<?php
/**
 * YS 商品搜尋 — Customizer 選項。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$options = [
	blocksy_rand_md5() => [
		'title'   => __( 'General', 'ys-cart-blocksy' ),
		'type'    => 'tab',
		'options' => [

			'ys_search_show_label' => [
				'label' => __( '顯示文字標籤', 'ys-cart-blocksy' ),
				'type'  => 'ct-switch',
				'value' => 'no',
			],

			blocksy_rand_md5() => [
				'type'      => 'ct-condition',
				'condition' => [ 'ys_search_show_label' => 'yes' ],
				'options'   => [
					'ys_search_label' => [
						'label'  => __( '標籤文字', 'ys-cart-blocksy' ),
						'type'   => 'text',
						'value'  => __( '搜尋', 'ys-cart-blocksy' ),
						'design' => 'block',
					],
				],
			],

			blocksy_rand_md5() => [
				'type'  => 'ct-title',
				'label' => __( '提示', 'ys-cart-blocksy' ),
				'desc'  => __( '本元件使用 YS CART「商品即時搜尋」功能（商店設定 → 功能模組）。功能關閉時前台不會輸出。', 'ys-cart-blocksy' ),
			],
		],
	],
];
