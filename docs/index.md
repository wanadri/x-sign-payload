---
layout: home

hero:
  name: "x-sign-payload"
  text: "Secure Request Signing"
  tagline: Cross-language HMAC-SHA256/SHA512 payload verification for PHP and JavaScript
  actions:
    - theme: brand
      text: Get Started
      link: /introduction
    - theme: alt
      text: View on GitHub
      link: https://github.com/wanadri/x-sign-payload

features:
  - icon: 🔐
    title: HMAC-SHA256/SHA512
    details: Industry-standard cryptographic hashing algorithms for secure signature generation.
  - icon: ⏱️
    title: Replay Protection
    details: Configurable timestamp validation prevents replay attacks with customizable window.
  - icon: 🛠️
    title: Multi-Framework
    details: Native support for Laravel, CakePHP, CodeIgniter, and JavaScript/TypeScript.
  - icon: ⚡
    title: Easy Integration
    details: Simple middleware setup with auto-generated secrets and CLI commands.
---

## Supported Languages & Frameworks

| Language   | Framework         | Status         |
| ---------- | ----------------- | -------------- |
| PHP        | Laravel 10+       | ✅ Available   |
| PHP        | CakePHP 4+        | ✅ Available   |
| PHP        | CodeIgniter 4     | ✅ Available   |
| JavaScript | Node.js / Browser | ✅ Available   |
| Python     | Django / Flask    | 🚧 Coming Soon |
| Ruby       | Ruby on Rails     | 🚧 Coming Soon |

<Playground />

## Try It Now

Generate a signed payload right here. Configure your secret key, choose whether to include timestamp protection, and see the generated signature in real-time. Toggle between different programming languages to see the implementation code.
