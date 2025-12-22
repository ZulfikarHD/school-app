# owasp-check

Perform comprehensive OWASP Top 10 security audit dengan **SMART TRACING** - automatically follow data flow dari component yang ditentukan.

---

## 🎯 SMART TRACING BEHAVIOR

### If CONTROLLER is specified:
**Auto-trace ke:**
1. **Routes** → cari semua routes yang menggunakan controller ini
2. **Form Requests** → cari Request classes yang digunakan controller
3. **Middleware** → check middleware di routes dan controller
4. **Services/Actions** → cari service classes yang dipanggil controller
5. **Models** → identify models yang diakses/modified
6. **Frontend Components** → trace Inertia pages/Vue components yang memanggil routes ini
7. **Frontend Forms** → check form components yang submit ke controller
8. **API Calls** → frontend code yang hit endpoints ini

### If FRONTEND/VUE is specified:
**Auto-trace ke:**
1. **Routes Called** → identify semua route() atau Wayfinder calls
2. **Controllers** → find controllers yang handle routes tersebut
3. **Form Requests** → check validation classes untuk endpoints
4. **Middleware** → trace middleware di routes yang dipanggil
5. **Services** → backend services yang digunakan controllers
6. **Models** → data models yang affected oleh operations
7. **Related Components** → parent/child Vue components dalam flow
8. **State Management** → check stores/composables yang digunakan

### Trace Strategy:
- Start dari file yang dispesifikasi
- Grep untuk imports, route calls, Inertia renders
- Follow chain sampai menemukan semua related files
- Analyze entire flow end-to-end

---

## 🔒 SECURITY CHECKS (OWASP Top 10 2021)

### BACKEND (A01-A10):
**A01 - Broken Access Control**
- Missing auth middleware, IDOR, unauthorized access, privilege escalation
- Check: gates, policies, middleware, route protection, user context validation

**A02 - Cryptographic Failures**
- Hardcoded secrets, weak encryption, unencrypted sensitive data
- Check: .env exposure, plain text passwords, weak hashing, missing HTTPS enforcement

**A03 - Injection**
- SQL injection (raw queries), command injection, NoSQL injection, LDAP
- Check: DB::raw(), shell_exec(), unvalidated input in queries

**A04 - Insecure Design**
- Missing rate limiting, no input validation, weak business logic
- Check: rate limiting, request validation, business rule enforcement

**A05 - Security Misconfiguration**
- Debug mode on, default credentials, verbose errors, exposed config
- Check: APP_DEBUG, stack traces in production, .env.example secrets

**A06 - Vulnerable Components**
- Outdated packages, known CVEs (composer.json, package.json)
- Check: dependency versions, abandoned packages

**A07 - Authentication Failures**
- Weak passwords, missing MFA, broken session management, no rate limit on login
- Check: password rules, session timeout, remember tokens, login throttling

**A08 - Data Integrity Failures**
- Missing integrity checks, unsigned updates, insecure deserialization
- Check: unserialize(), mass assignment, fillable/guarded, signed URLs

**A09 - Logging Failures**
- Insufficient logging, logging sensitive data (passwords, tokens)
- Check: Log statements, audit trails, PII in logs

**A10 - SSRF**
- Unvalidated URLs, internal resource access via user input
- Check: Http::get($userInput), file_get_contents with user URLs

### FRONTEND (F01-F10):
**F01 - XSS Vulnerabilities**
- DOM XSS, v-html, dangerouslySetInnerHTML, innerHTML, eval()
- Check: unescaped user input rendering, dynamic HTML injection

**F02 - Client Storage Exposure**
- Sensitive data di localStorage/sessionStorage/cookies tanpa encryption
- Check: tokens, PII, credentials stored client-side

**F03 - Exposed Secrets**
- API keys, tokens visible di source code atau committed .env
- Check: hardcoded keys, .env in git, VITE_* vars exposure

**F04 - CORS Misconfig**
- Overly permissive CORS, credentials exposed to wrong origins
- Check: Access-Control-Allow-Origin: *, withCredentials issues

**F05 - Clickjacking**
- Missing X-Frame-Options, no CSP frame-ancestors
- Check: frame-busting code, security headers

**F06 - Client-Side Logic Flaws**
- Auth/validation hanya di frontend, price manipulation possible
- Check: disabled buttons without backend validation, client-side pricing

**F07 - Data Exposure**
- Tokens in URLs/query params, console.log sensitive data, source maps in prod
- Check: URL params with tokens, debugging logs, .map files published

**F08 - Open Redirects**
- Unvalidated redirect URLs, callback parameters
- Check: router.push(userInput), window.location with user data

**F09 - Prototype Pollution**
- Unsafe Object.assign, spread operator dengan untrusted data
- Check: merging user input into objects, __proto__ access

**F10 - Vulnerable Frontend Deps**
- Outdated npm packages, known CVEs in Vue/React/libs
- Check: package.json versions, npm audit results

### CROSS-CUTTING:
- **API Security**: endpoint protection, authentication headers, response sanitization
- **Auth Flow**: login/logout/refresh complete flow, session management
- **Authorization**: both frontend AND backend enforcement
- **Input Validation**: frontend forms AND backend Request validation
- **Error Handling**: avoid leaking stack traces, DB details, internal paths

---

## 📋 OUTPUT FORMAT

For each vulnerability found:

```
### [SEVERITY] VULNERABILITY_NAME
**File**: `path/to/file.php:123`
**Type**: [OWASP Category]

**Issue**:
[Clear explanation of vulnerability]

**Attack Scenario**:
[How attacker exploits this]

**Current Code**:
```[language]
[vulnerable code snippet]
```

**Secure Fix**:
```[language]
[fixed code with security best practices]
```

**Impact**: [Business/technical impact]
**Priority**: [P0/P1/P2/P3]
```

### Severity Levels:
- 🔴 **CRITICAL**: Immediate exploitation possible, data breach risk
- 🟠 **HIGH**: Significant risk, exploitable with moderate effort
- 🟡 **MEDIUM**: Limited risk, requires specific conditions
- 🟢 **LOW**: Minor issue, defense-in-depth recommendation

---

## 💾 DOCUMENTATION

**Auto-save audit report to**:
`security_logs/owasp_audit_{component_name}_{YYYY-MM-DD}.md`

**Include in report**:
1. Executive Summary (total vulns by severity)
2. Component Trace Map (visual flow diagram)
3. Detailed Findings (grouped by OWASP category)
4. Recommended Fixes (prioritized action items)
5. Testing Checklist (verification steps)
6. References (OWASP links, CVE details)

---

## 🎬 EXECUTION FLOW

1. **Identify Entry Point** (controller atau Vue component)
2. **Smart Trace** (follow imports/calls/routes automatically)
3. **Map Components** (create relationship diagram)
4. **Security Scan** (apply all OWASP checks ke traced files)
5. **Aggregate Findings** (deduplicate, prioritize)
6. **Generate Report** (structured markdown dengan fixes)
7. **Provide Summary** (critical stats, next actions)

**Note**: Focus pada authentication, authorization, data validation, API endpoints, database queries, file operations, external requests, sensitive data handling, and complete request/response lifecycle.
