<?php
/**
 * YS cart icon options.
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
				'label'      => __( 'Icon size', 'ys-cart-blocksy' ),
				'type'       => 'ct-slider',
				'min'        => 10,
				'max'        => 50,
				'value'      => 18,
				'responsive' => true,
			],

			'ys_cart_show_badge' => [
				'label' => __( 'Show count badge', 'ys-cart-blocksy' ),
				'type'  => 'ct-switch',
				'value' => 'yes',
			],

			'ys_cart_show_label' => [
				'label' => __( 'Show label', 'ys-cart-blocksy' ),
				'type'  => 'ct-switch',
				'value' => 'no',
			],

			blocksy_rand_md5() => [
				'type'      => 'ct-condition',
				'condition' => [ 'ys_cart_show_label' => 'yes' ],
				'options'   => [
					'ys_cart_label' => [
						'label'  => __( 'Label', 'ys-cart-blocksy' ),
						'type'   => 'text',
						'value'  => __( 'Cart', 'ys-cart-blocksy' ),
						'design' => 'block',
					],
				],
			],
		],
	],

	blocksy_rand_md5() => [
		'title'   => __( 'Design', 'ys-cart-blocksy' ),
		'type'    => 'tab',
		'options' => [
			'ysIconColor' => [
				'label'      => __( 'Icon color', 'ys-cart-blocksy' ),
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
