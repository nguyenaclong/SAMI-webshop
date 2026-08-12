# 2 Step Webshop

**Plugin Version:** 1.4.9  
**Requires:** WordPress 6.0+ / WooCommerce 7.0+  
**Text Domain:** `2-step-webshop`  
**License:** Proprietary

---

## Mô tả / Description

**2 Step Webshop** là một plugin WordPress độc lập, cung cấp giao diện cửa hàng tùy chỉnh hai bước cho nhà hàng, tích hợp hoàn toàn với WooCommerce. Plugin không phụ thuộc vào theme, hoạt động như một lớp giao diện riêng biệt đặt trên nền tảng WooCommerce.

**2 Step Webshop** is a standalone WordPress plugin providing a custom two-step shop interface for restaurants, fully integrated with WooCommerce. It operates independently of the active theme, functioning as a dedicated UI layer on top of WooCommerce.

---

## ✨ Features

### 🛍️ Two-Step Shop Layout
- Custom shop layout rendered via shortcode `[custom_shop]`
- Step 1: Browse & add products to cart
- Step 2: Select pickup time or delivery details before checkout
- Animated floating cart button with live item count badge
- Optional drawer-style cart sidebar

### 🎨 Fully Configurable Design System
- **Style Tab** in admin with individual style options per UI element:
  - Font sizes (title, body, header, price)
  - Colors (background, header, modal, price, text)
  - Border radii (cards, modals, pills/buttons)
  - Container widths (max-width, sidebar, modal, drawer)
- Color palette with quick-select preset circles
- CSS variable–based dynamic theming (no hard-coded styles in templates)
- Glassmorphism and modern visual aesthetics

### 🕐 Pickup Scheduler
- Configurable opening and closing times (global defaults)
- Per-day opening/closing hours for Monday–Sunday with individual toggles
- "Use same hours for all days" toggle for quick global configuration
- Time slot dropdown with configurable interval (15 / 30 / 45 / 60 minutes)
- Lead time buffer (prep time) to offset available time slots from now
- Store open/closed status detection used throughout the layout

### 📅 Opening Days Management
- Enable or disable individual days of the week
- Per-day custom opening and closing hours (shown/hidden based on global toggle)
- Opening hours displayed in the store info popup and brand header

### 🚗 Pickup & Delivery Support
- Toggle between Local Pickup and Delivery fulfillment methods
- Method switcher capsule on the shop page
- Delivery address autocomplete (Google Maps integration-ready)
- Configurable delivery zone description shown in the Store Info popup
- Google Maps embed URL configurable directly from admin

### 🏪 Restaurant Information & Branding
- **Restaurant Name**: Defaults to WordPress Site Title; custom override option for the webshop template
- **Store Address**: Defaults to WooCommerce store address; custom override option for the webshop template
- **Store Logo**: Defaults to Theme Customizer logo; custom Media Library override for the webshop template
- **Hero Banner Image**: Configurable header image with URL or Media Library upload
- **Google Maps URL**: Inline map embed in the Store Info popup
- **Delivery Zone Info**: Custom description text for delivery area and conditions
- Settings links for quick navigation to WordPress General Settings, WooCommerce Settings, and Theme Customizer

### 📧 Email Templates & Notifications
- Custom HTML email templates for:
  - **Customer Order Confirmation** (subject + body)
  - **Admin Order Notification** (subject + body)
- Enable/disable custom templates toggle (falls back to WooCommerce default emails when disabled)
- Template placeholder variables using `{...}` syntax:

  | Placeholder | Description |
  |---|---|
  | `{restaurant_name}` | Restaurant / shop name |
  | `{restaurant_address}` | Store address |
  | `{restaurant_logo}` | Logo image HTML |
  | `{customer_name}` | Customer full name |
  | `{customer_email}` | Customer email address |
  | `{customer_phone}` | Customer phone number |
  | `{order_number}` | WooCommerce order number |
  | `{order_date}` | Pickup / delivery date |
  | `{order_table}` | Full HTML order items table |
  | `{fulfillment_method}` | "Pickup" or "Delivery" |
  | `{pickup_delivery_time}` | Selected time slot |
  | `{special_request}` | Customer special requests |

