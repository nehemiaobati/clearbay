# ARCHIVE / DEPRECATED: CodeIgniter 4 Handbook (Legacy)

> [!WARNING]
> This file contains deprecated rules and legacy architectural patterns. It is preserved for reference only. For current project standards, please refer to `.clinerules/clinerules.md`.

### **Guiding Principles**

## Meta-Rules: Managing This Handbook

1.  **Universal Applicability**: This document describes a standard for **ANY** CodeIgniter 4 project. It must remain project-agnostic.
2.  **No Specifics**: Do not verify or enforce rules using specific project file names (e.g., `GeminiController`). Use generic terms (`DomainController`, `BillingService`).
3.  **Living Document**: When a new architectural pattern is proven effective and generic, update this file.
4.  **First Source**: Read this file **before** starting any task on a new or existing project to align with the architectural standard.

- **Simple over Easy:** Prioritize objective simplicity (disentangled, single responsibility) over subjective ease (familiarity, shortcuts). "Easy" allows you to move fast today; "Simple" allows you to keep moving fast in the future.
- **Clarity over Cleverness:** Code must be simple, readable, and self-documenting.
- **Security is Not Optional:** Every line of code must be written with security as a primary concern.
- **Consistency is Key:** The framework and these rules provide one right way to build features. Follow it.
- **Fat Services, Skinny Controllers:** Business logic belongs in services, not controllers.

---

### **Part 1: The Four Layers of the Application**

**Note:** The project's primary architectural pattern is Modular (see Part 8). The following MVC-S layers exist _within_ each Module or within the core `app` directory for shared components.

The project follows a strict **Model-View-Controller-Service (MVC-S)** architecture.

#### **1.1. Controllers (`app/Controllers/` or `app/Modules/[ModuleName]/Controllers/`)**

- **DO:** Orchestrate the request-response cycle. Call Services and Models. Return a Response or a Redirect.
- **DON'T:** Contain business logic. Access the database directly. Contain complex calculations.

#### **1.2. Models (`app/Models/` or `app/Modules/[ModuleName]/Models/`)**

- **DO:** Handle all database interactions. Use the Query Builder and Entities.
- **DON'T:** Contain business logic. Be called directly from a View.

#### **1.3. Services (`app/Libraries/`)**

- **DO:** Contain all business logic (e.g., payment processing, API interactions). Be reusable. Be registered in `app/Config/Services.php`.
- **DON'T:** Handle HTTP-specific tasks like reading `POST` data. That is the Controller's job.

#### **1.4. Views (`app/Views/` or `app/Modules/[ModuleName]/Views/`)**

- **DO:** Display data passed from a Controller. Contain minimal presentation logic (loops, conditionals). Escape all output with `esc()`.
- **DON'T:** Perform database queries. Contain business logic.

#### **1.5. Database (`app/Database/` or `app/Modules/[ModuleName]/Database/`)**

- All schema changes MUST be managed via Migration files.
- Initial or test data MUST be handled by Seeder files.
- Directly altering the database schema outside of migrations is strictly FORBIDDEN.

#### **1.6. Helpers (`app/Helpers/`)**

- Helpers are for small, reusable, stateless procedural functions.
- All new helpers MUST be registered in `app/Config/Autoload.php`.
- Helpers MUST NOT contain business logic, perform database queries, or interact with external services.

#### **1.7. Configuration (app/Config/)**

- Sensitive or environment-specific settings MUST be managed via the `.env` file.
- All custom configuration files MUST be placed in a dedicated directory (e.g., `app/Config/Custom/`).
- Custom configuration files MUST use the `Config\Custom` namespace.

#### **1.8. Architectural Topology: Parallel vs. Braided**

- **Parallel Structure:** Dependencies must flow vertically (Controller -> Domain Service -> Sub-Services).
- **The "Ping Pong" Prohibition:** Triangular dependencies are **FORBIDDEN**.
  - _Definition:_ A Controller talking to a Main Service AND that Main Service's dependency.
  - _Example:_ `MainController` talking to `SubService` (e.g., `FormattingService`) while `MainService` also uses `SubService`.
  - _Fix:_ Use the **Facade Pattern**. The Main Service (`MainService`) must wrap the required methods of the Sub-Service (`SubService`) so the Controller has a single point of entry.
- **Brother-Service Isolation:** Services at the same level (e.g., `ModuleAService` and `ModuleBService`) should generally NOT call each other directly. If orchestration is needed, create a higher-level "Orchestrator Service" or handle it in the Controller.
- **Goal:** Clean, parallel execution stacks that don't "criss-cross" or braid together, ensuring ease of debugging and future scaling.

---

