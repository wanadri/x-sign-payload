import type { AxiosInstance, InternalAxiosRequestConfig } from 'axios';
import type { XSignConfig } from './types';
import { XSignClient } from './client';

/**
 * Create an Axios interceptor that automatically signs requests
 */
export function createXSignInterceptor(
  client: XSignClient
): (config: InternalAxiosRequestConfig) => InternalAxiosRequestConfig | Promise<InternalAxiosRequestConfig> {
  return (config: InternalAxiosRequestConfig) => {
    // Only sign POST, PUT, PATCH requests with body
    if (!['post', 'put', 'patch'].includes(config.method?.toLowerCase() || '')) {
      return config;
    }

    const body = config.data ? JSON.stringify(config.data) : '';
    const result = client.sign(body);

    // Add signature headers
    config.headers = config.headers || {};
    config.headers['X-Signature'] = result.headers['X-Signature'];
    
    if (result.headers['X-Timestamp']) {
      config.headers['X-Timestamp'] = result.headers['X-Timestamp'];
    }

    return config;
  };
}

/**
 * Apply X-Sign interceptor to an Axios instance
 */
export function applyXSignInterceptor(
  axiosInstance: AxiosInstance,
  config: XSignConfig
): void {
  const client = new XSignClient(config);
  axiosInstance.interceptors.request.use(createXSignInterceptor(client));
}

/**
 * Create a pre-configured Axios instance with X-Sign
 */
export function createXSignAxios(
  config: XSignConfig,
  axiosConfig?: Parameters<typeof import('axios')['create']>[0]
): AxiosInstance {
  // Dynamic import to avoid requiring axios as a hard dependency
  const axios = require('axios');
  const instance = axios.create({
    headers: {
      'Content-Type': 'application/json',
    },
    ...axiosConfig,
  });

  applyXSignInterceptor(instance, config);
  return instance;
}
