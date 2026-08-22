<?php
/**
 * ys-cart-blocksy 真瀏覽器 e2e（headless Chrome `--dump-dom`）。
 *
 * 每支 `tests/e2e/*.html` 是一個 fixture 樣板：載入**真實核心** `assets/js/ys-ec-cart.js`／CSS、本外掛的
 * JS／CSS，以及核心 `templates/cart/mini-cart.php` 在這裡用最小 stub 渲染出來的真實標記；fixture 自己跑
 * 步驟並把 `{ done, pass, fail, results }` 寫進 `<script id="ys-e2e-results">`，本 runner 讀回判定。
 *
 * 相依（缺任一 → exit 2，不是綠）：
 *   - 核心原始碼：env `YS_CART_CORE_PATH`，預設 plugins-dev 兄弟目錄 `../ys-cart`
 *   - Chrome／Chromium：env `YS_E2E_CHROME`，預設自動找常見安裝路徑
 * 任一斷言失敗／fixture 沒跑完 → exit 1。
 *
 * 用法：php tests/run-e2e.php [fixture-name ...]
 *   env `YS_E2E_BLOCKSY_JS=/path/to/other.js`：以別的 JS 取代本外掛 JS（紅證：對 v1.2.0 的 JS 應紅）
 *   env `YS_E2E_KEEP_OUT=1`：保留 `tests/e2e/.out/` 的渲染結果
 */

declare(strict_types=1);

$root = dirname( __DIR__ );
$e2e  = __DIR__ . DIRECTORY_SEPARATOR . 'e2e';

$fail = static function ( string $msg, int $code ): never {
	fwrite( STDERR, "e2e: {$msg}\n" );
	exit( $code );
};

// ---- 相依：核心原始碼 ----
$core = (string) ( getenv( 'YS_CART_CORE_PATH' ) ?: dirname( $root ) . DIRECTORY_SEPARATOR . 'ys-cart' );
$core = rtrim( str_replace( '\\', '/', $core ), '/' );
foreach ( [ 'assets/js/ys-ec-cart.js', 'assets/css/ys-ec-cart.css', 'assets/css/ys-ec-checkout.css', 'templates/cart/mini-cart.php' ] as $rel ) {
	if ( ! is_file( $core . '/' . $rel ) ) {
		$fail( "YS CART 核心缺 {$rel}（YS_CART_CORE_PATH={$core}）", 2 );
	}
}

// ---- 相依：Chrome ----
$chrome = (string) ( getenv( 'YS_E2E_CHROME' ) ?: '' );
if ( '' === $chrome ) {
	$candidates = [
		'C:/Program Files/Google/Chrome/Application/chrome.exe',
		'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
		getenv( 'LOCALAPPDATA' ) ? str_replace( '\\', '/', (string) getenv( 'LOCALAPPDATA' ) ) . '/Google/Chrome/Application/chrome.exe' : '',
		'/usr/bin/google-chrome',
		'/usr/bin/google-chrome-stable',
		'/usr/bin/chromium',
		'/usr/bin/chromium-browser',
		'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
	];
	foreach ( $candidates as $candidate ) {
		if ( '' !== $candidate && is_file( $candidate ) ) { $chrome = $candidate; break; }
	}
}
if ( '' === $chrome || ! is_file( $chrome ) ) {
	$fail( 'Chrome 不存在（設 YS_E2E_CHROME）', 2 );
}

// ---- 渲染核心 mini-cart 樣板（真實標記） ----
require __DIR__ . '/e2e/support/wp-stubs.php';
$render_mini_cart = static function () use ( $core ): string {
	$items = [
		'k1' => [ 'title' => 'YS CART Demo 實體商品', 'variant_label' => '黑色／M', 'price' => 1280, 'qty' => 1, 'image' => '' ],
		'k2' => [ 'title' => '第二件商品', 'price' => 300, 'qty' => 2, 'image' => '' ],
	];
	$totals       = [ 'subtotal' => 1880, 'item_count' => 3 ];
	$checkout_url = '#ys-e2e-checkout';
	$cart_url     = '#ys-e2e-cart-page';
	ob_start();
	include $core . '/templates/cart/mini-cart.php';
	return (string) ob_get_clean();
};
$mini_cart_html = $render_mini_cart();
if ( ! str_contains( $mini_cart_html, 'id="ys-ec-mini-cart-panel"' ) || ! str_contains( $mini_cart_html, 'id="ys-ec-mini-cart-close"' ) ) {
	$fail( '核心 mini-cart 樣板渲染結果缺 #ys-ec-mini-cart-panel／#ys-ec-mini-cart-close', 2 );
}

