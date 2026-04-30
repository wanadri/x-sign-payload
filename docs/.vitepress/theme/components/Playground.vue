<template>
  <div class="playground">
    <h2>🔐 Payload Signer Playground</h2>
    <p>Try generating signed payloads with different configurations.</p>
    
    <div class="playground-section">
      <h3>Configuration</h3>
      <div class="config-row">
        <label class="toggle-label">
          <input type="checkbox" v-model="includeTimestamp" />
          <span>Include Timestamp (Replay Protection)</span>
        </label>
      </div>
      
      <div class="config-row">
        <label>Algorithm:</label>
        <select v-model="algorithm">
          <option value="sha256">HMAC-SHA256</option>
          <option value="sha512">HMAC-SHA512</option>
        </select>
      </div>
      
      <div class="config-row">
        <label>Secret Key:</label>
        <input 
          type="text" 
          v-model="secret" 
          placeholder="Enter your secret key..."
          class="secret-input"
        />
        <button @click="generateSecret" class="btn-small">Generate Random</button>
      </div>
    </div>

    <div class="playground-section">
      <h3>Payload Data</h3>
      <div class="tabs">
        <button 
          :class="['tab', { active: inputMode === 'keyvalue' }]"
          @click="inputMode = 'keyvalue'"
        >
          Key-Value Pairs
        </button>
        <button 
          :class="['tab', { active: inputMode === 'json' }]"
          @click="inputMode = 'json'"
        >
          JSON Format
        </button>
      </div>

      <div v-if="inputMode === 'keyvalue'" class="keyvalue-input">
        <div v-for="(pair, index) in keyValuePairs" :key="index" class="pair-row">
          <input 
            type="text" 
            v-model="pair.key" 
            placeholder="Key"
            class="key-input"
          />
          <input 
            type="text" 
            v-model="pair.value" 
            placeholder="Value"
            class="value-input"
          />
          <button @click="removePair(index)" class="btn-remove">×</button>
        </div>
        <button @click="addPair" class="btn-add">+ Add Field</button>
      </div>

      <div v-else class="json-input">
        <textarea 
          v-model="jsonPayload" 
          rows="6"
          placeholder='{"email": "user@example.com", "name": "John Doe"}'
        ></textarea>
        <p v-if="jsonError" class="error">{{ jsonError }}</p>
      </div>
    </div>

    <div class="playground-section">
      <h3>Generated Output</h3>
      <div class="output-box">
        <div class="output-row">
          <label>X-Timestamp:</label>
          <code>{{ timestamp || 'Not included' }}</code>
        </div>
        <div class="output-row">
          <label>X-Signature:</label>
          <code class="signature">{{ signature || 'Enter secret and payload to generate' }}</code>
        </div>
        <div class="output-row full-width">
          <label>Raw Message:</label>
          <code class="raw-message">{{ rawMessage }}</code>
        </div>
      </div>
    </div>

    <div class="playground-section">
      <h3>Code Example</h3>
      <div class="tabs">
        <button 
          v-for="lang in languages" 
          :key="lang.id"
          :class="['tab', { active: selectedLang === lang.id }]"
          @click="selectedLang = lang.id"
        >
          {{ lang.name }}
        </button>
      </div>
      <pre class="code-block"><code>{{ codeExample }}</code></pre>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

const includeTimestamp = ref(true)
const algorithm = ref('sha256')
const secret = ref('')
const inputMode = ref('keyvalue')
const selectedLang = ref('javascript')

const keyValuePairs = ref([
  { key: 'email', value: 'user@example.com' },
  { key: 'name', value: 'John Doe' }
])

const jsonPayload = ref('{"email": "user@example.com", "name": "John Doe"}')
const jsonError = ref('')

const languages = [
  { id: 'javascript', name: 'JavaScript' },
  { id: 'php', name: 'PHP' },
  { id: 'python', name: 'Python' },
  { id: 'bash', name: 'cURL' }
]

const payload = computed(() => {
  if (inputMode.value === 'keyvalue') {
    const obj = {}
    keyValuePairs.value.forEach(pair => {
      if (pair.key) {
        // Try to parse as number or boolean, otherwise keep as string
        let value = pair.value
        if (value === 'true') value = true
        else if (value === 'false') value = false
        else if (!isNaN(value) && value !== '') value = Number(value)
        obj[pair.key] = value
      }
    })
    return obj
  } else {
    try {
      jsonError.value = ''
      return JSON.parse(jsonPayload.value)
    } catch (e) {
      jsonError.value = 'Invalid JSON format'
      return {}
    }
  }
})