- Multiple admin notification email recipients (comma-separated)
- Admin email address configurable from the plugin admin UI (overrides WooCommerce default)
- Email language setting (Auto / German / English)

### ⚙️ Admin Settings Panel
- Full-featured admin UI at **WooCommerce → 2-Step Webshop**
- Three main tabs:
  1. **General Settings** — Restaurant info, hero banner, map, pickup/delivery rules, opening hours
  2. **Style** — Visual customization options for all UI elements
  3. **Email Form & Templates** — Email recipients, subject lines, and HTML template editors
- 384px max-width flex grid layout for individual option items
- Horizontal color palette rows for quick color preset selection
- Inline settings links to WordPress and WooCommerce settings pages

### 🔒 Security
- CSRF nonce protection on all AJAX endpoints (`tsw_ajax_nonce`)
- Output escaping with `esc_html`, `esc_attr`, `esc_url` throughout templates
- CSS value sanitization via `tsw_sanitize_css_value()` (validates hex, rgb/rgba/hsl/hsla, CSS var())
- All settings sanitized via `sanitize_text_field`, `esc_url_raw`, and custom checkbox sanitizer

### 🌍 Internationalization (i18n)
- Full Gettext support (`__()`, `_e()`, `esc_html_e()`, `esc_attr_e()`)
- German (`de_DE`) translation included (`.po` + compiled `.mo`)
- WPML-compatible (`wpml-config.xml` included)
- Polylang-compatible string registration

### 🔧 WooCommerce Integration
- Cart fragments (AJAX live cart updates without page reload)
- Compatible with WooCommerce sessions for pickup time persistence
- EC card payment minimum threshold enforcement
- Custom checkout field rendering (pickup/delivery date, time, special request)
- Floating cart synced with WooCommerce cart totals

---

## 📦 File Structure

```
2-step-webshop/
├── 2-step-webshop.php              # Main plugin file, global helpers
├── README.md                       # This file
├── wpml-config.xml                 # WPML string configuration
├── assets/
│   ├── css/
│   │   ├── custom-shop.css         # Frontend shop styles
│   │   └── admin-settings.css      # Admin panel styles
│   └── js/
│       ├── custom-shop.js          # Frontend shop logic
│       ├── checkout.js             # Checkout step logic
│       └── admin-settings.js       # Admin panel interactivity
├── includes/
│   ├── class-ajax-handlers.php     # AJAX endpoints (cart, session)
│   ├── class-checkout.php          # Checkout integration, email hooks
│   ├── class-pickup-scheduler.php  # Time slot generation, open/closed logic
│   ├── class-settings.php          # Admin settings UI and registration
│   ├── class-shortcode.php         # [custom_shop] shortcode handler
│   ├── class-woo-compat.php        # WooCommerce compatibility layer
│   └── class-woo-fragments.php     # Cart fragment AJAX responses
├── languages/
│   ├── 2-step-webshop-de_DE.po     # German translation source
│   └── 2-step-webshop-de_DE.mo     # German translation binary
└── templates/
    └── custom-shop-layout.php       # Main shop page HTML template
```

---

## 📖 Setup Guide / Hướng Dẫn Cấu Hình Chi Tiết

### 1. WooCommerce Pre-Setup (Bắt buộc trong WooCommerce)

Before activating Local Pickup and Delivery in the 2-Step Webshop plugin, complete these required configuration steps in WooCommerce:

#### A. Configure WooCommerce Store Address
1. Go to **WooCommerce → Settings → General**.
2. Fill in **Store Address**, **City**, **Postcode / ZIP**, and **Country / State**.
3. *Note*: This address is displayed in the webshop header and store info popup by default (unless overridden in the plugin settings).

