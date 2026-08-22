<?php
/**
 * ys-cart-blocksy 統一 runner：一次跑
 *   1. tests/run-static.php          靜態契約（header items／5 原則）
 *   2. tests/regression/*.php        版本結構契約
 *   3. tests/run-e2e.php             真瀏覽器 e2e（需核心原始碼＋Chrome；缺＝exit 2，不是綠）
 *
 * exit 2：任一段相依缺（就算其他段全綠）；exit 1：任一段失敗；0：全綠。
 * 用法：php tests/run.php            （env：YS_CART_CORE_PATH、YS_E2E_CHROME 見 run-e2e.php）
 */

declare(strict_types=1);

$php   = PHP_BINARY;
$tests = __DIR__;

$run = static function ( string $label, array $cmd ): int {
	echo "\n### {$label}\n";
	$spec  = [ 0 => [ 'file', 'php://stdin', 'r' ], 1 => [ 'pipe', 'w' ], 2 => [ 'pipe', 'w' ] ];
	$proc  = proc_open( $cmd, $spec, $pipes );
	if ( ! is_resource( $proc ) ) { echo "  (cannot start)\n"; return 1; }
	echo stream_get_contents( $pipes[1] );
	$err = stream_get_contents( $pipes[2] );
	fclose( $pipes[1] ); fclose( $pipes[2] );
	$code = proc_close( $proc );
	if ( '' !== trim( $err ) ) { echo $err; }
	echo "  => exit {$code}\n";
	return $code;
};

$codes = [];
$codes['static'] = $run( 'static contract', [ $php, $tests . '/run-static.php' ] );
$regressions = glob( $tests . '/regression/*.php' ) ?: [];
sort( $regressions );
foreach ( $regressions as $file ) {
	$codes[ 'regression/' . basename( $file ) ] = $run( 'regression ' . basename( $file ), [ $php, $file ] );
}
$codes['e2e'] = $run( 'e2e (headless Chrome, real core JS/CSS/template)', [ $php, $tests . '/run-e2e.php' ] );

echo "\n=== summary\n";
$worst = 0;
foreach ( $codes as $name => $code ) {
	printf( "  %-44s %s\n", $name, 0 === $code ? 'OK' : ( 2 === $code ? 'MISSING PREREQUISITE' : 'FAIL' ) );
	if ( 2 === $code ) { $worst = 2; } elseif ( 0 !== $code && 2 !== $worst ) { $worst = 1; }
}
exit( $worst );
