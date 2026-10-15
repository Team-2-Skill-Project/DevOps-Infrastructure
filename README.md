# SkillMatch — DevOps & Infrastructure Platform

![Target OS](https://img.shields.io/badge/Target_OS-CentOS_Stream_10_|_Rocky_Linux_9-purple?style=flat-square)
![Container Runtime](https://img.shields.io/badge/Runtime-Docker_CE_29.8_|_Compose_v2-blue?style=flat-square)
![Gateway](https://img.shields.io/badge/Gateway-Nginx_|_FirewallD_|_Fail2ban-red?style=flat-square)
![Backend](https://img.shields.io/badge/Backend-Node.js_22_|_Express_5_|_MySQL_8.0-green?style=flat-square)
![AI Engine](https://img.shields.io/badge/AI_Engine-FastAPI_|_PostgreSQL_16_|_Qdrant-yellow?style=flat-square)
![CI/CD](https://img.shields.io/badge/CI%2FCD-GitHub_Actions-brightgreen?style=flat-square)
![Telemetry](https://img.shields.io/badge/Telemetry-Prometheus_|_Grafana_|_cAdvisor-orange?style=flat-square)
![Security](https://img.shields.io/badge/Security-SELinux_Enforcing_|_Non--Root_UID_10001-blueviolet?style=flat-square)

> Central DevOps, platform engineering, and infrastructure repository for the **SkillMatch** career intelligence and automated job matching ecosystem. This repository manages host OS hardening, multi-service Docker containerization, perimeter security, reverse proxy routing, telemetry, operational runbooks, and automated CI/CD deployment pipelines.

---

## 1. Engineering Mission & Repository Scope

The SkillMatch application layer is partitioned across specialized services (Node.js Backend, Python AI Engine, React Web Client, and Flutter Mobile). 

This repository serves as the single source of truth for the platform, deployment, and security operations:
* **Host & OS Hardening:** CentOS Stream 10 baseline provisioning, strict FirewallD port controls (`22`, `80`, `443`), Fail2ban brute-force protection, Ed25519 SSH key enforcement, and SELinux running in `Enforcing` mode.
* **Service Orchestration:** Multi-container runtime orchestration via Docker Compose v2 with custom bridge network isolation (`skillmatch-net`).
* **Container Security Baseline:** Hardened multi-stage Docker builds, unprivileged execution (`USER 10001:10001` - `appuser:appgroup`), `tini` PID 1 init zombie process reaping, read-only rootfilesystems where possible, and scoped permission boundaries (`750` on `storage/`, `logs/`, `tmp/`).
* **Ingress & Perimeter Defense:** Nginx reverse proxy routing, TLS termination, payload size restrictions (5MB for CV uploads), burst rate limiting, and HTTP security headers.
* **Untrusted Document Sandbox:** Air-gapped execution environment (`network_mode: "none"`, `cap_drop: [ALL]`, cgroup memory ceilings) for parsing raw candidate CV files (PDF/DOCX).
* **Continuous Integration & Automated Sync:** Multi-repository GitHub Actions pipelines running static security analysis, container linting, and automated cross-repo synchronization into this staging monorepo.
* **Full-Stack Telemetry:** Prometheus metric scrapers, Grafana visualization dashboards, cAdvisor container profiling, Node Exporter host metrics, and k6 performance load tests.

---

## 2. Platform Architecture & Service Topology

The platform runtime operates on a hardened CentOS Stream 10 host (`192.168.1.85`), orchestrating containerized services across segregated network boundaries:

```
[Clients: Web Browser, Mobile App, API Consumers]
                  │ (HTTPS: 443 / HTTP: 80 / SSH: 22)
                  ▼
[CentOS Stream 10 Perimeter Gateway (192.168.1.85)]
  ├── FirewallD (Ingress strictly filtered: ports 22, 80, 443)
  ├── Fail2ban (Active jail monitoring systemd journal for brute-force abuse)
  └── Nginx Reverse Proxy / Ingress Gateway
        ├── /api/health ──────► Health check pass-through (bypasses rate limiter)
        ├── /api/v1/cv/upload ─► 5MB body limit + strict burst rate limiter
        ├── /api/ ────────────► 10 req/s general rate limit ──► [skillmatch-backend:8000]
        ├── / ────────────────► SPA Web Traffic ──────────────► [skillmatch-frontend:8080]
        └── /metrics ─────────► Denied (No external route)
                  │
                  ▼
[Docker Compose Network: skillmatch-net (Bridge)]
  │
  ├── Application Layer:
  │     ├── Backend API: Node.js 22 LTS / Express 5 / Sequelize ORM (Port: 8000)
  │     │     └── Runtime: node:22-alpine, non-root UID 10001, tini init
  │     ├── AI Service: Python 3.11 / FastAPI / LangChain / Celery (Port: 8001)
  │     │     └── Document parsing, skill extraction, taxonomy canonicalization
  │     └── Frontend Client: React 19 / Vite / Tailwind CSS v4 (Port: 8080)
  │
  ├── Persistence & Vector Layer:
  │     ├── MySQL 8.0 (Port: 3306) — Primary relational store for Node.js Backend
  │     ├── PostgreSQL 16 Alpine (Port: 5432) — Relational store for AI Engine
  │     ├── Redis 7 Alpine (Port: 6379) — Task queues, cache, and sliding rate limits
  │     └── Qdrant Vector DB (Port: 6333 / 6334) — Skill & job profile vector collections
  │
  └── Telemetry & Monitoring Stack (monitoring/):
        ├── Prometheus (Port: 9090) — Scrapes cAdvisor, Node Exporter & app metrics
        ├── Grafana (Port: 3000) — Real-time telemetry dashboards (admin/skillmatch)
        ├── cAdvisor (Port: 8085) — Container CPU, memory, and I/O consumption
        └── Node Exporter (Port: 9100) — Host kernel, storage, and networking metrics
```

---

## 3. Technology Stack Ground Truth

| Component | Technology | Version / Runtime | Deployment Details |
| :--- | :--- | :--- | :--- |
| **Host OS** | CentOS Stream 10 / Rocky 9 | Kernel `6.12.x` | SELinux `Enforcing`, FirewallD, Fail2ban |
| **Container Engine** | Docker CE & Compose Plugin | Docker `29.8+` / Compose `v2` | Native cgroup v2, daemon log limits (`20MB x 3`) |
| **Backend API** | **Node.js + Express 5** | Node `22-alpine` | Sequelize ORM, MySQL2, JWT auth, non-root `UID 10001` |
| **Relational DB (Backend)** | **MySQL** | `8.0` | Primary application database (Port `3306`) |
| **AI Enrichment Engine** | Python / FastAPI | Python `3.11` | PyPDF, LangChain, Qdrant Client, Celery |
| **Relational DB (AI)** | PostgreSQL | `16-alpine` | AI state and enrichment storage (Port `5432`) |
| **Queue & Cache** | Redis | `7-alpine` | Cache, Celery broker, sliding-window rate limiters |
| **Vector Database** | Qdrant | `v1.13.0` | Cosine similarity vectors for semantic matching |
| **Frontend Web** | React / Vite | React `19` / Node `22` | Tailwind CSS v4, TanStack Query v5, Nginx |
| **Observability** | Prometheus & Grafana | `v2.51` / `10.4.1` | RED metrics, cAdvisor (`v0.49`), Node Exporter (`v1.7`) |

---

## 4. Repository Structure

```
DevOps-Infrastructure/
├── Backend/                                # Node.js 22 + Express 5 Backend Service
│   ├── Dockerfile                          # Multi-stage production build (alpine, UID 10001, tini)
│   ├── package.json                        # Dependencies (Express, Sequelize, MySQL2, Jest)
│   ├── server.js                           # Root HTTP entrypoint & DB connection bootstrap
│   ├── src/                                # Controllers, models, migrations, routes, services
│   ├── scripts/                            # DB migration, OpenAPI generators, and mock seeds
│   └── tests/                              # Jest unit and integration test suite
├── Frontend/                               # React 19 Frontend Web Client
│   ├── Dockerfile                          # Multi-stage build + Nginx static server
│   ├── nginx.conf                          # SPA client routing and proxy settings
│   ├── package.json                        # Vite, React 19, Tailwind 4 dependencies
│   └── src/                                # Application features, hooks, components
├── AI/                                     # FastAPI AI & Semantic Matching Service
│   ├── Dockerfile                          # Python 3.11 runtime container
│   ├── requirements.txt                    # FastAPI, LangChain, Qdrant, Celery packages
│   ├── src/                                # CV parser, taxonomy matcher, roadmap engine
│   └── tests/                              # Pytest test suite
├── monitoring/                             # Telemetry & Performance Stack
│   ├── docker-compose.monitoring.yml       # Prometheus, Grafana, cAdvisor, Node Exporter
│   ├── prometheus/                         # Scrape targets and alert rules (prometheus.yml)
│   ├── grafana/                            # Provisioned dashboards and Prometheus datasources
│   └── load_tests/                         # k6 load and capacity testing scripts
├── scripts/                                # Host Hardening & Verification Suite
│   ├── bootstrap.sh                        # CentOS baseline installer & environment setup
│   ├── collect-baseline.sh                 # System metrics & OS baseline collector
│   ├── collect-hardening-baseline.sh       # Security hardening audit collector
│   ├── health-check.py                     # Non-destructive host resource health checker
│   ├── security-audit.py                   # Automated SELinux, SSH, and FirewallD auditor
│   └── verify-server.sh                    # Formal server verification test suite (8 invariants)
├── docker-compose.yml                      # Full-stack multi-service container orchestration
└── Architecture/                           # Vector diagrams and platform topology specs
```

---

## 5. Threat Model & DevSecOps Controls

| Threat Vector | Potential Impact | Implemented Countermeasure |
| :--- | :--- | :--- |
| **Malicious CV Binary** | Buffer overflow, RCE via crafted PDF/DOCX attachments. | Stream inspection, strict MIME-type validation, isolated unprivileged container execution (`UID 10001`), planned air-gapped sandbox (`--network none`). |
| **Decompression Bomb** | Host memory/disk exhaustion causing denial of service. | Nginx and Express enforce a strict 5MB payload ceiling (`client_max_body_size 5M`); cgroup memory limits prevent cascading failures. |
| **Privilege Escalation** | Container breakout or host takeover. | Containers run as non-root `UID 10001:10001` (`appuser:appgroup`); no container binds `/var/run/docker.sock`. Linux capabilities dropped (`cap_drop: ALL` on untrusted workloads). |
| **SSH / Gateway Brute-Force** | Unauthorized host ingress and credential brute-forcing. | FirewallD restricts ingress to ports 22, 80, 443; SSH password authentication disabled in favor of Ed25519 keys; Fail2ban actively jails repeating abusers. |
| **Database Injection & Data Leakage** | SQL injection, unauthorized record access. | Sequelize ORM parameterized queries; strict Zod input validation schemas; scoped database user permissions. |
| **Token Hijacking & Secret Exposure** | Unauthorized administrative or candidate operations. | Secrets managed exclusively via `.env` and GitHub Secrets; `.gitignore` guards all credential files; JWT tokens signed with SHA-256 secrets. |

---

## 6. Operations & Runbooks

### 6.1 Server Baseline Verification & Security Audit
To verify that the host satisfies all platform invariants and security baselines:
```bash
# Run non-destructive resource health check (CPU, RAM, Disk, Sockets)
python3 scripts/health-check.py

# Audit SELinux mode, SSH configuration, and FirewallD rules
python3 scripts/security-audit.py

# Run the complete server verification suite (evaluates 8 formal security invariants)
bash scripts/verify-server.sh
```

### 6.2 Launching the Application Stack
Launch all containerized services under Docker Compose:
```bash
# Build and start all services in detached mode
docker compose up -d --build

# Inspect running containers and health checks
docker compose ps
```

### 6.3 Database Migrations & Seeding
Once the `skillmatch-mysql` container is healthy, run database migrations and initial seeders:
```bash
# Execute Sequelize migrations
docker compose exec backend npm run migrate

# (Optional) Seed mock jobs, companies, and notification taxonomy
docker compose exec backend npm run seed:mock
```

### 6.4 Launching the Monitoring Stack
The monitoring stack runs on the shared `skillmatch-net` bridge network:
```bash
cd monitoring
docker compose -f docker-compose.monitoring.yml up -d
```
* **Grafana Dashboard:** Access at `http://<HOST_IP>:3000` (`admin` / `skillmatch`)
* **Prometheus Targets:** Access at `http://<HOST_IP>:9090/targets`
* **cAdvisor Metrics:** Access at `http://<HOST_IP>:8085`

### 6.5 Running k6 Performance & Load Tests
Execute load tests to evaluate API capacity under concurrency:
```bash
# Run baseline load test
k6 run monitoring/load_tests/load-test.js
```

---

## 7. Multi-Repository Automated CI/CD Sync

The SkillMatch ecosystem is distributed across dedicated service repositories under the `@Team-2-Skill-Project` GitHub Organization:
* [`Team-2-Skill-Project/Backend-JS`](https://github.com/Team-2-Skill-Project/Backend-JS)
* [`Team-2-Skill-Project/Frontend`](https://github.com/Team-2-Skill-Project/Frontend)
* [`Team-2-Skill-Project/AI`](https://github.com/Team-2-Skill-Project/AI)

Whenever a commit is merged into `main` of any service repository, an automated GitHub Actions workflow (`sync-to-devops.yml`) syncs the updated codebase into this repository using `rsync` and commits the changes under the bot identity:

```
Service Push (main) ──► sync-to-devops.yml ──► rsync ──► DevOps-Infrastructure/ ──► Staged for Compose & CI
```

This ensures that this infrastructure repository continuously reflects the latest production-ready code across all microservices for end-to-end integration testing and staging deployments.

---

## 8. Governance & Engineering Ownership

* **Lead Infrastructure & DevSecOps Engineer:** Omar (`0xTT-byte` / `omarfattouh.work@gmail.com`)
* **Organization:** Team 2 — SkillMatch Platform Project (`Team-2-Skill-Project`)
* **Maintenance & Inquiries:** For host access requests, firewall exceptions, or secret rotations, contact the infrastructure lead.
