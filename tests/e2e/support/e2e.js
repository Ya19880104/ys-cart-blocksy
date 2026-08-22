/* e2e fixture 共用：斷言、等待、收尾（結果寫進 <script id="ys-e2e-results">，runner 讀回）。 */
window.__YS_E2E__ = { results: [], pass: 0, fail: 0, done: false };
window.ysE2E = {
	assert: function (name, cond, detail) {
		var ok = !!cond;
		window.__YS_E2E__.results.push({ name: name + (ok || detail === undefined ? '' : ' [' + String(detail) + ']'), ok: ok });
		if (ok) { window.__YS_E2E__.pass++; } else { window.__YS_E2E__.fail++; }
	},
	tick: function (ms) { return new Promise(function (r) { setTimeout(r, ms || 25); }); },
	key: function (target, key, opts) {
		var ev = new KeyboardEvent('keydown', Object.assign({ key: key, bubbles: true, cancelable: true }, opts || {}));
		(target || document).dispatchEvent(ev);
		return ev;
	},
	finish: function () {
		var e2e = window.__YS_E2E__;
		e2e.done = true;
		var el = document.getElementById('ys-e2e-results');
		if (el) { el.textContent = JSON.stringify(e2e); }
		var box = document.getElementById('result');
		if (box) {
			box.innerHTML = '<h2 class="' + (e2e.fail === 0 ? 'pass' : 'fail') + '">' + (e2e.fail === 0 ? '✓ ALL PASS' : '✗ FAIL') + ' (' + e2e.pass + '/' + (e2e.pass + e2e.fail) + ')</h2>'
				+ e2e.results.map(function (r) { return '<div class="row ' + (r.ok ? 'pass' : 'fail') + '">' + (r.ok ? '✓' : '✗') + ' ' + r.name + '</div>'; }).join('');
		}
	},
	run: function (steps) {
		var start = function () {
			Promise.resolve().then(function () { return window.ysE2E.tick(50); }).then(steps).catch(function (err) {
				window.ysE2E.assert('fixture 執行中拋出例外', false, err && err.message ? err.message : String(err));
			}).then(window.ysE2E.finish);
		};
		if (document.readyState === 'complete') { start(); } else { window.addEventListener('load', start); }
	}
};
