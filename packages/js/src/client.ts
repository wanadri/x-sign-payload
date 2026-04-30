import type { XSignConfig, SignOptions, SignResult } from './types';
import { Signer } from './signer';

/**
 * High-level client for signing requests
 * Works in both Node.js and Browser environments
 */
export class XSignClient {
  private signer: Signer;
  private config: XSignConfig;

  constructor(config: XSignConfig) {
    this.config = config;
    this.signer = new Signer(config);
  }

  /**
   * Sign a request payload (Node.js only - synchronous)
   */
  sign(body: string | object, options?: SignOptions): SignResult {
    const bodyString = typeof body === 'string' ? body : JSON.stringify(body);
    return this.signer.sign(bodyString, options);
  }

  /**
   * Sign a request payload (Browser - async with Web Crypto API)
   */
  async signAsync(body: string | object, options?: SignOptions): Promise<SignResult> {
    const bodyString = typeof body === 'string' ? body : JSON.stringify(body);
    
    // Browser environment
    if (typeof window !== 'undefined' && window.crypto) {
      return this.signBrowser(bodyString, options);
    }

    // Node.js - use sync version
    return this.signer.sign(bodyString, options);
  }

  /**
   * Browser signing using Web Crypto API
   */
  private async signBrowser(body: string, options?: SignOptions): Promise<SignResult> {
    const algorithm = options?.algorithm ?? this.config.algorithm ?? 'sha256';
    const enableTimestamp = this.config.enableTimestamp ?? true;
    
    let message: string;
    let timestamp: string | undefined;

    if (enableTimestamp) {
      timestamp = String(options?.timestamp ?? Date.now());
      message = `${timestamp}.${body}`;
    } else {
      message = body;
    }

    const encoder = new TextEncoder();
    const keyData = encoder.encode(this.config.secret);
    const messageData = encoder.encode(message);

    const cryptoKey = await window.crypto.subtle.importKey(
      'raw',
      keyData,
      { name: 'HMAC', hash: algorithm === 'sha512' ? 'SHA-512' : 'SHA-256' },
      false,
      ['sign']
    );

    const signature = await window.crypto.subtle.sign('HMAC', cryptoKey, messageData);
    const hashArray = Array.from(new Uint8Array(signature));
    const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('');

    const fullSignature = `${algorithm}=${hashHex}`;

    const headers: SignResult['headers'] = {
      'X-Signature': fullSignature,
      'Content-Type': 'application/json',
    };

    if (timestamp) {
      headers['X-Timestamp'] = timestamp;
    }

    return {
      headers,
      signature: fullSignature,
      timestamp,
    };
  }
}

/**
 * Generate a secure random secret
 */
export function generateSecret(bytes: number = 32): string {
  if (typeof window !== 'undefined' && window.crypto) {
    // Browser
    const array = new Uint8Array(bytes);
    window.crypto.getRandomValues(array);
    return Array.from(array)
      .map(b => b.toString(16).padStart(2, '0'))
      .join('');
  }

  // Node.js
  const crypto = require('crypto');
  return crypto.randomBytes(bytes).toString('hex');
}
