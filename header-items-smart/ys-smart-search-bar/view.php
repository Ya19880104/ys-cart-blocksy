<?php
/**
 * YS 智慧搜尋框 — 前台 render（重用 ys-cart-smart-search 的 render_bar()）。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $device ) ) {
	$device = 'desktop';
}

if ( ! class_exists( '\YangSheep\SmartSearch\Frontend\YSSsShortcodes' ) ) {
	if ( is_customize_preview() ) {
		echo '<span class="ys-cart-blocksy-item ys-cart-blocksy-disabled" ' . blocksy_attr_to_html( $attr ) . '>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. esc_html__( '需啟用 YS CART 智慧搜尋', 'ys-cart-blocksy' )
			. '</span>';
	}
	return;
}

$ys_placeholder = trim( (string) blocksy_akg( 'ys_smart_bar_placeholder', $atts, '' ) );
$ys_width       = (string) blocksy_akg( 'ys_smart_bar_width', $atts, 'md' );
if ( ! in_array( $ys_width, [ 'sm', 'md', 'lg', 'full' ], true ) ) {
	$ys_width = 'md';
}

$ys_bar_atts = [];
if ( '' !== $ys_placeholder ) {
	$ys_bar_atts['placeholder'] = $ys_placeholder;
}
?>

<div
	class="ys-cart-blocksy-item ys-cart-blocksy-search-bar ys-cart-blocksy-smart-bar ys-cart-blocksy-search-bar--<?php echo esc_attr( $ys_width ); ?>"
	<?php echo blocksy_attr_to_html( $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	echo \YangSheep\SmartSearch\Frontend\YSSsShortcodes::render_bar( $ys_bar_atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 智慧搜尋輸出已自含跳脫。
	?>
</div>