#### B. Enable Local Pickup & Delivery Shipping Methods
1. Go to **WooCommerce → Settings → Shipping → Shipping Zones**.
2. Add or Edit your primary Shipping Zone (e.g., "Germany" or "Local Area").
3. **Local Pickup Setup**:
   - Click **Add shipping method**.
   - Select **Local Pickup** and click **Continue**.
   - Set the method title (e.g., "Local Pickup" / "Selbstabholung") and tax status.
4. **Delivery Setup**:
   - Click **Add shipping method**.
   - Select **Flat Rate** (or your designated shipping method) for Delivery.
   - Configure cost rules if applicable.

> ⚠️ **Important**: If WooCommerce has no `Local Pickup` shipping method added to active zones, WooCommerce checkout may fail to present pickup options during order placement.

---

### 2. Plugin Setup (Cấu hình 2-Step Webshop)

Navigate to **WooCommerce → 2-Step Webshop** in the WordPress admin panel:

#### Tab 1: General Settings (Cài Đặt Chung & Local Pickup)
1. **Restaurant Information & Branding**:
   - **Restaurant Name**: Auto-fetches WP Site Title. Enable **Override** to set a custom webshop title.
   - **Store Address**: Auto-fetches WooCommerce store address. Enable **Override** to set a custom webshop address.
   - **Store Logo**: Auto-fetches Customizer logo. Enable **Override** to select a custom logo from Media Library.
2. **Header Banner & Map Settings**:
   - **Custom Header Banner Image**: Upload a high-resolution banner image shown at the top of the webshop.
   - **Google Maps Embed URL**: Paste a Google Maps embed URL (`https://maps.google.com/maps?q=...&output=embed`). If left blank, the plugin automatically generates a map iframe from the Store Address.
   - **Delivery Zone Label & Fee**: Enter zone title and fee description shown in the Store Info popup.
3. **Local Pickup & Opening Hours**:
   - **Global Opening & Closing Times**: Set standard operating hours (e.g., `11:30` to `22:00`).
   - **Opening Days**: Check/uncheck days of the week (Monday–Sunday).
   - **Per-Day Hours**: Uncheck *"Use same opening & closing hours for all open days"* to specify distinct open/close times per day.
   - **Time Slot Interval**: Choose dropdown granularity (`15`, `30`, `45`, or `60` minutes).
   - **Prep Buffer (Minutes)**: Set lead time buffer (e.g., `25` mins) to offset the earliest available slot from current time.
   - **EC Card Min Total (€)**: Set minimum cart total required for card payment methods.
   - **Fulfillment Method Toggles**: Check **Enable Local Pickup** and/or **Enable Delivery**.

#### Tab 2: Style & Colors (Tùy Chỉnh Giao Diện)
- Customize primary button colors, background colors, font sizes, border radii, container max-width, sidebar width, modal width, and drawer width.
- Preset palette circles automatically detect active theme colors (Blocksy, Astra, GeneratePress).

#### Tab 3: Email Form & Templates (Cấu Hình Email)
- **Admin Recipients**: Enter one or multiple comma-separated email addresses (e.g., `admin@restaurant.de, manager@restaurant.de`).
- **Notification Email Language**: Select `Auto` (matches customer order language), `Always English`, or `Always German`.
- **Custom HTML Email Templates**: Toggle **Enable Custom HTML Email Templates** to use custom HTML email subjects and body templates with `{...}` placeholders (`{restaurant_name}`, `{order_table}`, `{pickup_delivery_time}`, etc.).

---

### 3. Page Setup & Shortcode (Tạo Trang Webshop)

1. Go to **Pages → Add New**.
2. Title the page (e.g., `Webshop` or `Online Order`).
3. Set page slug to `webshop` (URL: `example.com/webshop/`).
4. Insert shortcode: `[two_step_webshop_layout]` (or `[custom_shop]`).
5. Publish the page.

---

### 4. Multi-Language Setup (Polylang / WPML)