const timestamp = computed(() => {
  return includeTimestamp.value ? Date.now().toString() : ''
})

const rawMessage = computed(() => {
  const bodyString = JSON.stringify(payload.value)
  return includeTimestamp.value 
    ? `${timestamp.value}.${bodyString}`
    : bodyString
})

// Simple HMAC implementation for demo
async function generateHMAC(message, secret, algo) {
  const encoder = new TextEncoder()
  const keyData = encoder.encode(secret)
  const messageData = encoder.encode(message)
  
  const cryptoKey = await crypto.subtle.importKey(
    'raw',
    keyData,
    { name: 'HMAC', hash: algo === 'sha512' ? 'SHA-512' : 'SHA-256' },
    false,
    ['sign']
  )
  
  const signature = await crypto.subtle.sign('HMAC', cryptoKey, messageData)
  return Array.from(new Uint8Array(signature))
    .map(b => b.toString(16).padStart(2, '0'))
    .join('')
}

const signature = ref('')

watch([rawMessage, secret, algorithm], async () => {
  if (secret.value && rawMessage.value && rawMessage.value !== '{}') {
    try {
      const hash = await generateHMAC(rawMessage.value, secret.value, algorithm.value)
      signature.value = `${algorithm.value}=${hash}`
    } catch (e) {
      signature.value = 'Error generating signature'
    }
  } else {
    signature.value = ''
  }
}, { immediate: true })

function generateSecret() {
  const array = new Uint8Array(32)
  crypto.getRandomValues(array)
  secret.value = Array.from(array)
    .map(b => b.toString(16).padStart(2, '0'))
    .join('')
}

function addPair() {
  keyValuePairs.value.push({ key: '', value: '' })
}

function removePair(index) {
  keyValuePairs.value.splice(index, 1)
}

const codeExample = computed(() => {
  const examples = {
    javascript: `import { XSignClient } from 'x-sign-payload';

const xSign = new XSignClient({
  secret: '${secret.value || 'your-secret'}',
  algorithm: '${algorithm.value}',
  enableTimestamp: ${includeTimestamp.value}
});

const payload = ${JSON.stringify(payload.value, null, 2)};

const headers = await xSign.sign(payload);
// headers = {
//   'X-Timestamp': '${timestamp.value}',
//   'X-Signature': '${signature.value || 'sha256=...'}',
//   'Content-Type': 'application/json'
// }`,

    php: `<?php
use Wanadri\\XSignPayload\\Core\\SignatureVerifier;
use Wanadri\\XSignPayload\\Core\\Config;

$config = new Config([
    'secret' => '${secret.value || 'your-secret'}',
    'algorithm' => '${algorithm.value}',
    'enable_timestamp' => ${includeTimestamp.value ? 'true' : 'false'},
]);

$payload = ${JSON.stringify(payload.value, null, 4).replace(/"/g, "'")};
$body = json_encode($payload);
$timestamp = ${timestamp.value || 'time()' * 1000};

$message = "{$timestamp}.{$body}";
$signature = hash_hmac('${algorithm.value.replace('sha', 'sha')}', $message, $config->secret);

// X-Signature: ${algorithm.value}={signature.value || '...'}
// X-Timestamp: {$timestamp}`,

    python: `import hmac
import hashlib
import json
import time

secret = b'${secret.value || 'your-secret'}'
payload = ${JSON.stringify(payload.value, null, 4)}
body = json.dumps(payload, separators=(',', ':'))

${includeTimestamp.value ? `timestamp = str(int(time.time() * 1000))` : `timestamp = ''`}
message = f"{timestamp}.{body}" if timestamp else body

signature = hmac.new(
    secret,
    message.encode(),
    hashlib.${algorithm.value.replace('sha', 'sha')}
).hexdigest()

# X-Signature: ${algorithm.value}={signature.value or '...'}
# X-Timestamp: {timestamp}`,

    bash: `# Generate signature with cURL
curl -X POST https://api.example.com/endpoint \\
  -H "Content-Type: application/json" \\
  -H "X-Timestamp: ${timestamp.value || '\$(date +%s%3N)'}" \\
  -H "X-Signature: ${signature.value || 'sha256=YOUR_SIGNATURE'}" \\
  -d '${JSON.stringify(payload.value)}'`
  }
  
  return examples[selectedLang.value]
})
</script>

