# Quick Setup

## PHP Setup

:::tabs
== Laravel
**1. Publish the Config**

```bash
php artisan vendor:publish --provider="Wanadri\XSignPayload\Frameworks\Laravel\XSignPayloadServiceProvider"
```

**2. Generate Secret**

```bash
php artisan x-sign:install
```

This command will generate a secure 256-bit secret and add it to your `.env` file.

**3. Configure Environment**

```env
X_SIGN_SECRET=your_generated_secret_here
X_SIGN_ALGORITHM=sha256
X_SIGN_ENABLE_TIMESTAMP=true
X_SIGN_REPLAY_WINDOW=10
```

**4. Configure Settings (Optional)**

```php
// config/x-sign-payload.php
return [
    'secret' => env('X_SIGN_SECRET'),
    'algorithm' => env('X_SIGN_ALGORITHM', 'sha256'),
    'enable_timestamp' => env('X_SIGN_ENABLE_TIMESTAMP', true),
    'replay_window' => env('X_SIGN_REPLAY_WINDOW', 10),
];
```

== CakePHP
**1. Load the Plugin**

```bash
bin/cake plugin load Wanadri/XSignPayload
```

**2. Generate Secret**

```bash
bin/cake xsign generate-secret
```

**3. Configure**

```php
// config/x_sign_payload.php
<?php
return [
    'secret' => env('X_SIGN_SECRET'),
    'algorithm' => 'sha256',
    'enable_timestamp' => true,
    'replay_window' => 10,
];
```

== CodeIgniter 4
**1. Copy Config File**

```bash
cp app/ThirdParty/x-sign-payload/Config/XSign.php app/Config/
```

**2. Generate Secret**

```bash
php spark xsign:generate-secret
```

**3. Configure**

```php
<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class XSign extends BaseConfig
{
    public string $secret = 'your_generated_secret_here';
    public string $algorithm = 'sha256';
    public bool $enableTimestamp = true;
    public int $replayWindow = 10;
}
```

:::

---

## JavaScript / TypeScript Setup

### 1. Create Client Instance

```typescript
import { XSignClient } from "x-sign-payload";

const xSign = new XSignClient({
  secret: process.env.X_SIGN_SECRET, // Or pass directly
  algorithm: "sha256", // 'sha256' | 'sha512'
  enableTimestamp: true, // Enable replay protection
});

export default xSign;
```

### 2. Environment Variables

Create `.env` file:

```env
X_SIGN_SECRET=your_secret_here
X_SIGN_ALGORITHM=sha256
```

---

## Verify Your Setup

:::tabs
== PHP

```bash
php artisan tinker
> config('x-sign-payload.secret');
```

== JavaScript

```typescript
import { XSignClient } from "x-sign-payload";

const client = new XSignClient({ secret: "test" });
const headers = await client.sign({ test: "data" });
console.log(headers);
```

:::

---

## Next Steps

- [How to Use](./how-to-use) - Learn how to sign and verify requests
- [Middleware Implementation](./middleware-implementation) - Protect your routes
