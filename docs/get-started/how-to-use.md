# How to Use

## Basic Usage Flow

1. **Frontend**: Sign the request payload before sending
2. **Backend**: Verify the signature before processing

---

## JavaScript / TypeScript

### Sign a Request (Frontend)

:::tabs
== Axios Interceptor

```typescript
import axios from "axios";
import { XSignClient, createXSignInterceptor } from "x-sign-payload";

const xSign = new XSignClient({
  secret: process.env.X_SIGN_SECRET,
  algorithm: "sha256",
});

const apiClient = axios.create({
  baseURL: "https://api.example.com",
  headers: { "Content-Type": "application/json" },
});

// Add interceptor to auto-sign requests
apiClient.interceptors.request.use(createXSignInterceptor(xSign));

// Now all POST/PUT/PATCH requests are automatically signed
const response = await apiClient.post("/register", {
  email: "user@example.com",
  name: "John Doe",
});
```

== Fetch Wrapper

```typescript
import { createXSignFetch } from "x-sign-payload";

const xSignFetch = createXSignFetch({
  secret: process.env.X_SIGN_SECRET,
  baseUrl: "https://api.example.com",
});

const response = await xSignFetch("/register", {
  method: "POST",
  body: {
    email: "user@example.com",
    name: "John Doe",
  },
});
```

== Manual Signing

```typescript
import { XSignClient } from "x-sign-payload";

const xSign = new XSignClient({ secret: "your-secret" });

// Generate headers manually
const headers = await xSign.sign({
  email: "user@example.com",
  name: "John Doe",
});

// headers = {
//   'X-Timestamp': '1714291200000',
//   'X-Signature': 'sha256=a3f9c2e8...',
// }

fetch("https://api.example.com/register", {
  method: "POST",
  headers: headers,
  body: JSON.stringify({ email: "user@example.com", name: "John Doe" }),
});
```

:::

---

## PHP

### Verify a Request (Backend)

The middleware automatically verifies incoming requests. If verification fails, it throws an appropriate exception:

```php
use Wanadri\XSignPayload\Exceptions\MissingHeadersException;
use Wanadri\XSignPayload\Exceptions\ExpiredRequestException;
use Wanadri\XSignPayload\Exceptions\InvalidSignatureException;

// In your exception handler
public function render($request, Throwable $exception)
{
    if ($exception instanceof MissingHeadersException) {
        return response()->json(
            ['error' => 'Missing signature headers'],
            401
        );
    }

    if ($exception instanceof ExpiredRequestException) {
        return response()->json(
            ['error' => 'Request expired - timestamp too old'],
            401
        );
    }

    if ($exception instanceof InvalidSignatureException) {
        return response()->json(
            ['error' => 'Invalid signature'],
            401
        );
    }

    return parent::render($request, $exception);
}
```

### Manual Verification

```php
use Wanadri\XSignPayload\Core\SignatureVerifier;
use Wanadri\XSignPayload\Core\Config;

$config = new Config([
    'secret' => 'your-secret',
    'algorithm' => 'sha256',
    'enable_timestamp' => true,
    'replay_window' => 10,
]);

$verifier = new SignatureVerifier();

$isValid = $verifier->verify(
    signature: 'sha256=a3f9c2e8d4b71a6f...', // From X-Signature header
    body: '{"email":"user@example.com"}',      // Raw request body
    config: $config,
    timestamp: 1714291200000                   // From X-Timestamp header
);
```

---

## Configuration Options

### Algorithm

Choose between `sha256` (default) or `sha512`:

```typescript
const xSign = new XSignClient({
  secret: "your-secret",
  algorithm: "sha512", // Stronger but slower
});
```

### Disable Timestamp (Not Recommended)

For specific use cases where timestamp validation isn't needed:

```typescript
const xSign = new XSignClient({
  secret: "your-secret",
  enableTimestamp: false, // Disables replay protection
});
```

:::warning Caution
Disabling timestamp removes replay attack protection. Only use this for specific scenarios like webhooks with idempotency keys.
:::

### Custom Replay Window

Adjust the acceptable timestamp drift:

```typescript
const xSign = new XSignClient({
  secret: "your-secret",
  replayWindow: 5, // 5 minutes (default is 10)
});
```

---

## Working with Different Content Types

### JSON Payload (Default)

```typescript
// Automatically handled - no extra config needed
const headers = await xSign.sign({ key: "value" });
```

### Form Data

```typescript
const formData = new FormData();
formData.append("file", file);

// Convert to string for signing
const bodyString = JSON.stringify(Object.fromEntries(formData));
const headers = await xSign.sign(bodyString);
```

### Plain Text

```typescript
const headers = await xSign.sign("plain text payload");
```

---

## Error Handling

### JavaScript

```typescript
import { XSignClient } from "x-sign-payload";

try {
  const xSign = new XSignClient({ secret: "" }); // Empty secret
} catch (error) {
  console.error("Configuration error:", error.message);
}

// Common errors:
// - Missing secret
// - Invalid algorithm choice
// - Payload too large
```

### PHP

```php
use Wanadri\XSignPayload\Exceptions\XSignException;

try {
    $config = new Config(['secret' => '']);
} catch (XSignException $e) {
    // Handle configuration error
}
```

---

## Next Steps

- [Middleware Implementation](./middleware-implementation) - Learn how to protect your API routes
- Check the API reference for advanced usage patterns
