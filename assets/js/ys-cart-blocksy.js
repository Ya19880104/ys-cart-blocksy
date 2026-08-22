/**
 * YS CART Blocksy 整合 — 頁首購物車 → 迷你購物車 drawer（v1.2.1）。
 *
 * 只做一件事：把「drawer 模式」的頁首購物車 icon 接到核心的迷你購物車面板
 * （#ys-ec-mini-cart-panel，核心 ys-ec-cart.js 以 .ys-ec-mini-cart-open 開關）。
 * 品項重繪、移除、badge、加入購物車後自動開啟，全部仍由核心 JS 負責——本檔不複製任何購物車邏輯。
 *
 * 狀態的唯一真值是面板的 .ys-ec-mini-cart-open（核心與本檔都只動它）；MutationObserver 把開／關的
 * 副作用（aria-expanded、背景 inert、焦點進出）同步到任何一方造成的變化。
 *
 * v1.2.1（R48 #2／#3／Minor）
 *  - 只在面板「真的能顯示」時才攔截點擊：面板不存在、或 #ys-ec-mini-cart／面板被 CSS display:none
 *    藏起來（核心結帳頁就是這樣藏的）→ 不 preventDefault，<a href> 照常前往購物車頁。
 *  - dialog 契約：面板補 role="dialog"、aria-labelledby（核心樣板的標題）。我們自己輸出的抽屜（有
 *    drawer host）是 modal：aria-modal、背景 inert（不支援 inert 的瀏覽器退 aria-hidden）、Tab 焦點圈；
 *    核心右下角浮動面板是非 modal popover：只做 role／名稱／焦點進出，不鎖背景。
 *    開啟時焦點進面板；關閉時焦點回 opener（核心自動開啟時 opener＝當時的焦點元素，通常是加入購物車按鈕）。
 *  - 點擊改在 bubble phase、不 stopPropagation（佈景／analytics 的 document 監聽照常收到）；開關延後到
 *    這次事件派送結束（setTimeout 0），核心「點面板外就關閉」的 document 監聽不會把剛開的面板又關掉。
 *    開／關的依據在 capture phase 先讀（核心監聽可能在我們之前就把面板關了）。
 */
