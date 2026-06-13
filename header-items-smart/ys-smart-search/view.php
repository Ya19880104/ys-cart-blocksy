<?php
/**
 * YS 智慧搜尋 Icon — 前台 render。
 *
 * 重用 ys-cart-smart-search 的 renderer：render_icon() 輸出 [data-ys-ss-open]
 * 觸發鈕，彈窗容器與互動 JS 由智慧搜尋外掛自管（render 時自動 enqueue）。
 *
 * @package YangSheep\CartBlocksy
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $device ) ) {
	$device = 'desktop';
}

// fail-soft：智慧搜尋外掛不在（或被停用後殘留 placement）→ 前台不輸出。
if ( ! class_exists( '\YangSheep\SmartSearch\Frontend\YSSsShortcodes' ) ) {
	if ( is_customize_preview() ) {
		echo '<span class="ys-cart-blocksy-item ys-cart-blocksy-disabled" ' . blocksy_attr_to_html( $attr ) . '>' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			. esc_html__( '需啟用 YS CART 智慧搜尋', 'ys-cart-blocksy' )
			. '</span>';
	}
	return;
}
?>

<span
	class="ys-cart-blocksy-item ys-cart-blocksy-smart-search"
	<?php echo blocksy_attr_to_html( $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	echo \YangSheep\SmartSearch\Frontend\YSSsShortcodes::render_icon(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 智慧搜尋輸出已自含跳脫。
	?>
</span>
