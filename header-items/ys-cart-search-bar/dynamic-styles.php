<?php
/**
 * YS 搜尋框 — 選項驅動 CSS。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'blocksy_assemble_selector' ) ) {
	return;
}

// 輸入框高度 → --ys-bar-height
$ys_bar_height = blocksy_akg( 'ysBarHeight', $atts, 40 );

if ( 40 !== $ys_bar_height ) {
	blocksy_output_responsive( [
		'css'          => $css,
		'tablet_css'   => $tablet_css,
		'mobile_css'   => $mobile_css,
		'selector'     => blocksy_assemble_selector( $root_selector ),
		'variableName' => 'ys-bar-height',
		'value'        => $ys_bar_height,
	] );
}

// 文字／背景／邊框 → --ys-bar-text / --ys-bar-bg / --ys-bar-border
blocksy_output_colors( [
	'value'      => blocksy_akg( 'ysBarColor', $atts ),
	'default'    => [
		'text'       => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ],
		'background' => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ],
		'border'     => [ 'color' => Blocksy_Css_Injector::get_skip_rule_keyword( 'DEFAULT' ) ],
	],
	'css'        => $css,
	'tablet_css' => $tablet_css,
	'mobile_css' => $mobile_css,
	'variables'  => [
		'text'       => [
			'selector' => blocksy_assemble_selector( $root_selector ),
			'variable' => 'ys-bar-text',
		],
		'background' => [
			'selector' => blocksy_assemble_selector( $root_selector ),
			'variable' => 'ys-bar-bg',
		],
		'border'     => [
			'selector' => blocksy_assemble_selector( $root_selector ),
			'variable' => 'ys-bar-border',
		],
	],
	'responsive' => true,
] );
