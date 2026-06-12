<?php
/**
 * YS 帳號 — Blocksy header 元件設定。
 *
 * selective_refresh 列出全部選項 key：任何選項變更時 Customizer 以
 * partial refresh 重新 server-render 本元件（免 sync.js 即有即時預覽）。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$config = [
	'name'              => __( 'YS 帳號', 'ys-cart-blocksy' ),
	'description'       => __( '連到 YS CART 會員中心（未登入時連到登入頁）。', 'ys-cart-blocksy' ),
	'selective_refresh' => [
		'ys_account_loggedout_target',
		'ys_account_loggedout_custom_url',
		'ys_account_loggedin_target',
		'ys_account_loggedin_custom_url',
		'ys_account_label',
		'ys_account_show_label',
	],
	'translation_keys'  => [
		[ 'key' => 'ys_account_label' ],
	],
];
