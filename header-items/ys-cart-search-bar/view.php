<?php
/**
 * YS 搜尋框（行內）— 前台 render。
 *
 * 重用核心：YSSearchShortcode::render_search_box() —— 表單送出落到商店頁
 * （?ys_ec_search= 參數），ajax_search 啟用時輸入即時顯示結果 dropdown
 * （核心 ys-ec-search.js 綁 .ys-ec-live-search-input）。本元件零 JS。
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

$ys_placeholder = trim( (string) blocksy_akg( 'ys_search_bar_placeholder', $atts, '' ) );
$ys_width       = (string) blocksy_akg( 'ys_search_bar_width', $atts, 'md' );
if ( ! in_array( $ys_width, [ 'sm', 'md', 'lg', 'full' ], true ) ) {
	$ys_width = 'md';
}

$ys_box_atts = [];
if ( '' !== $ys_placeholder ) {
	$ys_box_atts['placeholder'] = $ys_placeholder;
}
?>

<div
	class="ys-cart-blocksy-item ys-cart-blocksy-search-bar ys-cart-blocksy-search-bar--<?php echo esc_attr( $ys_width ); ?>"
	<?php echo blocksy_attr_to_html( $attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php
	echo \YangSheep\Ecommerce\Frontend\YSSearchShortcode::render_search_box( $ys_box_atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- 核心輸出已自含跳脫。
	?>
</div>
