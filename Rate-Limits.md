# Telegram Bot API Rate Limits (2026)

| Category | Limit | Notes |
|-----------|---------|---------|
| Global bot token | ~30 messages/sec | Shared across all chats, groups, and channels for a single bot token. |
| Private chat | ~1 message/sec/chat | Short bursts are tolerated. |
| Group | ~20 messages/min/group | Applies per group. |
| Channel | ~20 messages/min/channel | Telegram does not officially document a separate channel limit, but practical behavior is similar to groups. |
| Paid Broadcast | Up to 1000 messages/sec | Requires Telegram Stars paid broadcast feature. |
| getUpdates | No documented rate limit | Does not count toward message sending limits. |
| Webhook connections | Up to 100 concurrent connections | Controlled by `max_connections`. |
| HTTP 429 response | `retry_after` seconds | Telegram specifies how long to wait before retrying. |
| Hourly limit | Not documented | Telegram uses second/minute windows instead. |
| Daily limit | Not documented | No known daily cap if rate limits are respected. |
| sendMessage | Counts toward limits | Outgoing message. |
| sendPhoto | Counts toward limits | Outgoing media. |
| sendVideo | Counts toward limits | Outgoing media. |
| sendDocument | Counts toward limits | Outgoing file. |
| sendMediaGroup | Counts toward limits | Media items may be counted individually. |
| editMessageText | Counts toward limits | Message edits are rate-limited. |
| editMessageCaption | Counts toward limits | Caption edits are rate-limited. |
| deleteMessage | Counts toward limits | Deletions are rate-limited. |
| answerCallbackQuery | Counts toward limits | Callback responses are rate-limited. |
| sendChatAction | Usually counts | Typing/upload indicators. |
| --- | --- | --- |
| Safe bulk broadcast rate | 20–25 messages/sec | Recommended to avoid 429 errors. |
| Safe AI streaming edits | 1–2 edits/sec/chat | Recommended for continuously edited messages. |
| Safe news channel posting | ≤20 messages/min/channel | Recommended operational limit. |
| Large bots (>100k users) | Queue + rate limiter recommended | Helps avoid bursts and throttling. |
| Scope of limits | Per bot token | Limits are not shared between different bot tokens, even on the same server/IP. |