If using Polylang or WPML for multi-language support:
1. **Page Translation**: Create translated versions of the Webshop page (e.g. `/de/webshop/`) and insert the shortcode.
2. **String Translation**: The plugin automatically registers UI strings with Polylang/WPML (`pll_register_string`). Translate string constants in **Polylang → String Translations** if needed.
3. **Email Language Sync**: Customer confirmation emails automatically adapt to the order language stored at checkout.

---

## Nhật ký thay đổi (Changelog)

### Phiên bản 1.4.9 — Chuẩn Hóa Admin UI, Nâng Cấp Nguồn Ảnh Modal High-Res & Khôi Phục Thẻ Fulfillment

**Phát hành:** 05/08/2026

**Tính năng & Cải tiến:**
- **Nâng Cấp Nguồn Ảnh Modal Xem Chi Tiết Sản Phẩm (Product Info Popup)**:
  - Cập nhật thẻ thẻ sản phẩm trong template `custom-shop-layout.php` tự động xuất thuộc tính `data-full-image` chứa URL ảnh gốc chất lượng cao (`full`/`large`).
  - Cập nhật hàm `openProductModal()` trong JavaScript ưu tiên tải ảnh sắc nét high-resolution, giải quyết hoàn toàn tình trạng ảnh bị mờ/vỡ nét khi mở popup.
- **Chuẩn Hóa Giao Diện Quản Trị Admin UI (Layout Normalization)**:
  - Khắc phục lỗi tràn cột 3 của thanh Save Settings (`.csp-admin-footer`) bằng cách chuyển `<form>` sang cấu trúc block 100% width.
  - Sửa lỗi lồng thẻ HTML div gây đẩy lệch Tab 2 và Tab 3 ra ngoài container chính.
  - Khôi phục thẻ **Fulfillment Methods & Services** (Bật/tắt Nhận tại cửa hàng & Giao hàng) tại vị trí đầu Tab 1 Admin UI.
  - Chuyển đổi bảng `table.form-table` ở mục Restaurant Information sang hệ thống lưới `.csp-card-grid` đồng bộ với tất cả các tab khác.
- **Tự Động Căn Chỉnh Brand Header**:
  - Tự động xóa thẻ placeholder logo khi không có logo cửa hàng, giúp Tiêu đề thương hiệu (Brand Title) căn thẳng lề tuyệt đối với Subheader.

### Phiên bản 1.4.8 — Sửa Lỗi Khung Giờ Vận Chuyển & Ẩn Địa Chỉ Giao Hàng Khi Nhận Tại Cửa Hàng

**Phát hành:** 04/08/2026

**Tính năng & Cải tiến:**
- **Ẩn Checkbox Giao Hàng Đến Địa Chỉ Khác Khi Chọn Local Pickup**:
  - Tự động ẩn checkbox `Ship to a different address?` và các trường địa chỉ giao hàng riêng khi khách chọn phương thức Nhận tại cửa hàng (**Local Pickup**).
- **Khắc Phục Lỗi "No shipping method has been selected"**:
  - Tự động khớp mã phương thức giao hàng (`rate_id`) với cấu hình gói vận chuyển của WooCommerce.
  - Thêm cơ chế tự động tạo phương thức vận chuyển dự phòng (Fallback Rate) giúp quy trình checkout không bao giờ bị gián đoạn do thiếu cài đặt Shipping Zones.

### Phiên bản 1.4.7 — Tùy Chọn Hiển Thị Ảnh Danh Mục & Căn Chỉnh Cột Header Danh Mục

**Phát hành:** 04/08/2026

**Tính năng & Cải tiến:**
- **Tùy Chọn Hiển Thị Ảnh & Mô Tả Danh Mục Trong Danh Sách Sản Phẩm**:
  - Bổ sung tùy chọn bật/tắt hiển thị ảnh đại diện danh mục (`Show Product Category Image`) và mô tả danh mục (`Show Product Category Description`) trong General Settings.
  - Sắp xếp thứ tự các phần tử trong header danh mục theo dạng cột đứng: **Ảnh danh mục → Tiêu đề danh mục → Mô tả danh mục → Danh sách sản phẩm trong danh mục**.
