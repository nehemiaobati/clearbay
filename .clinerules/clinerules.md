# CodeIgniter 4 System Architecture Standard (.clinerules)

> [!WARNING]
> This file contains absolute, non-negotiable architectural mandates for this repository. Any deviation from these rules during code review, generation, or modification constitutes a system failure and is strictly FORBIDDEN.

---

## 0. Philosophy: Simple vs. Easy

> _"Conflating [Simple and Easy] is why we’re drowning in complexity."_
> — Rich Hickey

This project adheres to a strict philosophical standard. Every architectural decision is weighed against these definitions:

- **Simple (Objective)**: Disentangled, linear, single-responsibility. Harder to design, but trivial to maintain.
- **Easy (Subjective)**: Familiar, near-at-hand, minimal typing. Quick to start, but creates "complected" (braided) knots that kill modularity.

**Rule**: We prioritize **Simple** (Architectural Purity) over **Easy** (Developer Convenience).

---

## Part 1: Core Meta-Rules

1. **Universal Applicability**: These rules apply to EVERY file, class, and method within the application. No exceptions are permitted.
2. **Project-Agnostic Specification**: All rules and examples MUST use generic placeholders (e.g., `DomainController`, `BillingService`, `user_id`) rather than specific domain names or temporary files.
3. **No Ambiguity**: All instructions MUST use RFC 2119 deterministic terms (MUST, MUST NOT, REQUIRED, SHALL, FORBIDDEN). Permissive phrases like "should", "generally", "recommended", or "best practice" are FORBIDDEN.
4. **First Source Check**: The AI agent MUST read and align with this file before initiating any task.

---

## Part 2: Philosophy: Simple over Easy

This architecture prioritizes **Simplicity** (objective, unentangled design) over **Ease** (subjective, familiar convenience).

| Attribute | Simple | Easy |
|-----------|--------|------|
| **Nature** | Objective system structure | Subjective developer comfort |
| **Focus** | Single responsibility, disentangled | Familiar, adjacent, copy-paste |
| **Cost** | Upfront design and refactoring | Immediate speed |
| **Outcome** | Long-term reliability and maintainability | Short-term speed, long-term technical debt |

**Core Mandate**: Architectural purity is NEVER sacrificed for developer convenience.

---

## Part 3: Folder Structure & Modular Architecture

The application strictly enforces a **Modular Model-View-Controller-Service (MVC-S)** architecture.

### 3.1 Directory Layout

All business features MUST be isolated inside self-contained modules under `app/Modules/`. The root `app/` directory is reserved solely for core, shared infrastructure.

A module directory MUST adhere to this structural layout:

```text
app/Modules/[ModuleName]/
├── Config/              # Module-specific configuration
│   └── Routes.php       # REQUIRED: Manual route registration
├── Controllers/         # HTTP Request/Response orchestration
├── Database/
│   ├── Migrations/      # Schema modifications
│   └── Seeds/           # Initial data population
├── Entities/            # Business data objects with casting
├── Helpers/             # Module-specific standalone functions
├── Libraries/           # Services (sole location of business logic)
├── Models/              # Database access layer
└── Views/               # Presentation templates
```

All directories are OPTIONAL unless explicitly marked REQUIRED. Create ONLY directories that contain files.

### 3.2 Module Registration Protocol

1. **Command Generation**: Modules MUST be generated via `php spark make:module [ModuleName]`.

2. **Namespace Registration**: The module's namespace MUST be registered in `app/Config/Autoload.php` within the `$psr4` array:

   ```php
   public $psr4 = [
       APP_NAMESPACE => APPPATH,
       'App\Modules\[ModuleName]' => APPPATH . 'Modules/[ModuleName]',
   ];
   ```

3. **Route Registration**: Routes MUST be defined within `app/Modules/[ModuleName]/Config/Routes.php`. To prevent default namespace failures, routes MUST be wrapped inside a route group with an explicit, fully-qualified module namespace:

   ```php
   <?php
   
   namespace App\Modules\[ModuleName]\Config;
   
   use CodeIgniter\Router\RouteCollection;
   
   /**
    * @var RouteCollection $routes
    */
   
   $routes->group('domain', ['namespace' => 'App\Modules\[ModuleName]\Controllers'], static function ($routes) {
       // Define all module routes here with explicit names
       $routes->get('action', 'DomainController::action', ['as' => 'module.domain.action']);
   });
   ```

