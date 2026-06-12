<?php
/**
 * YS 帳號 — 前台 render。
 *
 * 收到的變數（由 Blocksy builder 傳入）：$atts、$attr、$device、$item_id…
 * 根元素必須輸出 blocksy_attr_to_html( $attr )（Customizer selective refresh 依賴）。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $device ) ) {
	$device = 'desktop';
}

// fail-soft：核心不在就不輸出任何東西。
if ( ! class_exists( '\YangSheep\Ecommerce\Services\Setup\YSPageResolver' ) ) {
	return;
}

$ys_logged_in = is_user_logged_in();

$ys_target = (string) blocksy_akg(
	$ys_logged_in ? 'ys_account_loggedin_target' : 'ys_account_loggedout_target',
	$atts,
	$ys_logged_in ? 'dashboard' : 'login'
);
$ys_custom = trim( (string) blocksy_akg(
	$ys_logged_in ? 'ys_account_loggedin_custom_url' : 'ys_account_loggedout_custom_url',
	$atts,
	''
) );

if ( 'custom' === $ys_target && '' !== $ys_custom ) {
	$ys_url = $ys_custom;
} elseif ( $ys_logged_in ) {
	$ys_url = \YangSheep\Ecommerce\Services\Setup\YSPageResolver::dashboard_url();
} else {
	$ys_url = \YangSheep\Ecommerce\Services\Setup\YSPageResolver::login_url();
}

$ys_show_label = 'yes' === (string) blocksy_akg( 'ys_account_show_label', $atts, 'no' );
$ys_label      = (string) blocksy_akg( 'ys_account_label', $atts, __( '帳號', 'ys-cart-blocksy' ) );
$ys_aria       = $ys_logged_in
	? __( '會員中心', 'ys-cart-blocksy' )
	: __( '登入', 'ys-cart-blocksy' );
?>

<a
	href="<?php echo esc_url( $ys_url ); ?>"
	class="ys-cart-blocksy-item ys-cart-blocksy-account"
	aria-label="<?php echo esc_attr( $ys_aria ); ?>"
	<?php echo blocksy_attr_to_html( $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<svg class="ys-cart-blocksy-icon" aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
	<?php if ( $ys_show_label && '' !== $ys_label ) : ?>
		<span class="ys-cart-blocksy-label"><?php echo esc_html( $ys_label ); ?></span>
	<?php endif; ?>
</a>
