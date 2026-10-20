# Magepow Core - Admin Notifications (Update / Security / Promotion)

> Convention (option A): default values live in
> `etc/config.xml` and are NOT exposed as admin input fields. Merchants can only
> opt in/out (Yes/No). To change the feed URL or message content, edit
> `etc/config.xml` and release a new build.

This module adds a standard Magento admin notification system based on
`Magento_AdminNotification`.

## 1. What it includes

| Type | Where it is displayed | Where the value comes from |
|---|---|---|
| Update notice | System Messages (header banner on every admin page) | `magepow_core/notification/update/*` in `config.xml` |
| Security notice | System Messages | `magepow_core/notification/security/*` in `config.xml` |
| Promotion | System Messages | `magepow_core/notification/promotion/*` in `config.xml` |
| Feed (remote RSS/JSON) | Notifications inbox (`adminnotification_inbox`) | `magepow_core/notification/feed_url` in `config.xml` |

Messages are registered in the message pool in `etc/di.xml`
(`Magento\Framework\Notification\MessageList`).

## 2. Install / upgrade

```bash
php bin/magento setup:upgrade
php bin/magento cache:flush
```

## 3. Configuration (Stores > Configuration > Magepow > Community > Admin Notifications)

Merchants can only change these (Yes/No switches and one button):

- **Enable Notifications**: master switch (disabled hides everything).
- **Fetch Now**: pulls the remote feed immediately.
- **Receive Product Updates / Security / News / Special Offers**: opt in/out per
  notification type (sent to the server so it can filter items).
- **Update / Security / Promotion - Enabled**: show or hide each header banner.

There are NO admin input fields for URL, message, severity, or frequency.
To change those values:

| What to change | Where to edit |
|---|---|
| Feed URL | `etc/config.xml` > `magepow_core/notification/feed_url` |
| Frequency | `etc/config.xml` > `.../frequency` (hours, default 48) |
| Latest version | `etc/config.xml` > `.../update/latest_version` |
| Security text / URL / severity | `etc/config.xml` > `.../security/*` |
| Promotion text / URL / severity / dates | `etc/config.xml` > `.../promotion/*` |

Run `setup:upgrade` and `cache:flush` afterwards to deploy the change.

## 4. Usage

### 4.1. Viewing notifications

- **System Messages**: banner at the top of every admin page with Magento's
  dismiss action.
- **Inbox**: bell icon > View All; yellow "Incoming Message" popup on login when
  unread items exist.

### 4.2. Push a message manually (test / urgent)

```bash
php bin/magento magepow:notify --type=feed --force
php bin/magento magepow:notify --type=security --title="Security patch" --message="Update now" --url="https://magepow.com/security.html"
```

### 4.3. Automatic refresh

- Observer on `controller_action_predispatch` pulls the feed when an admin browses
  the backend (double-guard: backend session plus persisted timestamp).
- Cron job `magepow_core_fetch_feed` runs hourly (`etc/crontab.xml`).

## 5. Technical notes

- Feed URL is enriched with query segments:
  `/domain/{domain}/modules/{base64}/notifications/{base64}` (shop domain plus
  installed `Magepow_*` module versions and preferences) so the server knows what
  to return.
- Only items newer than the installation date are imported; values are escaped
  with `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE)`.
- Skipped when `Magento_AdminNotification` is disabled; requests send
  `User-Agent` and `Referer` headers.
- Feed errors are caught and never break the admin area.
