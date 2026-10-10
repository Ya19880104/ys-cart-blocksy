<?php
/**
 * v1.2.6：YS CART 商品詳情頁不輸出 Blocksy 頁面標題（DEMO SITE #5(2) 雙 H1）。
 *
 * 行為（每案獨立行程，boot 一次）：
 *   - 商品詳情路由：blocksy:single:has-default-hero 回 false（hero 不輸出）。
 *   - 商店列表、分類頁、其他頁面：回傳 filter 進來的原值。
 *   - 核心有 YSPageResolver::is_product_detail_request() 時只用它；沒有時用 fallback。
 *   - Blocksy 或 YS CART 不在時不註冊。
 *
 * 必紅（只在拋棄式工作樹改壞）：
 *   (a) 拿掉路由條件，每一頁都回 false → 商店／分類／其他頁變紅。
 *   (b) 拿掉 add_filter → 商品頁仍是進來的 true，且 registered=no。
 *   (c) fallback 改成永遠 false → 沒有核心方法的商品路由不再關 hero。
 *
 * 此檔在實作之前對 v1.2.5 為紅。
 */

declare(strict_types=1);

function ys_v126_child(): void {
	$root = dirname( __DIR__, 2 );
	if ( ! defined( 'ABSPATH' ) ) {
		define( 'ABSPATH', $root . DIRECTORY_SEPARATOR );
	}

	$GLOBALS['ys_v126_filters'] = [];

	function plugin_dir_path( string $file ): string {
		return dirname( $file ) . DIRECTORY_SEPARATOR;
	}
	function plugin_dir_url( string $file ): string {
		return 'http://example.test/wp-content/plugins/ys-cart-blocksy/';
	}
	function plugin_basename( string $file ): string {
		return 'ys-cart-blocksy/ys-cart-blocksy.php';
	}
	function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ): void {
		unset( $priority, $accepted_args );
		$GLOBALS['ys_v126_filters'][ (string) $hook ][] = $callback;
	}
	function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ): void {
		add_filter( $hook, $callback, $priority, $accepted_args );
	}
	function apply_filters( $hook, $value ) {
		foreach ( $GLOBALS['ys_v126_filters'][ (string) $hook ] ?? [] as $callback ) {
			$value = $callback( $value );
		}
		return $value;
	}
	function has_filter( $hook, $callback = false ): bool {
		unset( $callback );
		return isset( $GLOBALS['ys_v126_filters'][ (string) $hook ] )
			&& [] !== $GLOBALS['ys_v126_filters'][ (string) $hook ];
	}
	function get_template(): string {
		$template = getenv( 'YS_V126_TEMPLATE' );
		return is_string( $template ) ? $template : '';
	}
	function get_query_var( $key, $default = '' ) {
		if ( 'ys_ec_product_slug' !== (string) $key || ! array_key_exists( 'ys_v126_slug', $GLOBALS ) ) {
			return $default;
		}
		return $GLOBALS['ys_v126_slug'];
	}
	function sanitize_title( $title ): string {
		$title = strtolower( trim( (string) $title ) );
		$title = preg_replace( '/[^a-z0-9_-]+/', '-', $title );
		return trim( (string) $title, '-' );
	}
	function wp_unslash( $value ) {
		return $value;
	}
	function did_action( $hook ): int {
		unset( $hook );
		return 0;
	}
	function get_option( $option, $default = false ) {
		unset( $option );
		return $default;
	}
	function register_activation_hook( $file, $callback ): void {
		unset( $file, $callback );
	}

	if ( '1' === getenv( 'YS_V126_HAS_CART' ) && ! defined( 'YS_ECOMMERCE_VERSION' ) ) {
		define( 'YS_ECOMMERCE_VERSION', '9.9.9-test' );
	}

	if ( 'present' === getenv( 'YS_V126_CORE' ) ) {
		eval( <<<'PHP'
namespace YangSheep\Ecommerce\Services\Setup {
	class YSPageResolver {
		public static function is_product_detail_request(): bool {
			return getenv( 'YS_V126_CORE_SAYS' ) === '1';
		}
	}
}
PHP
		);
	}

	$slug_kind = getenv( 'YS_V126_SLUG_KIND' );
	if ( 'array' === $slug_kind ) {
		$GLOBALS['ys_v126_slug'] = [ 'not-a-string' ];
	} elseif ( is_string( $slug_kind ) && 'string' === $slug_kind ) {
		$GLOBALS['ys_v126_slug'] = (string) getenv( 'YS_V126_SLUG' );
	}

	$get_product = getenv( 'YS_V126_GET' );
	if ( is_string( $get_product ) ) {
		$_GET['product'] = $get_product;
	}
	$uri = getenv( 'YS_V126_URI' );
	$_SERVER['REQUEST_URI'] = is_string( $uri ) ? $uri : '/';

	require $root . DIRECTORY_SEPARATOR . 'ys-cart-blocksy.php';
	\YangSheep\CartBlocksy\YSCartBlocksyPlugin::boot();

	$incoming_raw = getenv( 'YS_V126_INCOMING' );
	if ( '0' === $incoming_raw ) {
		$incoming = false;
	} elseif ( ! is_string( $incoming_raw ) || '' === $incoming_raw || '1' === $incoming_raw ) {
		$incoming = true;
	} else {
		$incoming = $incoming_raw;
	}

	$actual     = apply_filters( 'blocksy:single:has-default-hero', $incoming );
	$registered = has_filter( 'blocksy:single:has-default-hero' );

	$expect_raw = getenv( 'YS_V126_EXPECT' );
	if ( 'false' === $expect_raw ) {
		$expected = false;
	} elseif ( 'true' === $expect_raw ) {
		$expected = true;
	} else {
		$expected = $expect_raw;
	}
	$want_registered = 'yes' === getenv( 'YS_V126_REGISTERED' );

	$ok = ( $actual === $expected ) && ( $registered === $want_registered );
	echo $ok ? "PASS\n" : "FAIL\n";
	echo 'actual=' . ys_v126_export( $actual ) . ' expected=' . ys_v126_export( $expected );
	echo ' registered=' . ( $registered ? 'yes' : 'no' );
	echo ' want_registered=' . ( $want_registered ? 'yes' : 'no' ) . "\n";
	exit( $ok ? 0 : 1 );
}