(function () {
	'use strict';

	var OPEN_CLASS = 'ys-ec-mini-cart-open';
	var TRIGGER_SELECTOR = 'a.ys-cart-blocksy-cart[data-ys-cart-blocksy-action="drawer"]';
	var TITLE_ID = 'ys-ec-mini-cart-title';
	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
	var SKIP_INERT_TAGS = { SCRIPT: 1, STYLE: 1, LINK: 1, TEMPLATE: 1, NOSCRIPT: 1, META: 1 };

	var stateOpen = false;     // 上次同步到的開關狀態
	var pendingOpener = null;  // open() 帶進來的 opener（核心自己開的沒有）
	var opener = null;         // 關閉時要把焦點還給誰
	var inerted = [];          // 本檔加上 inert／aria-hidden 的元素（關閉時還原）
	var openAtCapture = false; // 點擊 trigger 當下（capture phase）面板是否已開

	function panel() { return document.getElementById('ys-ec-mini-cart-panel'); }
	function wrapper() { return document.getElementById('ys-ec-mini-cart'); }
	function host() { return document.querySelector('[data-ys-cart-blocksy-drawer-host]'); }
	function isModal() { return !!host(); }
	function triggers() { return document.querySelectorAll(TRIGGER_SELECTOR); }

	function findTrigger(event) {
		var t = event.target;
		if (!t || typeof t.closest !== 'function') return null;
		return t.closest(TRIGGER_SELECTOR);
	}

	function displayed(el) {
		return !!el && window.getComputedStyle(el).display !== 'none';
	}

	/** 面板「真的能顯示」：存在，且它與 #ys-ec-mini-cart 都沒被 CSS display:none 藏起來（核心結帳頁）。 */
	function drawerAvailable() {
		var p = panel(), w = wrapper();
		return !!p && !!w && displayed(w) && displayed(p);
	}

	function isOpen() {
		var p = panel();
		return !!p && p.classList.contains(OPEN_CLASS);
	}

	function setExpanded(open) {
		triggers().forEach(function (el) {
			el.setAttribute('aria-expanded', open ? 'true' : 'false');
		});
		var h = host();
		if (h) h.classList.toggle('ys-cart-blocksy-drawer-host--open', !!open);
	}

	function focusEl(el) {
		if (!el || typeof el.focus !== 'function') return false;
		try { el.focus({ preventScroll: true }); } catch (e) { el.focus(); }
		return document.activeElement === el;
	}

	function visibleFocusables(p) {
		return Array.prototype.filter.call(p.querySelectorAll(FOCUSABLE), function (el) {
			return el.getClientRects().length > 0 && window.getComputedStyle(el).visibility !== 'hidden';
		});
	}

	/**
	 * 焦點進面板：第一個可聚焦元素，沒有就面板本身（tabindex=-1）。
	 * 核心浮動面板的 visibility 走 transition（開啟瞬間還是 hidden、聚焦會被瀏覽器拒絕），所以失敗就
	 * 隔幾十毫秒再試，最多約 0.4 秒；期間面板被關掉就放棄。
	 */
	function focusIntoPanel(p, attempt) {
		if (!stateOpen || !isOpen()) return;
		if (focusEl(visibleFocusables(p)[0]) || focusEl(p)) return;
		if (attempt >= 8) return;
		window.setTimeout(function () { focusIntoPanel(p, attempt + 1); }, 50);
	}

	/** 核心樣板沒有 dialog 語意；這裡只在缺的時候補（將來核心樣板自帶就以核心為準）。 */
	function prepareDialog() {
		var p = panel();
		if (!p) return;
		if (!p.hasAttribute('role')) p.setAttribute('role', 'dialog');
		if (!p.hasAttribute('tabindex')) p.setAttribute('tabindex', '-1');
		if (isModal() && !p.hasAttribute('aria-modal')) p.setAttribute('aria-modal', 'true');
		if (!p.hasAttribute('aria-labelledby') && !p.hasAttribute('aria-label')) {
			var heading = p.querySelector('.ys-ec-mini-cart-header h1, .ys-ec-mini-cart-header h2, .ys-ec-mini-cart-header h3, .ys-ec-mini-cart-header h4, .ys-ec-mini-cart-header h5');
			if (heading) {
				if (!heading.id) heading.id = TITLE_ID;
				p.setAttribute('aria-labelledby', heading.id);
			} else {
				var trig = triggers()[0];
				p.setAttribute('aria-label', (trig && trig.getAttribute('aria-label')) || 'Cart');
			}
		}
	}

	/** modal：從 drawer host 往上到 body，每一層的兄弟節點都 inert（遮罩在 host 內，不受影響）。 */
	function applyInert() {
		restoreInert();
		var root = host() || wrapper();
		if (!root) return;
		var supportsInert = 'inert' in HTMLElement.prototype;
		var node = root;
		while (node && node !== document.body && node.parentElement) {
			var parent = node.parentElement;
			Array.prototype.forEach.call(parent.children, function (sib) {
				if (sib === node || SKIP_INERT_TAGS[sib.tagName]) return;
				var rec = { el: sib, inert: false, hidden: false };
				if (supportsInert) {
					if (sib.hasAttribute('inert')) return; // 別人加的，不動
					sib.setAttribute('inert', '');
					rec.inert = true;
				} else {
					if (sib.getAttribute('aria-hidden') === 'true') return;
					sib.setAttribute('aria-hidden', 'true');
					rec.hidden = true;
				}
				inerted.push(rec);
			});
			node = parent;
		}
	}

	function restoreInert() {
		inerted.forEach(function (rec) {
			if (rec.inert) rec.el.removeAttribute('inert');
			if (rec.hidden) rec.el.removeAttribute('aria-hidden');
		});
		inerted = [];
	}

	function enterOpen() {
		var p = panel();
		setExpanded(true);
		if (!p || !drawerAvailable()) {
			// 核心在面板被藏起來的頁面自動開啟：只同步 aria，不鎖背景、不搬焦點。
			pendingOpener = null;
			return;
		}
		var active = document.activeElement;
		opener = pendingOpener || (active && active !== document.body && !p.contains(active) ? active : null);
		pendingOpener = null;
		if (isModal()) applyInert();
		focusIntoPanel(p, 0);
	}

	function leaveOpen() {
		var p = panel();
		setExpanded(false);
		restoreInert();
		var active = document.activeElement;
		var lost = !active || active === document.body || (!!p && p.contains(active));
		if (lost) {
			// 焦點還給 opener；opener 聚焦不了（已移除／被 Blocksy 響應式 CSS 藏起來的另一份 header）就回到
			// 可見的頁首 icon；都不行就至少別讓焦點停在已隱藏的面板裡。
			var returned = !!opener && document.contains(opener) && focusEl(opener);
			if (!returned) {
				Array.prototype.some.call(triggers(), function (el) {
					returned = el.getClientRects().length > 0 && focusEl(el);
					return returned;
				});
			}
			if (!returned && active && active !== document.body && typeof active.blur === 'function') {
				active.blur();
			}
		}
		opener = null;
	}

	function syncState() {
		var now = isOpen();
		if (now === stateOpen) return;
		stateOpen = now;
		if (now) { enterOpen(); } else { leaveOpen(); }
	}

	function open(byTrigger) {
		var p = panel();
		if (!p || !drawerAvailable()) return false;
		pendingOpener = byTrigger || null;
		p.classList.add(OPEN_CLASS);
		syncState();
		return true;
	}

	function close() {
		var p = panel();
		if (!p) return;
		p.classList.remove(OPEN_CLASS);
		syncState();
	}

	// capture：只讀狀態、不碰事件（核心的 outside-click 監聽可能在我們的 bubble 監聽之前就把面板關了）。
	document.addEventListener('click', function (event) {
		if (findTrigger(event)) openAtCapture = isOpen();
	}, true);

	// 頁首 icon：委派到 document（Blocksy header 可能被 customizer selective refresh 重繪）。
	document.addEventListener('click', function (event) {
		var trigger = findTrigger(event);
		if (!trigger) return;
		// 修飾鍵／非左鍵：交給瀏覽器（新分頁開購物車頁）。
		if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
		if (!drawerAvailable()) return; // 面板不存在／被藏（結帳頁）→ 走 href 前往購物車頁
		event.preventDefault();
		var wasOpen = openAtCapture;
		// 延後到這次事件派送結束：核心「點面板外就關閉」跑完後才開，不會被它關掉；
		// 也不需要 stopPropagation（佈景／analytics 的監聽照常收到這次點擊）。
		window.setTimeout(function () {
			if (wasOpen) { close(); } else { open(trigger); }
		}, 0);
	});

	// 背景遮罩關閉（遮罩在 #ys-ec-mini-cart 之外，核心的 outside-click 也會關；這裡同步狀態）。
	document.addEventListener('click', function (event) {
		var t = event.target;
		if (t && t.hasAttribute && t.hasAttribute('data-ys-cart-blocksy-drawer-backdrop')) close();
	});

	// Esc 關閉；modal 時 Tab 在面板內循環。
	document.addEventListener('keydown', function (event) {
		if (!isOpen()) return;
		if (event.key === 'Escape' || event.key === 'Esc') {
			event.preventDefault();
			close();
			return;
		}
		if (event.key !== 'Tab' || !isModal()) return;
		var p = panel();
		if (!p) return;
		var items = visibleFocusables(p);
		if (!items.length) { event.preventDefault(); focusEl(p); return; }
		var first = items[0], last = items[items.length - 1], active = document.activeElement;
		if (!p.contains(active)) { event.preventDefault(); focusEl(event.shiftKey ? last : first); return; }
		if (event.shiftKey && (active === first || active === p)) { event.preventDefault(); focusEl(last); }
		else if (!event.shiftKey && active === last) { event.preventDefault(); focusEl(first); }
	});

	// 核心自己開關面板時（加入購物車自動開啟、× 關閉、點外面關閉）同步副作用。
	function init() {
		prepareDialog();
		var p = panel();
		if (p && window.MutationObserver) {
			new MutationObserver(syncState).observe(p, { attributes: true, attributeFilter: ['class'] });
		}
		syncState();
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	window.YsCartBlocksy = window.YsCartBlocksy || {};
	window.YsCartBlocksy.miniCart = { open: open, close: close, isOpen: isOpen, available: drawerAvailable };
})();
