# Changelog

## [1.2.4] - 2026-09-13

### Changed

- 內嵌 YS Plugin Hub Client 同步至受審的 `2.0.7` full runtime，工具箱市集與日誌改走 WordPress REST，保留既有權限、資料 owner、更新器與 first-loader 契約。Blocksy 元件、Customizer 設定、前台資產與迷你購物車核心門檻不變。

## [1.2.3] - 2026-09-13

### Changed

- 內嵌 YS Plugin Hub Client 同步至受審的 `2.0.6` full runtime：納入 Shipping 基準的 updater、快取資料、schema readback 與日誌防護，並統一工具箱選單的晚期排序與去重。
- 外掛最低 PHP 版本調整為 8.2，與內嵌 Hub Client 的執行環境一致；Blocksy 元件、Customizer 設定與迷你購物車核心門檻不變。

## [1.2.2] - 2026-08-23

R49 對 1.2.1 的三個 Minor 收斂；不改行為契約（狀態唯一真值仍是核心面板 `.ys-ec-mini-cart-open`、零複製購物車邏輯、
add-on 5 原則）、最低核心版本不變（`YS_CART_BLOCKSY_DRAWER_MIN_CORE = '2.58.2'`）。

### Fixed

- **selective refresh 後 `aria-expanded` 暫時 stale**：Customizer selective refresh 會把頁首元件整個重繪
  （新的 `a.ys-cart-blocksy-cart` 節點、伺服器端印的 `aria-expanded="false"`），面板若正開著，新 trigger 的 aria
  與實況不符。現在另有一個 `MutationObserver`（`document.body`，`childList + subtree`）只在**新增的節點是／包含
  trigger** 時（在新增的子樹內查，不對每次 DOM 變動掃全頁）去抖後 `setExpanded(isOpen())`，把 aria 同步回面板實況。
- **inert 退路沒精確還原原始 `aria-hidden`**：不支援 `inert` 的瀏覽器走 `aria-hidden="true"` 退路，1.2.1 關閉時一律
  移除屬性，原本 `aria-hidden="false"`（或其他值）的元素就被改掉。現在記錄每個元素原本的值（`null`＝沒有屬性），
  關閉時精確還原（沒有屬性→移除；有→設回原值）；原本就是 `"true"` 的照舊不動。
- **e2e runner 留下 ignored `.out/*.html`**：`tests/run-e2e.php` 每支 fixture 的渲染檔跑完沒刪（只刪 Chrome profile）。
  現在未設 `YS_E2E_KEEP_OUT` 時渲染檔與 profile 都刪（BROKEN 也一樣，要看結果就設 `YS_E2E_KEEP_OUT=1` 重跑），
  `.out/` 空了也移除目錄。

### 測試

- `tests/e2e/drawer-host-smoke.html` 加 selective refresh 步驟（面板開著時把 `#hdr` 換成全新 trigger → 新 trigger
  必須同步為 `aria-expanded="true"`，關閉後 `false`；關著時重繪與無關節點新增都不會誤同步）：24 → 31 斷言。
- 新 fixture `tests/e2e/inert-fallback-smoke.html`：載入本外掛 JS 之前 `delete HTMLElement.prototype.inert`
  走退路，三個背景元素（沒有 `aria-hidden`／`"false"`／`"true"`）開啟都變 `"true"`（原本 `"true"` 不動）、關閉
  精確還原，跑兩輪：19 斷言。
- `tests/regression/v121_mini_cart_drawer.php`：f1 版本門檻 1.2.2；新增 d7（trigger 新增的 MutationObserver 同步）、
  d8（inert 退路記錄原值）、h3（runner 清 `.out/`）；h2 fixture 清單加新 fixture：25 → 28 斷言。
- 紅證：`YS_E2E_BLOCKSY_JS=<v1.2.1 的 ys-cart-blocksy.js> php tests/run-e2e.php` → 3 條 ★ 紅（drawer-host-smoke 1、
  inert-fallback-smoke 2）；`php tests/run.php` 全綠：靜態 25／結構 28／e2e 70。

## [1.2.1] - 2026-08-22

### Fixed

- **結帳頁點頁首購物車沒反應**（R48 #2）：核心在結帳頁以 CSS `display:none !important` 隱藏迷你購物車，
  1.2.0 只檢查面板存在就 `preventDefault()`，於是開了一個看不見的面板。現在只在面板「真的能顯示」
  （`#ys-ec-mini-cart` 與面板的 computed `display` 都不是 `none`）時才攔截；否則不攔截，`<a href>` 照常
  前往購物車頁。修飾鍵／非左鍵點擊也交給瀏覽器（新分頁開購物車頁）。
