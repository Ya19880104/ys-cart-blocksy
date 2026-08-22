<?php
/**
 * Regression: v1.2.x 頁首購物車 → 核心迷你購物車 drawer（結構契約）
 *
 * 行為（開關、dialog 焦點生命週期、結帳頁不攔截、與核心監聽的互動）由 tests/e2e/ 的真瀏覽器 fixture 驗證；
 * 本檔只釘結構：哪裡宣告、哪裡輸出、最低核心版本四處一致、5 原則不變。
 *
 *   (a) 購物車元件有 ys_cart_click_action（drawer／link，預設 drawer）且進 selective_refresh
 *   (b) view.php 輸出 data-ys-cart-blocksy-action；drawer 模式宣告 request_mini_cart_drawer()、保留 href 退路、
 *       核心低於 YS_CART_BLOCKSY_DRAWER_MIN_CORE 時退回 link
 *   (c) 外掛只在「drawer 被宣告 ＋ 核心浮動購物車關閉 ＋ chrome 未 opt-out」時，在 wp_footer(20) 以核心
 *       同一份樣板輸出迷你購物車（零複製購物車邏輯）；核心浮動購物車開著時什麼都不輸出（避免雙 ID）；
 *       JS／CSS 相依核心 handle
 *   (d) JS：可顯示性守門（computed display）、bubble phase＋延後開關、不 stopPropagation、修飾鍵放行、
 *       dialog 屬性、inert、Tab 焦點圈、Esc、MutationObserver 同步；無 fetch／XHR；
 *       v1.2.2：新增 trigger（selective refresh 重繪）的 MutationObserver 同步、inert 退路記錄原值精確還原
 *   (e) CSS：抽屜 override、浮動面板開啟瞬間 visibility 0s、面板 focus 無外框；不改核心 class 名
 *   (f) 版本兩處同步且 >= 1.2.2；5 原則不變
 *   (g) 最低核心版本：主檔常數＝README＝CHANGELOG 引用；detector／view 都用常數；Requires Plugins: ys-cart
 *   (h) 測試在 repo：.gitignore 不忽略 tests/；統一 runner 與四支 e2e fixture 存在；e2e runner 跑完清 .out/
 */

$root = dirname( __DIR__, 2 );
$pass = 0; $fail = 0;
$read = static function ( string $p ) use ( $root ): string {
	$full = $root . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $p );
	return is_file( $full ) ? str_replace( "\r\n", "\n", (string) file_get_contents( $full ) ) : '';
};
$check = static function ( string $name, bool $ok, string $detail = '' ) use ( &$pass, &$fail ): void {
	if ( $ok ) { $pass++; echo "  PASS  {$name}\n"; return; }
	$fail++; echo "  FAIL  {$name}" . ( '' !== $detail ? " [{$detail}]" : '' ) . "\n";
};
$code_lines = static fn ( string $src ): string => implode( "\n", array_filter( explode( "\n", $src ), static fn ( string $l ): bool => 1 !== preg_match( '/^\s*(\/\/|\*|\/\*|#)/', $l ) ) );

$options   = $read( 'header-items/ys-cart-cart/options.php' );
$config    = $read( 'header-items/ys-cart-cart/config.php' );
$view      = $read( 'header-items/ys-cart-cart/view.php' );
$plugin    = $read( 'src/YSCartBlocksyPlugin.php' );
$detector  = $read( 'src/YSBlocksyDetector.php' );
$js        = $read( 'assets/js/ys-cart-blocksy.js' );
$css       = $read( 'assets/css/ys-cart-blocksy.css' );
$main      = $read( 'ys-cart-blocksy.php' );
$readme    = $read( 'README.md' );
$changelog = $read( 'CHANGELOG.md' );
$gitignore = $read( '.gitignore' );
$runner    = $read( 'tests/run-e2e.php' );
$js_code   = $code_lines( $js );

echo "-- v1.2.x mini cart drawer (structure) --\n";

$check( 'a1：options 有 ys_cart_click_action（ct-radio，drawer／link，預設 drawer）',
	str_contains( $options, "'ys_cart_click_action' => [" )
		&& 1 === preg_match( "/'ys_cart_click_action' => \[\s*'label'[^\]]*'type'\s*=>\s*'ct-radio'[^\]]*'value'\s*=>\s*'drawer'/s", $options )
		&& str_contains( $options, "'drawer' => __( 'Open mini cart drawer'" )
		&& str_contains( $options, "'link'   => __( 'Go to cart page'" ) );
$check( 'a2：config selective_refresh 含 ys_cart_click_action', str_contains( $config, "'ys_cart_click_action'," ) );

