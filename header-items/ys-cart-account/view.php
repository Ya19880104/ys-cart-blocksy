<?php
/**
 * YS 帳號 — 前台 render（v1.1.0）。
 *
 * 核心模式（預設）：直接呼叫核心 `YSUserIcon::render()` —— 與 [ys_ec_user_icon]
 * 100% 相同：未登入點擊開核心登入 Modal（YSAuthGate wp_footer 輸出、ys-ec-auth.js
 * 驅動）、登入後顯示會員下拉（我的帳號／訂單紀錄／訂單查詢／登出，JS click toggle）。
 * 核心 ys-ec-common.css + ys-ec-auth.js 已全前台載入，零額外接線。
 *
 * 連結模式：v1.0.0 行為（未登入→登入頁／登入後→會員中心，皆可自訂連結）。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $device ) ) {
	$device = 'desktop';
}

if ( ! class_exists( '\YangSheep\Ecommerce\Services\Setup\YSPageResolver' ) ) {
	return;
}

$ys_mode       = (string) blocksy_akg( 'ys_account_mode', $atts, 'core' );
$ys_show_label = 'yes' === (string) blocksy_akg( 'ys_account_show_label', $atts, 'no' );
$ys_label      = (string) blocksy_akg( 'ys_account_label', $atts, __( '帳號', 'ys-cart-blocksy' ) );
$ys_logged_in  = is_user_logged_in();

/* ── 核心模式 ── */
if ( 'core' === $ys_mode && class_exists( '\YangSheep\Ecommerce\Frontend\YSUserIcon' ) ) {
	?>
	<span
		class="ys-cart-blocksy-item ys-cart-blocksy-account ys-cart-blocksy-account--core"
		<?php echo blocksy_attr_to_html( $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
		<?php
		echo \YangSheep\Ecommerce\Frontend\YSUserIcon::render( [ 'class' => 'ys-cart-blocksy-account-core' ] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 核心輸出已自含跳脫。
		?>
		<?php if ( $ys_show_label && '' !== $ys_label ) : ?>
			<span class="ys-cart-blocksy-label"><?php echo esc_html( $ys_label ); ?></span>
		<?php endif; ?>
	</span>
	<?php
	return;
}

/* ── 連結模式（v1.0.0 行為） ── */
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

$ys_aria = $ys_logged_in
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