### **Part 2: The Request & Response Protocol**

#### **2.1. Routing: Named Routes are Law**

- Every route MUST be assigned a unique name (e.g., `['as' => 'users.profile']`).
- ALL URLs (HTML links, Redirects, AJAX endpoints, Fetch calls) MUST be generated using `url_to('route.name')`. CI4 Hardcoded URLs (e.g., /users/get) are strictly **FORBIDDEN**.

#### **2.1.1. Forbidden Routing Patterns (Legacy)**

- **Auto-Routing**: `$routes->setAutoRoute(true)` is **FORBIDDEN**. It bypasses security filters and obscures architecture.
- **Closure Logic**: Putting logic inside route closures (`$routes->get(..., function() { ... })`) is **FORBIDDEN**. Move logic to Controllers.
- **Unnamed Routes**: Routes without `['as' => 'name']` are **FORBIDDEN**.

#### **2.2. The 3 Steps of a Form Submission (Post/Redirect/Get)**

- **Step 1 (POST):** The controller method processes form data. It **MUST NOT** return a `view()`.
- **Step 2 (Redirect):** After processing, the controller stores messages in session "flash data" (`success`, `error`, `warning`, `info`) and MUST return a `redirect()` response.
- **Step 3 (GET):** The new page's controller renders a view, which reads and displays the flash data using the `flash_messages.php` partial.

#### **2.3. Filters: The Gatekeepers**

- Filters (`app/Filters/`) MUST be used for all security and access control (e.g., AuthFilter, AdminFilter, BalanceFilter).

#### **2.4. Global View Data: The `BaseController`**

- Data required on every page MUST be prepared and passed to the view system within `BaseController::initController()`.

---

### **Part 3: Code, Security, & Performance Mandates**

#### **3.1. Code Quality & Documentation**

- All PHP files MUST be PSR-12 compliant and start with `declare(strict_types=1);`.
- Every class, property, and method MUST have a complete and accurate PHPDoc block.
- **Helper Methods:**
  - Private helper methods MUST be prefixed with an underscore (e.g., `_getUserSettings()`, `_buildResponse()`).
  - Helper methods MUST be placed in a dedicated section marked with `// --- Helper Methods ---` or similar comment.
  - Helper methods MUST be ordered **before** the public methods that use them for better code readability.
  - Each helper should have a single, clear responsibility and be reusable where applicable.

#### **3.2. Security**

- **Data Escaping:** All dynamic data rendered in a view MUST be escaped with `esc()`.
- **CSRF Protection:**
  - MUST be enabled globally.
  - All HTML `POST` forms MUST include `csrf_field()`.
  - **Backend Responsibility:** The Backend MUST include a fresh CSRF token in every JSON response (success or error or edge cases) to allow the frontend to refresh its token (e.g., `['token' => csrf_hash()]`).
  - **Streaming:** For SSE, the fresh CSRF token MUST be sent in the initial data packet (or early in the stream) to ensure the client can recover if the stream is interrupted.
  - **Frontend Handling:** All AJAX/Fetch/EventSource requests MUST listen for and update the CSRF token from the response payload to prevent "disallowed action" errors on subsequent requests.
  - **Strict Native Usage:** Manual cookie override logic is FORBIDDEN. Passing the native token via payload (`csrf_hash()`) is the ONLY permitted synchronization method.
- **Database Interaction:** The Query Builder or Entities are the ONLY permitted methods.
- **Validation:** All user-supplied data MUST be validated using the Validation library before use.
- **Throttling:** The Throttler MUST be enabled on authentication and password reset routes.
- **reCAPTCHA:**
  - **Views:** MUST retrieve the site key via `service('recaptchaService')->getSiteKey()`.
  - **Controllers:** MUST verify the response via `service('recaptchaService')->verify($response)`.
  - **Configuration:** Keys (`recaptcha_siteKey`, `recaptcha_secretKey`) MUST be stored in `.env` and accessed only by the Service. Custom config files are FORBIDDEN.

#### **3.3. Transactional Integrity: All or Nothing**

- All operations involving multiple database `write` actions (INSERT, UPDATE, DELETE) MUST be wrapped in a database transaction.
- All operations involving financial data (e.g., updating `balance`) MUST be wrapped in a transaction.
- A transaction's status MUST be checked after completion. On failure, a `critical` log entry MUST be created.

#### **3.4. Performance**

- Auto-routing MUST be disabled (`$autoRoute = false`).
- Use pagination (`paginate()`) for lists. Avoid `findAll()` on large tables.
- The deployment script MUST run `php spark optimize` (see **Part 11** for the full boot sequence).

#### **3.5. Error Handling & Logging**

