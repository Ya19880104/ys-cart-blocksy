<?php
/**
 * 環境偵測（fail-soft 的根）。
 *
 * 所有對外行為都以這裡的判斷 gate：缺 Blocksy 或缺 YS CART 時元件不輸出、
 * 資產不載入、只留外掛頁提示，絕不 fatal。
 *
 * @package YangSheep\CartBlocksy
 */

namespace YangSheep\CartBlocksy;

defined( 'ABSPATH' ) || exit;

final class YSBlocksyDetector {

	/**
	 * 是否為 Blocksy 主題（含 child theme — 以 parent template 判斷）。
	 */
	public static function is_blocksy(): bool {
		return 'blocksy' === get_template();
	}

	/**
	 * YS CART 核心是否存在。
	 */
	public static function has_ys_cart(): bool {
		return defined( 'YS_ECOMMERCE_VERSION' )
			|| class_exists( '\YangSheep\Ecommerce\YSEcommerce' );
	}

	public static function ready(): bool {
		return self::is_blocksy() && self::has_ys_cart();
	}

	/**
	 * 核心「商品即時搜尋」功能是否啟用（搜尋 icon 的 overlay / live 結果都靠它）。
	 */
	public static function ajax_search_enabled(): bool {
		if ( ! class_exists( '\YangSheep\Ecommerce\YSEcommerce' ) ) {
			return false;
		}

		try {
			$core = \YangSheep\Ecommerce\YSEcommerce::get_instance();
			if ( is_object( $core ) && method_exists( $core, 'is_feature_enabled' ) ) {
				return (bool) $core->is_feature_enabled( 'ajax_search' );
			}
		} catch ( \Throwable $e ) {
			// 核心初始化異常時視為未啟用，不阻塞頁面。
		}

		return false;
	}
}
