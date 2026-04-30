import type { XSignConfig } from "./types";
import { XSignClient } from "./client";

/**
 * Options for createXSignFetch
 */
export interface XSignFetchOptions {
  /** Base URL for all requests */
  baseUrl?: string;
  /** Default headers */
  headers?: Record<string, string>;
  /** X-Sign configuration */
  xsign: XSignConfig;
}

/**
 * Create a fetch wrapper that automatically signs requests
 */
export function createXSignFetch(options: XSignFetchOptions) {
  const { baseUrl, headers: defaultHeaders, xsign } = options;
  const client = new XSignClient(xsign);

  return async function xsignFetch(
    input: string | URL,
    init?: RequestInit,
  ): Promise<Response> {
    const url =
      baseUrl && typeof input === "string"
        ? `${baseUrl.replace(/\/$/, "")}/${input.replace(/^\//, "")}`
        : input;

    const method = (init?.method || "GET").toUpperCase();

    // Only sign POST, PUT, PATCH with body
    let signatureHeaders: Record<string, string> = {};

    if (["POST", "PUT", "PATCH"].includes(method) && init?.body) {
      const body =
        typeof init.body === "string" ? init.body : JSON.stringify(init.body);

      const result = client.sign(body);
      Object.entries(result.headers).forEach(([key, value]) => {
        if (value !== undefined) {
          signatureHeaders[key] = value;
        }
      });
    }

    const headers = new Headers(init?.headers);

    // Add default headers
    Object.entries(defaultHeaders || {}).forEach(([key, value]) => {
      if (!headers.has(key)) {
        headers.set(key, value);
      }
    });

    // Add signature headers
    Object.entries(signatureHeaders).forEach(([key, value]) => {
      headers.set(key, value);
    });

    return fetch(url, {
      ...init,
      headers,
    });
  };
}

/**
 * Create a signed fetch Request
 */
export function createSignedRequest(
  input: string | URL,
  init: RequestInit,
  config: XSignConfig,
): Request {
  const client = new XSignClient(config);
  const method = (init.method || "GET").toUpperCase();

  let headers = new Headers(init.headers);

  if (["POST", "PUT", "PATCH"].includes(method) && init.body) {
    const body =
      typeof init.body === "string" ? init.body : JSON.stringify(init.body);

    const result = client.sign(body);

    Object.entries(result.headers).forEach(([key, value]) => {
      if (value !== undefined) {
        headers.set(key, value);
      }
    });
  }

  return new Request(input, {
    ...init,
    headers,
  });
}
