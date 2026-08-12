=== 2 Step Webshop ===
Contributors: ai-assistant
Tags: woocommerce, webshop, restaurant, pickup, delivery, order-scheduler, two-step-checkout
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.4.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plugin đặt hàng nhà hàng 2 bước độc lập dành cho WordPress & WooCommerce, tích hợp bộ chọn giờ nhận/giao hàng, giỏ hàng trượt, tùy chỉnh giao diện và template email HTML đa ngôn ngữ (EN/DE).

== Description ==

**2 Step Webshop** là plugin nâng cấp giao diện đặt hàng chuyên nghiệp cho nhà hàng, quán ăn, tiệm bánh và cửa hàng thực phẩm trên WordPress.

Plugin hoạt động hoàn toàn độc lập với theme, tạo ra một trải nghiệm đặt hàng 2 bước nhanh chóng (Step 1: Chọn món & Giỏ hàng, Step 2: Đặt lịch nhận/giao hàng & Thanh toán).

### Tính Năng Nổi Bật:
* **Giao diện 2 Bước Chuẩn Nhà Hàng**: Tách biệt rõ ràng bước chọn món và bước đặt lịch thanh toán.
* **Bộ Lịch Hẹn Giờ Nhận / Giao Hàng Độc Lập**: Cho phép cài đặt giờ mở/đóng cửa toàn cầu hoặc riêng từng ngày trong tuần, khoảng chia phút (15, 30, 45, 60 phút) và thời gian chuẩn bị (Buffer mins).
* **Quản Lý Phương Thức Đặt Hàng**: Cho phép bật/tắt linh hoạt **Local Pickup** (Nhận tại cửa hàng) và **Delivery** (Giao hàng tận nơi).
* **Tùy Chọn Hiển Thị Danh Mục**: Cho phép hiển thị Ảnh đại diện danh mục, Mô tả danh mục xếp theo dạng cột đứng (Ảnh -> Tiêu đề -> Mô tả -> Sản phẩm) và căn chỉnh văn bản (Trái / Giữa / Phải).
* **Tùy Chọn Giá Sản Phẩm Variable**: Chuyển đổi giữa hiển thị Giá thấp nhất (`Cheapest Price Only`) hoặc Đầy đủ khoảng giá (`Full Price Range`).
* **Email HTML Đa Ngôn Ngữ (EN / DE)**: Chỉnh sửa riêng biệt mẫu email xác nhận cho Khách hàng và Quản trị viên theo Tiếng Anh và Tiếng Đức.

== Installation / Hướng Dẫn Cấu Hình Chi Tiết Từ A Đến Z (Tiếng Việt) ==

Dưới đây là hướng dẫn cài đặt và cấu hình chi tiết từ một trang WordPress mới tinh (Clean Install) để hệ thống hoạt động hoàn hảo 100%.

---

### YÊU CẦU TIỀN ĐỀ (PREREQUISITES)
Trước khi cài đặt, hãy đảm bảo trang WordPress của bạn đã cài đặt và kích hoạt các plugin sau:
1. **WooCommerce** (Phiên bản 7.0 trở lên) - *Bắt buộc*.
2. **Polylang** hoặc **WPML** (Tùy chọn) - *Dành cho website đa ngôn ngữ EN/DE*.

---

### BƯỚC 1: CÀI ĐẶT & KÍCH HOẠT PLUGIN
1. Tải thư mục plugin `2-step-webshop` vào đường dẫn `/wp-content/plugins/`.
2. Truy cập **WordPress Admin → Plugins → Installed Plugins**.
3. Tìm **2 Step Webshop** và nhấn **Activate** (Kích hoạt).
4. *Lưu ý*: Khi kích hoạt trên WordPress mới tinh, plugin sẽ tự động:
   - Tạo trang **Webshop** (`/webshop/`) chứa shortcode `[two_step_webshop_layout]`.
   - Khởi tạo mặc định các tùy chọn mở cửa và phương thức thanh toán **Trực tiếp / Tiền mặt (COD)**.

---

### BƯỚC 2: CẤU HÌNH WOOCOMMERCE BẮT BUỘC
Để WooCommerce xử lý được đơn hàng Pickup và Delivery, bạn cần thực hiện 2 bước trong WooCommerce:

#### A. Cấu Hình Địa Chỉ Cửa Hàng
1. Vào **WooCommerce → Cài đặt (Settings) → Cài đặt chung (General)**.
2. Điền đầy đủ: **Địa chỉ dòng 1**, **Thành phố**, **Mã bưu chính (Postcode)**, và **Quốc gia / Bang**.
3. Chọn **Đơn vị tiền tệ** (ví dụ: `Euro (€)` hoặc `Việt Nam Đồng (₫)`).