- Detailed error reporting MUST be disabled in production (set `display_errors` to 0; see **Part 9** for logging protocols).
- **Development:** Use `log_message()` for system events and developer-facing errors.
  - Use `session()->setFlashdata()` to communicate action outcomes to the user.

#### **3.6. Session Management**

- **Handler:** Sessions MUST use the `DatabaseHandler` driver to ensure compatibility with serverless and stateless environments.
- **Storage Schema:** The `ci_sessions` table's `data` column MUST be defined as `MEDIUMBLOB` (16MB) or larger. The default `BLOB` (64KB) is insufficient and forbidden.
- **Data Hygiene:** Storing large binary data (images, audio, PDFs) or large datasets in the session is **STRICTLY FORBIDDEN**.
  - Use temporary file storage (e.g., `WRITEPATH . 'uploads/'`) or cloud storage for binary assets.
  - Pass file paths or IDs to the view, not the raw data itself.

#### **3.7. Ephemeral File Handling (The Unlink Pattern)**

- **Principle:** The application MUST behave as if it is stateless/serverless. No user-generated files (uploads, generated media) should persist on disk after the request is completed.
- **Procedure:**
  - **Served Media:** Immediately after `readfile($path)`, you MUST call `@unlink($path)`.
  - **Processed Uploads:** Immediately after reading the file into memory (e.g., `file_get_contents`), you MUST call `@unlink($path)`.
  - **Generated Documents:** Intermediate files (HTML) and final outputs (PDF/DOCX) MUST be deleted immediately after they are read for the response.
  - **Retention Tip:** To retain files for debugging or history (e.g., in a persistent environment), simply comment out the `@unlink($path)` line.

#### **3.8. Streaming Protocols (SSE)**

- **Header Management:**
  - Headers MUST be flushed immediately (`ob_flush(); flush();`) to prevent server-side buffering delays.
  - The `Content-Type` MUST be `text/event-stream`.
- **Session Locking:**
  - You MUST call `session_write_close()` before entering the streaming loop. This releases the session file lock, allowing other requests (like CSRF checks) to proceed parallel to the long-running stream.
- **CSRF Synchronization:**
  - The stream MUST send a structured data packet containing the new CSRF token as one of its first events. This ensures the client remains authenticated even if the stream fails midway.

---

### **Part 4: Frontend & UI Mandates**

- Bootstrap 5 is the sole CSS framework.
- All pages MUST extend the master layout (e.g., `app/Views/layouts/default.php`).
- Common UI elements MUST be created as partials in `app/Views/partials/`.
- The Privacy Policy page MUST detail all cookies used.
- **URL Generation:**
  - ALL URLs (HTML links, Redirects, AJAX endpoints, Fetch calls) MUST be generated using `url_to('route.name')`.
  - Hardcoded URLs are strictly **FORBIDDEN**.

---

### **Part 5: Environment & Deployment Checklist**

- All credentials and API keys MUST be in the `.env` file. The `.env` file MUST NOT be committed to version control.
- In production, `CI_ENVIRONMENT` MUST be set to `production`.
- The server's document root MUST point to the `/public` directory.
- Deployments MUST run `composer install --no-dev` (see **Part 11** for the full checklist).
- Development files (`tests/`, `spark`, etc.) MUST be removed from the production server.

---

### **Part 6: Entities & Data Integrity**

- **Principle:** Arrays are for configuration; Objects are for business data. Passing associative arrays between layers is **FORBIDDEN** for business entities.
- **Location:** Entities MUST be located in `app/Entities/` or `app/Modules/[ModuleName]/Entities/`.
- **Model Configuration:** All Models MUST be configured to return Entity instances (e.g., `protected $returnType = \App\Entities\User::class;`).
- **Data Casting:** Entities MUST use the `$casts` property to enforce strict data types (e.g., `'is_active' => 'boolean'`, `'settings' => 'json'`).
- **Mutation:** Data formatting MUST be handled via Entity accessors and mutators (e.g., `setPassword(string $pass)`), never in the Controller.
- **Scope:** Entities are for data shaping only. They MUST NOT perform database queries or contain service-level business logic.

---

### **Part 7: The Unified Frontend Workflow (The 'Blueprint' Method)**

- **Philosophy:** Prioritize Bootstrap 5 utilities over custom CSS.
- **Core Components:**
  - **Container:** Page content MUST be wrapped in `<div class="container my-5">`.
  - **Card:** All primary content displays MUST use `<div class="card blueprint-card">`.
  - **Header:** All pages MUST have a `<div class="blueprint-header">`.
  - **Color Palette:** All styling MUST be theme-aware. Hardcoding colors is **FORBIDDEN**.
    1.  Use theme-aware Bootstrap utilities first (e.g., `bg-body-tertiary`).
    2.  Use project CSS variables second (e.g., `var(--card-bg)`).
