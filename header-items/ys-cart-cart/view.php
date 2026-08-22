<?php
/**
 * YS cart icon frontend render.
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $device ) ) {
	$device = 'desktop';
}

if (
	! class_exists( '\YangSheep\Ecommerce\Handlers\YSCartHandler' )
	|| ! class_exists( '\YangSheep\Ecommerce\Services\Setup\YSPageResolver' )
) {
	return;
}

$ys_count      = (int) \YangSheep\Ecommerce\Handlers\YSCartHandler::get_instance()->get_cart_count();
$ys_cart_url   = \YangSheep\Ecommerce\Services\Setup\YSPageResolver::url( 'cart', 'cart/' );
$ys_show_badge = 'yes' === (string) blocksy_akg( 'ys_cart_show_badge', $atts, 'yes' );
$ys_show_label = 'yes' === (string) blocksy_akg( 'ys_cart_show_label', $atts, 'no' );
$ys_label      = (string) blocksy_akg( 'ys_cart_label', $atts, __( 'Cart', 'ys-cart-blocksy' ) );
// v1.2.0：點擊行為（drawer＝開啟核心迷你購物車；link＝前往購物車頁）。href 永遠保留購物車頁當
// no-JS／drawer 不存在時的退路。
$ys_action = 'link' === (string) blocksy_akg( 'ys_cart_click_action', $atts, 'drawer' ) ? 'link' : 'drawer';
if ( 'drawer' === $ys_action && class_exists( '\YangSheep\CartBlocksy\YSCartBlocksyPlugin' ) ) {
	\YangSheep\CartBlocksy\YSCartBlocksyPlugin::request_mini_cart_drawer();
}
?>

<a
	href="<?php echo esc_url( $ys_cart_url ); ?>"
	class="ys-cart-blocksy-item ys-cart-blocksy-cart"
	data-ys-cart-blocksy-action="<?php echo esc_attr( $ys_action ); ?>"
	<?php if ( 'drawer' === $ys_action ) : ?>aria-haspopup="dialog" aria-expanded="false" aria-controls="ys-ec-mini-cart-panel"<?php endif; ?>
	aria-label="<?php echo esc_attr__( 'Cart', 'ys-cart-blocksy' ); ?>"
	<?php echo blocksy_attr_to_html( $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<span class="ys-cart-blocksy-cart-icon-wrap">
		<svg class="ys-cart-blocksy-icon" aria-hidden="true" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<circle cx="9" cy="21" r="1"/>
			<circle cx="20" cy="21" r="1"/>
			<path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
		</svg>
		<?php if ( $ys_show_badge ) : ?>
			<span class="ys-cart-blocksy-cart-badge" data-ys-cart-blocksy-count <?php echo $ys_count < 1 ? 'hidden' : ''; ?>>
				<?php echo esc_html( (string) $ys_count ); ?>
			</span>
		<?php endif; ?>
	</span>
	<?php if ( $ys_show_label && '' !== $ys_label ) : ?>
		<span class="ys-cart-blocksy-label"><?php echo esc_html( $ys_label ); ?></span>
	<?php endif; ?>
</a>
