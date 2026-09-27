# SkillMatch — Project Skills & Capabilities

> **Workspace:** `~/Projects/SkillMatch`  
> **Connected Notes:** [[SkillMatch DevOps — Full Project Plan]] · [[SkillMatch DevOps — Engineer Handbook]] · [`AGENTS.md`](./AGENTS.md)

---

## 1. Technology Skills Matrix

* **Backend:** PHP 8.3 / Laravel 11 (Composer, Eloquent, Sanctum, JWT, Pest PHP)
* **AI Engine:** Python 3.11 / FastAPI (Uvicorn, Qdrant, Google Gemini, Pytest)
* **Frontend:** React 19 / Vite 6 / Tailwind CSS 4 (Redux Toolkit, TanStack Query, Oxlint)
* **Infrastructure:** CentOS Stream 10 host (`192.168.1.85`), Docker CE 29.8, Docker Compose v5

---

## 2. Core Execution Commands

### Host Verification (Run on VM)
```bash
# 8-invariant security audit (Target: 100% PASS, Exit Code 0)
sudo bash scripts/verify-server.sh

# Vitality health check
sudo python3 scripts/health-check.py

# Security baseline audit
sudo python3 scripts/security-audit.py
```

### Docker & Compose
```bash
# Start local development stack
docker compose up -d --build

# Service status & logs
docker compose ps
docker compose logs -f backend

# Stop stack
docker compose down
```

### Backend (Laravel 11)
```bash
# Run database migrations & seeders
docker compose exec backend php artisan migrate --force
docker compose exec backend php artisan db:seed --force

# Launch Redis queue worker
docker compose exec -d backend php artisan queue:work redis --queue=default,scraping,ai

# Run test suite
docker compose exec backend ./vendor/bin/pest
```

### AI Service (FastAPI)
```bash
# Run tests & dev server
pytest
uvicorn src.main:app --host 0.0.0.0 --port 8001 --reload
```

---

## 3. Engineering Guardrails

1. **Non-Root Containers:** Always run as `USER 10001:10001` (`appuser:appgroup`).
2. **Signal Handling:** Always use `ENTRYPOINT ["/sbin/tini", "--"]`.
3. **Multi-Stage Builds:** Separate build tools from runtime images.
4. **Secrets Discipline:** Never commit `.env` or secrets; always use `.env.example`.