4. **Helper Loading**: Globally loading module-specific helper files via `app/Config/Autoload.php` is FORBIDDEN. To preserve performance and isolate execution, module-level helper files MUST be dynamically declared and loaded within the destination controller's `$helpers` array property using their namespaced paths:

   ```php
   protected $helpers = ['App\Modules\[ModuleName]\Helpers\domain'];
   ```

---

## Part 4: Layer Responsibilities

### 4.1 Controllers (`app/Modules/[ModuleName]/Controllers/`)

Controllers are HTTP request handlers. They orchestrate input validation and delegate to services.

- **MUST** extend `CodeIgniter\Controller` or `App\Controllers\BaseController`.
- **MUST** declare return types as `string|ResponseInterface` inside method signatures to satisfy PHP strict typing rules under all outcome variations.
- **MUST** validate incoming HTTP requests using the Validation service.
- **MUST** invoke Service classes to process business operations.
- **MUST** return `ResponseInterface` objects (via `$this->response` or `return redirect()`) or `string` values (via `return view()`).
- **MUST** pass SEO variables (`pageTitle`, `metaDescription`, `canonicalUrl`, `robotsTag`) in the view data array:

  ```php
  return view('App\Modules\[ModuleName]\Views\domain\view', [
      'pageTitle'       => 'Page Title',
      'metaDescription' => 'Meta description content',
      'canonicalUrl'    => url_to('named.route'),
      'robotsTag'       => 'index, follow',
      'data'            => $serviceResult,
  ]);
  ```

- **MUST NOT** execute database queries directly.
- **MUST NOT** perform business calculations or logic.
- **MUST NOT** instantiate the file system directly (use Services).
- **MUST NOT** generate inline HTML.

### 4.2 Services (`app/Modules/[ModuleName]/Libraries/`)

Services encapsulate 100% of business logic and external integrations.

- **MUST** be instantiated via the Service container: `service('serviceName')`.
- **MUST** be registered in the module-level `Config/Services.php` file. If a service is generic, shared, and consumed across multiple modules, it MUST be registered in the global `app/Config/Services.php` file.
- **MUST** contain all business logic, external API integrations, file system operations, and transaction initialization.
- **MUST** use dependency injection through the constructor for required services.
- **MUST** return standardized arrays or Entity instances.
- **MUST NOT** access HTTP-specific globals (`$_POST`, `$_GET`, `$_SERVER`) directly.
- **MUST NOT** manage HTTP redirects or session flash data directly.

**Service Dependency Topology (Parallel vs. Braided)**:

The architecture enforces clean, parallel execution stacks that do not "criss-cross" or braid together, ensuring ease of debugging and future scaling.

- **Parallel Structure**: Dependencies MUST flow strictly vertically: `Controller -> Main Service -> Sub-Service`.

- **The "Ping Pong" Prohibition**: Triangular dependencies are FORBIDDEN.
  - **Definition**: A Controller talking to a Main Service AND that Main Service's dependency (Sub-Service).
  - **Example**: `MainController` talking to `SubService` (e.g., formatting helper) while `MainService` also uses `SubService`.
  - **Fix**: Use the **Facade Pattern**. The Main Service (`MainService`) MUST wrap the required methods of the Sub-Service (`SubService`) so the Controller has a single point of entry.

- **Brother-Service Isolation**: Services at the same level (e.g., `ModuleAService` and `ModuleBService`) MUST NOT call each other directly. If orchestration is needed, create a higher-level "Orchestrator Service" or handle coordination in the Controller.

### 4.3 Models (`app/Modules/[ModuleName]/Models/`)

Models are the database access layer. They handle data persistence and retrieval.

- **MUST** extend `CodeIgniter\Model`.
- **MUST** define the `$table` property specifying the database table name.
- **MUST** define `$primaryKey` property (defaults to `id` if omitted).
- **MUST** define `$returnType` as a fully-qualified Entity class:

  ```php
  protected $returnType = \App\Modules\[ModuleName]\Entities\DomainEntity::class;
  ```

