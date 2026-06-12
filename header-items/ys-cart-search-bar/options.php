<?php
/**
 * YS 搜尋框 — Customizer 選項。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$options = [
	blocksy_rand_md5() => [
		'title'   => __( 'General', 'ys-cart-blocksy' ),
		'type'    => 'tab',
		'options' => [

			'ys_search_bar_placeholder' => [
				'label'  => __( '提示文字（placeholder）', 'ys-cart-blocksy' ),
				'type'   => 'text',
				'value'  => '',
				'design' => 'block',
				'desc'   => __( '留空使用 YS CART 商店設定中的預設提示文字。', 'ys-cart-blocksy' ),
			],

			'ys_search_bar_width' => [
				'label'   => __( '寬度', 'ys-cart-blocksy' ),
				'type'    => 'ct-select',
				'value'   => 'md',
				'design'  => 'block',
				'choices' => blocksy_ordered_keys( [
					'sm'   => __( '窄（200px）', 'ys-cart-blocksy' ),
					'md'   => __( '中（280px）', 'ys-cart-blocksy' ),
					'lg'   => __( '寬（360px）', 'ys-cart-blocksy' ),
					'full' => __( '填滿可用空間', 'ys-cart-blocksy' ),
				] ),
			],
		],
	],
];
