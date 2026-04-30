# Middleware Implementation

Learn how to protect your API routes using middleware for each supported framework.

---

## PHP Frameworks

:::tabs
== Laravel
**Route-Level Middleware**

```php
use Illuminate\Support\Facades\Route;

// Single route
Route::post('/api/register', [RegisterController::class, 'store'])
    ->middleware('x-sign.verify');

// Route group
Route::middleware(['x-sign.verify'])->group(function () {
    Route::post('/api/register', [RegisterController::class, 'store']);
    Route::post('/api/login', [LoginController::class, 'store']);
});
```

**Global Middleware**

```php
// Laravel 11+ (bootstrap/app.php)
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\Wanadri\XSignPayload\Frameworks\Laravel\Middleware\VerifyXSignPayload::class);
})

// Laravel 10 (app/Http/Kernel.php)
protected $middleware = [
    \Wanadri\XSignPayload\Frameworks\Laravel\Middleware\VerifyXSignPayload::class,
];
```

**Exclude Routes**

```php
Route::post('/api/webhook', [WebhookController::class, 'handle'])
    ->withoutMiddleware(['x-sign.verify']);
```

== CakePHP
**Application Middleware**

```php
use Wanadri\XSignPayload\Frameworks\CakePHP\Middleware\XSignMiddleware;

public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    $middlewareQueue->add(new XSignMiddleware());
    return $middlewareQueue;
}
```

**Route-Specific**

```php
$routes->scope('/api', function (RouteBuilder $builder) {
    $builder->registerMiddleware('xsign', new XSignMiddleware());
    $builder->applyMiddleware('xsign');
    $builder->post('/register', 'Api::register');
});
```

**Conditional**

```php
$routes->scope('/api', function (RouteBuilder $builder) {
    $builder->get('/public/status', 'Api::status'); // Public

    $builder->registerMiddleware('xsign', new XSignMiddleware());
    $builder->applyMiddleware('xsign');
    $builder->post('/register', 'Api::register'); // Protected
});
```

== CodeIgniter 4
**Filter Configuration**

```php
// app/Config/Filters.php
class Filters extends BaseConfig
{
    public array $aliases = [
        'xsign' => \Wanadri\XSignPayload\Frameworks\CodeIgniter\Filters\XSignFilter::class,
    ];

    public array $globals = [
        'before' => [
            'xsign' => ['except' => ['api/public/*', 'webhook/*']],
        ],
    ];
}
```

**Route-Specific**

```php
// app/Config/Routes.php
$routes->group('api', ['filter' => 'xsign'], function($routes) {
    $routes->post('register', 'ApiController::register');
    $routes->post('login', 'ApiController::login');
});
```

**Controller-Level**

```php
class ApiController extends Controller
{
    public function __construct()
    {
        $this->filters = [
            'xsign' => ['except' => ['publicMethod']],
        ];
    }
}
```

:::

---

## JavaScript (Frontend)

While JavaScript doesn't have "middleware" in the backend sense, here are common patterns:

### Axios Global Interceptor

```typescript
import axios from "axios";
import { XSignClient, createXSignInterceptor } from "x-sign-payload";

const xSign = new XSignClient({
  secret: process.env.X_SIGN_SECRET,
});

// Apply globally
axios.interceptors.request.use(createXSignInterceptor(xSign));

// Or create dedicated client
const apiClient = axios.create({ baseURL: "https://api.example.com" });
apiClient.interceptors.request.use(createXSignInterceptor(xSign));

export { apiClient };
```

### Next.js API Routes

```typescript
// middleware.ts (in project root)
import { NextResponse } from "next/server";
import type { NextRequest } from "next/server";

export function middleware(request: NextRequest) {
  // Middleware runs before API routes
  // You can verify signatures here or use the backend package

  return NextResponse.next();
}

export const config = {
  matcher: "/api/:path*",
};
```

### Nuxt.js Plugin

```typescript
// plugins/xsign.client.ts
import { XSignClient, createXSignInterceptor } from "x-sign-payload";

export default defineNuxtPlugin((nuxtApp) => {
  const config = useRuntimeConfig();

  const xSign = new XSignClient({
    secret: config.public.xSignSecret,
  });

  // Apply to $fetch
  nuxtApp.hook("app:created", () => {
    globalThis.$fetch = globalThis.$fetch.create({
      onRequest({ options }) {
        // Add signature headers
        const headers = await xSign.sign(options.body);
        Object.assign(options.headers, headers);
      },
    });
  });
});
```

---

## Best Practices

### 1. Skip Unnecessary Routes

Don't sign/verify:

- Static assets
- Public health checks
- OAuth callbacks
- Routes with other auth mechanisms (JWT, OAuth)

```php
// Laravel example
Route::middleware(['x-sign.verify'])->group(function () {
    // Protected routes
})->withoutMiddleware(['x-sign.verify']);
```

### 2. Webhook Considerations

Webhooks often need special handling:

```php
// Disable timestamp for webhooks with idempotency
Route::post('/webhook/stripe', [WebhookController::class, 'handle'])
    ->middleware('x-sign.verify:skip_timestamp');
```

### 3. Error Responses

Return consistent error responses:

```php
// Laravel exception handler
public function register(): void
{
    $this->renderable(function (XSignException $e) {
        return response()->json([
            'error' => [
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'type' => class_basename($e),
            ]
        ], 401);
    });
}
```

### 4. Logging

Log verification failures for monitoring:

```php
// In middleware or exception handler
Log::warning('Signature verification failed', [
    'ip' => $request->ip(),
    'url' => $request->url(),
    'timestamp' => $request->header('X-Timestamp'),
    'error' => $exception->getMessage(),
]);
```

---

## Testing Your Implementation

### curl Test

```bash
# Generate signature (use your actual JS/PHP script)
curl -X POST https://api.example.com/register \
  -H "Content-Type: application/json" \
  -H "X-Timestamp: 1714291200000" \
  -H "X-Signature: sha256=your_signature_here" \
  -d '{"email":"test@example.com"}'
```

### Expected Responses

| Status                | Meaning                            |
| --------------------- | ---------------------------------- |
| 200                   | Signature valid, request processed |
| 401 Missing Headers   | X-Timestamp or X-Signature missing |
| 401 Expired           | Timestamp outside replay window    |
| 401 Invalid Signature | Signature doesn't match            |

---

## Troubleshooting

### "Missing signature headers"

- Ensure your frontend is actually sending the headers
- Check for CORS preflight issues
- Verify the interceptor is applied

### "Request expired"

- Check server and client time synchronization
- Increase replay window temporarily for testing
- Verify timezone settings

### "Invalid signature"

- Ensure same secret on both sides
- Check algorithm matches (sha256 vs sha512)
- Verify raw body isn't being modified
- Ensure timestamp format is correct (milliseconds)

---

_Your API is now secured with request signing!_