- **MUST** define `$allowedFields` as an array of writable column names (FORBIDDEN to omit).
- **MUST NOT** use Model-level field casting (`$casts` property) when returning Entities, as model-level casts run in direct conflict with Entity property casting. Entity property casting MUST manage data transformation.
- **MUST NOT** contain business calculations.
- **MUST NOT** be invoked directly from Views.

### 4.4 Entities (`app/Modules/[ModuleName]/Entities/`)

Entities are strict data objects that represent business records with type safety.

- **MUST** extend `CodeIgniter\Entity\Entity`.
- **MUST** be used for all business data representation. Passing raw associative arrays between application layers is FORBIDDEN.
- **MUST** utilize the Entity `$casts` property to enforce type safety:

  ```php
  protected $casts = [
      'id'         => 'integer',
      'is_active'  => 'boolean',
      'metadata'   => 'json-array',
  ];
  ```

- **MUST** utilize `$dates` array for timestamp properties. Properties in `$dates` are returned as `\CodeIgniter\I18n\Time|null`, NOT `string`.
- **MUST** house data-shaping logic via accessors and mutators (e.g., `setPassword(string $pass)`).
- **MUST NOT** query the database.
- **MUST NOT** contain service-level operations.
- **MUST NOT** depend on external services.

### 4.5 Views (`app/Modules/[ModuleName]/Views/`)

Views render Presentation Layer output.

- **MUST** extend the master layout file: `<?= $this->extend('layouts/default') ?>`.
- **MUST NOT** contain nested conditional statements (maximum nesting depth of 1).
- **MUST NOT** contain any PHP code blocks exceeding 5 lines.
- **MUST** escape ALL output using the native `esc()` helper: `<?= esc($variable) ?>`.
- **MUST NOT** make database calls.
- **MUST NOT** instantiate Services or Models.

### 4.6 Helpers (`app/Helpers/` or `app/Modules/[ModuleName]/Helpers/`)

Helpers are small, stateless, reusable procedural functions.

- **MUST** be pure functions (no side effects).
- **MUST** be loaded dynamically when execution scope demands them.
- **MUST NOT** contain business logic (use Services).
- **MUST NOT** execute database queries (use Models/Services).
- **MUST NOT** perform stateful operations.

---

## Part 5: Database Management & Schema

### 5.1 Migrations & Seeders

- **Migrations MANDATORY**: All schema modifications MUST use Migration classes. Direct table alteration via CLI or GUI tools on any environment is FORBIDDEN.
- **Seeder Protocol**: Initial data states MUST be created exclusively through Seeder classes invoked via `php spark db:seed MainSeeder`.
- **Compression Protocol**: Consolidating migrations is permitted ONLY for fresh environment setup with documented Compression and Implementation Plans.

### 5.2 Index Requirements

`addKey()` in migrations is REQUIRED for all columns used in:
- Foreign key references
- `WHERE` clause conditions (`status`, `type`, `state`)
- Lookup columns (`slug`, `hash`, `email`)
- Timestamp range queries (`created_at`, `updated_at`)

Use composite keys for frequently paired filters: `$this->forge->addKey(['user_id', 'status'])`.

### 5.3 Query Standards

- Raw SQL queries via `$db->query()` are FORBIDDEN except for complex reporting queries reviewed by a senior developer.
- `SELECT *` is FORBIDDEN. Always use explicit column selection: `$this->select('id, name, status')`.
- Column names MUST be `snake_case` and match Entity property names exactly.

### 5.4 Transaction Integrity

Any execution chain containing more than one database write operation (INSERT, UPDATE, DELETE) MUST be wrapped in a transaction:

```php
$db = \Config\Database::connect();
$db->transStart();

try {
    $this->modelA->insert($dataA);
    $this->modelB->update($id, $dataB);
    
    $db->transComplete();
} catch (\Exception $e) {
    $db->transRollback();
    
    log_message('critical', 'Transaction failure in DomainService::operation', [
        'exception' => $e->getMessage(),
        'trace'     => $e->getTraceAsString(),
    ]);
    
    throw $e;
}
```

