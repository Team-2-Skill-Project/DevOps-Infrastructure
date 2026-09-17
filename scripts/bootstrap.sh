#!/usr/bin/env bash

# ==============================================================================
# SkillMatch DevOps & Infrastructure Platform
# Host Bootstrap Script
#
# Target:
#   - CentOS Stream 9
#   - Rocky Linux 9
#
# Purpose:
#   Prepare a clean Linux host for the SkillMatch infrastructure.
#
# Responsibilities:
#   - Verify supported OS and version
#   - Update system packages
#   - Configure required repositories
#   - Install host prerequisites
#   - Install Docker CE + Docker Compose
#   - Install FirewallD, Fail2ban and Nginx
#   - Configure automatic security updates
#   - Create SkillMatch host directories
#   - Apply a minimal firewall baseline
#   - Verify the resulting host baseline
#
# NOT responsible for:
#   - Application deployment
#   - Docker Compose application stack
#   - Production Nginx virtual hosts
#   - Application secrets
#   - CI/CD
#   - Monitoring stack
#   - Backup jobs
#   - Application-specific security configuration
#
# Production configuration will be handled by later infrastructure layers,
# primarily Ansible and Docker Compose.
#
# ==============================================================================

set -euo pipefail

readonly APP_ROOT="/opt/skillmatch"
readonly FIREWALL_ZONE="public"

SUDO=""

PASS_COUNT=0
FAIL_COUNT=0
WARN_COUNT=0


# ==============================================================================
# Logging
# ==============================================================================

log() {
    echo "[+] $*"
}

warn() {
    echo "[!] $*"
    WARN_COUNT=$((WARN_COUNT + 1))
}

fail() {
    echo "[FAIL] $*"
    FAIL_COUNT=$((FAIL_COUNT + 1))
}


# ==============================================================================
# Privilege Handling
# ==============================================================================

require_privileges() {
    if [[ "${EUID}" -eq 0 ]]; then
        SUDO=""
        return
    fi

    if ! command -v sudo >/dev/null 2>&1; then
        echo "[ERROR] sudo is required when running as a non-root user."
        exit 1
    fi

    sudo -v
    SUDO="sudo"
}


# ==============================================================================
# Operating System Verification
# ==============================================================================

verify_os() {
    log "Checking operating system..."

    if [[ ! -f /etc/os-release ]]; then
        echo "[ERROR] /etc/os-release not found."
        exit 1
    fi

    # shellcheck disable=SC1091
    source /etc/os-release

    case "${ID}" in
        centos|rocky)
            ;;
        *)
            echo "[ERROR] Unsupported operating system:"
            echo "        ${PRETTY_NAME:-unknown}"
            echo
            echo "Supported:"
            echo "        CentOS Stream 9"
            echo "        Rocky Linux 9"
            exit 1
            ;;
    esac

    if [[ "${VERSION_ID%%.*}" != "9" ]]; then
        echo "[ERROR] Unsupported OS major version: ${VERSION_ID}"
        echo "[ERROR] This bootstrap targets version 9."
        exit 1
    fi

    log "Detected: ${PRETTY_NAME}"
}


# ==============================================================================
# System Update
# ==============================================================================

update_system() {
    log "Updating system packages..."

    ${SUDO} dnf update -y
}


# ==============================================================================
# Repository Configuration
# ==============================================================================

configure_repositories() {
    log "Configuring required repositories..."

    # Required for dnf config-manager.
    ${SUDO} dnf install -y dnf-plugins-core

    # CodeReady Builder / crb: several EPEL packages resolve build/runtime
    # deps against this repo. Present as "crb" on both CentOS Stream 9 and
    # Rocky 9 (Rocky calls it PowerTools on 8, crb on 9). Non-fatal if
    # missing on some mirror layouts.
    if ! ${SUDO} dnf config-manager --set-enabled crb 2>/dev/null; then
        warn "Could not enable CRB repository (continuing without it)."
    fi

    # Docker CE repository.
    if [[ ! -f /etc/yum.repos.d/docker-ce.repo ]]; then
        log "Adding Docker CE repository..."

        ${SUDO} dnf config-manager \
            --add-repo \
            https://download.docker.com/linux/centos/docker-ce.repo

        # CentOS Stream 9 resolves dnf's $releasever to "9-stream", but
        # Docker only publishes packages under /centos/9/. Left as-is, the
        # repo silently has zero packages and docker-ce fails to resolve.
        # Pin it to the actual RHEL-compatible major version instead.
        local docker_releasever
        docker_releasever="$(rpm -E '%{rhel}')"

        ${SUDO} sed -i \
            "s/\$releasever/${docker_releasever}/g" \
            /etc/yum.repos.d/docker-ce.repo

        log "Docker CE repository pinned to release ${docker_releasever}."
    else
        log "Docker CE repository already configured."
    fi

    # EPEL provides Fail2ban on RHEL-compatible systems.
    if ! rpm -q epel-release >/dev/null 2>&1; then
        log "Installing EPEL repository..."

        ${SUDO} dnf install -y epel-release
    else
        log "EPEL repository already installed."
    fi

    # podman-docker ships a /usr/bin/docker shim that file-conflicts with
    # docker-ce's own /usr/bin/docker. It's common on minimal CentOS/Rocky
    # images with container tools preinstalled. Remove it before install
    # rather than let dnf fail mid-transaction.
    if rpm -q podman-docker >/dev/null 2>&1; then
        warn "podman-docker conflicts with docker-ce; removing it."
        ${SUDO} dnf remove -y podman-docker
    fi

    ${SUDO} dnf makecache
}