- **Tùy Chọn Căn Chỉnh Văn Bản (Text Alignment)**:
  - Bổ sung tùy chọn `Category Header & Text Alignment` với 3 chế độ: `Left (Trái)`, `Center (Giữa)`, `Right (Phải)`.
  - Áp dụng căn chỉnh đồng bộ cho tiêu đề danh mục, mô tả danh mục, ảnh danh mục và danh sách sản phẩm.

### Phiên bản 1.4.6 — Tùy Chọn Hiển Thị Giá Sản Phẩm & Template Email Đa Ngôn Ngữ (EN/DE)

**Phát hành:** 04/08/2026

**Tính năng & Cải tiến:**
- **Tùy Chọn Hiển Thị Giá Cho Sản Phẩm Tùy Chọn (Variable Products)**:
  - Thêm cài đặt `Variable Product Price Display Mode` trong General Settings.
  - Cho phép người quản trị lựa chọn giữa hiển thị giá rẻ nhất (`Cheapest Price Only`, ví dụ `5,00 €`) hoặc hiển thị đầy đủ khoảng giá (`Full Price Range`, ví dụ `5,00 € – 12,00 €`).
- **Template Email HTML Phân Loại Theo Ngôn Ngữ Tiếng Anh (EN) & Tiếng Đức (DE)**:
  - Hỗ trợ tạo và chỉnh sửa riêng tiêu đề (Subject) và nội dung (HTML Body) email cho Tiếng Anh (`EN`) và Tiếng Đức (`DE`) trong Tab 3 Email Form & Templates.
  - Cung cấp sẵn mẫu HTML mặc định chuẩn đẹp, chuyên nghiệp, responsive và đầy đủ thẻ thay thế `{...}`.
  - Tự động nhận diện ngôn ngữ đơn hàng (`_order_language`) để gửi đúng bản tiếng Anh hoặc tiếng Đức cho khách hàng và quản trị viên.
- **Tối Ưu Giao Diện & Trải Nghiệm Admin UI**:
  - Thêm nút **Sao chép Shortcode 1-click** (`[two_step_webshop_layout]`) với phản hồi trực quan trên thanh header admin.
  - Xóa tùy chọn trùng lặp `tsw_admin_email_language` giữa Tab 1 và Tab 3 (giữ tập trung sạch tại Tab 3).
  - Căn chỉnh biểu tượng nút (icon alignment) và bổ sung thông báo cảnh báo trực quan khi người dùng vô tình tắt cả 2 phương thức Local Pickup và Delivery.

### Phiên bản 1.4.5 — Kiểm Tra Cài Đặt Admin UI & Template Email HTML Tùy Chỉnh

**Phát hành:** 04/08/2026

**Tính năng & Cải tiến:**
- **Kiểm tra & Rà soát toàn bộ 53 Cài đặt Admin UI**:
  - Xác nhận và kết nối tất cả các tùy chọn trong 3 tab: *General Settings*, *Style & Colors*, và *Email Form & Templates*.
- **Hỗ trợ Template Email HTML Tùy Chỉnh**:
  - Kết nối bộ lọc `woocommerce_email_content_customer_processing_order`, `woocommerce_email_content_customer_completed_order`, và `woocommerce_email_content_new_order` với cài đặt `tsw_customer_email_body` và `tsw_admin_email_body`.
  - Tự động thay thế tất cả biến `{...}` trong nội dung HTML email (tên nhà hàng, địa chỉ, logo, thông tin khách hàng, bảng đơn hàng itemized `{order_table}`, phương thức đặt hàng, v.v.).
- **Đồng Bộ CSS Variable Động Cho Style Tab**:
  - Kết nối biến CSS `var(--tsw-price-color)` và `var(--tsw-price-font-size)` cho giá sản phẩm (`.product-price`).
  - Kết nối biến CSS `var(--tsw-drawer-width)` cho chiều rộng giỏ hàng trượt (`.csp-cart-drawer-container`).
  - Kết nối biến CSS `var(--tsw-pill-border-radius)` cho bo góc capsule bộ chuyển đổi phương thức (`.csp-method-switcher-capsule`, `.csp-method-btn`).
  - Mở rộng danh sách thẻ selector trong inline CSS shortcode để áp dụng đầy đủ style tùy chỉnh cho tất cả container modal và drawer.