All financial balance modifications (credits, debits, transfers) MUST use transactions regardless of operation count.

---

## Part 6: Code Quality, Typing & Documentation

1. **PSR-12 Compliance**: Every PHP file MUST be PSR-12 compliant.

2. **Strict Typing**: The declaration `declare(strict_types=1);` MUST be the first statement in every PHP file.

3. **Namespace Declaration**: The `namespace` declaration MUST follow immediately after `declare()`, with exactly one blank line before class declaration.

4. **PHPDoc Standards**:
   - Every class MUST contain a descriptive PHPDoc block with `@package`, `@author`, and `@since` tags.
   - Every method MUST contain complete PHPDoc with `@param`, `@return`, and `@throws` where applicable.
   - Every property MUST specify type and description.
   - Entity PHPDoc properties MUST match the dynamic cast types returned by CodeIgniter.

5. **Naming Conventions**:
   - Variables/Properties: MUST be `snake_case` (e.g., `$user_id`, `$form_errors`).
   - Methods: MUST be `camelCase` (e.g., `processPayment()`, `validateInput()`).
   - Classes/Controllers: MUST be `PascalCase` (e.g., `OrderService`, `AdminController`).
   - Array keys: MUST be `snake_case`.
   - Private helpers: MUST be prefixed with underscore (e.g., `private function _normalizeData()`).
   - Private helpers MUST be grouped beneath `// --- Helper Methods ---` and defined BEFORE public methods that invoke them.

6. **Type Safety**:
   - All methods MUST have explicit return types (`: bool`, `: array`, `: RedirectResponse`, `: string|ResponseInterface`).
   - Inline PHPDoc type hints are REQUIRED for variables assigned from framework dynamic methods:

     ```php
     /** @var \App\Modules\[ModuleName]\Entities\DomainEntity|null $entity */
     $entity = $this->model->find($id);
     ```

---

## Part 7: Request, Response & Protocol

### 7.1 Routing Configuration

- **Auto-Routing DISABLED**: Auto-routing is FORBIDDEN. Set `public bool $autoRoute = false;` in `app/Config/Routing.php`.
- **No Route Closures**: Placing logic inside route closures (`$routes->get('/', function(){...})`) is FORBIDDEN.
- **Named Routes MANDATORY**: Every defined route MUST have an explicit name using the `as` option:

  ```php
  $routes->get('profile/settings', 'ProfileController::settings', ['as' => 'profile.settings']);
  $routes->post('profile/settings', 'ProfileController::updateSettings', ['as' => 'profile.settings.update']);
  ```

- **Route Groups**: Use `$routes->group()` to organize by feature or access level. Group callbacks MUST be `static function`.
- **URL Generation**: All links, form actions, and redirects MUST use named route resolution via `url_to()`. Hardcoded paths are FORBIDDEN.

### 7.2 Form Submission (Post/Redirect/Get Pattern)

All POST request handlers MUST implement the PRG pattern:

1. The POST handler controller MUST NOT return `view()` directly.
2. The controller MUST process data, set flash messages via `session()->setFlashdata()`, and return `redirect()`.
3. The redirect target MUST be a named GET route.
4. The GET destination controller renders the view and displays flash messages via `app/Views/partials/flash_messages.php`.

```php
public function store(): RedirectResponse
{
    if (! $this->validate($rules)) {
        return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }
    
    service('domainService')->create($this->request->getPost());
    
    return redirect()->to(url_to('domain.index'))
                     ->with('success', 'Record created successfully');
}
```

### 7.3 AJAX Responses

All JSON responses MUST return a uniform envelope format:

```php
return $this->response->setJSON([
    'status'     => 'success',           // 'success', 'error', or 'validation_error'
    'message'    => 'Human-readable summary',
    'result'     => $data,               // Payload object/array
    'errors'     => $validationErrors,   // Validation error array
    'csrf_token' => csrf_hash(),         // REQUIRED: Fresh CSRF token
]);
```

### 7.4 Server-Sent Events (SSE) Streaming

