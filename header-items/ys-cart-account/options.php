<?php
/**
 * YS 帳號 — Customizer 選項（v1.1.0：核心模式／連結模式 + 尺寸顏色）。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$options = [
	blocksy_rand_md5() => [
		'title'   => __( 'General', 'ys-cart-blocksy' ),
		'type'    => 'tab',
		'options' => [

			'ys_account_mode' => [
				'label'   => __( '行為模式', 'ys-cart-blocksy' ),
				'type'    => 'ct-select',
				'value'   => 'core',
				'design'  => 'block',
				'choices' => blocksy_ordered_keys( [
					'core' => __( '與核心相同（未登入開登入視窗／登入後下拉選單）', 'ys-cart-blocksy' ),
					'link' => __( '簡單連結', 'ys-cart-blocksy' ),
				] ),
			],

			blocksy_rand_md5() => [
				'type'      => 'ct-condition',
				'condition' => [ 'ys_account_mode' => 'link' ],
				'options'   => [

					'ys_account_loggedout_target' => [
						'label'   => __( '未登入時連到', 'ys-cart-blocksy' ),
						'type'    => 'ct-select',
						'value'   => 'login',
						'design'  => 'block',
						'choices' => blocksy_ordered_keys( [
							'login'  => __( 'YS CART 登入頁', 'ys-cart-blocksy' ),
							'custom' => __( '自訂連結', 'ys-cart-blocksy' ),
						] ),
					],

					blocksy_rand_md5() => [
						'type'      => 'ct-condition',
						'condition' => [ 'ys_account_loggedout_target' => 'custom' ],
						'options'   => [
							'ys_account_loggedout_custom_url' => [
								'label'  => __( '未登入自訂連結', 'ys-cart-blocksy' ),
								'type'   => 'text',
								'value'  => '',
								'design' => 'block',
							],
						],
					],

					'ys_account_loggedin_target' => [
						'label'   => __( '登入後連到', 'ys-cart-blocksy' ),
						'type'    => 'ct-select',
						'value'   => 'dashboard',
						'design'  => 'block',
						'choices' => blocksy_ordered_keys( [
							'dashboard' => __( 'YS CART 會員中心', 'ys-cart-blocksy' ),
							'custom'    => __( '自訂連結', 'ys-cart-blocksy' ),
						] ),
					],

					blocksy_rand_md5() => [
						'type'      => 'ct-condition',
						'condition' => [ 'ys_account_loggedin_target' => 'custom' ],
						'options'   => [
							'ys_account_loggedin_custom_url' => [
								'label'  => __( '登入後自訂連結', 'ys-cart-blocksy' ),
								'type'   => 'text',
								'value'  => '',
								'design' => 'block',
							],
						],
					],
				],
			],

			'ysIconSize' => [
				'label'      => __( 'Icon 尺寸', 'ys-cart-blocksy' ),
				'type'       => 'ct-slider',
				'min'        => 10,
				'max'        => 50,
				'value'      => 18,
				'responsive' => true,
			],

			'ys_account_show_label' => [
				'label' => __( '顯示文字標籤', 'ys-cart-blocksy' ),
				'type'  => 'ct-switch',
				'value' => 'no',
			],

			blocksy_rand_md5() => [
				'type'      => 'ct-condition',
				'condition' => [ 'ys_account_show_label' => 'yes' ],
				'options'   => [
					'ys_account_label' => [
						'label'  => __( '標籤文字', 'ys-cart-blocksy' ),
						'type'   => 'text',
						'value'  => __( '帳號', 'ys-cart-blocksy' ),
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