# ==============================================================================
# Host Package Installation
# ==============================================================================

install_packages() {
    log "Installing host prerequisites..."

    # --allowerasing lets dnf resolve remaining container-tooling conflicts
    # (e.g. runc/containerd provided by two repos) instead of aborting the
    # whole transaction. It only activates when a real conflict exists.
    ${SUDO} dnf install -y --allowerasing \
        ca-certificates \
        curl \
        wget \
        git \
        vim \
        tmux \
        htop \
        net-tools \
        nmap \
        tcpdump \
        jq \
        unzip \
        tar \
        rsync \
        openssl \
        gcc \
        make \
        bash-completion \
        python3 \
        python3-pip \
        python3-devel \
        libmagic \
        policycoreutils-python-utils \
        firewalld \
        fail2ban \
        nginx \
        dnf-automatic \
        docker-ce \
        docker-ce-cli \
        containerd.io \
        docker-buildx-plugin \
        docker-compose-plugin
}


# ==============================================================================
# Service Configuration
# ==============================================================================

configure_services() {
    log "Enabling required host services..."

    # These are host-level services required by the infrastructure.
    ${SUDO} systemctl enable --now firewalld
    ${SUDO} systemctl enable --now docker
    ${SUDO} systemctl enable --now fail2ban

    # Nginx is installed as the future ingress component, but its
    # production configuration belongs to the Ansible/configuration layer.
    #
    # Do not start Nginx here until the actual SkillMatch configuration
    # has been deployed.

    log "Host services configured."
}


# ==============================================================================
# Firewall Baseline
# ==============================================================================

configure_firewall() {
    log "Configuring minimal FirewallD baseline..."

    # Make sure the intended zone exists.
    if ! ${SUDO} firewall-cmd --get-zones | grep -qw "${FIREWALL_ZONE}"; then
        fail "Firewall zone '${FIREWALL_ZONE}' does not exist."
        return
    fi

    # Keep SSH reachable.
    ${SUDO} firewall-cmd \
        --permanent \
        --zone="${FIREWALL_ZONE}" \
        --add-service=ssh

    # Intended public ingress.
    ${SUDO} firewall-cmd \
        --permanent \
        --zone="${FIREWALL_ZONE}" \
        --add-service=http

    ${SUDO} firewall-cmd \
        --permanent \
        --zone="${FIREWALL_ZONE}" \
        --add-service=https

    ${SUDO} firewall-cmd --reload

    log "Firewall baseline applied."
}


# ==============================================================================
# Automatic Security Updates
# ==============================================================================

configure_automatic_updates() {
    log "Configuring automatic security updates..."

    local config="/etc/dnf/automatic.conf"

    if [[ ! -f "${config}" ]]; then
        fail "Missing DNF automatic configuration: ${config}"
        return
    fi

    # Configure security-only updates.
    if grep -qE '^upgrade_type[[:space:]]*=' "${config}"; then
        ${SUDO} sed -i \
            's/^upgrade_type[[:space:]]*=.*/upgrade_type = security/' \
            "${config}"
    else
        echo "upgrade_type = security" | ${SUDO} tee -a "${config}" >/dev/null
    fi

    # Apply updates automatically.
    if grep -qE '^apply_updates[[:space:]]*=' "${config}"; then
        ${SUDO} sed -i \
            's/^apply_updates[[:space:]]*=.*/apply_updates = yes/' \
            "${config}"
    else
        echo "apply_updates = yes" | ${SUDO} tee -a "${config}" >/dev/null
    fi

    ${SUDO} systemctl enable --now dnf-automatic.timer

    log "Automatic security updates configured."
}


