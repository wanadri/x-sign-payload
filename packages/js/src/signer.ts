import type { XSignConfig, SignOptions, VerifyOptions, SignResult } from './types';

/**
 * Error thrown when signature verification fails
 */
export class XSignError extends Error {
  constructor(message: string) {
    super(message);
    this.name = 'XSignError';
  }
}

/**
 * Core signer class for HMAC-SHA256/SHA512 payload signing
 */
export class Signer {
  private secret: string;
  private algorithm: 'sha256' | 'sha512';
  private enableTimestamp: boolean;
  private replayWindow: number;

  constructor(config: XSignConfig) {
    if (!config.secret) {
      throw new XSignError('Secret is required');
    }
    
    this.secret = config.secret;
    this.algorithm = config.algorithm ?? 'sha256';
    this.enableTimestamp = config.enableTimestamp ?? true;
    this.replayWindow = config.replayWindow ?? 10;

    if (!['sha256', 'sha512'].includes(this.algorithm)) {
      throw new XSignError('Algorithm must be sha256 or sha512');
    }

    if (this.replayWindow < 1) {
      throw new XSignError('Replay window must be at least 1 minute');
    }
  }

  /**
   * Sign a request payload
   */
  sign(body: string, options?: SignOptions): SignResult {
    const algorithm = options?.algorithm ?? this.algorithm;
    
    let message: string;
    let timestamp: string | undefined;

    if (this.enableTimestamp) {
      timestamp = String(options?.timestamp ?? Date.now());
      message = `${timestamp}.${body}`;
    } else {
      message = body;
    }

    const signature = `${algorithm}=${this.hmac(message, algorithm)}`;

    const headers: SignResult['headers'] = {
      'X-Signature': signature,
      'Content-Type': 'application/json',
    };

    if (timestamp) {
      headers['X-Timestamp'] = timestamp;
    }

    return {
      headers,
      signature,
      timestamp,
    };
  }

  /**
   * Verify a request signature
   */
  verify(options: VerifyOptions): boolean {
    const { signature, body, timestamp } = options;

    // Parse signature format
    const parts = signature.split('=');
    if (parts.length !== 2) {
      throw new XSignError('Invalid signature format');
    }

    const [algo, providedHash] = parts;

    if (algo !== this.algorithm) {
      throw new XSignError('Algorithm mismatch');
    }

    // Validate timestamp
    if (this.enableTimestamp) {
      if (!timestamp) {
        throw new XSignError('Timestamp required');
      }
      this.validateTimestamp(timestamp);
    }

    // Compute expected signature
    const message = this.enableTimestamp && timestamp
      ? `${timestamp}.${body}`
      : body;
    
    const expectedHash = this.hmac(message, algo as 'sha256' | 'sha512');

    // Constant-time comparison
    if (!this.timingSafeEqual(expectedHash, providedHash)) {
      throw new XSignError('Signature does not match');
    }

    return true;
  }

  /**
   * Generate HMAC signature
   */
  private hmac(message: string, algorithm: 'sha256' | 'sha512'): string {
    // Node.js environment
    if (typeof window === 'undefined') {
      const crypto = require('crypto');
      return crypto
        .createHmac(algorithm, this.secret)
        .update(message)
        .digest('hex');
    }

    // Browser environment - using Web Crypto API
    // Note: This is async in browser, so we use a synchronous approach
    // In production, you might want to make this async
    throw new XSignError('Browser signing requires async implementation. Use XSignClient instead.');
  }

  /**
   * Validate timestamp is within replay window
   */
  private validateTimestamp(timestamp: string): void {
    const now = Date.now();
    const requestTime = parseInt(timestamp, 10);
    const diffMinutes = Math.abs(now - requestTime) / 1000 / 60;

    if (diffMinutes > this.replayWindow) {
      throw new XSignError(`Request timestamp is outside ${this.replayWindow} minute window`);
    }
  }

  /**
   * Constant-time string comparison
   */
  private timingSafeEqual(a: string, b: string): boolean {
    if (a.length !== b.length) {
      return false;
    }

    let result = 0;
    for (let i = 0; i < a.length; i++) {
      result |= a.charCodeAt(i) ^ b.charCodeAt(i);
    }
    return result === 0;
  }
}
