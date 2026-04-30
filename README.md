# x-sign-payload

> Secure HMAC-SHA256/SHA512 payload signing and verification for PHP and JavaScript

[![PHP Tests](https://github.com/wanadri/x-sign-payload/actions/workflows/test.yml/badge.svg)](https://github.com/wanadri/x-sign-payload/actions)
[![npm version](https://badge.fury.io/js/x-sign-payload.svg)](https://www.npmjs.com/package/x-sign-payload)
[![Packagist Version](https://img.shields.io/packagist/v/wanadri/x-sign-payload)](https://packagist.org/packages/wanadri/x-sign-payload)

## Features

- 🔐 **HMAC-SHA256/SHA512** - Industry-standard cryptographic hashing
- ⏱️ **Replay Protection** - Configurable timestamp validation
- 🛠️ **Multi-Framework** - Laravel, CakePHP, CodeIgniter, Node.js, Browser
- ⚡ **Easy Integration** - Simple middleware and auto-signing
- 🌐 **Cross-Language** - Sign in JS, verify in PHP (and vice versa)

## Quick Start

### PHP (Laravel)
```bash
composer require wanadri/x-sign-payload
php artisan x-sign:install
```

```php
// routes/api.php
Route::post('/webhook', [WebhookController::class, 'handle'])
    ->middleware('x-sign.verify');
```

### JavaScript (Node.js)
```bash
npm install x-sign-payload
```

```javascript
import { XSignClient } from 'x-sign-payload';

const client = new XSignClient({ 
  secret: process.env.X_SIGN_SECRET 
});

const { headers } = client.sign({ user: 'john@example.com' });
// headers = { 'X-Signature': 'sha256=...', 'X-Timestamp': '...' }
```

### JavaScript (Browser)
```javascript
import { XSignClient } from 'x-sign-payload';

const client = new XSignClient({ secret: 'your-secret' });

const headers = await client.signAsync({ action: 'submit' });
fetch('/api/submit', { 
  method: 'POST', 
  headers,
  body: JSON.stringify({ action: 'submit' })
});
```

## Documentation

Full documentation is available at: https://wanadri.github.io/x-sign-payload/

- [Installation](https://wanadri.github.io/x-sign-payload/get-started/installation)
- [Quick Setup](https://wanadri.github.io/x-sign-payload/get-started/quick-setup)
- [How to Use](https://wanadri.github.io/x-sign-payload/get-started/how-to-use)
- [Middleware Implementation](https://wanadri.github.io/x-sign-payload/get-started/middleware-implementation)

## Supported Languages & Frameworks

| Language | Framework | Status |
|----------|-----------|--------|
| PHP | Laravel 10+ | ✅ Available |
| PHP | CakePHP 4+ | ✅ Available |
| PHP | CodeIgniter 4 | ✅ Available |
| JavaScript | Node.js / Browser | ✅ Available |
| Python | Django / Flask | 🚧 Coming Soon |
| Ruby | Ruby on Rails | 🚧 Coming Soon |

## Monorepo Structure

```
x-sign-payload/
├── packages/
│   ├── php/          # PHP package (wanadri/x-sign-payload)
│   └── js/           # JavaScript package (x-sign-payload)
├── docs/             # VitePress documentation
└── tests/
    └── cross-language/ # JS/PHP interoperability tests
```

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md).

## Support

- ⭐ Star this repository
- 🐛 [Report issues](https://github.com/wanadri/x-sign-payload/issues)
- ☕ [Buy me a coffee](https://www.buymeacoffee.com/wanadri)

## License

MIT License - see [LICENSE](LICENSE) file.

---

Made with ❤️ by [wanadri](https://github.com/wanadri)