- The `Content-Type` header MUST be set to `text/event-stream`.
- Server-side buffering MUST be bypassed immediately with `ob_flush(); flush();` after each output.
- `session_write_close()` MUST be called before entering streaming loops to prevent session locks.
- The first stream packet MUST transmit the fresh CSRF token.

---

## Part 8: Security Protocols

### 8.1 CSRF Enforcement

- CSRF protection MUST be enabled globally in `app/Config/Security.php`.
- Every HTML form with `method="post"`, `put`, `patch`, or `delete` MUST include `csrf_field()`.
- Every JSON response (success, validation error, or exception) MUST include `csrf_token` with a fresh hash via `csrf_hash()`.
- Manual CSRF token passing via cookie headers is FORBIDDEN.

### 8.2 Input Validation

- Controllers MUST validate ALL incoming input using the Validation service.
- Validation rules MUST be strictly defined and reviewed.

### 8.3 Environment Secrets

- API keys, secrets, and credentials MUST reside ONLY in the `.env` file.
- `.env` file MUST be excluded from version control via `.gitignore`.
- Access secrets via `env('KEY_NAME')` or `service('settings')` ONLY.
- Custom config files for secrets are FORBIDDEN.

### 8.4 Rate Limiting & Throttling

The application MUST implement rate limiting to protect routes against brute-force attacks and resource abuse using `App\Filters\ThrottleFilter` mapped to the `throttle` filter alias in `app/Config/Filters.php`.

#### 8.4.1 Filter Architecture (`app/Filters/ThrottleFilter.php`)

- **Class & Namespace**: The filter class MUST reside under the `App\Filters` namespace and implement `CodeIgniter\Filters\FilterInterface`.
- **Throttler Injection**: The filter MUST invoke CodeIgniter's native token bucket throttler service via `\Config\Services::throttler()`.
- **Parameter Parsing**: Dynamic route arguments (limit and seconds bucket window) MUST be supported inside the `before()` hook:
  - **Default Limit**: `60` requests.
  - **Default Window**: `60` seconds.
  - Limits MUST fall back to defaults if parameters are omitted.
- **Key Isolation**: To prevent cross-route collisions, tracking cache keys MUST be uniquely isolated per IP address and specific URI request path using MD5 hashing:
  ```php
  $ip = $request->getIPAddress();
  $key = 'throttle_' . md5($ip . $request->getUri()->getPath());
  ```
- **Lifecycle Interruption Responses**:
  - **AJAX and JSON Clients**: If the request contains the `X-Requested-With: XMLHttpRequest` header OR an `Accept` header containing `application/json`, the filter MUST bypass redirect logic, set the HTTP response status code strictly to `429` (Too Many Requests), and return a JSON payload providing the fresh synchronized CSRF token:
    ```php
    return service('response')
        ->setStatusCode(429)
        ->setJSON([
            'status'     => 'error',
            'message'    => 'Too many requests. Please try again later.',
            'csrf_token' => csrf_hash()
        ]);
    ```
  - **Standard Web Clients**: For standard web rendering requests, the filter MUST issue a redirect back (`redirect()->back()`) alongside a session flash error message:
    ```php
    return redirect()->back()->with('error', 'Too many requests. Please wait a moment before trying again.');
    ```
- **Filter Cleanup**: No operations are required in the `after()` method.

#### 8.4.2 Route Definition Rules

- **Mandatory Application**: Throttling parameters MUST be explicitly defined at the route level for:
  - All authentication actions (e.g., login submissions, registrations, password resets).
  - All authorization endpoints (e.g., 2FA verify steps, API token requests).
  - High-resource consumption routes (e.g., AI generations, massive data exports).
- **Route Options Formatting**: Limit parameters MUST be mapped inside the route configuration using colon-separated parameters within the `filter` option:
  ```php
  $routes->post('login/authenticate', 'AuthController::authenticate', [
      'as'     => 'login.authenticate', 
      'filter' => 'throttle:5,60' // Max 5 requests per 60 seconds per IP
  ]);
  ```

### 8.5 XSS Prevention

- ALL View output MUST be escaped using `esc()`.
- Unescaped output requires explicit security review and approval comments.

---

## Part 9: Stateless File Handling (The Unlink Pattern)

