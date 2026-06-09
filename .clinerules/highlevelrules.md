# CodeIgniter 4 Handbook: The "Simple over Easy" Standard

## Meta-Rules

1.  **Universal**: Applies to **ANY** CI4 project. Project-agnostic.
2.  **No Specifics**: Use generic terms (`BillingService`, not `GeminiService`).
3.  **First Source**: Read before starting any task.

---

## 0. Core Philosophy

**Prioritize Simple (Architectural Purity/Disentangled) over Easy (Developer Convenience).**

- **Simple**: Single-responsibility, linear, independent. Harder to design, trivial to maintain.
- **Easy**: Intertwined, "quick fix", familiar but kills modularity.

---

## 1. Project Initialization & Architecture

### 1.1 Boot Protocol (3 Steps)

1.  **Install**: `composer install` (`--no-dev` for prod).
2.  **Migrate**: `php spark migrate --all`.
3.  **Seed**: `php spark db:seed MainSeeder`.

### 1.2 Modular MVC-S Structure

- **Core** (`app/`): Shell/Shared resources.
- **Modules** (`app/Modules/`): Self-contained Features.

```text
app/Modules/Feature/
├── Config/      # Routes.php (Manual reg required)
├── Controllers/ # HTTP Orchestration
├── Database/    # Migrations, Seeds
├── Entities/    # Business Objects (Data shape)
├── Libraries/   # Services (Business Logic)
├── Models/      # DB Interaction
└── Views/       # Presentation
```

### 1.3 Generator & Tooling

- **Generate**: `php spark make:module [Name]`.
- **Register**: Add namespace `App\Modules\Name` in `app/Config/Autoload.php`.
- **Route**: Define in `app/Modules/Name/Config/Routes.php`.
- **Custom Commands** (Source: `tooling_setup.md`): `make:module`, `db:backup`, `db:restore`.

### 1.4 Code Standards & Quality

- **Strict**: `declare(strict_types=1);` and PSR-12.
- **Docs**: Complete PHPDoc for all classes/methods.
- **Private Helpers**:
  - **Visibility**: `private function _helperName()`.
  - **Location**: Grouped under `// --- Helper Methods ---`.
  - **Order**: Defined **before** public usage.
- **Type Accuracy**:
  - **Inline PHPDoc**: MANDATORY for ambiguous returns (e.g., `/** @var User|null $user */ $user = $model->find($id);`).
  - **Entity Casting**: dates MUST be `Time`, arrays MUST be `array`. Match CI dynamic casts.

---

## 2. Layer Responsibilities

### 2.1 Controllers (Orchestration)

- **Role**: Validate Input → Call Service → Return Response. "Skinny Controller" pattern.
- **Forbidden**: DB calls, Business Logic, HTML generation.
- **SEO**: MUST pass standard SEO `$data` (PageTitle, MetaDesc, Robots).

### 2.2 Services (Business Logic)

- **Role**: **Sole** location for Logic, Calculations, & API Interactions.
- **Responsibilities**: Transactions, File Processing, 3rd Party APIs.
- **Topology**: Vertical flow only. **Triangular/Brother-dependencies FORBIDDEN**.
  - Use **Facade Pattern** to wrap sub-service calls for controllers.
  - Use **Orchestrators** for cross-module logic.

### 2.3 Models (Data Access)

- **Role**: Fetch/Store. "Objects for Data, Arrays for Config."
- **Config**: `returnType = Entity`, strict `allowedFields`.
- **Forbidden**: Business logic, Direct Calls from Views.

### 2.4 Views (Presentation)

- **Role**: Display only.
- **Security**: `esc($var)` MANDATORY for all dynamic output.
- **Logic**: Loops/Simple conditionals only.

### 2.5 Database

- **Management**: Strict **Migrations** & **Seeds**. No manual schema changes.
- **Migration Strategy**:
  - **Active Dev/Prod**: Incremental updates.
  - **Fresh Environments**: **Compression Plan** & **Implementation Plan** MANDATORY before merging updates.
- **Indexing**: `addKey()` in `createTable` MANDATORY for: `status`, `user_id`, `type`, `slug`, `hash`, timestamps.

### 2.6 Helpers / Config / Performance

- **Helpers**: Pure, stateless functions. Registered in `Autoload.php`.
- **Secrets**: MUST live in `.env`.
- **Performance**:
  - **Pagination**: MANDATORY for lists.
  - **Auto-Routing**: `false` MANDATORY.
  - **Optimize**: Run `php spark optimize` in production.

---

## 3. Data & Protocol

### 3.1 Routing & Form Handling

- **Routing**: Named routes (`as`), Grouped (`$routes->group`), `static` callbacks. No closures.
- **PRG Pattern**: POST → Validate → Service → Redirect (`back()->with()`).
- **Forbidden**: Returning Views from POST methods.

### 3.2 Stateless & Files (The Unlink Pattern)

- **Principle**: Filesystem is ephemeral. Treat disk as temp space only.
- **Action**: Upload → Process → **Delete** (`@unlink`). No persistence.
- **Tempfile**: Randomized naming (`getRandomName`). Service MUST handle cleanup.
- **Session**: IDs only. No binary data. `DatabaseHandler` with `MEDIUMBLOB`.

### 3.3 API & AJAX

- **Format**: Standard JSON (`status`, `message`, `result`, `errors`, `csrf_token`).
- **CSRF**: **MANDATORY** rotation in every JSON response (Success/Error/Edge Case).
- **SSE (Streaming)**:
  - `Content-Type: text/event-stream`.
  - Immediate flush (`ob_flush(); flush()`).
  - `session_write_close()` before loop (unblocks parallel requests).
  - Fresh CSRF in initial packet.

---

## 4. Security & Observability

### 4.1 Security Mandates

1.  **CSRF**: Global. `csrf_field()` in forms, token update in JS from response.
2.  **Validation**: Strict Controller-level validation.
3.  **reCAPTCHA**: Verify via Service. Keys in `.env` only.
4.  **Throttling**: MANDATORY for Auth & Resource-heavy (AI/Crypto) routes.
5.  **Transactions**: MANDATORY for **ANY** DB modification.

### 4.2 Observability & Deployment

1.  **Logging**: `writable/logs/` with context arrays. `critical`, `error`, `info`.
2.  **Testing**: Default to NO test unless requested. PHPUnit required if tested.
3.  **Exceptions**: Catch `\Throwable` in Controllers to prevent white screens/leaks.
4.  **Deployment**: `CI_ENVIRONMENT = production`. Doc root strictly `/public`.

---

## 5. Frontend Blueprints

- **Stack**: Bootstrap 5 (Utility-first). Views extend `layouts/default`.
- **Structure**: Container > Blueprint Header > Blueprint Card.
- **Theme**: No hardcoded colors. Theme-aware utilities or CSS vars only.
- **SEO & Social**: OpenGraph & Twitter Cards **MANDATORY** for link previews.
  - Indexing: `index, follow` for public; `noindex, follow` for auth/dashboards.
- **Feedback**: Bootstrap Alerts for results. Toasts for connectivity/system only.
- **Partials**: Located in `app/Views/partials/` (e.g., `flash_messages.php`).