function ys_v126_export( $value ): string {
	if ( true === $value ) {
		return 'true';
	}
	if ( false === $value ) {
		return 'false';
	}
	return var_export( $value, true );
}

if ( '1' === getenv( 'YS_V126_CHILD' ) ) {
	ys_v126_child();
}

$root = dirname( __DIR__, 2 );
$pass = 0;
$fail = 0;
$read = static function ( string $rel ) use ( $root ): string {
	$full = $root . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $rel );
	if ( ! is_file( $full ) ) {
		return '';
	}
	return str_replace( "\r\n", "\n", (string) file_get_contents( $full ) );
};
$check = static function ( string $name, bool $ok, string $detail = '' ) use ( &$pass, &$fail ): void {
	$line = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS, 1 )[0]['line'] ?? 0;
	if ( $ok ) {
		$pass++;
		echo "  PASS  {$name} (line {$line})\n";
		return;
	}
	$fail++;
	echo "  FAIL  {$name} (line {$line})" . ( '' !== $detail ? " [{$detail}]" : '' ) . "\n";
};

$run_case = static function ( string $name, array $env ) use ( $root, $check ): void {
	$base = getenv();
	if ( ! is_array( $base ) ) {
		$base = [];
	}
	$base['YS_V126_CHILD'] = '1';
	foreach ( $env as $key => $value ) {
		$base[ $key ] = $value;
	}
	$spec = [ 0 => [ 'pipe', 'r' ], 1 => [ 'pipe', 'w' ], 2 => [ 'pipe', 'w' ] ];
	$proc = proc_open( [ PHP_BINARY, __FILE__ ], $spec, $pipes, $root, $base );
	if ( ! is_resource( $proc ) ) {
		$check( $name, false, 'cannot start child' );
		return;
	}
	fclose( $pipes[0] );
	$out  = stream_get_contents( $pipes[1] );
	$err  = stream_get_contents( $pipes[2] );
	fclose( $pipes[1] );
	fclose( $pipes[2] );
	$code = proc_close( $proc );
	$err  = trim( (string) $err );
	$ok   = 0 === $code && '' === $err;
	$check( $name, $ok, trim( (string) $out ) . ( '' !== $err ? " stderr={$err}" : '' ) );
};