To maintain compatibility with stateless and serverless platforms:

- **Immediate Cleanup**: After a temporary file is read into memory or served to the client, `@unlink($path)` MUST be called immediately.
- **Randomized Naming**: Temporary file names MUST use framework-generated random names (`$file->getRandomName()`).
- **Centralized Storage**: Uploads MUST be stored in `WRITEPATH . 'uploads/[type]/[userId]/'`.
- **No Persistent Local Storage**: Storing uploaded assets permanently on local disk is FORBIDDEN. Offload to external object storage (S3, GCS, etc.) within the same request cycle.
- **Session Data Restrictions**: Saving binary data or raw files in session storage is FORBIDDEN. Store IDs ONLY. Use the `DatabaseHandler` session driver with the `data` column configured as `MEDIUMBLOB` or larger for serialized data.
- **Directory Creation**: Services MUST handle directory creation (`mkdir($path, 0755, true)`) and file moving.

---

## Part 10: Error Handling, Logging & Environment

### 10.1 Exception Handling

- Controllers MUST catch `\Throwable` in all action methods to prevent information leaks.
- Service layer methods MUST bubble up domain-specific exceptions with context OR return standardized error arrays:
  ```php
  ['status' => 'error', 'message' => 'Human readable message']
  ```
- Unhandled exceptions in Production mode MUST display the static error layout from `app/Views/errors/html/production.php`.

### 10.2 Logging Standards

- Logging MUST use `log_message($level, $message, $context)` with array contexts:

  ```php
  // REQUIRED:
  log_message('error', 'Payment processing failed', [
      'transaction_id' => $transactionId,
      'user_id'        => $userId,
      'gateway'        => $gateway,
  ]);
  ```

- **Log Levels**:
  - `critical`: System unusable (DB down, disk full). Triggers immediate alert.
  - `error`: Runtime failures (transaction rollbacks, upload failures).
  - `info`: Key business events (user login, report generated).

### 10.3 User Feedback

- Flash messages MUST use `session()->setFlashdata()`.
- UI Standard: Use persistent Bootstrap Alerts for operation results (Success/Error/Warning). Transient Toasts are reserved for system connectivity issues ONLY.

### 10.4 Environment Configuration

| Environment | `CI_ENVIRONMENT` | `display_errors` | Debug Toolbar |
|-------------|------------------|------------------|---------------|
| Development | `development`    | `1`              | Enabled       |
| Staging     | `testing`        | `0`              | Disabled      |
| Production  | `production`     | `0`              | Disabled      |

- Committing `d()`, `dd()`, `die()`, `var_dump()`, or `print_r()` statements to version control is FORBIDDEN.

---

## Part 11: Frontend Blueprints

### 11.1 Framework & Structure

- **Framework**: Bootstrap 5 (Utility-first approach).
- **Layouts**: All views MUST extend `layouts/default`.
- **Partials**: Reusable UI chunks MUST be placed in `partials/` (e.g., `flash_messages.php`).
- **Structure (The Blueprint Method)**:
  - **Container**: Wrap content in `<div class="container my-5">`.
  - **Header**: Use `<div class="blueprint-header">`.
  - **Card**: Use `<div class="card blueprint-card">`.

### 11.2 Theme Awareness

Hardcoding colors is FORBIDDEN. Priority order:
1. Use Theme-aware Bootstrap utilities first (e.g., `bg-body-tertiary`).
2. Use project CSS variables second (e.g., `var(--card-bg)`).

### 11.3 UI Components

- **Inputs**: All text inputs MUST use Bootstrap 5 "Floating labels".
- **Buttons**:
  - Primary Action: `btn-primary`
  - Secondary Action: `btn-outline-secondary`
  - Destructive Action: `btn-danger`

### 11.4 SEO & Social Sharing

**Meta Data**: Controllers MUST pass these variables in view data:
- `pageTitle`
- `metaDescription`
- `canonicalUrl`
- `robotsTag`
- `metaImage`

**Indexing Strategy**:
- **Public Pages**: USE `index, follow` (Marketing, informative, and public tool pages).
- **Private/Auth Pages**: USE `noindex, follow` (Auth forms, User dashboards, Admin panels).

