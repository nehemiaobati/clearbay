# clinerulesnpm.md

### **Extension: Hybrid Frontend Architecture (Node.js/TypeScript)**

**Note:** This document specifically governs the integration of Node.js, NPM, and TypeScript modules within the CodeIgniter framework.

#### **9.1. Directory Structure**

* **Source Code:** All raw JavaScript, TypeScript, and CSS source files MUST be located in `resources/[module_name]/`.
* **Compiled Output:** The build process MUST output files to `public/assets/[module_name]/`.
* **Public Directory:** The `public/` directory is reserved for CodeIgniter. Raw source code MUST NEVER exist here.

#### **9.2. The Build Pipeline (Vite)**

* **Configuration:** `vite.config.ts` MUST use `rollupOptions` to map specific inputs (e.g., `resources/learn/main.tsx`) to specific outputs.
* **Git Protocols:** `node_modules/` is gitignored. `public/assets/` (compiled) is committed.

#### **9.3. The Scaffolding Protocol (Boilerplate Injection)**

When initializing the environment or a new module, the AI MUST execute this **Automation Sequence** without pausing for user confirmation between steps:

1. **Generate:** Run `npm create vite@latest temp-vite -- --template react-ts` (Non-interactive mode).
2. **Harvest Configuration:**
    * Move `temp-vite/package.json` to Project Root (If root `package.json` exists, merge dependencies).
    * Move `temp-vite/tsconfig.json` (and references) to Project Root.
    * Move `temp-vite/vite.config.ts` to Project Root.
3. **Harvest Source:**
    * Create directory `resources/[module_name]/`.
    * Move contents of `temp-vite/src/` into `resources/[module_name]/`.
4. **Cleanup:** Delete `temp-vite/` folder, `index.html`, and `public/` (from the vite template).
5. **Configure:** Update `vite.config.ts` to point `input` to `resources/[module_name]/main.tsx` and `outDir` to `public/assets`.
6. **Install:** Run `npm install`.

#### **9.4. The PHP-to-JavaScript Bridge**

* **Mount Points:** Views MUST provide a unique HTML container ID (e.g., `<div id="module-root"></div>`).
* **Data Injection:** Data (CSRF, URLs) MUST be passed via `window.CI_CONFIG`.
* **Script Loading:** Load scripts via `base_url('assets/[module_name]/app.js')`.

---

### **Reference: Quick Start Workflow**

To initialize a React module in one pass:

1. **Execute Scaffolding Protocol (Rule 9.3):** Generates files, moves them to `resources/`, cleans up.
2. **Create Controller/View:** Standard CodeIgniter MVC setup.
3. **Build:** Run `npm run build` to confirm success.

//
@clinerulesnpm.md
Execute the "Scaffolding Protocol" (Rule 9.3) to initialize the frontend environment and create a module named "learn".

1. Use the `npm create vite` automation sequence.
2. Move the generated source code into `resources/learn`.
3. Configure `vite.config.ts` for CodeIgniter output paths immediately.
4. Run `npm run build` to verify the pipeline is clean.

Do not pause between file movements. Complete the scaffolding in one continuous task.