# ==============================================================================
# SkillMatch Filesystem
# ==============================================================================

create_directories() {
    log "Creating SkillMatch host directories..."

    ${SUDO} install -d -m 0750 "${APP_ROOT}"

    # Untrusted uploaded files.
    ${SUDO} install -d -m 0700 "${APP_ROOT}/quarantine"
    ${SUDO} install -d -m 0700 "${APP_ROOT}/uploads"

    # Operational directories.
    ${SUDO} install -d -m 0750 "${APP_ROOT}/backups"
    ${SUDO} install -d -m 0750 "${APP_ROOT}/logs"

    log "SkillMatch directories created."
}


# ==============================================================================
# Verification Helpers
# ==============================================================================

check_command() {
    local command_name="$1"

    if command -v "${command_name}" >/dev/null 2>&1; then
        log "PASS: command '${command_name}'"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "Missing command: ${command_name}"
    fi
}


check_package() {
    local package="$1"

    if rpm -q "${package}" >/dev/null 2>&1; then
        log "PASS: package '${package}'"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "Missing package: ${package}"
    fi
}


check_service() {
    local service="$1"

    if systemctl is-active --quiet "${service}"; then
        log "PASS: service '${service}' is active"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "Service '${service}' is not active"
    fi
}


# ==============================================================================
# Security and Infrastructure Verification
# ==============================================================================

