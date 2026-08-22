<?php
declare(strict_types=1);

/**
 * ys-cart-blocksy contract（ADR-057 P1 + v1.1.0 擴充）。
 *
 * 鎖定：
 *   B1  入口版本 header == 常數且 >= 1.0.0；CHANGELOG 記錄 1.0.0。
 *   B2  Plugin 掛 blocksy:header:items-paths；主程式無 admin 頁/REST/admin-ajax（5 原則）。
 *   B3  三個原始元件目錄齊備（config/options/view）。
 *   B4  每個 view 印 blocksy_attr_to_html($attr) 且 guard class_exists（fail-soft）。
 *   B5  account 用 YSPageResolver（連結模式）；search 用核心 YSSearchShortcode。
 *   B6  config selective_refresh 列出 options.php 的全部 ys_ 開頭選項 key。
 *   B7  CSS：icon trigger + 寬度檔位 + keywords 收起。
 *   B8  結果視窗 override 只准 z-index（不得寫死 left/right/width/position）。
 *   B9  v1.1.0：版本 >= 1.1.0 + CHANGELOG 記錄 1.1.0。
 *   B10 v1.1.0：account 核心模式（YSUserIcon::render，預設 core）。
 *   B11 v1.1.0：三原始元件皆有 dynamic-styles.php（尺寸 theme-icon-size + 顏色 blocksy_output_colors）。
 *   B12 v1.1.0：兩個智慧搜尋元件存在於 header-items-smart/、guard YSSsShortcodes、Plugin 條件式註冊。
 *   B13 v1.1.0：主程式接上 Hub Client（vendor autoload + YSPluginHubClient::register slug）。
 */

$root = dirname(__DIR__, 2);
$pass = 0; $fail = 0;
$check = static function (string $label, bool $ok) use (&$pass, &$fail): void {
    if ($ok) { ++$pass; echo "[PASS] {$label}\n"; return; }
    ++$fail; echo "[FAIL] {$label}\n";
};
$read = static function (string $rel) use ($root): string {
    $full = $root . '/' . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    return is_file($full) ? (string) file_get_contents($full) : '';
};

$main     = $read('ys-cart-blocksy.php');
$plugin   = $read('src/YSCartBlocksyPlugin.php');
$detector = $read('src/YSBlocksyDetector.php');
$css      = $read('assets/css/ys-cart-blocksy.css');
$log      = $read('CHANGELOG.md');

// B1
preg_match('/Version:\s*([0-9.]+)/', $main, $vh);
preg_match("/YS_CART_BLOCKSY_VERSION', '([0-9.]+)'/", $main, $vc);
$check(
    'B1 version header/constant match, >= 1.0.0, CHANGELOG records 1.0.0',
    '' !== ($vh[1] ?? '') && ($vh[1] ?? '') === ($vc[1] ?? '')
    && version_compare($vh[1] ?? '0', '1.0.0', '>=')
    && str_contains($log, '## [1.0.0]')
);

// B2
$all_src = $main . $plugin . $detector;
$check(
    'B2 registers blocksy:header:items-paths; no admin page/REST/admin-ajax',
    str_contains($plugin, "blocksy:header:items-paths")
    && str_contains($plugin, "header-items")
    && !preg_match('/add_menu_page|add_submenu_page|register_rest_route|wp_ajax_|admin_post_/', $all_src)
);

// B3 + B4 + B6
$items = ['ys-cart-account', 'ys-cart-search', 'ys-cart-search-bar', 'ys-cart-cart'];
foreach ($items as $item) {
    $config  = $read("header-items/{$item}/config.php");
    $options = $read("header-items/{$item}/options.php");
    $view    = $read("header-items/{$item}/view.php");

    $check(
        "B3 {$item}: config/options/view all present",
        '' !== $config && '' !== $options && '' !== $view
        && str_contains($config, '$config = [')
        && str_contains($options, '$options = [')
    );

    $check(
        "B4 {$item}: view prints blocksy_attr_to_html(\$attr) and guards class_exists",
        str_contains($view, 'blocksy_attr_to_html( $attr )')
        && str_contains($view, 'class_exists(')
    );

    preg_match_all("/'(ys_[a-z0-9_]+)'\s*=>\s*\[/", $options, $optKeys);
    $missing = [];
    foreach (array_unique($optKeys[1] ?? []) as $key) {
        if (!str_contains($config, "'{$key}'")) {
            $missing[] = $key;
        }
    }
    $check("B6 {$item}: all ys_ option keys listed in selective_refresh" . ($missing ? ' (missing: ' . implode(',', $missing) . ')' : ''), [] === $missing);
}

// B5
$accountView   = $read('header-items/ys-cart-account/view.php');
$searchView    = $read('header-items/ys-cart-search/view.php');
$searchBarView = $read('header-items/ys-cart-search-bar/view.php');
$cartView      = $read('header-items/ys-cart-cart/view.php');
$check(
    'B5 account uses YSPageResolver; search items use core YSSearchShortcode',
    str_contains($accountView, 'YSPageResolver::dashboard_url()')
    && str_contains($accountView, 'YSPageResolver::login_url()')
    && str_contains($searchView, 'YSSearchShortcode::render_search_icon()')
    && str_contains($searchBarView, 'YSSearchShortcode::render_search_box(')
);

