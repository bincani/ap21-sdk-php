# AP21 SDK PHP - Improvement Plan

## Executive Summary

This document consolidates findings from a code review of the AP21 PHP SDK. The SDK provides object-oriented access to the AP21 retail/inventory management API, supporting XML-based resources with HTTP operations (GET, POST, PUT, DELETE).

**Files Reviewed:**
- `README.md`
- `lib/Ap21SDK.php`
- `lib/HTTPResource.php`, `lib/HTTPResourceInterface.php`
- `lib/HTTPXMLResource.php`
- `lib/CurlRequest.php`, `lib/HttpRequest.php`, `lib/HttpRequestJson.php`, `lib/HttpRequestXml.php`
- `lib/Freestock.php`

---

## Critical Issues (Fix Immediately)

### 1. SSL Verification Disabled
**File:** `lib/CurlRequest.php:56-57`
**Severity:** CRITICAL - Security Vulnerability

```php
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
```

**Impact:** All API communications are vulnerable to man-in-the-middle attacks. Credentials and data transmitted in plain sight to attackers.

**Fix:** Enable SSL verification, allow override only via explicit config:
```php
$verifySSL = self::$config[CURLOPT_SSL_VERIFYPEER] ?? true;
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $verifySSL ? 2 : false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $verifySSL);
```

---

### 2. Infinite Loop on 429 Rate Limit
**File:** `lib/CurlRequest.php:174-204`
**Severity:** HIGH - Reliability

```php
while (1) {
    // ... no max retry, no backoff, retry logic commented out
}
```

**Impact:** If API returns 429 repeatedly, SDK hangs forever. Commented-out retry logic (lines 190-203) is never executed.

**Fix:** Implement proper retry with exponential backoff:
```php
$maxRetries = 5;
$retryCount = 0;
while ($retryCount < $maxRetries) {
    // ... existing code ...
    if (self::$lastHttpCode == 429) {
        $retryAfter = $response->getHeader('Retry-After') ?? pow(2, $retryCount);
        sleep((int)$retryAfter);
        $retryCount++;
        continue;
    }
    break;
}
```

---

### 3. Undefined Variable in Freestock::processResponse
**File:** `lib/Freestock.php:53`
**Severity:** HIGH - Runtime Error

```php
return ["please select a resource", $childResource];  // $childResource undefined
```

**Impact:** PHP warning/error when calling `Freestock->get()` directly.

**Fix:** Either define `$childResource` or return proper error:
```php
return ["error" => "Please select a child resource", "available" => $this->childResource];
```

---

## High Priority Issues

### 4. Pagination Bug - stristr() Comparison
**Files:**
- `lib/HTTPResource.php:589, 592`
- `lib/HTTPXMLResource.php:300, 303`

**Severity:** HIGH - Pagination Broken

```php
if (stristr($responseHeaders['link'], '; rel="'.$type.'"') > -1)  // WRONG
```

**Impact:** `stristr()` returns `string|false`, not `int`. Comparison `> -1` is always true for non-false values.

**Fix:** Use correct comparison:
```php
if (stristr($responseHeaders['link'], '; rel="'.$type.'"') !== false)
```

---

### 5. Rate Limiting Never Enforced
**File:** `lib/Ap21SDK.php:216-240`
**Severity:** MEDIUM - API Ban Risk

`checkApiCallLimit()` method exists but is never called from HTTP request methods.

**Fix:** Call from `CurlRequest::processRequest()` before each request:
```php
protected static function processRequest($ch) {
    Ap21SDK::checkApiCallLimit();
    // ... rest of method
}
```

---

## Medium Priority Issues

### 6. README/Code Configuration Mismatch
**Files:** `README.md` vs `lib/Ap21SDK.php`, `lib/HTTPResource.php`
**Severity:** MEDIUM - Developer Experience

| README Shows | Code Uses | Location |
|--------------|-----------|----------|
| `ApiUrl` | `ShopUrl` | Ap21SDK.php:148 |
| `ApiUser` | `ApiKey` | Ap21SDK.php:177 |
| `ApiPassword` | `Password` | Ap21SDK.php:179 |

**Additionally:** HTTPResource.php:145-154 uses `ApiUser`/`ApiPassword` - inconsistent with Ap21SDK.php.

**Fix:** Standardize on one naming convention and update README.

---

### 7. Shopify Code Remnants
**File:** `lib/Ap21SDK.php:169-189`
**Severity:** MEDIUM - Code Quality

`setAdminUrl()` builds Shopify-style URLs (`/admin/api/$version/`) which don't match AP21 API patterns.

**Fix:** Remove or refactor for actual AP21 URL structure.

---

### 8. Code Duplication Across HTTP Classes
**Files:** `HttpRequest.php`, `HttpRequestJson.php`, `HttpRequestXml.php`
**Severity:** MEDIUM - Maintainability

Duplicated methods:
- `shouldRetry()` - identical in all 3 classes
- `processRequest()` - nearly identical
- Response processing patterns

**Fix:** Extract common logic to base class or trait.

---