**Sửa lỗi:**
- **Sửa lỗi cảnh báo PHP Undefined Variable `$yesterday_day`**:
  - Đã định nghĩa biến `$yesterday_day` và lấy chính xác giờ đóng cửa của ngày hôm trước (`$yesterday_closing`) trong `TSW_Pickup_Scheduler::is_store_currently_open()` khi kiểm tra trạng thái mở cửa qua đêm.

---

### Phiên bản 1.4.4 — Nhóm thẻ Giờ Mở Cửa & Tùy Chỉnh Thông Tin Nhà Hàng

**Phát hành:** 30/07/2026

**Tính năng mới:**
- Nhóm hai thẻ "Giờ Mở Cửa Theo Ngày" và "Cài Đặt Pickup & Giờ Nhà Hàng" thành một thẻ duy nhất **"Local Pickup & Opening Hours"** để giao diện admin gọn gàng hơn.
- Thêm tùy chọn ghi đè tùy chỉnh (custom override toggle) cho **Tên Nhà Hàng**, **Địa Chỉ Cửa Hàng**, và **Logo Cửa Hàng** trong thẻ "Restaurant Information & Settings Links":
  - Mỗi thông tin có nút chuyển đổi để bật/tắt giá trị tùy chỉnh riêng cho template webshop
  - Khi bật, hiển thị ô nhập văn bản hoặc bộ chọn media để nhập giá trị riêng
  - Khi tắt, tự động lấy giá trị từ WordPress General Settings hoặc WooCommerce
- Thêm 3 hàm helper toàn cục: `tsw_get_restaurant_name()`, `tsw_get_store_address()`, `tsw_get_store_logo_url()` — tự động ưu tiên giá trị tùy chỉnh nếu được bật
- Cập nhật tất cả template và class sử dụng các helper mới thay vì gọi trực tiếp `get_bloginfo()` và `get_option()`

**Sửa lỗi:**
- Sửa lỗi thiếu dấu đóng ngoặc `});` trong `admin-settings.js` gây lỗi cú pháp JavaScript

---

### Phiên bản 1.4.3 — Tab Email & Giờ Mở Cửa Theo Từng Ngày

**Phát hành:** 30/07/2026

**Tính năng mới:**
- Thêm tab **"Email Form & Templates"** (Tab thứ 3) trong giao diện admin:
  - Cài đặt địa chỉ email admin nhận thông báo đơn hàng (hỗ trợ nhiều email, cách nhau bởi dấu phẩy)
  - Bật/tắt template email tùy chỉnh
  - Chỉnh sửa tiêu đề và nội dung HTML cho email xác nhận khách hàng và email thông báo admin
  - Hộp chú giải với danh sách đầy đủ các biến `{...}` có thể sử dụng trong template
- Thêm hỗ trợ giờ mở cửa theo từng ngày trong tuần (Thứ 2 đến Chủ nhật):
  - Toggle "Dùng cùng một giờ mở/đóng cửa cho tất cả ngày" — khi bật dùng giờ toàn cục, khi tắt hiển thị ô nhập riêng cho từng ngày
  - Các tùy chọn: `pickup_use_same_hours`, `pickup_opening_time_{day}`, `pickup_closing_time_{day}`
- Thêm hàm helper `tsw_get_day_opening_hours($day_key)` — tự động chọn giờ đúng theo ngày trong tuần
- Lọc WooCommerce để gửi email thông báo đơn hàng đến nhiều địa chỉ email admin tùy chỉnh