#### B. Cấu Hình Vùng Giao Hàng & Phương Thức Vận Chuyển (Shipping Zones)
1. Vào **WooCommerce → Cài đặt (Settings) → Giao hàng (Shipping) → Vùng giao hàng (Shipping Zones)**.
2. Nhấn **Thêm vùng giao hàng (Add shipping zone)** (hoặc Chỉnh sửa vùng mặc định).
3. **Cấu hình Nhận tại cửa hàng (Local Pickup)**:
   - Nhấn **Thêm phương thức giao hàng (Add shipping method)**.
   - Chọn **Nhận tại cửa hàng (Local Pickup)** và nhấn **Thêm (Continue)**.
4. **Cấu hình Giao hàng tận nơi (Delivery)**:
   - Nhấn **Thêm phương thức giao hàng (Add shipping method)**.
   - Chọn **Đồng giá (Flat rate)** cho giao hàng tận nơi.

> ⚠️ **Chú ý quan trọng**: Nếu trong WooCommerce chưa thêm phương thức `Local Pickup` vào Shipping Zone, WooCommerce checkout có thể chặn không cho đặt đơn hàng Pickup.

---

### BƯỚC 3: CẤU HÌNH PLUGIN 2-STEP WEBSHOP (ADMIN UI)
Vào **WooCommerce → 2-Step Webshop** trên thanh menu Admin:

#### 1. Tab General Settings (Cài Đặt Chung & Lịch Nhận Hàng)
* **Header Banner & Map Settings**:
  - **Custom Header Banner Image**: Tải lên ảnh banner sắc nét hiển thị ở đầu trang webshop.
  - **Google Maps Embed URL**: Dán link nhúng Google Maps (`https://maps.google.com/maps?q=...&output=embed`). Nếu bỏ trống, hệ thống tự tạo bản đồ từ Địa chỉ cửa hàng.
  - **Delivery Zone Label & Fee**: Nhập tên vùng giao hàng và phí hiển thị trong popup thông tin cửa hàng.
  - **Variable Product Price Display Mode**: Chọn `Cheapest Price Only (e.g. 5,00 €)` hoặc `Full Price Range (e.g. 5,00 € – 12,00 €)`.
* **Product Category Display & Text Alignment**:
  - Tích chọn **Show Product Category Image** (Hiển thị ảnh danh mục).
  - Tích chọn **Show Product Category Description** (Hiển thị mô tả danh mục).
  - Chọn **Category Header & Text Alignment**: `Left (Trái)`, `Center (Giữa)`, hoặc `Right (Phải)`.
* **Local Pickup & Opening Hours**:
  - **Global Opening & Closing Times**: Nhập giờ mở cửa/đóng cửa mặc định (ví dụ: `11:30` đến `22:00`).
  - **Use same opening & closing hours for all open days**: Tích chọn để dùng chung giờ, hoặc bỏ tích để nhập giờ riêng cho từng ngày từ Thứ Hai đến Chủ Nhật.
  - **Time Slot Interval**: Chọn khoảng chia phút cho dropdown chọn giờ (`15`, `30`, `45`, hoặc `60` phút).
  - **Prep Buffer (Minutes)**: Thời gian chuẩn bị tối thiểu (ví dụ: `25` phút) trước khi khách có thể chọn slot giờ sớm nhất.
  - **EC Card Min Total (€)**: Giá trị đơn hàng tối thiểu áp dụng cho thanh toán thẻ.
  - **Fulfillment Method Toggles**: Tích chọn **Enable Local Pickup** và/hoặc **Enable Delivery**.

#### 2. Tab Style & Colors (Tùy Chỉnh Màu Sắc & Giao Diện)
* Tùy chỉnh màu nút bấm chính, màu hover, bo góc nút (`border-radius`), chiều rộng Sidebar, chiều rộng Modal, và chiều rộng Giỏ hàng trượt Drawer.
* Nút tròn màu sắc tự động nhận diện bảng màu của các theme phổ biến (Blocksy, Astra, GeneratePress).

#### 3. Tab Email Form & Templates (Cấu Hình Mẫu Email HTML)
* **Admin Recipients**: Nhập một hoặc nhiều email quản trị viên cách nhau bằng dấu phẩy (ví dụ: `admin@restaurant.de, manager@restaurant.de`).
* **Admin Notification Email Language**: Chọn `Follow order language`, `Always English`, hoặc `Always German`.
* **Enable Custom HTML Email Templates**: Tích chọn để bật mẫu email tùy chỉnh.
* **Mẫu Email Khách Hàng & Quản Trị Viên (EN / DE)**:
  - Chọn tab `🇬🇧 English (EN)` hoặc `🇩🇪 German (DE)` để chỉnh sửa tiêu đề và nội dung HTML.
  - Sử dụng các thẻ thay thế: `{restaurant_name}`, `{restaurant_address}`, `{restaurant_logo}`, `{customer_name}`, `{customer_email}`, `{customer_phone}`, `{order_number}`, `{order_date}`, `{order_table}`, `{fulfillment_method}`, `{pickup_delivery_time}`, `{special_request}`.

