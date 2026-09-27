FROM node:22-alpine AS builder 

WORKDIR /app

# Cache package manifests for fast rebuilds 
COPY package.json package-lock.json ./
RUN npm ci 

# Copy source code and compile Vite bundle 
COPY . . 
RUN npm run build

FROM nginx:alpine 

RUN apk add --no-cache tini 

RUN addgroup -g 10001 appgroup && \
    adduser -u 10001 -G appgroup -s /bin/sh -D appuser 

# Configuration dirs ownership 
RUN mkdir -p /var/cache/nginx /var/log/nginx /usr/share/nginx/html /tmp && \
    chown -R appuser:appgroup /var/cache/nginx /var/log/nginx /usr/share/nginx/html /etc/nginx /tmp && \
    chmod -R 775 /var/cache/nginx /var/log/nginx /usr/share/nginx/html /tmp

WORKDIR /usr/share/nginx/html 

COPY --from=builder --chown=appuser:appgroup /app/dist ./

COPY --chown=appuser:appgroup nginx.conf /etc/nginx/nginx.conf

# Drop privileges to non-root
USER appuser

EXPOSE 8080

ENTRYPOINT ["/sbin/tini", "--"]
CMD ["nginx", "-g", "daemon off;"]