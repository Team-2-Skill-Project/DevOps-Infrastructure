# SkillMatch — DevOps & Infrastructure Platform

![Target OS](https://img.shields.io/badge/Target_OS-CentOS_Stream_9_|_Rocky_Linux_9-purple?style=flat-square)
![Container Runtime](https://img.shields.io/badge/Runtime-Docker_Compose_v2-blue?style=flat-square)
![Gateway](https://img.shields.io/badge/Gateway-Nginx_|_FirewallD_|_Fail2ban-red?style=flat-square)
![CI/CD](https://img.shields.io/badge/CI%2FCD-GitHub_Actions-green?style=flat-square)
![Telemetry](https://img.shields.io/badge/Telemetry-Prometheus_|_Grafana_|_Loki-orange?style=flat-square)
![Disaster Recovery](https://img.shields.io/badge/Disaster_Recovery-GPG_AES--256_|_S3_Drill-blueviolet?style=flat-square)

> Central operational repository for host provisioning, ingress gateway security, container runtime hardening, CI/CD deployment pipelines, telemetry, and disaster recovery automation for the **SkillMatch** career intelligence platform.

---

## 1. Repository Scope & Engineering Mission

Application source code is partitioned across peer repositories (Flutter Mobile, Web Admin Dashboard, Backend API, and AI Enrichment Engine).

This repository serves as the single source of truth for the platform and infrastructure engineering layer:
* **Host & OS Hardening:** CentOS Stream 9 / Rocky Linux 9 baseline configuration, FirewallD perimeter defense, Fail2ban intrusion prevention, and SELinux enforcement.
* **Ingress & Abuse Protection:** Nginx reverse proxy configuration, TLS termination via Certbot, burst rate limiting, and HTTP request payload caps.
* **Container Security & Sandboxing:** Multi-stage container builds with non-root execution contexts (`UID 10001+`), cgroup v2 resource limits, and an air-gapped (`--network none`) sandbox for processing untrusted CV documents.
* **CI/CD Pipelines:** GitHub Actions workflows governing static analysis (Semgrep), secret detection (Gitleaks), container vulnerability auditing (Trivy), SBOM generation (Syft), and automated SSH deployments with health probe validation and rapid automated rollback.
* **Disaster Recovery:** Empirical backup and restore procedures with measured Recovery Time Objective (RTO $\le$ 15m) and Recovery Point Objective (RPO $\le$ 24h via daily encrypted snapshots; sub-hour RPO target for future continuous WAL archiving).
* **Observability:** Centralized RED metrics (Prometheus), structured log aggregation (Loki/Promtail), real-time dashboards (Grafana), and out-of-band alert webhooks (Alertmanager).

---

## 2. Infrastructure Architecture & Network Segregation (v3.1)

The deployment architecture operates on a single hardened host partitioned into distinct operational and network security zones:

```
[Clients: Mobile App, Web Admin, Probes]
                  │ (HTTPS:443 / SSH:22)
                  ▼
[CentOS Stream 9 Perimeter Gateway]
  ├── FirewallD (Ingress strictly restricted to ports 22, 80, 443)
  ├── Fail2ban (Automated jail for brute-force SSH and HTTP abuses)
  └── Nginx Reverse Proxy (:8000 Internal Target)
        ├── /health ────────► Uptime probe pass-through (Bypasses rate limiting)
        ├── /api/v1/cv/upload ► 5MB payload limit + 2 req/s burst limit
        ├── /api/ ──────────► 10 req/s general rate limit
        └── /metrics ───────► Denied (No external route)
                  │
                  ▼
[Docker Compose Network Segregation]
  │
  ├── [ingress_net] (Bridge)
  │     └── Nginx ◄──► Backend API
  │
  ├── [app_net] (Internal Bridge, No Host Routing)
  │     ├── Backend API
  │     ├── Redis 7 (Protected mode, requirepass, AOF persistence)
  │     ├── Scraper & Asynchronous Workers (Celery)
  │     └── ClamAV Daemon (TCP:3310 inline antivirus scan)
  │
  ├── [db_net] (Internal Bridge, No Host Routing)
  │     ├── Backend API
  │     ├── PostgreSQL 15 (Relational core, pg_isready healthcheck)
  │     ├── Migration Runner (Scoped pre-deployment schema runner)
  │     └── Parser Shim Supervisor (Persists validated skills JSON)
  │
  ├── [isolated_sandbox] (network_mode: "none", No Network Stack)
  │     └── CV Parser Container (cap_drop: ALL, 256MB cgroup, 15s timeout)
  │           │ (Raw extracted JSON over Unix Socket / IPC pipe)
  │           ▼
  │     Parser Shim Supervisor (Monitors timeouts & cgroups - NO docker.sock)
  │
  └── [monitor_net] (Internal Bridge)
        ├── Prometheus (Scrapes Backend :8000, Parser Shim :9100, Node Exporter)
        ├── Grafana (:3000 Internal / Web Admin Access)
        ├── Loki (:3100 Log Store) & Promtail
        └── Alertmanager (Pushes critical alerts to Slack / Discord)
```

> **Standalone Architecture Diagram:** Available as a vector PDF at [SkillMatch_Architecture_Diagram.pdf](Architecture/SkillMatch_Architecture_Diagram.pdf).

---

## 3. Threat Model & Infrastructure Controls

| Threat Vector | Potential Impact | Implemented Infrastructure Countermeasure |
| :--- | :--- | :--- |
| **Malicious CV Binary** | Remote Code Execution (RCE), buffer overflow via corrupted PDF/DOCX. | Document stream-scanned inline via ClamAV; parsed inside a container with `network_mode: "none"`, `cap_drop: [ALL]`, and non-root `UID 10002`. |
| **Decompression / Zip-Bomb** | Host disk or memory exhaustion causing cascading denial of service. | Nginx enforces a strict `client_max_body_size 5M`; parser runtime is throttled by a 256MB memory cgroup and a 15-second wall clock timeout. |
| **Docker Socket Privilege Escalation** | Root takeover of host system via container socket binding. | `CV_PARSER_SHIM` does **not** mount `/var/run/docker.sock`. Timeouts are tracked on the Unix socket; OOM kills are read from `/sys/fs/cgroup/.../memory.events`. |
| **Host Root Compromise & Backup Theft** | Exfiltration and permanent exposure of all historical customer CVs and records. | Cloud KMS envelope encryption. Master key material remains off-host. Revoking the host IAM role protects all cold backups in S3. |
| **Commercial LLM API Cost Runaway** | Uncapped scraping triggering thousands of enrichment calls. | Unique composite constraint `job_hash = SHA256(source + url + title + company)` deduplicates jobs; daily token circuit breaker defers excess tasks. |
| **Incomplete Schema Migration** | Failed deployment causing database inconsistency or downtime. | `MIGRATION_RUNNER` runs pre-flight validation with scoped credentials before new container images are promoted. |

---

## 4. Repository Structure

### 4.1 Current Repository Structure

Files currently committed and active in the repository:

```
DevOps-Infrastructure-SkillMatch/
├── README.md                                 # Platform infrastructure engineering documentation
├── Architecture/
│   └── SkillMatch_Architecture_Diagram.pdf   # Vector network & service topology diagram
└── scripts/
    ├── bootstrap.sh                          # CentOS baseline package installer & environment setup
    ├── collect-baseline.sh                   # System metrics and OS state baseline collection
    ├── collect-hardening-baseline.sh         # Security hardening & audit state baseline
    ├── health-check.py                       # Read-only host health check (CPU, memory, disk, network)
    ├── security-audit.py                     # Read-only security baseline audit (SELinux, SSH, ports)
    └── verify-server.sh                      # Combined host audit suite and report generator
```

### 4.2 Target / Planned Architecture (Implementation Roadmap)

> [!NOTE]
> **Implementation Roadmap:** The components below represent the designed target architecture to be introduced across upcoming project milestones as defined in the engineering plan:

```
DevOps-Infrastructure-SkillMatch/
├── Makefile                                  # [Planned - Week 2] Master operational entrypoint for tasks
├── .env.example                              # [Planned - Week 3] Exhaustive sanitized configuration template
├── docker/                                   # [Planned - Week 2]
│   ├── Dockerfile.backend                    # Multi-stage, non-root Python/Node runtime (UID 10001)
│   ├── Dockerfile.parser                     # Air-gapped document extractor (UID 10002, --network none)
│   ├── Dockerfile.shim                       # Parser supervisor, DB proxy & metrics exporter (UID 10003)
│   ├── docker-compose.yml                    # Production Compose stack with cgroups, networks, healthchecks
│   └── docker-compose.override.yml           # Local developer overrides (port mappings, volume mounts)
├── nginx/                                    # [Planned - Week 2/3]
│   ├── nginx.conf                            # Ingress settings, rate-limiting zones, buffer caps, JSON logs
│   └── conf.d/
│       └── skillmatch.conf                   # Virtual host reverse proxy, 5MB upload limit, security headers
├── monitoring/                               # [Planned - Week 7]
│   ├── prometheus/
│   │   ├── prometheus.yml                    # Telemetry scrape jobs (Backend, Shim, Node Exporter)
│   │   └── alerts.yml                        # Metric alerting rules (5xx rate, P95 latency, OOM kills)
│   └── loki/
│       └── loki-config.yml                   # Structured log storage and retention configuration
├── scripts/                                  # [Planned additions]
│   ├── backup.sh                             # [Planned - Week 6] Automated GPG AES-256 dump with offsite sync
│   ├── restore.sh                            # [Planned - Week 6] Empirical disaster recovery drill script
│   ├── deploy.sh                             # [Planned - Week 5] Commit-SHA deployment with health validation
│   ├── rollback.sh                           # [Planned - Week 5] Fast automated rollback to previous known-good SHA
│   ├── parse_worker.py                       # [Planned - Week 6] Air-gapped CV extraction script
│   └── parser_shim.py                        # [Planned - Week 6] Supervisor & Prometheus exporter
├── security/                                 # [Planned - Week 8]
│   └── .pre-commit-config.yaml               # Gitleaks, ShellCheck, Black, and file hygiene hooks
└── .github/workflows/                        # [Planned - Week 4/5]
    ├── backend-ci.yml                        # Gitleaks, Semgrep SAST, pip-audit, Trivy, Syft SBOM
    ├── flutter-ci.yml                        # Flutter analyze, test, and debug APK build verification
    └── deploy-staging.yml                    # SSH-based continuous delivery to CentOS staging server
```

---

## 5. Operations & Runbooks

### 5.1 Environment Configuration
*(Planned Implementation — Week 3 Milestone)*

Before executing operational tasks, generate a local `.env` file from the sanitized template:
```bash
cp .env.example .env
chmod 600 .env
```
Ensure database passwords, GPG passphrases, Cloud KMS ARNs, and webhook URLs are configured.

### 5.2 Server Health & Security Verification
To verify that the underlying CentOS / Rocky Linux host satisfies all security invariants using the active scripts in `scripts/`:
```bash
# Run non-destructive host health audit
python3 scripts/health-check.py

# Run comprehensive security baseline audit
python3 scripts/security-audit.py

# Run full server verification suite (generates timestamped report)
bash scripts/verify-server.sh
```

### 5.3 Database Backup & Disaster Recovery Drill
*(Planned Implementation — Week 6 Milestone)*

Disaster recovery procedures will be verified regularly against empirical SLA thresholds:

* **Trigger Encrypted Backup:**
  ```bash
  # Local test mode (skips S3 upload)
  bash scripts/backup.sh --local

  # Production mode (syncs encrypted archive and SHA-256 manifest to AWS S3)
  bash scripts/backup.sh
  ```

* **Execute Disaster Recovery Drill:**
  The restore script provisions an isolated, disposable PostgreSQL container, decrypts the archive using GPG AES-256, validates the schema and row counts, measures elapsed execution time, and tears down the sandbox:
  ```bash
  bash scripts/restore.sh --drill
  ```
  *Target RTO:* $\le 15\text{ minutes}$<br>
  *Target RPO:* $\le 24\text{ hours}$ (daily offsite encrypted snapshot cadence via Rclone / AWS CLI; continuous WAL streaming is a planned future enhancement)

### 5.4 Application Deployment & Automated Rollback
*(Planned Implementation — Week 5 Milestone)*

Deployments are immutable and pinned to Git commit SHAs:
```bash
# Deploy a specific commit SHA
bash scripts/deploy.sh <COMMIT_SHA>
```
**Pipeline Lifecycle:**
1. Records currently active container SHA for rollback safety.
2. Executes pre-flight database schema migrations via `MIGRATION_RUNNER`.
3. Recreates backend containers using the target commit image.
4. Probes `http://localhost:8000/health` continuously for 30 seconds.
5. If the probe fails, immediately triggers `scripts/rollback.sh` to restore the previous known-good image.

> [!NOTE]
> **Deployment Strategy & Downtime Characteristics:**
> The deployment strategy recreates the backend container (`docker compose up -d --no-deps backend`). Because container recreation stops the existing container before starting the new image, brief service disruption occurs during the restart window; it is therefore a fast-recreate deployment rather than strictly zero-downtime. Achieving true zero-downtime would require blue/green deployment or multi-replica rolling updates behind Nginx upstream health checks. The current architecture emphasizes immutability, automated health validation, and rapid recovery ($\text{MTTR} \le 2\text{ min}$) via automated rollback upon failure.

---

## 6. Key Performance Indicators (DevOps KPIs)

| Operational Domain | Metric / SLA | Target | Verification Method |
| :--- | :--- | :--- | :--- |
| **Disaster Recovery** | Recovery Time Objective (RTO) | **$< 15\text{ min}$** | Measured runtime of `restore.sh --drill`. |
| **Data Durability** | Recovery Point Objective (RPO) | **$\le 24\text{ hr}$** | Offsite S3/remote encrypted snapshot timestamp age and cron execution logs (daily snapshot cadence). |
| **Delivery Speed** | CI Quality Gate Latency | **$< 5\text{ min}$ ($p95$)** | GitHub Actions execution time across all test/SAST jobs. |
| **Resilience** | Deployment MTTR | **$\le 2\text{ min}$** | Automated rollback duration upon failed deployment probe. |
| **Security Hygiene** | Unpatched Criticals / Highs | **Zero** | Merge-blocking gate enforced by Trivy and Semgrep in CI. |
| **Scraper Reliability** | Duplicate Posting Ratio | **$0\%$** | Unique database constraint enforcement on `job_hash`. |
| **Cost Governance** | LLM API Budget Adherence | **$100\%$** | Circuit breaker tripping at daily token allocation limit. |

---

## 7. Governance & Team Ownership

Maintained by the **SkillMatch DevOps & Infrastructure Engineering Team**.<br>
Inquiries regarding host access, secrets rotation, firewall rules, or pipeline modifications should be directed to the infrastructure lead.