$check( 'b1：view 輸出 data-ys-cart-blocksy-action 且只有 drawer／link 兩值',
	str_contains( $view, "\$ys_action = 'link' === (string) blocksy_akg( 'ys_cart_click_action', \$atts, 'drawer' ) ? 'link' : 'drawer';" )
		&& str_contains( $view, 'data-ys-cart-blocksy-action="<?php echo esc_attr( $ys_action ); ?>"' ) );
$check( 'b2：drawer 模式宣告 request_mini_cart_drawer()，href 仍指向購物車頁（no-JS 退路），aria-controls 指向核心面板',
	str_contains( $view, "if ( 'drawer' === \$ys_action && class_exists( '\\YangSheep\\CartBlocksy\\YSCartBlocksyPlugin' ) )" )
		&& str_contains( $view, 'YSCartBlocksyPlugin::request_mini_cart_drawer();' )
		&& str_contains( $view, 'href="<?php echo esc_url( $ys_cart_url ); ?>"' )
		&& str_contains( $view, 'aria-controls="ys-ec-mini-cart-panel"' ) );
$check( 'b3：核心低於最低版本時 view 退回 link（在 request_mini_cart_drawer 之前判）',
	1 === preg_match( "/if \( 'drawer' === \\\$ys_action && ! \\\\YangSheep\\\\CartBlocksy\\\\YSBlocksyDetector::core_supports_mini_cart_drawer\(\) \) \{\s*\\\$ys_action = 'link';\s*\}/", $view )
		&& strpos( $view, 'core_supports_mini_cart_drawer' ) < strpos( $view, 'request_mini_cart_drawer' ) );

$check( 'c1：wp_footer 優先序 20（在核心 YSMiniCart 之後）',
	str_contains( $plugin, "add_action( 'wp_footer', [ self::class, 'maybe_render_mini_cart_drawer' ], 20 );" ) );
$render = '';
if ( 1 === preg_match( '/public static function maybe_render_mini_cart_drawer\(\): void \{(.*?)\n\t\}\n/s', $plugin, $m ) ) { $render = $m[1]; }
$check( 'c2：核心浮動購物車開著時不輸出（避免兩份同 ID 的迷你購物車）',
	str_contains( $render, "is_feature_enabled( 'floating_cart' )" ) && 1 === preg_match( "/is_feature_enabled\( 'floating_cart' \) \) \{\s*return;/", $render ) );
$check( 'c3：與核心同一個 chrome opt-out 契約（ys_ec_render_standard_chrome piece=mini_cart）',
	str_contains( $render, "apply_filters( 'ys_ec_render_standard_chrome', true, [ 'piece' => 'mini_cart' ] )" ) );
$check( 'c4：用核心同一份樣板 templates/cart/mini-cart.php 與同一組變數，零複製購物車邏輯',
	str_contains( $render, "YS_ECOMMERCE_PATH . 'templates/cart/mini-cart.php'" )
		&& str_contains( $render, 'include $template_path;' )
		&& str_contains( $render, '$items        = $cart[\'items\']' )
		&& str_contains( $render, '$totals       = $cart[\'totals\']' )
		&& ! str_contains( $plugin, 'ys-ec-mini-cart-item' ) );
$check( 'c5：只在 drawer 被宣告時輸出（request_mini_cart_drawer 旗標），且 host 帶遮罩',
	str_contains( $render, 'if ( ! self::$drawer_requested' )
		&& str_contains( $render, 'data-ys-cart-blocksy-drawer-host' )
		&& str_contains( $render, 'data-ys-cart-blocksy-drawer-backdrop' ) );
$check( 'c6：JS 相依核心 ys-ec-cart script、CSS 相依核心 ys-ec-cart style（已註冊時）、JS in_footer',
	str_contains( $plugin, "wp_script_is( 'ys-ec-cart', 'registered' ) ? [ 'ys-ec-cart' ] : []" )
		&& str_contains( $plugin, "wp_style_is( 'ys-ec-cart', 'registered' ) ? [ 'ys-ec-cart' ] : []" ) );

$check( 'd1：JS 只在面板「真的能顯示」時攔截：drawerAvailable() 檢查 #ys-ec-mini-cart 與面板的 computed display',
	str_contains( $js_code, 'function drawerAvailable()' )
		&& str_contains( $js_code, "window.getComputedStyle(el).display !== 'none'" )
		&& str_contains( $js_code, 'if (!drawerAvailable()) return;' )
		&& str_contains( $js_code, 'event.preventDefault();' ) );
