<?php
/**
 * YS cart icon header item.
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$config = [
	'name'              => __( 'YS Cart', 'ys-cart-blocksy' ),
	'description'       => __( 'YS CART cart icon with optional item count badge.', 'ys-cart-blocksy' ),
	'selective_refresh' => [
		'ys_cart_click_action',
		'ys_cart_show_badge',
		'ys_cart_show_label',
		'ys_cart_label',
	],
	'translation_keys'  => [
		[ 'key' => 'ys_cart_label' ],
	],
];