echo "-- v1.2.6 product hero --\n";

$hero      = $read( 'src/YSBlocksyProductHero.php' );
$plugin    = $read( 'src/YSCartBlocksyPlugin.php' );
$css       = $read( 'assets/css/ys-cart-blocksy.css' );
$main      = $read( 'ys-cart-blocksy.php' );
$changelog = $read( 'CHANGELOG.md' );
$readme    = $read( 'README.md' );

$check(
	'src：商品頁 hero 只在 ready() 時註冊 blocksy:single:has-default-hero，核心方法存在就用核心',
	str_contains( $hero, "add_filter( 'blocksy:single:has-default-hero', [ self::class, 'filter_has_default_hero' ] )" )
		&& str_contains( $hero, 'if ( ! YSBlocksyDetector::ready() ) {' )
		&& str_contains( $hero, "method_exists( \$resolver, 'is_product_detail_request' )" )
		&& str_contains( $hero, '核心 2.69.10 以後改用核心判斷' )
		&& str_contains( $plugin, 'YSBlocksyProductHero::register();' )
);
$check(
	'src：不輸出標題的方式不是 CSS，也不改頁面 post meta',
	'' !== $hero
		&& ! str_contains( $hero, 'display:' )
		&& ! str_contains( $hero, 'wp_add_inline_style' )
		&& ! str_contains( $hero, 'update_post_meta' )
		&& ! str_contains( $hero, 'has_hero_section' )
		&& ! str_contains( $css, 'hero-section' )
		&& ! preg_match( '/add_menu_page|register_rest_route|wp_ajax_|admin-ajax|dbDelta/', $hero )
);

preg_match( '/^\s*\*\s*Version:\s*([0-9.]+)/m', $main, $header_version );
preg_match( "/define\( 'YS_CART_BLOCKSY_VERSION', '([0-9.]+)' \)/", $main, $constant_version );
$check(
	'版本 1.2.6：入口 Version 與 YS_CART_BLOCKSY_VERSION 相同',
	'1.2.6' === ( $header_version[1] ?? '' ) && '1.2.6' === ( $constant_version[1] ?? '' ),
	( $header_version[1] ?? '?' ) . '/' . ( $constant_version[1] ?? '?' )
);
$check(
	'CHANGELOG 1.2.6 說明商品頁不再有兩個標題，商店與分類頁不變',
	str_contains( $changelog, '## [1.2.6] - 2026-10-10' )
		&& str_contains( $changelog, '商品頁不再出現兩個標題（H1）：在 YS CART 商品頁不輸出 Blocksy 的頁面標題；商店與分類頁不變。' )
);
$check(
	'README：目前版本 1.2.6，並寫明 2.69.10 以後用核心判斷',
	str_contains( $readme, '目前版本：**1.2.6**' )
		&& str_contains( $readme, '2.69.10' )
		&& str_contains( $readme, 'is_product_detail_request' )
);

$ready = [
	'YS_V126_TEMPLATE' => 'blocksy',
	'YS_V126_HAS_CART' => '1',
];