verify_baseline() {
    log "Running host baseline verification..."

    # --------------------------------------------------------------------------
    # SELinux
    # --------------------------------------------------------------------------

    local selinux_status
    # Guarded: under `set -e`, an unguarded failed command substitution
    # (e.g. getenforce missing on a stripped image) would kill the whole
    # script instead of just failing this one check.
    selinux_status="$(getenforce 2>/dev/null || echo "Unavailable")"

    if [[ "${selinux_status}" == "Enforcing" ]]; then
        log "PASS: SELinux is Enforcing"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "SELinux is not Enforcing: ${selinux_status}"
    fi


    # --------------------------------------------------------------------------
    # FirewallD
    # --------------------------------------------------------------------------

    if ${SUDO} firewall-cmd --state >/dev/null 2>&1; then
        log "PASS: FirewallD is running"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "FirewallD is not running"
    fi

    if ${SUDO} firewall-cmd \
        --zone="${FIREWALL_ZONE}" \
        --query-service=ssh >/dev/null 2>&1
    then
        log "PASS: SSH allowed in ${FIREWALL_ZONE} zone"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "SSH is not allowed in ${FIREWALL_ZONE} zone"
    fi

    if ${SUDO} firewall-cmd \
        --zone="${FIREWALL_ZONE}" \
        --query-service=http >/dev/null 2>&1
    then
        log "PASS: HTTP allowed in ${FIREWALL_ZONE} zone"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "HTTP is not allowed in ${FIREWALL_ZONE} zone"
    fi

    if ${SUDO} firewall-cmd \
        --zone="${FIREWALL_ZONE}" \
        --query-service=https >/dev/null 2>&1
    then
        log "PASS: HTTPS allowed in ${FIREWALL_ZONE} zone"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "HTTPS is not allowed in ${FIREWALL_ZONE} zone"
    fi


    # --------------------------------------------------------------------------
    # SSH
    # --------------------------------------------------------------------------

    check_service "sshd"


    # --------------------------------------------------------------------------
    # Docker
    # --------------------------------------------------------------------------

    check_package "docker-ce"
    check_package "docker-ce-cli"
    check_package "containerd.io"
    check_package "docker-buildx-plugin"
    check_package "docker-compose-plugin"

    check_service "docker"

    if ${SUDO} docker --version >/dev/null 2>&1; then
        log "PASS: Docker CLI available"
        ${SUDO} docker --version
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "Docker CLI unavailable"
    fi

    if ${SUDO} docker compose version >/dev/null 2>&1; then
        log "PASS: Docker Compose available"
        ${SUDO} docker compose version
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "Docker Compose unavailable"
    fi


    # --------------------------------------------------------------------------
    # Docker Runtime
    # --------------------------------------------------------------------------

    log "Testing Docker runtime..."

    if ${SUDO} docker run --rm hello-world >/dev/null 2>&1; then
        log "PASS: Docker runtime test"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "Docker runtime test failed"
    fi


    # --------------------------------------------------------------------------
    # Fail2ban
    # --------------------------------------------------------------------------

    check_package "fail2ban"
    check_service "fail2ban"


    # --------------------------------------------------------------------------
    # Nginx
    # --------------------------------------------------------------------------

    check_package "nginx"

    if ${SUDO} nginx -t >/dev/null 2>&1; then
        log "PASS: Nginx configuration syntax"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "Nginx configuration test failed"
    fi

    if systemctl is-active --quiet nginx; then
        log "PASS: Nginx is active"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        warn "Nginx is installed but not active; production configuration belongs to Ansible."
    fi


    # --------------------------------------------------------------------------
    # Automatic Updates
    # --------------------------------------------------------------------------

    check_package "dnf-automatic"

    if systemctl is-enabled --quiet dnf-automatic.timer; then
        log "PASS: dnf-automatic.timer enabled"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "dnf-automatic.timer is not enabled"
    fi

    if systemctl is-active --quiet dnf-automatic.timer; then
        log "PASS: dnf-automatic.timer active"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "dnf-automatic.timer is not active"
    fi

    local automatic_config="/etc/dnf/automatic.conf"

    if grep -Eq '^[[:space:]]*upgrade_type[[:space:]]*=[[:space:]]*security[[:space:]]*$' \
        "${automatic_config}"
    then
        log "PASS: automatic updates configured for security updates"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "dnf-automatic upgrade_type is not set to security"
    fi

    if grep -Eq '^[[:space:]]*apply_updates[[:space:]]*=[[:space:]]*yes[[:space:]]*$' \
        "${automatic_config}"
    then
        log "PASS: automatic updates application enabled"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "dnf-automatic apply_updates is not enabled"
    fi


    # --------------------------------------------------------------------------
    # Required Commands
    # --------------------------------------------------------------------------

    check_command "git"
    check_command "curl"
    check_command "wget"
    check_command "jq"
    check_command "rsync"
    check_command "openssl"
    check_command "python3"
    check_command "nmap"
    check_command "tcpdump"


    # --------------------------------------------------------------------------
    # SkillMatch Directories
    # --------------------------------------------------------------------------

    for directory in \
        "${APP_ROOT}" \
        "${APP_ROOT}/quarantine" \
        "${APP_ROOT}/uploads" \
        "${APP_ROOT}/backups" \
        "${APP_ROOT}/logs"
    do
        if [[ -d "${directory}" ]]; then
            log "PASS: directory ${directory}"
            PASS_COUNT=$((PASS_COUNT + 1))
        else
            fail "Missing directory: ${directory}"
        fi
    done


    # --------------------------------------------------------------------------
    # Directory Permissions
    # --------------------------------------------------------------------------

    local quarantine_permissions
    local uploads_permissions

    quarantine_permissions="$(stat -c '%a' "${APP_ROOT}/quarantine")"
    uploads_permissions="$(stat -c '%a' "${APP_ROOT}/uploads")"

    if [[ "${quarantine_permissions}" == "700" ]]; then
        log "PASS: quarantine permissions are 0700"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "Quarantine permissions are ${quarantine_permissions}; expected 0700"
    fi

    if [[ "${uploads_permissions}" == "700" ]]; then
        log "PASS: uploads permissions are 0700"
        PASS_COUNT=$((PASS_COUNT + 1))
    else
        fail "Uploads permissions are ${uploads_permissions}; expected 0700"
    fi
}


# ==============================================================================
# Final Summary
# ==============================================================================

print_summary() {
    echo
    echo "================================================================"
    echo " SkillMatch Host Bootstrap Summary"
    echo "================================================================"
    echo " PASS:     ${PASS_COUNT}"
    echo " WARNINGS: ${WARN_COUNT}"
    echo " FAILURES: ${FAIL_COUNT}"
    echo "================================================================"

    if [[ "${FAIL_COUNT}" -gt 0 ]]; then
        echo "[FAIL] Host bootstrap completed with failures."
        exit 1
    fi

    echo "[PASS] SkillMatch host bootstrap completed successfully."
}


# ==============================================================================
# Main
# ==============================================================================

main() {
    echo
    echo "================================================================"
    echo " SkillMatch DevOps & Infrastructure Platform"
    echo " Host Bootstrap"
    echo "================================================================"
    echo

    require_privileges
    verify_os
    update_system
    configure_repositories
    install_packages
    configure_services
    configure_firewall
    configure_automatic_updates
    create_directories
    verify_baseline
    print_summary
}

main "$@"