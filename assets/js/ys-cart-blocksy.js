/**
 * YS CART Blocksy 整合 — 頁首購物車 → 迷你購物車 drawer（v1.2.0）。
 *
 * 只做一件事：把「drawer 模式」的頁首購物車 icon 接到核心的迷你購物車面板
 * （#ys-ec-mini-cart-panel，核心 ys-ec-cart.js 以 .ys-ec-mini-cart-open 開關）。
 * 品項重繪、移除、badge、加入購物車後自動開啟，全部仍由核心 JS 負責——本檔不複製任何購物車邏輯。
 *
 * 退路：面板不在頁上（核心樣板缺、chrome 被 opt-out、JS 太早執行）時不攔截點擊，讓 <a href> 照常前往購物車頁。
 */
(function () {
	'use strict';

	var OPEN_CLASS = 'ys-ec-mini-cart-open';

	function panel() {
		return document.getElementById('ys-ec-mini-cart-panel');
	}

	function triggers() {
		return document.querySelectorAll('a.ys-cart-blocksy-cart[data-ys-cart-blocksy-action="drawer"]');
	}

	function setExpanded(isOpen) {
		triggers().forEach(function (el) {
			el.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});
		var host = document.querySelector('[data-ys-cart-blocksy-drawer-host]');
		if (host) {
			host.classList.toggle('ys-cart-blocksy-drawer-host--open', !!isOpen);
		}
	}

	function isOpen() {
		var p = panel();
		return !!p && p.classList.contains(OPEN_CLASS);
	}

	function open() {
		var p = panel();
		if (!p) return false;
		p.classList.add(OPEN_CLASS);
		setExpanded(true);
		var close = document.getElementById('ys-ec-mini-cart-close');
		if (close && typeof close.focus === 'function') {
			try { close.focus({ preventScroll: true }); } catch (e) { close.focus(); }
		}
		return true;
	}

	function close() {
		var p = panel();
		if (!p) return;
		p.classList.remove(OPEN_CLASS);
		setExpanded(false);
	}

	// 頁首 icon：委派到 document（Blocksy header 可能被 customizer selective refresh 重繪）。
	document.addEventListener('click', function (event) {
		var trigger = event.target.closest ? event.target.closest('a.ys-cart-blocksy-cart[data-ys-cart-blocksy-action="drawer"]') : null;
		if (!trigger) return;
		if (!panel()) return; // 沒有面板 → 走 href 前往購物車頁
		event.preventDefault();
		// 核心在 document 上有「點擊 #ys-ec-mini-cart 以外就關閉」的監聽；同一個點擊事件冒泡到
		// document 會把剛開的面板又關掉，所以這裡停止冒泡。
		event.stopPropagation();
		if (isOpen()) { close(); } else { open(); }
	}, true);

	// 背景遮罩與 Esc 關閉（遮罩在 #ys-ec-mini-cart 之外，核心的 outside-click 也會關；這裡同步 aria）。
	document.addEventListener('click', function (event) {
		if (event.target && event.target.hasAttribute && event.target.hasAttribute('data-ys-cart-blocksy-drawer-backdrop')) {
			close();
		}
	});
	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape' && isOpen()) { close(); }
	});

	// 核心自己開關面板時（加入購物車自動開啟、× 關閉、點外面關閉）同步 aria-expanded／host 狀態。
	function observePanel() {
		var p = panel();
		if (!p || !window.MutationObserver) return;
		new MutationObserver(function () { setExpanded(isOpen()); })
			.observe(p, { attributes: true, attributeFilter: ['class'] });
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', observePanel);
	} else {
		observePanel();
	}

	window.YsCartBlocksy = window.YsCartBlocksy || {};
	window.YsCartBlocksy.miniCart = { open: open, close: close, isOpen: isOpen };
})();