<style scoped>
.playground {
  background: var(--vp-c-bg-soft);
  border-radius: 12px;
  padding: 24px;
  margin: 24px 0;
}

.playground h2 {
  margin-top: 0;
  color: var(--vp-c-brand-1);
}

.playground h3 {
  font-size: 16px;
  margin: 20px 0 12px;
  color: var(--vp-c-text-1);
}

.playground-section {
  background: var(--vp-c-bg);
  border-radius: 8px;
  padding: 16px;
  margin-bottom: 16px;
}

.config-row {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.config-row label {
  font-weight: 500;
  min-width: 100px;
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
}

.toggle-label input[type="checkbox"] {
  width: 18px;
  height: 18px;
  cursor: pointer;
}

.secret-input {
  flex: 1;
  min-width: 250px;
  padding: 8px 12px;
  border: 1px solid var(--vp-c-divider);
  border-radius: 6px;
  background: var(--vp-c-bg);
  color: var(--vp-c-text-1);
}

select {
  padding: 8px 12px;
  border: 1px solid var(--vp-c-divider);
  border-radius: 6px;
  background: var(--vp-c-bg);
  color: var(--vp-c-text-1);
  cursor: pointer;
}

.btn-small {
  padding: 6px 12px;
  font-size: 12px;
  border: 1px solid var(--vp-c-brand-1);
  background: transparent;
  color: var(--vp-c-brand-1);
  border-radius: 4px;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-small:hover {
  background: var(--vp-c-brand-1);
  color: white;
}

.tabs {
  display: flex;
  gap: 4px;
  margin-bottom: 16px;
  border-bottom: 1px solid var(--vp-c-divider);
}

.tab {
  padding: 8px 16px;
  border: none;
  background: transparent;
  color: var(--vp-c-text-2);
  cursor: pointer;
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
}

.tab:hover {
  color: var(--vp-c-text-1);
}

.tab.active {
  color: var(--vp-c-brand-1);
  border-bottom-color: var(--vp-c-brand-1);
}

.keyvalue-input {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.pair-row {
  display: flex;
  gap: 8px;
  align-items: center;
}

.key-input, .value-input {
  flex: 1;
  padding: 8px 12px;
  border: 1px solid var(--vp-c-divider);
  border-radius: 6px;
  background: var(--vp-c-bg);
  color: var(--vp-c-text-1);
}

.btn-remove {
  width: 32px;
  height: 32px;
  border: none;
  background: var(--vp-c-danger-soft);
  color: var(--vp-c-danger-1);
  border-radius: 6px;
  cursor: pointer;
  font-size: 18px;
  line-height: 1;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-remove:hover {
  background: var(--vp-c-danger-1);
  color: white;
}

.btn-add {
  padding: 8px 16px;
  border: 1px dashed var(--vp-c-brand-1);
  background: transparent;
  color: var(--vp-c-brand-1);
  border-radius: 6px;
  cursor: pointer;
  font-weight: 500;
  margin-top: 8px;
}

.btn-add:hover {
  background: var(--vp-c-brand-soft);
}

textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid var(--vp-c-divider);
  border-radius: 6px;
  background: var(--vp-c-bg);
  color: var(--vp-c-text-1);
  font-family: monospace;
  font-size: 14px;
  resize: vertical;
}

.error {
  color: var(--vp-c-danger-1);
  font-size: 14px;
  margin-top: 8px;
}

.output-box {
  background: var(--vp-c-bg-soft);
  border-radius: 8px;
  padding: 16px;
}

.output-row {
  display: flex;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 12px;
  flex-wrap: wrap;
}

.output-row.full-width {
  flex-direction: column;
}

.output-row label {
  font-weight: 500;
  min-width: 120px;
  color: var(--vp-c-text-2);
}

.output-row code {
  flex: 1;
  background: var(--vp-c-bg);
  padding: 8px 12px;
  border-radius: 4px;
  font-family: monospace;
  font-size: 13px;
  word-break: break-all;
  color: var(--vp-c-text-1);
}

.output-row code.signature {
  color: var(--vp-c-brand-1);
  font-weight: 500;
}

.output-row code.raw-message {
  font-size: 12px;
  max-height: 100px;
  overflow-y: auto;
  white-space: pre-wrap;
}

.code-block {
  background: var(--vp-c-bg-soft);
  padding: 16px;
  border-radius: 8px;
  overflow-x: auto;
  font-size: 13px;
  line-height: 1.6;
}

.code-block code {
  font-family: 'JetBrains Mono', 'Fira Code', monospace;
}
</style>
