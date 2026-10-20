# Magepow Core - Thông báo admin (Update / Security / Promotion)

> Quy ước (tùy chọn A): giá trị mặc định nằm trong
> `etc/config.xml` và KHÔNG được expose thành field nhập liệu trên admin.
> Người bán hàng chỉ được bật/tắt (Yes/No). Muốn đổi feed URL hay nội dung
> thông báo thì sửa `etc/config.xml` và release bản build mới.

Module này bổ sung hệ thống thông báo admin chuẩn của Magento dựa trên
`Magento_AdminNotification`.

## 1. Module gồm những gì

| Loại | Hiển thị ở đâu | Giá trị lấy từ đâu |
|---|---|---|
| Thông báo update | System Messages (banner đầu trang admin) | `magepow_core/notification/update/*` trong `config.xml` |
| Thông báo bảo mật | System Messages | `magepow_core/notification/security/*` trong `config.xml` |
| Khuyến mãi | System Messages | `magepow_core/notification/promotion/*` trong `config.xml` |
| Feed (RSS/JSON từ xa) | Notifications inbox (`adminnotification_inbox`) | `magepow_core/notification/feed_url` trong `config.xml` |

Các message được đăng ký trong message pool tại `etc/di.xml`
(`Magento\Framework\Notification\MessageList`).

## 2. Cài đặt / nâng cấp

```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## 3. Cấu hình (Stores > Configuration > Magepow > Community > Admin Notifications)

Người bán hàng chỉ được sửa các mục sau (công tắc Yes/No và một nút bấm):

- **Enable Notifications**: công tắc chính (tắt thì ẩn tất cả).
- **Fetch Now**: kéo feed từ xa ngay lập tức.
- **Receive Product Updates / Security / News / Special Offers**: bật/tắt từng loại
  thông báo (gửi lên server để server lọc nội dung).
- **Update / Security / Promotion - Enabled**: hiện/ẩn từng banner đầu trang.

KHÔNG có field nhập URL, nội dung, severity hay tần suất trên admin.
Muốn đổi các giá trị này:

| Cần đổi | Nơi cần sửa |
|---|---|
| Feed URL | `etc/config.xml` > `magepow_core/notification/feed_url` |
| Tần suất | `etc/config.xml` > `.../frequency` (đơn vị giờ, mặc định 48) |
| Latest version | `etc/config.xml` > `.../update/latest_version` |
| Security text / URL / severity | `etc/config.xml` > `.../security/*` |
| Promotion text / URL / severity / dates | `etc/config.xml` > `.../promotion/*` |

Sau đó chạy `setup:upgrade` và `cache:flush` để áp dụng thay đổi.

## 4. Cách sử dụng

### 4.1. Xem thông báo

- **System Messages**: banner ở đầu mọi trang admin, có nút dismiss của Magento.
- **Inbox**: icon chuông > View All; popup vàng "Incoming Message" hiện ra khi
  đăng nhập nếu có tin chưa đọc.

### 4.2. Đẩy message thủ công (test / khẩn cấp)

```bash
php bin/magento magepow:notify --type=feed --force
php bin/magento magepow:notify --type=security --title="Security patch" --message="Update now" --url="https://magepow.com/security.html"
```

### 4.3. Tự động làm mới

- Observer trên `controller_action_predispatch` kéo feed khi admin duyệt backend
  (chống gọi trùng hai lần: session backend cộng timestamp đã lưu).
- Cron job `magepow_core_fetch_feed` chạy mỗi giờ (`etc/crontab.xml`).

## 5. Ghi chú kỹ thuật

- Feed URL được nối thêm các đoạn tham số:
  `/domain/{domain}/modules/{base64}/notifications/{base64}` (domain shop cộng
  version của các module `Magepow_*` đã cài và preferences) để server biết cần
  trả về gì.
- Chỉ import các item mới hơn ngày cài đặt; giá trị được escape bằng
  `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE)`.
- Bỏ qua khi `Magento_AdminNotification` bị disable; request gửi kèm header
  `User-Agent` và `Referer`.
- Lỗi feed được bắt exception và không bao giờ làm hỏng khu vực admin.