**Social Sharing**: Layouts MUST include complete Open Graph AND Twitter Card meta tags:
- **Open Graph**: `og:type`, `og:url`, `og:title`, `og:description`, `og:image`
- **Twitter Card**: `twitter:card`, `twitter:site`, `twitter:title`, `twitter:description`, `twitter:image`, `twitter:image:alt`

> [!NOTE]
> LinkedIn uses Twitter Card tags for link previews; both sets are mandatory.

**Images**: Pass `metaImage` for specific content (e.g., blog posts, portraits); defaults to a standard brand image in the layout.

---

## Part 12: Testing

**Mandate**: No feature is complete without automated tests.

- **Tool**: PHPUnit (`php spark test`).
- **Unit Tests**: Test Services with mocked DB/API dependencies.
- **Feature Tests**: Test Controllers using real DB with `DatabaseTransactions` trait.

---

## Part 13: Boot & Deployment Standard Operating Procedure

### 13.1 Local Development Boot (3 Steps)

A developer MUST initialize the local workspace using exactly three sequential commands:

```bash
composer install
php spark migrate --all
php spark db:seed MainSeeder
```

### 13.2 Production Deployment Checklist

The deployment process MUST execute in this exact sequence:

1. **Environment Configuration**: Set `CI_ENVIRONMENT = production` and `display_errors = 0`.

2. **Dependency Installation**:
   ```bash
   composer install --no-dev --optimize-autoloader --no-interaction
   ```

3. **Framework Optimization**:
   ```bash
   php spark optimize
   ```
   This serializes and caches routing and configuration definitions.

4. **Database Migration**:
   ```bash
   php spark migrate --all
   ```

5. **Web Server Configuration**:
   - Document root MUST point strictly to `/public`.
   - Access to `/app`, `/system`, `/writable`, `/vendor` is FORBIDDEN.
   - `mod_rewrite` or equivalent MUST be enabled.
   - The `writable/` directory MUST be writable by the web server user.

---

> [!IMPORTANT]
> This file is a living document. Modifications require architectural review and team sign-off.

# Simple vs. Easy: Summary

This framework is based on the 2011 talk "Simple Made Easy" by Rich Hickey (creator of the Clojure programming language). It challenges the common industry habit of choosing "convenient" tools that eventually lead to unmanageable complexity.

## Simple (Objective)

- **Definition:** Originates from _simplex_, meaning "one fold" or "one braid."
- **Focus:** Concern, task, and role. It is about the lack of entanglement.
- **Key Characteristics:**
  - **Single Responsibility:** Each part does exactly one thing.
  - **Disentangled:** Components are not "braided" or tied together; they can be moved or changed independently.
  - **The Cost:** Requires significant upfront design, thought, and untangling.
  - **The Benefit:** Makes systems easier to understand, debug, and scale over the long term.

## Easy (Subjective)

- **Definition:** Originates from _adjacens_, meaning "lying nearby" or "at hand."
- **Focus:** Familiarity and accessibility.
- **Key Characteristics:**
  - **Near at Hand:** It's "easy" because it's already installed, familiar, or reachable.
  - **Frictionless:** "Copy, paste, ship" or "Install a package." It feels fast initially.
  - **The Trap:** What is "easy" (familiar) isn't always "simple" (unentangled).
  - **The Cost:** Choosing "easy" often creates "complected" (intertwined) systems that become impossible to change later.

## Comparison Table

| Feature     | Simple                             | Easy                                   |
| :---------- | :--------------------------------- | :------------------------------------- |
| **Nature**  | Objective (The system's structure) | Subjective (The developer's comfort)   |
| **Focus**   | One fold / one braid               | Adjacent / reachable                   |
| **Effort**  | Requires design and untangling     | "Just put it closer" / Familiarity     |
| **Action**  | Single responsibility              | Copy, paste, ship                      |
| **Outcome** | Long-term reliability and speed    | Short-term speed, long-term complexity |

## The Core Message

> _"Conflating [Simple and Easy] is why we’re drowning in complexity."_

While "easy" things allow us to move fast today, only "simple" designs allow us to keep moving fast in the future.