$check( 'd2：JS 點擊在 bubble phase、延後開關（setTimeout 0）、不 stopPropagation；capture 監聽只讀狀態',
	! str_contains( $js_code, 'stopPropagation' )
		&& 1 === substr_count( $js_code, '}, true);' )
		&& 1 === preg_match( "/document\.addEventListener\('click', function \(event\) \{\s*if \(findTrigger\(event\)\) openAtCapture = isOpen\(\);\s*\}, true\);/", $js_code )
		&& str_contains( $js_code, 'window.setTimeout(function () {' )
		&& str_contains( $js_code, 'if (wasOpen) { close(); } else { open(trigger); }' ) );
$check( 'd3：JS 修飾鍵／非左鍵放行（交給瀏覽器）',
	str_contains( $js_code, 'if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;' ) );
$check( 'd4：JS dialog 契約：role=dialog、tabindex=-1、aria-labelledby（核心標題）、modal 才 aria-modal',
	str_contains( $js_code, "p.setAttribute('role', 'dialog')" )
		&& str_contains( $js_code, "p.setAttribute('tabindex', '-1')" )
		&& str_contains( $js_code, "p.setAttribute('aria-labelledby', heading.id)" )
		&& str_contains( $js_code, "if (isModal() && !p.hasAttribute('aria-modal')) p.setAttribute('aria-modal', 'true');" ) );
$check( 'd5：JS modal 背景 inert（退 aria-hidden）＋關閉還原、Tab 焦點圈只在 modal、Esc 關閉',
	str_contains( $js_code, "sib.setAttribute('inert', '')" )
		&& str_contains( $js_code, "sib.setAttribute('aria-hidden', 'true')" )
		&& str_contains( $js_code, 'function restoreInert()' )
		&& str_contains( $js_code, "if (event.key !== 'Tab' || !isModal()) return;" )
		&& str_contains( $js_code, "event.key === 'Escape'" ) );
$check( 'd6：JS 焦點進面板／回 opener，狀態以 MutationObserver 同步核心造成的開關；無 fetch／XHR',
	str_contains( $js_code, 'function focusIntoPanel(' )
		&& str_contains( $js_code, 'opener = pendingOpener ||' )
		&& str_contains( $js_code, 'new MutationObserver(syncState)' )
		&& str_contains( $js_code, "var OPEN_CLASS = 'ys-ec-mini-cart-open';" )
		&& ! str_contains( $js_code, 'fetch(' ) && ! str_contains( $js_code, 'XMLHttpRequest' ) );
$check( 'd7：JS 以 MutationObserver（document.body，childList＋subtree）偵測新增的 trigger（selective refresh 重繪），去抖後 setExpanded(isOpen())；只在新增子樹含 trigger 時才同步',
	str_contains( $js_code, '.observe(document.body, { childList: true, subtree: true })' )
		&& str_contains( $js_code, 'addedNodes' )
		&& str_contains( $js_code, 'function containsTrigger(' )
		&& str_contains( $js_code, 'node.querySelector(TRIGGER_SELECTOR)' )
		&& str_contains( $js_code, 'window.clearTimeout(triggerSyncTimer)' )
		&& str_contains( $js_code, 'setExpanded(isOpen())' ) );
$check( 'd8：JS inert 退路記錄每個元素原本的 aria-hidden（null＝沒有屬性），關閉時精確還原（沒有→移除；有→設回原值）；原本 true 的不動',
	str_contains( $js_code, "var prev = sib.getAttribute('aria-hidden');" )
		&& str_contains( $js_code, "if (prev === 'true') return;" )
		&& str_contains( $js_code, 'rec.prevHidden = prev;' )
		&& str_contains( $js_code, "if (rec.prevHidden === null) { rec.el.removeAttribute('aria-hidden'); }" )
		&& str_contains( $js_code, "else { rec.el.setAttribute('aria-hidden', rec.prevHidden); }" ) );

$check( 'e1：CSS 隱藏核心浮動按鈕、面板貼右滿高、遮罩；不改核心 class 名',
	str_contains( $css, '.ys-cart-blocksy-drawer-host .ys-ec-mini-cart-toggle {' )
		&& str_contains( $css, 'display: none !important;' )
		&& str_contains( $css, '.ys-cart-blocksy-drawer-host .ys-ec-mini-cart-panel.ys-ec-mini-cart-open {' )
		&& str_contains( $css, '.ys-cart-blocksy-drawer-backdrop {' ) );