- **dialog 契約**（R48 #3）：trigger 宣告 `aria-haspopup="dialog"`，但核心面板沒有 dialog 語意、關閉也不還焦點。
  現在由本外掛 JS 在面板缺少時補上 `role="dialog"`、`aria-labelledby`（核心樣板標題 `#ys-ec-mini-cart-title`）、
  `tabindex="-1"`；開啟時焦點進面板（第一個可聚焦元素）、關閉時焦點回到開啟它的元素（核心加入購物車
  自動開啟時＝當時的焦點元素，通常是加入購物車按鈕）。本外掛自己輸出的抽屜是 **modal**：`aria-modal="true"`、
  背景 `inert`（不支援 `inert` 的瀏覽器退 `aria-hidden`）、Tab／Shift+Tab 在面板內循環；核心右下角浮動面板
  是非 modal popover，只做 role／名稱／焦點進出，不鎖背景。開／關的副作用一律由面板 `.ys-ec-mini-cart-open`
  的 MutationObserver 同步，核心自己開關（自動開啟、× 關閉、點外面關閉）也一致。

### Changed

- 點擊改在 bubble phase、不再 `stopPropagation()`（佈景／analytics 的 document 監聽照常收到）；開關延後到事件
  派送結束（`setTimeout 0`），核心「點面板外就關閉」的監聽不會把剛開的面板又關掉；開／關依據在 capture phase
  先讀。
- 最低核心版本只宣告一處：`YS_CART_BLOCKSY_DRAWER_MIN_CORE = '2.58.2'`（主檔），README／CHANGELOG 引用同一個數字；
  核心低於它時購物車元件自動退回「前往購物車頁」（`YSBlocksyDetector::core_supports_mini_cart_drawer()`），
  不宣告做不到的相依。主檔加 `Requires Plugins: ys-cart`（與 affiliate 一致）。

### 測試（R48 #4：測試入 repo，乾淨 clone 可重跑）

- `.gitignore` 不再忽略 `tests/`。`php tests/run.php` 一次跑：靜態契約、`tests/regression/`、真瀏覽器 e2e。
- 新增 `tests/e2e/`（headless Chrome `--dump-dom`，載入**真實核心** `ys-ec-cart.js`／CSS 與核心樣板
  `templates/cart/mini-cart.php` 渲染結果）：`drawer-host-smoke`（modal：開關、dialog 屬性、焦點進出、inert、
  Tab 循環、Esc／遮罩／× 關閉、核心自動開啟焦點同步、analytics 監聽仍收到點擊）、`drawer-checkout-smoke`
  （核心結帳 CSS 之下不攔截、走 href）、`floating-mode-smoke`（核心浮動面板：非 modal、核心 outside-click
  與本外掛 toggle 不打架）。對 1.2.0 的 JS 為 ★ 紅（重現 R48 #2／#3）。
- `tests/regression/v121_mini_cart_drawer.php`：1.2.x 結構契約（含最低核心版本四處一致）。

## [1.2.0] - 2026-08-22

### Added

- **頁首購物車 → 迷你購物車 drawer**（Phase 2 的 drawer 部分）。購物車元件新增「點擊行為」選項：
  **開啟迷你購物車**（預設）／前往購物車頁（1.1.x 行為）。drawer 模式直接開啟**核心的**迷你購物車
  （品項／數量／移除／小計／促銷提示／前往結帳／加入購物車後自動開啟），與核心右下角浮動購物車功能
  完全相同、零複製購物車邏輯：
  - 核心「右下角浮動購物車」**開著**時：頁首 icon 直接開關核心已輸出的面板。
  - 核心浮動購物車**關閉**時（建議組態：電商設定 → 功能 → 右下角浮動購物車 關閉）：本外掛在頁尾以核心
    同一份樣板（`templates/cart/mini-cart.php`、同一組 ID／JS／REST）輸出迷你購物車，改成右側抽屜樣式
    （隱藏浮動按鈕、面板貼右滿高、背景遮罩、Esc／遮罩關閉、`aria-expanded`／`aria-controls`）。
  - href 永遠保留購物車頁：no-JS 或面板不存在（chrome 被 opt-out）時照常前往 `/cart/`。
- 與核心共用 `ys_ec_render_standard_chrome`（piece=mini_cart）opt-out 契約。

### Changed

- 🔴 **行為變更**：購物車元件預設由「前往購物車頁」改為「開啟迷你購物車」。升級後點擊頁首購物車會開抽屜；
  要維持舊行為請到 外觀 → 自訂 → 頁首 → YS Cart → 點擊行為 選「前往購物車頁」。

### 相依

- 需核心 **YS CART ≥ 2.58.2**：2.58.2 修正了購物車變空／頁上無迷你購物車時數量 badge 不更新的核心問題
  （`[ys_ec_cart_icon]` 與本元件的 badge 都受影響）。舊核心仍可用，只是該情境 badge 會停在舊數字。

## [1.1.4] - 2026-07-28

### Fixed

- Stop the bundled YS Hub Client library from registering an invalid
  WooCommerce HPOS declaration from its vendor path.

