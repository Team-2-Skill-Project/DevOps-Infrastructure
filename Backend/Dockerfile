# ─── Stage 1: Build & Dependency Resolution ───
FROM node:22-alpine AS builder

WORKDIR /app

# Install build dependencies only if required for native compilations
RUN apk add --no-cache python3 make g++

# Copy dependency manifests
COPY package.json package-lock.json ./

# Install production dependencies only with strict lockfile enforcement
RUN npm ci --omit=dev

# ─── Stage 2: Hardened Production Runtime ───
FROM node:22-alpine AS runner

# Install tini for PID 1 signal management and curl for health probes
RUN apk add --no-cache tini curl

WORKDIR /app

ENV NODE_ENV=production \
    PORT=8000

# Create unprivileged application service user & group (UID/GID 10001)
RUN addgroup -g 10001 -S appgroup && \
    adduser -u 10001 -S appuser -G appgroup

# Copy isolated production dependencies from builder
COPY --from=builder --chown=10001:10001 /app/node_modules ./node_modules

# Copy application manifests and source code
COPY --chown=10001:10001 package.json package-lock.json ./
COPY --chown=10001:10001 .sequelizerc server.js ./
COPY --chown=10001:10001 src/ ./src/
COPY --chown=10001:10001 scripts/ ./scripts/

# Create runtime directories with strict permissions
RUN mkdir -p storage logs tmp uploads/cvs uploads/avatars && \
    chown -R 10001:10001 storage logs tmp uploads && \
    chmod -R 750 storage logs tmp uploads

# Drop root privileges
USER 10001:10001

EXPOSE 8000

# Container health probe validating the Express /api/health endpoint
HEALTHCHECK --interval=15s --timeout=5s --start-period=10s --retries=3 \
  CMD curl -f http://localhost:8000/api/health || exit 1

ENTRYPOINT ["/sbin/tini", "--"]
CMD ["node", "src/server.js"]