---

### BƯỚC 4: TẠO SẢN PHẨM & DANH MỤC WOOCOMMERCE
1. Vào **Sản phẩm (Products) → Danh mục (Categories)**:
   - Tạo các danh mục (ví dụ: *Khai vị*, *Món chính*, *Đồ uống*).
   - Tải lên ảnh đại diện danh mục (Thumbnail) và nhập mô tả ngắn nếu muốn hiển thị trên Webshop.
2. Vào **Sản phẩm (Products) → Thêm mới (Add New)**:
   - Tạo sản phẩm đơn giản hoặc sản phẩm tùy chọn (Variable Product với các chủng loại / kích cỡ).
   - Đặt giá bán và chọn Danh mục tương ứng.

---

### BƯỚC 5: HIỂN THỊ TRANG WEBSHOP
1. Nếu chưa có trang Webshop, vào **Trang (Pages) → Thêm trang mới (Add New)**.
2. Đặt tiêu đề `Webshop` và slug đường dẫn `/webshop/`.
3. Chèn shortcode: `[two_step_webshop_layout]`
4. Đăng trang (Publish).

---

## Frequently Asked Questions

= Làm sao để khách hàng chọn thời gian giao hàng/nhận hàng? =
Khi khách hàng nhấn "Tiến hành thanh toán" trên Webshop, một bảng điều khiển sẽ xuất hiện cho phép chọn ngày và khung giờ (Time Slot) còn mở cửa.

= Tôi có thể sử dụng plugin này với website Đa ngôn ngữ (Polylang / WPML) không? =
Có! Plugin hỗ trợ tự động đăng ký chuỗi dịch (`pll_register_string`) và tự động gửi email xác nhận bằng tiếng Anh hoặc tiếng Đức dựa theo ngôn ngữ đơn hàng của khách.

== Changelog ==

= 1.4.9 =
* Chuẩn hóa toàn bộ cấu trúc Admin UI: chuyển sang dạng block layout 100% width, loại bỏ lỗi lệch cột 3 trên màn hình Desktop.
* Khôi phục thẻ cấu hình Bật/Tắt phương thức nhận hàng (Local Pickup & Delivery) trong Admin UI Tab 1.
* Sửa lỗi lồng thẻ HTML div gây đẩy lệch Tab 2 và Tab 3 ra ngoài container chính.
* Nâng cấp độ phân giải ảnh hiển thị trong Modal xem chi tiết sản phẩm (Product Info Popup) từ medium thumbnail sang full-resolution.
* Bổ sung tính năng tự động khởi tạo danh sách kiểm tra cài đặt (Checklist & Prerequisites) và liên kết cấu hình nhanh trong Admin UI.
* Tự động điều chỉnh khớp lề Brand Header khi không tải ảnh logo cửa hàng.

= 1.4.8 =
* Sửa lỗi "No shipping method has been selected" bằng cơ chế tự động tạo phương thức giao hàng dự phòng (Fallback Rate).
* Tự động ẩn checkbox "Ship to a different address?" và các trường địa chỉ giao hàng riêng khi chọn Local Pickup.

= 1.4.7 =
* Thêm tùy chọn hiển thị Ảnh danh mục, Mô tả danh mục dạng cột đứng (Image -> Title -> Description -> Products).
* Thêm tùy chọn Căn chỉnh văn bản (Text Alignment: Left, Center, Right).
* Tự động khởi tạo trang Webshop và bật COD khi kích hoạt plugin trên WordPress mới.

= 1.4.6 =
* Thêm tùy chọn hiển thị giá sản phẩm Variable (Cheapest Price Only vs Full Price Range).
* Thêm mẫu Email HTML mặc định chuẩn đẹp phân loại theo Tiếng Anh (EN) và Tiếng Đức (DE).
* Tối ưu Admin UI, thêm nút Sao chép Shortcode 1-click và cảnh báo bảo vệ phương thức đặt hàng.

= 1.4.5 =
* Kiểm tra và rà soát toàn bộ 53 tùy chọn Admin UI.
* Sửa lỗi PHP Undefined variable $yesterday_day trong bộ lịch mở cửa qua đêm.