## [1.1.3] - 2026-06-18

### Added

- Add a `YS Cart` Blocksy header item that links to the YS CART cart page and
  displays an optional cart count badge. This keeps the header cart icon
  independent from WooCommerce after Woo modules are disabled.

## [1.1.2] - 2026-06-15

### Changed

- **元件更名 智慧搜尋 → 進階搜尋**：頁首建構器的「YS 智慧搜尋 Icon」「YS 智慧搜尋框」
  改為「**YS 進階搜尋 Icon**」「**YS 進階搜尋框**」，與 ys-cart-smart-search 1.4.0 的
  「進階搜尋」品牌一致（元件 id、行為不變，既有頁首配置不受影響）。

## [1.1.1] - 2026-06-13

### Fixed

- **智慧搜尋框送出鈕在頁首被主題塗成米色方塊**：Blocksy 頁首會對
  `button[type="submit"]` 套用佈景 palette 底色，導致「YS 智慧搜尋框」的放大鏡
  鈕變成米色方塊（核心 `[ys_ec_search]` 早有 `inputwrap > submit[type=submit]`
  高權重防護，智慧版原本缺）。補上同級樣式防護（不用 `!important`），讓送出鈕
  回到內嵌、透明、跟隨配色的放大鏡，與核心搜尋一致。
- **智慧搜尋框的即時結果面板在頁首過窄**：`.ys-ss-panel` 原本只貼齊輸入框寬度
  （sm/md 約 200–280px），商品結果擠成一條。比照核心 v2.52.30 在頁首放大到
  `max(容器寬, min(420px, 92vw))`，並改 `right` 錨定向左延伸（頁首搜尋多靠右，
  避免向右溢出視窗）。

## [1.1.0] - 2026-06-13

### Added

- **YS 帳號全面對齊核心 `[ys_ec_user_icon]`**：新「行為模式」選項，預設「與核心相同」——未登入點擊開啟核心登入視窗、**登入後顯示會員下拉選單**（我的帳號／訂單紀錄／訂單查詢／登出），登入前後樣式自動不同；原 v1.0.0 簡單連結行為保留為「簡單連結」模式。
- **三元件皆可調尺寸與顏色（對齊 Blocksy 原生選項）**：Icon 尺寸（responsive slider）＋ Icon 顏色（Initial／Hover，Design 分頁）；搜尋框另有輸入框高度與文字／背景／邊框三色。透過 Blocksy `dynamic-styles` 機制輸出實例層 CSS 變數，未設定時自動跟隨主題頁首文字色。
- **兩個智慧搜尋元件**（需安裝啟用 `ys-cart-smart-search`，未啟用時建構器完全不顯示）：「YS 智慧搜尋 Icon」（點擊開智慧彈窗）與「YS 智慧搜尋框」（行內智慧搜尋，placeholder／寬度／高度／顏色可調）。
- **自動更新**：內建 YS Plugin Hub Client（v2.0.2），透過 YS Plugin Hub 接收版本更新。

### Changed

- 「YS 商品搜尋」更名為「**YS 搜尋 Icon**」（元件 id 不變，既有頁首配置不受影響）。

## [1.0.1] - 2026-06-13

### Fixed

- 搜尋框結果視窗不再寫死 `left/right` 錨定，改交給核心 v2.52.30 的結果視窗
  定位機制（最小寬度 + 靠右自動向左延伸 + 手機/窄容器 compact ITEM）；
  外掛只保留 z-index 提升讓視窗蓋過頁首。搭配舊版核心（< 2.52.30）時
  視窗退回容器寬（原行為），不受影響。

## [1.0.0] - 2026-06-12

首發（ADR-057 Phase 1）。

### Added

- Blocksy 頁首建構器三個 YS CART 元件（經 `blocksy:header:items-paths` 官方擴充機制註冊，Blocksy 免費版即可用）：
  - **YS 帳號**：未登入 → YS CART 登入頁／登入後 → 會員中心，兩態皆可改自訂連結；可選文字標籤。
  - **YS 商品搜尋**：搜尋圖示 → 核心全屏商品即時搜尋 overlay；核心「商品即時搜尋」功能未啟用時前台不輸出（Customizer 預覽顯示提示）。
  - **YS 搜尋框**：行內即時搜尋框（提示文字可自訂、寬度四段：窄／中／寬／填滿）；表單送出落到商店頁搜尋結果。
- 選項變更全部走 Customizer selective refresh（server 重渲染），即時預覽免額外 JS。
- 對齊樣式：圖示 `currentColor` 跟隨 Blocksy 頁首文字色；行內結果 dropdown 提升 z-index 蓋過頁首；熱門關鍵字列在行內框收起。
- Fail-soft：缺 Blocksy／YS CART 不 fatal，僅外掛列表頁提示；元件 view 各自 guard `class_exists`。
