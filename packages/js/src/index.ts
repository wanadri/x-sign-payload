// Core exports
export { Signer, XSignError } from './signer';
export { XSignClient, generateSecret } from './client';
export type {
  XSignConfig,
  XSignHeaders,
  SignOptions,
  VerifyOptions,
  SignResult,
} from './types';

// Re-export for convenience
export { createXSignInterceptor, applyXSignInterceptor, createXSignAxios } from './axios';
export { createXSignFetch, createSignedRequest, type XSignFetchOptions } from './fetch';