### 9. Duplicated Methods in HTTPXMLResource
**File:** `lib/HTTPXMLResource.php:267-345`
**Severity:** MEDIUM - Maintainability

Methods copied from parent `HTTPResource`:
- `castString()`
- `getLinks()`, `getLink()`
- `getPrevLink()`, `getNextLink()`
- `getUrlParams()`, `getNextPageParams()`, `getPrevPageParams()`

**Fix:** Remove duplicates, rely on parent implementation.

---

## Low Priority Issues

### 10. No Configuration Validation
**File:** `lib/Ap21SDK.php:134-162`
**Severity:** LOW - Developer Experience

`config()` accepts any keys silently. Missing required params only fail at API call time.

**Fix:** Validate required keys on configuration:
```php
$required = ['ApiUrl', 'ApiUser', 'ApiPassword', 'CountryCode'];
foreach ($required as $key) {
    if (!isset($config[$key])) {
        throw new SdkException("Missing required config: $key");
    }
}
```

---

### 11. Static State Prevents Multiple Instances
**Files:** `Ap21SDK.php`, `CurlRequest.php`, `HttpRequest*.php`
**Severity:** LOW - Testability/Flexibility

All classes use static properties and methods, preventing:
- Multiple SDK instances with different configs
- Proper unit testing with mocks
- Dependency injection

**Fix:** Long-term refactor to instance-based design.

---

### 12. Dead/Commented Code
**Files:** Multiple
**Severity:** LOW - Code Quality

| File | Lines | Description |
|------|-------|-------------|
| `HTTPResource.php` | 552-574 | Commented processResponse block |
| `CurlRequest.php` | 190-203 | Commented retry logic |
| `Freestock.php` | 80-91 | Commented SKU flattening |
| `HTTPXMLResource.php` | 71-79 | Commented file_get_contents |

**Fix:** Remove or restore commented code.

---

### 13. Unused Properties
**File:** `lib/Freestock.php:14-16`
**Severity:** LOW - Code Quality

```php
protected $styles = [];
protected $styleCnt = 0;
```

Never used in the class.

**Fix:** Remove unused properties.

---

### 14. Misplaced innerHTML Method
**Files:** `HTTPResource.php:642-649`, `HttpRequestXml.php:198-205`
**Severity:** LOW - Code Organization

DOM manipulation method doesn't belong in HTTP resource classes.

**Fix:** Move to utility class or trait.

---

### 15. Wrong Content-Type in HttpRequest
**File:** `lib/HttpRequest.php:44`
**Severity:** LOW - Incorrect MIME Type

```php
self::$httpHeaders['Content-type'] = 'application/html';
```

`application/html` is not a valid MIME type. Should be `text/html`.

---

## Documentation Improvements

### README.md Enhancements Needed

1. **Fix typo:** `https://api21.end.pount/` → proper example URL
2. **Fix markdown:** `--` → `-` for FreeStock example
3. **Add available resources list:** Document all 12 resources
4. **Add error handling section:** Document exceptions and handling
5. **Add authentication section:** Clarify auth methods (Basic, Token, ApiKey)
6. **Add pagination documentation:** Show how to use `getNextLink()` etc.
7. **Add retry configuration:** Document `RequestRetryCallback`
8. **Add rate limiting info:** Document `AllowedTimePerCall` config

---

## Implementation Roadmap

### Phase 1: Critical Security & Stability (Immediate)
- [ ] Enable SSL verification with config override
- [ ] Fix 429 infinite loop with proper backoff
- [ ] Fix undefined `$childResource` variable

### Phase 2: Bug Fixes (1-2 days)
- [ ] Fix `stristr()` pagination bug (3 locations)
- [ ] Integrate rate limiting into request flow
- [ ] Fix Content-Type header

### Phase 3: Documentation (1 day)
- [ ] Standardize config key names
- [ ] Update README with correct examples
- [ ] Document all resources and methods
- [ ] Add error handling guide

### Phase 4: Code Quality (3-5 days)
- [ ] Remove code duplication across HTTP classes
- [ ] Remove Shopify remnants
- [ ] Clean up dead code and unused properties
- [ ] Add configuration validation

### Phase 5: Architecture (Future)
- [ ] Refactor from static to instance-based design
- [ ] Add proper dependency injection
- [ ] Add unit test suite
- [ ] Consider PSR-18 HTTP client compatibility

---

## Files to Modify (by priority)

| Priority | File | Changes |
|----------|------|---------|
| 1 | `lib/CurlRequest.php` | SSL, retry loop, rate limiting |
| 2 | `lib/Freestock.php` | Fix undefined variable |
| 3 | `lib/HTTPResource.php` | Fix stristr bug, remove dead code |
| 4 | `lib/HTTPXMLResource.php` | Fix stristr bug, remove duplicates |
| 5 | `README.md` | Update documentation |
| 6 | `lib/Ap21SDK.php` | Config validation, remove Shopify code |
| 7 | `lib/HttpRequest.php` | Fix Content-Type |
| 8 | `lib/HttpRequestJson.php` | Extract common code |
| 9 | `lib/HttpRequestXml.php` | Extract common code |