$run_case( 'core-product：核心判定是商品頁時 filter 回 false（路徑仍是商店列表）', $ready + [
	'YS_V126_CORE'       => 'present',
	'YS_V126_CORE_SAYS'  => '1',
	'YS_V126_URI'        => '/shop/',
	'YS_V126_EXPECT'     => 'false',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'core-path：核心判定不是商品頁時，路徑 shop/tea 也不關', $ready + [
	'YS_V126_CORE'       => 'present',
	'YS_V126_CORE_SAYS'  => '0',
	'YS_V126_URI'        => '/shop/tea/',
	'YS_V126_EXPECT'     => 'true',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'fallback-query：沒有核心方法時，ys_ec_product_slug 關 hero', $ready + [
	'YS_V126_CORE'       => 'absent',
	'YS_V126_SLUG_KIND'  => 'string',
	'YS_V126_SLUG'       => 'tea',
	'YS_V126_URI'        => '/shop/',
	'YS_V126_EXPECT'     => 'false',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'fallback-get：沒有核心方法時，?product= 關 hero', $ready + [
	'YS_V126_CORE'       => 'absent',
	'YS_V126_GET'        => 'oolong',
	'YS_V126_URI'        => '/shop/',
	'YS_V126_EXPECT'     => 'false',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'fallback-path：沒有核心方法時，路徑 shop/<slug> 關 hero', $ready + [
	'YS_V126_CORE'       => 'absent',
	'YS_V126_URI'        => '/shop/tea/',
	'YS_V126_EXPECT'     => 'false',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'shop：商店列表回傳原值 true', $ready + [
	'YS_V126_CORE'       => 'absent',
	'YS_V126_URI'        => '/shop/',
	'YS_V126_EXPECT'     => 'true',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'category-index：路徑 shop/category 不算商品頁', $ready + [
	'YS_V126_CORE'       => 'absent',
	'YS_V126_URI'        => '/shop/category/',
	'YS_V126_EXPECT'     => 'true',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'category：分類頁 shop/category/tea 回傳原值', $ready + [
	'YS_V126_CORE'       => 'absent',
	'YS_V126_URI'        => '/shop/category/tea/',
	'YS_V126_INCOMING'   => 'YS-KEEP',
	'YS_V126_EXPECT'     => 'YS-KEEP',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'other：非 YS CART 頁面回傳原值', $ready + [
	'YS_V126_CORE'       => 'absent',
	'YS_V126_URI'        => '/about/',
	'YS_V126_INCOMING'   => 'YS-KEEP',
	'YS_V126_EXPECT'     => 'YS-KEEP',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'keep-false：非商品頁進來已是 false 就維持 false', $ready + [
	'YS_V126_CORE'       => 'absent',
	'YS_V126_URI'        => '/shop/',
	'YS_V126_INCOMING'   => '0',
	'YS_V126_EXPECT'     => 'false',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'fallback-nonstring：query var 不是字串時不算商品頁（即使路徑像商品）', $ready + [
	'YS_V126_CORE'       => 'absent',
	'YS_V126_SLUG_KIND'  => 'array',
	'YS_V126_URI'        => '/shop/tea/',
	'YS_V126_EXPECT'     => 'true',
	'YS_V126_REGISTERED' => 'yes',
] );
$run_case( 'absent-blocksy：不是 Blocksy 就不註冊', [
	'YS_V126_TEMPLATE'   => 'twentytwentyfive',
	'YS_V126_HAS_CART'   => '1',
	'YS_V126_CORE'       => 'absent',
	'YS_V126_URI'        => '/shop/tea/',
	'YS_V126_EXPECT'     => 'true',
	'YS_V126_REGISTERED' => 'no',
] );
$run_case( 'absent-cart：沒有 YS CART 就不註冊', [
	'YS_V126_TEMPLATE'   => 'blocksy',
	'YS_V126_HAS_CART'   => '0',
	'YS_V126_CORE'       => 'absent',
	'YS_V126_SLUG_KIND'  => 'string',
	'YS_V126_SLUG'       => 'tea',
	'YS_V126_URI'        => '/shop/',
	'YS_V126_EXPECT'     => 'true',
	'YS_V126_REGISTERED' => 'no',
] );

echo "\nv1.2.6 product hero: {$pass} PASS / {$fail} FAIL\n";
exit( $fail > 0 || 0 === $pass ? 1 : 0 );
