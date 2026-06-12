<?php
/**
 * YS 商品搜尋（icon）— 前台 render。
 *
 * 重用核心：YSSearchShortcode::render_search_icon() 輸出 .ys-ec-search-icon-trigger，
 * 核心 ys-ec-search.js（ajax_search 啟用時全頁載）綁定點擊 → 開啟 wp_footer 的
 * 全屏 overlay（render_search_overlay 同樣由核心掛載）。本元件零 JS。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $device ) ) {
	$device = 'desktop';
}

if ( ! class_exists( '\YangSheep\Ecommerce\Frontend\YSSearchShortcode' ) ) {
	return;
}

// 功能未啟用：前台不輸出死 icon；Customizer 預覽顯示提示方便診斷。
if ( ! \YangSheep\CartBlocksy\YSBlocksyDetector::ajax_search_enabled() ) {
	if ( is_customize_preview() ) {
		echo '<span class="ys-cart-blocksy-item ys-cart-blocksy-disabled" ' . blocksy_attr_to_html( $attr ) . '>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. esc_html__( 'YS 即時搜尋未啟用', 'ys-cart-blocksy' )
			. '</span>';
	}
	return;
}

$ys_show_label = 'yes' === (string) blocksy_akg( 'ys_search_show_label', $atts, 'no' );
$ys_label      = (string) blocksy_akg( 'ys_search_label', $atts, __( '搜尋', 'ys-cart-blocksy' ) );
?>

<span
	class="ys-cart-blocksy-item ys-cart-blocksy-search"
	<?php echo blocksy_attr_to_html( $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	echo \YangSheep\Ecommerce\Frontend\YSSearchShortcode::render_search_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 核心輸出已自含跳脫。
	?>
	<?php if ( $ys_show_label && '' !== $ys_label ) : ?>
		<span class="ys-cart-blocksy-label"><?php echo esc_html( $ys_label ); ?></span>
	<?php endif; ?>
</span>
