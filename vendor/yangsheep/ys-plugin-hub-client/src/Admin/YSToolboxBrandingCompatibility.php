<?php
/**
 * Restore the shared parent label even when an older normalizer loaded first.
 *
 * @package YangSheep\PluginHubClient\Admin
 */

namespace YangSheep\PluginHubClient\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class YSToolboxBrandingCompatibility {

    private static bool $registered = false;

    public static function register(): void {
        if ( self::$registered ) {
            return;
        }

        self::$registered = true;
        // The entry registers the existing normalizer first. WordPress runs
        // callbacks at the same priority in registration order.
        add_action( 'admin_menu', array( self::class, 'normalize_label' ), PHP_INT_MAX );
    }

    public static function normalize_label(): void {
        global $menu;

        if ( ! is_array( $menu ) ) {
            return;
        }

        foreach ( $menu as &$item ) {
            if ( is_array( $item ) && 'ys-toolbox' === ( $item[2] ?? '' ) ) {
                $item[0] = esc_html__( 'YS Plugin', 'ys-plugin-hub-client' );
                $item[3] = esc_html__( 'YS Plugin', 'ys-plugin-hub-client' );
                break;
            }
        }
        unset( $item );
    }
}