$check( 'e2：CSS 浮動面板開啟瞬間 visibility 0s（只在頁上有 drawer trigger 時）、面板 focus 無外框',
	str_contains( $css, 'body:has(a.ys-cart-blocksy-cart[data-ys-cart-blocksy-action="drawer"]) .ys-ec-mini-cart-panel.ys-ec-mini-cart-open {' )
		&& str_contains( $css, 'transition: opacity 0.25s ease, transform 0.25s ease, visibility 0s;' )
		&& str_contains( $css, '.ys-cart-blocksy-drawer-host .ys-ec-mini-cart-panel:focus {' ) );

preg_match( '/^\s*\*\s*Version:\s*([0-9.]+)/m', $main, $v1 );
preg_match( "/define\( 'YS_CART_BLOCKSY_VERSION', '([0-9.]+)' \)/", $main, $v2 );
$check( 'f1：版本兩處同步且 >= 1.2.2', ( $v1[1] ?? '' ) === ( $v2[1] ?? '' ) && version_compare( $v1[1] ?? '0', '1.2.2', '>=' ), ( $v1[1] ?? '?' ) . '/' . ( $v2[1] ?? '?' ) );
$plugin_code = $code_lines( $plugin );
$check( 'f2：5 原則不變：無 add_menu_page／register_rest_route／wp_ajax／admin-ajax／dbDelta（只掃程式碼行）',
	! str_contains( $plugin_code, 'add_menu_page' ) && ! str_contains( $plugin_code, 'register_rest_route' )
		&& ! str_contains( $plugin_code, 'wp_ajax' ) && ! str_contains( $plugin_code, 'admin-ajax' ) && ! str_contains( $plugin_code, 'dbDelta' ) );

preg_match( "/define\( 'YS_CART_BLOCKSY_DRAWER_MIN_CORE', '([0-9.]+)' \)/", $main, $mc );
$min_core = $mc[1] ?? '';
$changelog_121 = '';
if ( 1 === preg_match( '/## \[1\.2\.1\].*?(?=\n## \[)/s', $changelog, $cm ) ) { $changelog_121 = $cm[0]; }
$check( 'g1：最低核心版本只宣告一處（主檔常數），README 與 CHANGELOG [1.2.1] 引用同一個數字',
	'' !== $min_core
		&& str_contains( $readme, "YS CART ≥ {$min_core}" ) && str_contains( $readme, 'YS_CART_BLOCKSY_DRAWER_MIN_CORE' )
		&& str_contains( $changelog_121, "YS_CART_BLOCKSY_DRAWER_MIN_CORE = '{$min_core}'" )
		&& 1 === substr_count( $main, "'{$min_core}'" ),
	'min=' . $min_core );
$check( 'g2：detector core_supports_mini_cart_drawer() 以常數比對核心 YS_ECOMMERCE_VERSION；主檔 Requires Plugins: ys-cart',
	str_contains( $detector, 'public static function core_supports_mini_cart_drawer(): bool' )
		&& str_contains( $detector, "version_compare( (string) YS_ECOMMERCE_VERSION, (string) YS_CART_BLOCKSY_DRAWER_MIN_CORE, '>=' )" )
		&& str_contains( $main, " * Requires Plugins: ys-cart\n" ) );

$check( 'h1：.gitignore 不再忽略 tests/（乾淨 clone 可重跑）',
	'' !== $gitignore && 1 !== preg_match( '#^/?tests/?\s*$#m', $gitignore ) );
$check( 'h2：統一 runner 與 e2e runner／四支 fixture 在 repo',
	is_file( $root . '/tests/run.php' ) && is_file( $root . '/tests/run-e2e.php' )
		&& is_file( $root . '/tests/e2e/drawer-host-smoke.html' )
		&& is_file( $root . '/tests/e2e/drawer-checkout-smoke.html' )
		&& is_file( $root . '/tests/e2e/floating-mode-smoke.html' )
		&& is_file( $root . '/tests/e2e/inert-fallback-smoke.html' )
		&& str_contains( $read( 'tests/run.php' ), 'run-e2e.php' ) );
$check( 'h3：e2e runner 未設 YS_E2E_KEEP_OUT 時刪掉渲染檔與 Chrome profile，.out/ 空了就移除目錄（不留 ignored 垃圾）',
	str_contains( $runner, "getenv( 'YS_E2E_KEEP_OUT' )" )
		&& str_contains( $runner, '@unlink( $rendered );' )
		&& str_contains( $runner, '$rm( $profile );' )
		&& str_contains( $runner, 'rmdir( $out_dir )' ) );

echo "\nv1.2.x mini cart drawer (structure): {$pass} PASS / {$fail} FAIL\n";
exit( $fail > 0 || 0 === $pass ? 1 : 0 );
