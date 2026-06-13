<?php
/**
 * YS 帳號 — 選項驅動 CSS。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'blocksy_assemble_selector' ) ) {
	return;
}

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
