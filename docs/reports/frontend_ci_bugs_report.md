# Technical Incident & Root Cause Analysis (RCA) Report

**Project:** SkillMatch Platform  
**Target Repository:** `Team-2-Skill-Project/Frontend`  
**Monorepo Target:** `Frontend/` in `DevOps-Infrastructure`  
**Report Date:** October 5, 2026  
**Author:** DevOps & Infrastructure Engineering  
**Classification:** Technical / Action Required  

---

## 1. Executive Summary

Following recent merges to the `main` branch of the Frontend repository (specifically PR #27 and associated commits), the automated Continuous Integration (CI) pipeline failed during the `frontend-tests` job. 

The pipeline is currently blocked at both the dependency installation stage (`npm ci`) and the static analysis stage (`npm run lint`). This failure prevents automated verification, Docker container packaging, and deployment progression.

Our technical investigation confirms that **the failures are caused by repository configuration and dependency hygiene issues in the Frontend repository**, rather than infrastructure or CI pipeline defects. This report provides the full forensic analysis, reproduction evidence, and step-by-step remediation actions for the Frontend team.

---

## 2. CI Pipeline Context & Execution Environment

The automated CI workflow is configured under [`.github/workflows/ci.yml`](file:///home/omar/Projects/SkillMatch/DevOps-Infrastructure/.github/workflows/ci.yml) to mirror standard production cloud environments:

* **Runner Image:** Ubuntu 24.04 LTS (`ubuntu-latest`)
* **Architecture:** `x86_64` (Linux Kernel 6.x)
* **Node.js Runtime:** `v22.x`
* **Package Manager:** `npm v10.x`
* **Executed Pipeline Sequence:**
  1. `npm ci`
  2. `npm run lint --if-present`
  3. `npm run build`

---

## 3. Incident Findings & Root Cause Analysis (RCA)

### Incident #1: Fatal `EBADPLATFORM` Error in `npm ci`

#### A. Failure Symptom
During the `npm ci` step on the Linux CI runner, the process immediately terminates with exit code 1:

```text
npm error code EBADPLATFORM
npm error notsup Unsupported platform for @rolldown/binding-win32-x64-msvc@1.2.11: wanted {"os":"win32","cpu":"x64"} (current: {"os":"linux","cpu":"x64"})
npm error notsup Valid os:   win32
npm error notsup Actual os:  linux
npm error notsup Valid cpu:  x64
npm error notsup Actual cpu: x64
```

#### B. Git Forensics
A Git history inspection of [`package.json`](file:///home/omar/Projects/SkillMatch/Frontend/package.json#L41) located the origin:

* **Commit:** `4808613091c7f394d5dae7d05e14125d3a6da49b`
* **Commit Message:** `feat: add Auth Dashboard and related components`
* **Author:** `asmaaghazala`
* **Diff Introduced:**
  ```diff
  --- a/package.json
  +++ b/package.json
  @@ -39,6 +40,7 @@
     "devDependencies": {
  +    "@rolldown/binding-win32-x64-msvc": "^1.2.9",
       "@tailwindcss/vite": "^4.3.3",
  ```

#### C. Root Cause Analysis
`@rolldown/binding-win32-x64-msvc` is a platform-specific native binary compiled exclusively for Microsoft Windows. 

When a developer installs dependencies on a Windows workstation, native compiler tools (such as Rolldown or Vite) download their platform-specific binary. If installed with certain CLI flags or manual package additions, npm persists that specific binding into `devDependencies`.

Because `npm ci` strictly validates that all declared dependencies match the current operating system, execution on Linux (`ubuntu-latest` or production Docker images) is aborted immediately by npm's platform constraint validator.

> [!IMPORTANT]
> Native platform bindings for bundlers (Rolldown, esbuild, SWC) are managed as `optionalDependencies` upstream by Vite. They must **never** be manually declared in `package.json`'s `dependencies` or `devDependencies`.

---

### Incident #2: Fatal Linter Failure on Minified Build Artifacts

#### A. Failure Symptom
When running `npm run lint`, the linter terminates with exit code 1:

```text
  React Hook "use" cannot be called at the top level. React Hooks must be called in a React function component or a custom React Hook function.
  code: react-hooks(rules-of-hooks)
  severity: error
  filename: dist/assets/index-BstfsW88.js
Found 3243 warnings and 1 error.
```

#### B. Git Forensics
Inspecting tracked files in Git revealed that compiled production files inside `dist/` were committed to version control:

* **Commit:** `c5d5e17`
* **Commit Message:** `refactor: update dashboard routing and layout structure`
* **Author:** `Hatem Elnemr`
* **Files Tracked in Git:**
  - `dist/.htaccess`
  - `dist/index.html`
  - `dist/assets/index-BstfsW88.js`
  - `dist/assets/index-Da0sFyTt.css`
  - `dist/assets/*.svg`

#### C. Root Cause Analysis
1. **Missing Entry in [`.gitignore`](file:///home/omar/Projects/SkillMatch/Frontend/.gitignore):** The project `.gitignore` file currently only contains:
   ```gitignore
   node_modules
   .env.local
   .env
   PROJECT_CODEBASE_GUIDE.md
   AUTH_LOGIC_CONVENTION.md
   refactoring_guidelines.md
   ```
   Because `dist/` is not listed, local builds were inadvertently staged and pushed to GitHub.
2. **Unscoped Lint Script in [`package.json`](file:///home/omar/Projects/SkillMatch/Frontend/package.json#L9):**
   ```json
   "lint": "oxlint"
   ```
   Without a path argument, `oxlint` parses every JavaScript file in the entire repository, including `dist/assets/index-BstfsW88.js`.
3. **False Positive in Minified Code:** The minifier generated variables/tokens named `use(...)`. Oxlint evaluates these against React 19 hook rules, throwing a fatal error because the minified function call occurs outside a React component.

When running `oxlint` scoped to `src/` (`oxlint src`), the linter reports:
```text
Found 29 warnings and 0 errors. Exit Code: 0.
```

---

## 4. Diagnostic Verification Matrix

| Test Step | Command Executed | Result | Status |
| :--- | :--- | :--- | :--- |
| Dependency Installation | `npm ci` | `EBADPLATFORM` (Wanted: win32, Actual: linux) | ❌ **FAIL** |
| Whole-Tree Linting | `npm run lint` (`oxlint`) | 1 Fatal Error on `dist/assets/index-BstfsW88.js` | ❌ **FAIL** |
| Scoped Linting | `npx oxlint src` | 0 Errors, 29 Warnings | ✅ **PASS** |
| Application Build | `npm run build` (`vite build`) | Built in 1.27s with valid chunks | ✅ **PASS** |

---

## 5. Step-by-Step Remediation Instructions for Frontend Team

Please execute the following remediation steps in the Frontend repository:

### Step 1: Remove the Windows-Exclusive Dependency
Run this command from the root of `Frontend/`:
```bash
npm uninstall @rolldown/binding-win32-x64-msvc
```
*This removes the line from `package.json` and regenerates a clean `package-lock.json`.*

### Step 2: Purge `dist/` from Git Index
Remove all compiled build artifacts from Git tracking without deleting your local source code:
```bash
git rm -r --cached dist/
```

### Step 3: Update `.gitignore`
Add the standard build artifact directories to [`.gitignore`](file:///home/omar/Projects/SkillMatch/Frontend/.gitignore):

```gitignore
# Dependencies
node_modules/

# Production build outputs
dist/
dist-ssr/

# Environment configurations
.env
.env.local
.env.*.local

# Logs and system caches
npm-debug.log*
yarn-debug.log*
yarn-error.log*
.DS_Store

# Documentation & Guidelines
PROJECT_CODEBASE_GUIDE.md
AUTH_LOGIC_CONVENTION.md
refactoring_guidelines.md
```

### Step 4: Scope the Lint Script
Update line 9 of [`package.json`](file:///home/omar/Projects/SkillMatch/Frontend/package.json#L9) to restrict linting to application source files:

```json
"scripts": {
  "dev": "vite",
  "build": "vite build",
  "lint": "oxlint src",
  "preview": "vite preview"
}
```

### Step 5: Verification & Push
Execute the full CI suite locally to verify the fix:

```bash
# 1. Clean verify dependencies
npm ci

# 2. Verify linting (must report 0 errors)
npm run lint

# 3. Verify production build
npm run build
```

Once all three commands succeed with exit code 0:
```bash
git add package.json package-lock.json .gitignore
git commit -m "fix(ci): remove win32 binding, untrack dist, and scope oxlint to src"
git push origin main
```

---

## 6. Preventive Best Practices & Recommendations

1. **Lockfile Hygiene in Multi-OS Teams:** When developers work across Windows, macOS, and Linux, team members must avoid manually installing architecture-specific subpackages.
2. **Pre-commit Hooks:** Consider installing Husky or pre-commit hooks that forbid committing files matching `dist/` or unformatted lockfiles.
3. **Branch Protection:** Ensure that passing the `frontend-tests` CI workflow is mandatory before any PR can be merged into `main`.
