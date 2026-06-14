# Changelog

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