- **Workflow:**
  - Controller MUST pass `pageTitle`, `metaDescription`, and `canonicalUrl` to the view.
  - All text inputs MUST use Bootstrap 5 "Floating labels".
  - Button hierarchy MUST be followed: `btn-primary` for primary, `btn-outline-secondary` for secondary, `btn-danger` for destructive actions.

---

### **Part 8: The Modular Architecture Mandate**

- **Principle:** The application MUST be structured by **feature** (Module), not by code type.
- **Location:** All modules MUST be located in the `app/Modules/` directory.
- **Structure:** Every module MUST contain `Config/`, `Controllers/`, `Database/`, `Entities/`, `Models/`, and `Views/` subdirectories.
- **Workflow:**
  1.  **Automated Generation:** Use the command `php spark make:module [ModuleName]` to generate the standard directory structure and boilerplate files.
  2.  **Manual Registration (if needed):** If the command fails to update `app/Config/Autoload.php`, register the module's namespace manually:
      Format ("'App\Modules\[ModuleName]' => APPPATH . 'Modules/[ModuleName]',")
  3.  **Routes:** Define routes in the generated `Config/Routes.php` file.
  4.  **Views:** Use the generated `Views/index.php` as a starting point.
  5.  When calling a module's view from its controller, the path MUST be fully qualified (e.g., `view('App\Modules\Blog\Views\blog\index', $data);`).
- **Core vs. Module:** The main `app/` directory is for the application's core shell. All distinct business features MUST be implemented as modules.

---

### **Part 9: Error Handling, Logging, & Debugging Protocols**

- **Principle:** In Development, errors MUST be visible and loud. In Production, errors MUST be silent to the user but strictly recorded in the logs.
- **Source of Truth:** The `writable/logs/` directory is the first step in any investigation.

#### **9.1. Logging Levels**

- **Use:** `log_message($level, $message, $context)`.
- **Context:** Logs MUST include context arrays (variables, user IDs, error traces), not just strings.
- **Hierarchy:**
  - `critical`: System unusable (DB down). Triggers immediate alert.
  - `error`: Runtime failure (Transaction rollback, Upload failed).
  - `info`: Key business events (User login, Report generated).

#### **9.2. Exception Strategy**

- **Primary:** Services SHOULD return a standardized error array: `['status' => 'error', 'message' => 'Human readable message']`.
- **Alternative:** For custom workflows, Services MAY throw specific, typed Exceptions (e.g., `throw new InsufficientFundsException()`).
- **Controllers:** MUST validate the returned `status` OR wrap Service calls in `try/catch` blocks.
  - If `status` is 'error', set User Flash Data (warnings) and redirect.
  - Catch `\Throwable` for unexpected crashes to prevent white screens.
- **Transactions:** On failure, the handler MUST rollback the transaction, log the specific error, and redirect safely.

#### **9.3. Debugging Workflow**

- **Development Only:**
  - Debug Toolbar MUST be enabled (`app/Config/Boot/development.php`).
  - `display_errors` set to 1.
  - Use `d()` (Kint) for inspecting variables.
- **Production Only:**
  - `display_errors` set to 0.
  - Use custom view `app/Views/errors/html/production.php`.
- **Forbidden:** Committing `d()`, `dd()`, or `die()` statements to the repository is strictly **FORBIDDEN**.

---

### **Part 10: Quality Assurance & Testing**

- **Principle:** No feature is "done" until it has a passing test.
- **Standard:** PHPUnit is the required testing framework (`php spark test`).
- **Unit Tests:** MUST be written for all Services and Helpers. Database connection is **FORBIDDEN** in unit tests.
- **Feature Tests:** MUST be written for Controllers and API endpoints. Database interactions MUST use the `DatabaseTransactions` trait to reset state after every test.
- **Data Generation:** Usage of `Fabricator` to generate test data is MANDATORY. Hardcoding arrays in test files is discouraged.

---

### **Part 11: The Boot Protocol (SOP)**

- **Goal:** A Developer must be able to boot the project in 3 commands.
- **New Environment Setup:**
  1.  `composer install`
  2.  `php spark migrate --all` (Runs core and module migrations)
  3.  `php spark db:seed [MainSeeder]` (Populates essential app data)
- **Production Launch:**
  - MUST run `composer install --no-dev --optimize-autoloader`.
  - MUST run `php spark optimize` to cache config and routes.
  - MUST point document root strictly to `/public`.

N/B

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