**Cập nhật:**
- Cập nhật bộ lịch pickup (`class-pickup-scheduler.php`) để sử dụng `tsw_get_day_opening_hours()`
- Cập nhật template `custom-shop-layout.php` hiển thị giờ mở cửa đúng theo ngày trong popup thông tin cửa hàng

---

### Phiên bản 1.4.2 — Bố Cục Admin 384px & Cải Tiến Tab Style

**Phát hành:** 29/07/2026

**Tính năng mới:**
- Thẻ cài đặt (`.csp-card`) được mở rộng toàn chiều rộng (`width: 100%`)
- Bố cục lưới flex cho các nhóm tùy chọn (`.csp-card-grid`): mỗi nhóm label + input có `max-width: 384px`, tự xuống dòng linh hoạt
- Vòng tròn bảng màu (`.csp-palette-circles`) hiển thị nằm ngang trong một hàng thay vì cột dọc

**Sửa lỗi:**
- Sửa bố cục bảng màu trong tab Style đang hiển thị dạng cột thay vì hàng

---

### Phiên bản 1.4.1 — Hình Ảnh & Thông Tin Cửa Hàng Động

**Phát hành:** 28/07/2026

**Tính năng mới:**
- Cài đặt **Hình ảnh Hero Banner** có thể chỉnh sửa từ admin (bộ chọn Media Library + URL tùy chỉnh)
- Cài đặt **URL Google Maps** để nhúng bản đồ vào popup thông tin cửa hàng
- Cài đặt **Mô tả vùng giao hàng** hiển thị trong popup thông tin cửa hàng
- Thêm liên kết nhanh đến WordPress General Settings, WooCommerce Settings, và Theme Customizer từ admin

---

### Phiên bản 1.4.0 — Dịch Thuật Tiếng Đức & Quốc Tế Hóa

**Phát hành:** 27/07/2026

**Tính năng mới:**
- Bọc tất cả chuỗi PHP bằng hàm Gettext (`__()`, `_e()`, `esc_html_e()`, `esc_attr_e()`)
- Đăng ký chuỗi JavaScript với Polylang/WPML
- Thêm file dịch thuật tiếng Đức: `languages/2-step-webshop-de_DE.po` và `.mo`
- Biên dịch file `.mo` bằng `msgfmt` qua `ddev exec`

---

### Phiên bản 1.3.x — Kiểm Tra Bảo Mật & Sửa Lỗi

**Phát hành:** 26/07/2026

**Sửa lỗi & Bảo mật:**
- Thêm bảo vệ CSRF nonce (`wp_create_nonce('tsw_ajax_nonce')`) cho tất cả AJAX endpoint: `clear_cart`, `update_cart_item_qty`, `save_pickup_time_session`
- Thêm escape đầu ra (`esc_html`, `esc_attr`, `esc_url`) trong tất cả template và fragment handler
- Thêm hàm `tsw_sanitize_css_value()` để xác thực và làm sạch giá trị CSS động (màu hex, rgb/rgba/hsl/hsla, CSS var())
- Xóa tất cả `console.log()` còn sót lại trong JavaScript production

---

### Phiên bản 1.0.0 — Phát Hành Lần Đầu

**Phát hành:** 2026

**Tính năng ban đầu:**
- Giao diện cửa hàng 2 bước tùy chỉnh qua shortcode `[custom_shop]`
- Tích hợp WooCommerce cart, checkout, và fragments
- Bộ lịch pickup với khung giờ tùy chỉnh
- Bộ chuyển đổi Pickup / Delivery
- Giao diện admin cơ bản với các cài đặt cốt lõi
- Hỗ trợ tùy chỉnh style qua CSS variable

---

## Yêu cầu hệ thống / Requirements

| Thành phần | Phiên bản tối thiểu |
|---|---|
| WordPress | 6.0 |
| WooCommerce | 7.0 |
| PHP | 7.4 |
| MySQL | 5.7 |

---

## Tác giả / Author

Plugin được phát triển cho hệ thống đặt hàng nhà hàng tích hợp WooCommerce.

---

*README được tạo tự động — Phiên bản 1.4.5*
