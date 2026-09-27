# AGENTS.md — SkillMatch Multi-Agent & Context Orientation Guide

> [!IMPORTANT]
> **Orientation Instruction for AI Agents (Antigravity, Claude, Codex, Gemini):**  
> Read this file first whenever opening the `~/Projects/SkillMatch` workspace. It defines the current engineering stage, verified architecture, active priorities, and connections to the master Obsidian knowledge base.

---

## 1. Project & Engineer Identity

* **Project:** SkillMatch Platform Infrastructure & DevSecOps
* **Lead Infrastructure Engineer:** Omar (`0xTT-byte` / `omarfattouh.work@gmail.com`)
* **Role for AI Assistants:** Senior DevSecOps Mentor & Collaborative Pair Programmer. Do not make unauthorized modifications without verifying with the engineer.
* **Target Host Infrastructure:** Local VM at `192.168.1.85` running **CentOS Stream 10** (Linux Kernel `6.12.0-267.el10.x86_64`) with Docker CE 29.8, Docker Compose v5, SELinux `Enforcing`, and active `firewalld`.

---

## 2. Connected Knowledge Base & Master Documents

This workspace is intrinsically linked to Omar's Obsidian Knowledge Base:

1. **Master Execution Roadmap (10 Weeks / 30 Sessions):**  
   📄 `file:///home/omar/Documents/Obsidian/SkillMatch%20DevOps%20%E2%80%94%20Full%20Project%20Plan.md`  
   *(Obsidian Wikilink: `[[SkillMatch DevOps — Full Project Plan]]`)*
2. **DevOps Engineer Handbook (7 Operational Modules & SOPs):**  
   📄 `file:///home/omar/Documents/Obsidian/SkillMatch%20DevOps%20%E2%80%94%20Engineer%20Handbook.md`  
   *(Obsidian Wikilink: `[[SkillMatch DevOps — Engineer Handbook]]`)*  
   *(Mirrored in repo: `~/Documents/DevOps-Infrastructure-SkillMatch/docs/HANDBOOK.md`)*
3. **Formal Verification & Hardening Report (PDF):**  
   📄 `file:///home/omar/SkillMatch_Infrastructure_Hardening_and_Verification_Report.pdf`  
   *(Also mirrored: `file:///home/omar/server_hardening_and_baseline_report.pdf`)*

---

## 3. Current Project Stage & Progress Tracking

```
[Week 1: Host Baseline & Security Hardening] ──► 100% COMPLETE ✅ (Verified Exit Code 0)
[Week 2: Docker Containerization & Baseline]   ──► IN PROGRESS 🟡 (Current Active Focus)
[Week 3: Environment Separation & Secrets]    ──► PENDING ⚪
[Week 4: DevSecOps CI, Scanning & SBOM]       ──► PENDING ⚪
[Week 5: Continuous Deployment & Rollback]     ──► PENDING ⚪
[Week 6: Encrypted Backups & Disaster Recovery]──► PENDING ⚪
[Week 7: Asynchronous Queues & Cost Controls]  ──► PENDING ⚪
[Week 8: Full-Stack Observability & RED]       ──► PENDING ⚪
[Week 9: Gateway IaC & CV Sandbox Pipeline]    ──► PENDING ⚪
[Week 10: Load Testing & Capstone Demo]        ──► PENDING ⚪
```

### Verified Completed Milestones (Week 1 / Sessions 1–3)
* [x] **Host Hardening:** Ed25519 SSH keys active; password and direct root login disabled (`/etc/ssh/sshd_config.d/01-hardening.conf`).
* [x] **Intrusion Defense:** Fail2ban active with `sshd` jail bound to native systemd journal (`fail2ban-client status sshd`).
* [x] **Container Storage:** Host-wide daemon log ceiling enforced in `/etc/docker/daemon.json` (`20MB x 3` rotations = 60MB max).
* [x] **Patching Automation:** `dnf-automatic.timer` active for unattended security errata.
* [x] **Security Invariants:** `verify-server.sh` evaluated all 8 security invariants with **100% PASS (Exit Code 0)**.

### Current Active Tasks (Week 2 / Sessions 4–6)
* [x] Task 2.1: Docker CE 29.8 and Compose plugin operational on both host and VM.
* [x] Task 2.2: Unprivileged Docker execution verified.
* [ ] **Task 2.3: Author `Dockerfile` for Backend** (multi-stage: Composer 2.7 builder $\to$ PHP 8.3 CLI/FPM alpine runner).
* [ ] **Task 2.4: Enforce security directives:** unprivileged user `UID 10001:10001`, `tini` as PID 1, and scoped permissions on `storage/` and `bootstrap/cache/`.
* [ ] **Task 2.5: Gitleaks integration:** Install `.pre-commit-config.yaml` and `.gitleaks.toml`.
* [ ] **Task 2.6: Author `.env.example`** with container network hostnames (`DB_HOST=postgres`, `REDIS_HOST=redis`).
* [ ] **Task 2.8: Author local `docker-compose.yml`** orchestrating Backend, PostgreSQL 16, and Redis 7 on isolated bridge network with health checks.

---

## 4. Multi-Repository Workspace Structure

```
~/Projects/SkillMatch/
├── AGENTS.md               # [THIS FILE] Multi-agent orientation & stage tracker
├── Backend/                # Laravel 11 / PHP 8.3 (Port 8000)
│   ├── composer.json       # Dependencies: Sanctum, Fortify, JWT, Pest, Inertia
│   ├── app/                # Core domain, JobDeduplicationService, JobFingerprintService
│   ├── database/           # 45+ migrations, seeders
│   └── routes/             # api.php, web.php, cv.php
├── AI/                     # Python 3.11 / FastAPI (Port 8001)
│   ├── requirements.txt    # FastAPI, Uvicorn, Qdrant client, Gemini, PyPDF, python-docx
│   ├── src/main.py         # FastAPI application entrypoint
│   └── tests/              # Pytest test suite
└── Frontend/               # React 19 / Vite 6 (Port 5173 / Port 80)
    ├── package.json        # Dependencies: Tailwind 4, Redux Toolkit, TanStack Query
    ├── src/                # React components, router, state management
    └── vite.config.js      # Vite build configuration
```

Related Infrastructure Repository:
* `~/Documents/DevOps-Infrastructure-SkillMatch/` (mirrored on VM at `~/SkillMatch/DevOps-Infrastructure-SkillMatch/`).
* Contains `scripts/` (`verify-server.sh`, `health-check.py`, `security-audit.py`, `bootstrap.sh`) and `docs/operations/`.

---

## 5. Non-Negotiable Engineering Directives for Agents

1. **Stack Ground Truth:**
   * Backend is **PHP 8.3 / Laravel 11** with Composer. Do NOT generate Python or Node code for the Backend.
   * AI Service is **Python 3.11 / FastAPI**.
   * Frontend is **React 19 / Vite 6 / Tailwind CSS 4**.
   * Database is **PostgreSQL 16 Alpine** (`pdo_pgsql`).
   * Queue & Cache is **Redis 7 Alpine** (`phpredis`).
2. **Container Security Standards:**
   * Always use multi-stage builds.
   * Never run containers as root. Use `USER 10001:10001` (`appuser:appgroup`).
   * Always supervise PID 1 with `tini`.
   * Scope writable paths strictly (never use `chmod 777`).
3. **Secrets Hygiene:**
   * Never commit plaintext secrets, `.env` files, or private keys.
   * Always maintain `.env.example` with sanitized placeholders.
4. **Interactive Discipline:**
   * Act as a mentor. Explain the rationale behind recommendations.
   * Do not make sweeping destructive changes without user confirmation.
