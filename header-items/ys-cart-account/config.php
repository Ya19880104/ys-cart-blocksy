<?php
/**
 * YS 帳號 — Blocksy header 元件設定（v1.1.0）。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

$config = [
	'name'              => __( 'YS 帳號', 'ys-cart-blocksy' ),
	'description'       => __( '與核心 [ys_ec_user_icon] 相同：未登入點擊開登入視窗、登入後顯示會員下拉選單；亦可切換為簡單連結模式。', 'ys-cart-blocksy' ),
	'selective_refresh' => [
		'ys_account_mode',
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
