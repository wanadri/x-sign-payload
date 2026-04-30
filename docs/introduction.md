# Introduction

## What is x-sign-payload?

**x-sign-payload** is a cross-language request signing package that provides secure HMAC-SHA256/SHA512 payload verification for multiple programming languages and frameworks.

## Purpose

The primary purpose of this package is to ensure **request integrity and authenticity** between your frontend applications and backend APIs. By cryptographically signing request payloads, you can:

- ✅ **Verify Request Authenticity** - Ensure requests come from trusted sources
- ✅ **Prevent Tampering** - Detect if payload data has been modified in transit
- ✅ **Block Replay Attacks** - Timestamp validation prevents old requests from being reused
- ✅ **Secure API Communication** - Add an extra layer of security beyond HTTPS

## How It Works

### Request Flow (Frontend to Backend)

```
1. User submits form
        ↓
2. Payload → JSON.stringify() → bodyString
        ↓
3. timestamp = Date.now()
        ↓
4. message = `${timestamp}.${bodyString}`
        ↓
5. signature = HMAC-SHA256(API_SECRET, message) → hex
        ↓
6. Headers: X-Timestamp, X-Signature → Backend
```

### Verification Flow (Backend)

```
1. Read X-Timestamp and X-Signature headers
        ↓
2. Check timestamp drift (default: 10 minutes)
        ↓
3. Reconstruct message: `${timestamp}.${body}`
        ↓
4. Compute expected signature
        ↓
5. Constant-time comparison
        ↓
6. ✅ Valid → Process request
        ↓
6. ❌ Invalid → 401 Unauthorized
```

## Supported Languages & Frameworks

| Language | Framework | Status |
|----------|-----------|--------|
| PHP | Laravel 10+ | ✅ Available |
| PHP | CakePHP 4+ | ✅ Available |
| PHP | CodeIgniter 4 | ✅ Available |
| JavaScript | Node.js / Browser | ✅ Available |
| Python | Django / Flask | 🚧 Coming Soon |
| Ruby | Ruby on Rails | 🚧 Coming Soon |

## Key Features

### 🔐 Cryptographic Security
- **HMAC-SHA256** and **HMAC-SHA512** algorithms
- **256-bit secret keys** with secure generation
- **Constant-time comparison** to prevent timing attacks

### ⏱️ Replay Protection
- Configurable timestamp validation
- Default 10-minute replay window
- Optional timestamp disabling for specific use cases

### 🛠️ Developer Experience
- Simple installation via package managers
- Framework-native middleware/filters
- Auto-generated secrets with CLI commands
- Consistent APIs across all languages

### ⚡ Performance
- Minimal overhead on request processing
- No external dependencies for core functionality
- Optimized for high-throughput applications

## When to Use This Package

**Ideal for:**
- API authentication between microservices
- Webhook verification
- Mobile app API security
- Third-party integration security
- Internal API protection

**Not suitable for:**
- Public/open APIs (use OAuth/API keys instead)
- Simple static websites
- When HTTPS is not available (signing requires transport security)

## Next Steps

- [Get Started](./get-started/installation) - Installation and setup guide
- [Support the Project](./support-me) - Help us maintain and improve
- [View Changelog](./changelog) - See what's new

---

*Secure your requests, protect your data.*
