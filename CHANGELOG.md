# Changelog

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