$check(
    'B16 cart item uses YS cart count and YS cart URL',
    str_contains($cartView, 'YSCartHandler::get_instance()->get_cart_count()')
    && str_contains($cartView, "YSPageResolver::url( 'cart'")
);

// B7
$check(
    'B7 CSS covers icon trigger + width tiers + keywords hidden',
    str_contains($css, '.ys-cart-blocksy-search .ys-ec-search-icon-trigger')
    && str_contains($css, '.ys-cart-blocksy-search-bar--full')
    && str_contains($css, '.ys-cart-blocksy-search-bar .ys-ec-search-keywords')
);

// B8
preg_match('/\.ys-cart-blocksy-search-bar \.ys-ec-search-results \{([^}]*)\}/s', $css, $resultsBlock);
$check(
    'B8 results override is z-index-only (no left/right pinning)',
    '' !== ($resultsBlock[1] ?? '')
    && str_contains($resultsBlock[1], 'z-index')
    && !preg_match('/left:|right:|width:|position:/', $resultsBlock[1])
);

// ── v1.1.0 ──

// B9
$check(
    'B9 version >= 1.1.0 + CHANGELOG records 1.1.0',
    version_compare($vh[1] ?? '0', '1.1.0', '>=')
    && str_contains($log, '## [1.1.0]')
);

// B10 — account core mode (default 'core', renders core YSUserIcon)
$accountOptions = $read('header-items/ys-cart-account/options.php');
$check(
    'B10 account core mode (YSUserIcon::render, default core)',
    str_contains($accountOptions, "'ys_account_mode'")
    && str_contains($accountOptions, "'value'   => 'core'")
    && str_contains($accountView, 'YSUserIcon::render(')
    && str_contains($accountView, "'core' === \$ys_mode")
);

// B11 — dynamic-styles for all 3 original items (size var + color)
$dsMissing = [];
foreach ($items as $item) {
    $ds = $read("header-items/{$item}/dynamic-styles.php");
    if ('' === $ds
        || !str_contains($ds, 'blocksy_output_responsive')
        || !str_contains($ds, 'blocksy_output_colors')
        || !str_contains($ds, "ysIcon")
    ) {
        // search-bar 用 ysBar* 變數而非 ysIcon*
        if ('ys-cart-search-bar' === $item
            && '' !== $ds
            && str_contains($ds, 'blocksy_output_responsive')
            && str_contains($ds, 'blocksy_output_colors')
            && str_contains($ds, 'ysBar')) {
            continue;
        }
        $dsMissing[] = $item;
    }
}
$check('B11 dynamic-styles (size+color) for all 3 items' . ($dsMissing ? ' (missing: ' . implode(',', $dsMissing) . ')' : ''), [] === $dsMissing);

// B12 — two smart-search items, gated registration
$smartIcon = $read('header-items-smart/ys-smart-search/view.php');
$smartBar  = $read('header-items-smart/ys-smart-search-bar/view.php');
$check(
    'B12 two smart-search items + conditional registration',
    '' !== $smartIcon && '' !== $smartBar
    && str_contains($smartIcon, 'YSSsShortcodes')
    && str_contains($smartBar, 'YSSsShortcodes')
    && str_contains($smartIcon, 'class_exists(')
    && str_contains($plugin, "defined( 'YS_SMART_SEARCH_VERSION' )")
    && str_contains($plugin, 'header-items-smart')
);

// B13 — Hub client wiring
$check(
    'B13 Hub client wired (vendor autoload + register slug)',
    str_contains($main, "vendor/autoload.php")
    && str_contains($main, 'YSPluginHubClient::register')
    && str_contains($main, "'slug'        => 'ys-cart-blocksy'")
);

// ── v1.1.1 ──

// B14 — 智慧搜尋框送出鈕：頁首中對齊核心乾淨樣式（透明內嵌、蓋過主題 button palette）
preg_match('/\.ys-ss-inputwrap > \.ys-ss-submit\[type="submit"\] \{([^}]*)\}/s', $css, $smartSubmit);
$check(
    'B14 smart submit reset (transparent inline, beats theme button palette)',
    '' !== ($smartSubmit[1] ?? '')
    && str_contains($smartSubmit[1], 'background: transparent')
    && str_contains($smartSubmit[1], 'position: absolute')
);

// B15 — 智慧搜尋面板：頁首中放大寬度 + right 錨定向左延伸（智慧版 JS 無 v2.52.30
// 量測機制，故由 Blocksy 提供尺寸；與 B8 核心結果視窗交給核心 JS 的策略並行不悖）
preg_match('/\.ys-cart-blocksy-search-bar \.ys-ss-panel \{([^}]*)\}/s', $css, $smartPanel);
$check(
    'B15 smart panel widened + left-extend in header (z-index + width + right anchor)',
    '' !== ($smartPanel[1] ?? '')
    && str_contains($smartPanel[1], 'z-index')
    && str_contains($smartPanel[1], 'width:')
    && str_contains($smartPanel[1], 'right: 0')
);

echo "\nv1.1.1 contract: PASS={$pass} FAIL={$fail}\n";
if ($fail > 0) {
    throw new RuntimeException("ys-cart-blocksy contract FAILED ({$fail})");
}
