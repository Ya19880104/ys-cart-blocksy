<?php
/**
 * YS 進階搜尋框 — Customizer 選項。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$options = [
	blocksy_rand_md5() => [
		'title'   => __( 'General', 'ys-cart-blocksy' ),
		'type'    => 'tab',
		'options' => [

			'ys_smart_bar_placeholder' => [
				'label'  => __( '提示文字（placeholder）', 'ys-cart-blocksy' ),
				'type'   => 'text',
				'value'  => '',
				'design' => 'block',
				'desc'   => __( '留空使用進階搜尋預設提示文字。', 'ys-cart-blocksy' ),
			],

			'ys_smart_bar_width' => [
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

			'ysBarHeight' => [
				'label'      => __( '輸入框高度', 'ys-cart-blocksy' ),
				'type'       => 'ct-slider',
				'min'        => 32,
				'max'        => 64,
				'value'      => 40,
				'responsive' => true,
			],
		],
	],

	blocksy_rand_md5() => [
		'title'   => __( 'Design', 'ys-cart-blocksy' ),
		'type'    => 'tab',
		'options' => [

			'ysBarColor' => [
				'label'      => __( '搜尋框顏色', 'ys-cart-blocksy' ),
				'type'       => 'ct-color-picker',
				'design'     => 'block:right',
				'responsive' => true,

				'value'      => [
					'text'       => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ],
					'background' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ],
					'border'     => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ],
				],

				'pickers'    => [
					[
						'title'   => __( '文字', 'ys-cart-blocksy' ),
						'id'      => 'text',
						'inherit' => 'var(--theme-text-color)',
					],
					[
						'title'   => __( '背景', 'ys-cart-blocksy' ),
						'id'      => 'background',
						'inherit' => '#ffffff',
					],
					[
						'title'   => __( '邊框', 'ys-cart-blocksy' ),
						'id'      => 'border',
						'inherit' => 'rgba(0,0,0,0.12)',
					],
				],
			],
		],
	],
];
