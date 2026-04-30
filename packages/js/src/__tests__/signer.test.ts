import { describe, it, expect } from 'vitest';
import { Signer, XSignError } from '../signer';

describe('Signer', () => {
  const config = {
    secret: 'test-secret-key-32bytes-long!!',
    algorithm: 'sha256' as const,
    enableTimestamp: false,
  };

  describe('constructor', () => {
    it('should require secret', () => {
      expect(() => new Signer({ secret: '' })).toThrow(XSignError);
    });

    it('should validate algorithm', () => {
      expect(() => new Signer({ 
        secret: 'test', 
        algorithm: 'md5' as any 
      })).toThrow(XSignError);
    });

    it('should validate replay window', () => {
      expect(() => new Signer({ 
        secret: 'test', 
        replayWindow: 0 
      })).toThrow(XSignError);
    });
  });

  describe('sign without timestamp', () => {
    it('should sign body', () => {
      const signer = new Signer(config);
      const result = signer.sign('{"test":"data"}');

      expect(result.signature).toMatch(/^sha256=[a-f0-9]{64}$/);
      expect(result.headers['X-Signature']).toBe(result.signature);
      expect(result.timestamp).toBeUndefined();
    });

    it('should include Content-Type header', () => {
      const signer = new Signer(config);
      const result = signer.sign('test');

      expect(result.headers['Content-Type']).toBe('application/json');
    });
  });

  describe('sign with timestamp', () => {
    it('should include timestamp', () => {
      const signer = new Signer({ ...config, enableTimestamp: true });
      const result = signer.sign('{"test":"data"}');

      expect(result.timestamp).toBeDefined();
      expect(result.headers['X-Timestamp']).toBe(result.timestamp);
    });

    it('should use custom timestamp', () => {
      const signer = new Signer({ ...config, enableTimestamp: true });
      const customTimestamp = 1234567890000;
      
      const result = signer.sign('test', { timestamp: customTimestamp });

      expect(result.timestamp).toBe(String(customTimestamp));
    });
  });

  describe('verify', () => {
    it('should verify valid signature', () => {
      const signer = new Signer(config);
      const body = '{"test":"data"}';
      const { signature } = signer.sign(body);

      expect(signer.verify({ signature, body })).toBe(true);
    });

    it('should reject invalid signature format', () => {
      const signer = new Signer(config);

      expect(() => signer.verify({ 
        signature: 'invalid-format', 
        body: 'test' 
      })).toThrow(XSignError);
    });

    it('should reject tampered body', () => {
      const signer = new Signer(config);
      const body = '{"test":"data"}';
      const { signature } = signer.sign(body);

      expect(() => signer.verify({ 
        signature, 
        body: '{"test":"tampered"}' 
      })).toThrow('Signature does not match');
    });

    it('should verify with timestamp', () => {
      const signer = new Signer({ ...config, enableTimestamp: true });
      const body = 'test';
      const { signature, timestamp } = signer.sign(body);

      expect(signer.verify({ signature, body, timestamp })).toBe(true);
    });

    it('should reject expired timestamp', () => {
      const signer = new Signer({ 
        ...config, 
        enableTimestamp: true,
        replayWindow: 1 
      });
      const body = 'test';
      const oldTimestamp = String(Date.now() - 2 * 60 * 1000); // 2 minutes ago

      expect(() => signer.verify({ 
        signature: `sha256=abc123`, 
        body, 
        timestamp: oldTimestamp 
      })).toThrow('outside 1 minute window');
    });
  });

  describe('algorithm', () => {
    it('should use sha512 when configured', () => {
      const signer = new Signer({ ...config, algorithm: 'sha512' });
      const result = signer.sign('test');

      expect(result.signature).toMatch(/^sha512=[a-f0-9]{128}$/);
    });
  });
});
