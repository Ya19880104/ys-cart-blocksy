<?php
/**
 * YS 搜尋 Icon — 選項驅動 CSS（Blocksy dynamic-styles 合約）。
 * scope 變數：$css/$tablet_css/$mobile_css/$atts/$root_selector（由 builder extract 注入）。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'blocksy_assemble_selector' ) ) {
	return;
}

// Icon 尺寸 → --theme-icon-size（外掛 CSS 消費）
$ys_icon_size = blocksy_akg( 'ysIconSize', $atts, 18 );

if ( 18 !== $ys_icon_size ) {
	blocksy_output_responsive( [
		'css'          => $css,
		'tablet_css'   => $tablet_css,
		'mobile_css'   => $mobile_css,
		'selector'     => blocksy_assemble_selector( $root_selector ),
		'variableName' => 'theme-icon-size',
		'value'        => $ys_icon_size,
	] );
}

// Icon 顏色 → --theme-icon-color / --theme-icon-hover-color
blocksy_output_colors( [
	'value'      => blocksy_akg( 'ysIconColor', $atts ),
	'default'    => [
		'default' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ],
		'hover'   => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ],
	],
	'css'        => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables'  => [
		'default' => [
			'selector' => blocksy_assemble_selector( $root_selector ),
			'variable' => 'theme-icon-color',
		],
		'hover'   => [
			'selector' => blocksy_assemble_selector( $root_selector ),
			'variable' => 'theme-icon-hover-color',
		],
	],
	'responsive' => true,
] );