// ---- fixture 清單 ----
$requested = array_slice( $argv, 1 );
$fixtures  = glob( $e2e . '/*.html' ) ?: [];
sort( $fixtures );
if ( [] !== $requested ) {
	$fixtures = array_values( array_filter( $fixtures, static fn ( string $f ): bool => in_array( basename( $f, '.html' ), $requested, true ) ) );
	if ( count( $fixtures ) !== count( $requested ) ) { $fail( '找不到指定的 fixture：' . implode( ',', $requested ), 2 ); }
}
if ( [] === $fixtures ) { $fail( 'tests/e2e/ 沒有 fixture', 2 ); }

$out_dir = $e2e . '/.out';
if ( ! is_dir( $out_dir ) && ! mkdir( $out_dir, 0777, true ) && ! is_dir( $out_dir ) ) { $fail( "無法建立 {$out_dir}", 2 ); }

$to_url = static fn ( string $path ): string => 'file:///' . ltrim( str_replace( '\\', '/', $path ), '/' );
$self_url = $to_url( $root );
$core_url = $to_url( $core );
$blocksy_js = (string) ( getenv( 'YS_E2E_BLOCKSY_JS' ) ?: '' );
$blocksy_js_url = '' !== $blocksy_js ? $to_url( realpath( $blocksy_js ) ?: $blocksy_js ) : $self_url . '/assets/js/ys-cart-blocksy.js';

$total_pass = 0; $total_fail = 0; $broken = 0;
foreach ( $fixtures as $fixture ) {
	$name = basename( $fixture, '.html' );
	$html = (string) file_get_contents( $fixture );
	$html = strtr( $html, [
		'{{CORE_URL}}'       => $core_url,
		'{{SELF_URL}}'       => $self_url,
		'{{BLOCKSY_JS_URL}}' => $blocksy_js_url,
		'{{MINI_CART_HTML}}' => $mini_cart_html,
	] );
	$rendered = $out_dir . '/' . $name . '.html';
	file_put_contents( $rendered, $html );
	$profile = $out_dir . '/profile-' . $name;
	$cmd = implode( ' ', [
		escapeshellarg( $chrome ),
		'--headless=new', '--disable-gpu', '--no-sandbox', '--allow-file-access-from-files',
		'--virtual-time-budget=10000', '--user-data-dir=' . escapeshellarg( $profile ),
		'--dump-dom', escapeshellarg( $to_url( $rendered ) ),
	] );
	$dom = (string) shell_exec( $cmd . ( '\\' === DIRECTORY_SEPARATOR ? ' 2>nul' : ' 2>/dev/null' ) );
	$decoded = null;
	if ( 1 === preg_match( '/<script[^>]*id="ys-e2e-results"[^>]*>(.*?)<\/script>/s', $dom, $m ) ) {
		$decoded = json_decode( html_entity_decode( trim( $m[1] ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ), true );
	}
	echo "== {$name}\n";
	if ( ! is_array( $decoded ) || empty( $decoded['done'] ) ) {
		$broken++;
		echo "  BROKEN  fixture 沒跑完或沒有結果（Chrome 輸出 " . strlen( $dom ) . " bytes）\n";
		continue;
	}
	foreach ( (array) ( $decoded['results'] ?? [] ) as $r ) {
		echo '  ' . ( ! empty( $r['ok'] ) ? 'PASS ' : 'FAIL ' ) . ' ' . (string) ( $r['name'] ?? '?' ) . "\n";
	}
	$p = (int) ( $decoded['pass'] ?? 0 ); $f = (int) ( $decoded['fail'] ?? 0 );
	$total_pass += $p; $total_fail += $f;
	echo "  -> {$p} PASS / {$f} FAIL\n";
	if ( ! getenv( 'YS_E2E_KEEP_OUT' ) ) {
		$rm = static function ( string $dir ) use ( &$rm ): void {
			foreach ( glob( $dir . '/{,.}*', GLOB_BRACE ) ?: [] as $e ) {
				if ( in_array( basename( $e ), [ '.', '..' ], true ) ) { continue; }
				is_dir( $e ) && ! is_link( $e ) ? $rm( $e ) : @unlink( $e );
			}
			@rmdir( $dir );
		};
		$rm( $profile );
	}
}

echo "\ne2e: {$total_pass} PASS / {$total_fail} FAIL / {$broken} BROKEN（core={$core}）\n";
exit( ( $total_fail > 0 || $broken > 0 || 0 === $total_pass ) ? 1 : 0 );
