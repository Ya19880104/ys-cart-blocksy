<?php
/**
 * YS 智慧搜尋 Icon — Customizer 選項。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$options = [
	blocksy_rand_md5() => [
		'title'   => __( 'General', 'ys-cart-blocksy' ),
		'type'    => 'tab',
		'options' => [

			'ysIconSize' => [
				'label'      => __( 'Icon 尺寸', 'ys-cart-blocksy' ),
				'type'       => 'ct-slider',
				'min'        => 10,
				'max'        => 50,
				'value'      => 20,
				'responsive' => true,
			],

			blocksy_rand_md5() => [
				'type'  => 'ct-title',
				'label' => __( '提示', 'ys-cart-blocksy' ),
				'desc'  => __( '彈窗內容（熱門關鍵字數量、搜尋內容與呈現）請至「YS CART → 智慧搜尋」設定。', 'ys-cart-blocksy' ),
			],
		],
	],

	blocksy_rand_md5() => [
		'title'   => __( 'Design', 'ys-cart-blocksy' ),
		'type'    => 'tab',
		'options' => [

			'ysIconColor' => [
				'label'      => __( 'Icon 顏色', 'ys-cart-blocksy' ),
				'type'       => 'ct-color-picker',
				'design'     => 'block:right',
				'responsive' => true,

				'value'      => [
					'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ],
					'hover'   => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ],
				],

				'pickers'    => [
					[
						'title'   => __( 'Initial', 'ys-cart-blocksy' ),
						'id'      => 'default',
						'inherit' => 'var(--theme-text-color)',
					],
					[
						'title'   => __( 'Hover', 'ys-cart-blocksy' ),
						'id'      => 'hover',
						'inherit' => 'var(--theme-palette-color-2)',
					],
				],
			],
		],
	],
];
