#!/usr/bin/env bash

set -euo pipefail

# RedOps RHEL Baseline Collection Script
# Day 1
#
# Purpose:
#   Collect a read-only snapshot of the RHEL server state.
#
# Usage:
#   ./collect-baseline.sh
#
# Output:
#   docs/operations/baseline/<timestamp>/
#
# Notes:
#   - Does NOT modify system configuration.
#   - Commands requiring elevated privileges use sudo.
#   - Intended to be safe to run repeatedly.
# ==========================================================

SCRIPT_NAME="$(basename "$0")"
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BASELINE_DIR="${PROJECT_ROOT}/docs/operations/baseline/${TIMESTAMP}"

mkdir -p "${BASELINE_DIR}"

LOG_FILE="${BASELINE_DIR}/collection.log"

exec > >(tee -a "${LOG_FILE}") 2>&1

# ============================================
# Helpers
# ===========================================

section() {
    echo
    echo "============================================"
    echo "$1"
    echo "============================================"
}

run_command() {
    local description="$1"
    shift
    
    echo
    echo "--- ${description} ---"
    if command -v "$1" >/dev/null 2>&1; then
        "$@" || {
            echo "[WARNING] Command failed: $*"
            return 0
        }
    else
        echo "[WARNING] Command not available: $1"
        return 0
    fi
}

# ------------------------------------------
# Start
# -----------------------------------------

section "CenOS Baseline Collection"

echo "Script:       ${SCRIPT_NAME}"
echo "Timestamp:    ${TIMESTAMP}"
echo "Hostname:     $(hostname)"
echo "User:         $(whoami)"
echo "Project Root: ${PROJECT_ROOT}"
echo "Output:       ${BASELINE_DIR}"

# ------------------------------------------
# System
# -----------------------------------------

section "SYSTEM"

{
    echo "### Hostname"
    hostnamectl
    
    echo
    echo "### OS"
    cat /etc/redhat-release
    
    echo
    echo "### Kernel"
    uname -a
    
    echo
    echo "### Architecture"
    uname -m
    
    echo
    echo "### Uptime"
    uptime
} | tee "${BASELINE_DIR}/system.txt"

# ------------------------------------------
# CPU
# ------------------------------------------

section "CPU"

lscpu | tee "${BASELINE_DIR}/cpu.txt"

# ------------------------------------------
# Memory
# ------------------------------------------

section "MEMORY"

free -h | tee "${BASELINE_DIR}/memory.txt"

# ------------------------------------------
# Storage
# ------------------------------------------

section "NETWORK"

{
    echo "### Interfaces"
    ip -br addr
    
    echo
    echo "### Routes"
    ip route show
    
    echo
    echo "### Listening Ports"
    sudo ss -tulpn
    
    echo
    echo "### DNS"
    if command -v resolvectl >/dev/null 2>&1 && systemctl is-active --quiet systemd-resolved; then
        resolvectl status
        
        elif command -v nmcli >/dev/null 2>&1; then
        nmcli dev show | grep -E 'IP4.DNS|IP6.DNS' || true
        
        elif [[ -f /etc/resolv.conf ]]; then
        cat /etc/resolv.conf
        
    fi
} | tee "${BASELINE_DIR}/network.txt"

# ------------------------------------------
# Users and privileges
# ------------------------------------------

section "USERS AND PRIVILEGES"

{
    echo "### Current identity"
    id
    
    echo
    echo "### Current user"
    whoami
    
    echo
    echo "### Current user's groups"
    groups
    
    echo
    echo "### sudo permissions"
    sudo -l
    
    echo
    echo "### Local users"
    getent passwd
    
    echo
    echo "### Local groups"
    getent group
} | tee "${BASELINE_DIR}/users.txt"

# ------------------------------------------
# Packages / repository state
# ------------------------------------------

section "PACKAGES AND REPOSITORIES"

{
    echo "### Enabled repos"
    sudo dnf repolist
    
    echo
    echo "### Installed packages count"
    rpm -qa | wc -l
    
    echo
    echo "### Recent DNF history"
    sudo dnf history list | head -20
} | tee "${BASELINE_DIR}/packages.txt"

# ------------------------------------------
# systemd
# ------------------------------------------

section "SYSTEMD"

{
    echo "### Failed services"
    systemctl --failed --no-pager
    
    echo
    echo "### Running services"
    systemctl list-units \
    --type=service \
    --state=running \
    --no-pager
    
    echo
    echo "### Enabled services"
    systemctl list-unit-files \
    --type=service \
    --state=enabled \
    --no-pager
} | tee "${BASELINE_DIR}/services.txt"

# ------------------------------------------
# SSH
# ------------------------------------------

section "SSH"

{
    echo "### SSHD service"
    systemctl status sshd --no-pager
    
    echo
    echo "### Effective SSH configuration"
    
    if command -v sshd >/dev/null 2>&1; then
        sudo sshd -T
    else
        echo "[WARNING] sshd is not installed."
    fi
} | tee "${BASELINE_DIR}/ssh.txt"

# ------------------------------------------
# Firewall
# ------------------------------------------

section "FIREWALL"

{
    echo "### Firewall state"
    
    if command -v firewall-cmd >/dev/null 2>&1; then
        sudo firewall-cmd --state
        
        echo
        echo "### Active zones"
        sudo firewall-cmd --get-active-zones
        
        echo
        echo "### Firewall configuration"
        sudo firewall-cmd --list-all
        
    else
        echo "[WARNING] firewalld is not installed."
    fi
} | tee "${BASELINE_DIR}/firewall.txt"

# ------------------------------------------
# SELinux
# ------------------------------------------

section "SELINUX"

{
    echo "### Enforcement mode"
    getenforce
    
    echo
    echo "### SELinux status"
    if command -v sestatus >/dev/null 2>&1; then
        sudo sestatus
    else
        echo "[WARNING] sestatus command not available."
    fi
    
    echo
    echo "### SELinux booleans"
    if command -v semanage >/dev/null 2>&1; then
        sudo semanage boolean -l
    else
        echo "[WARNING] semanage command not available."
    fi
    
    echo
    echo "### AVC denials - current boot"
    sudo ausearch -m AVC -ts boot 2>/dev/null || true
} | tee "${BASELINE_DIR}/selinux.txt"

# ------------------------------------------
# Logging
# ------------------------------------------

section "LOGGING"

{
    echo "### journald service"
    systemctl status systemd-journald --no-pager
    
    echo
    echo "### Journal disk usage"
    journalctl --disk-usage
    
    echo
    echo "### Current boot warnings/errors"
    journalctl -p warning -b --no-pager
} | tee "${BASELINE_DIR}/logging.txt"

# ------------------------------------------
# Resources
# ------------------------------------------

section "RESOURCES"

{
    echo "### Uptime/load"
    uptime
    
    echo
    echo "### Memory usage"
    free -h
    
    echo
    echo "VM statistics"
    if command -v vmstat >/dev/null 2>&1; then
        vmstat 1 5
    else
        echo "[WARNING] vmstat not available."
    fi
    
    echo
    echo "### CPU Information"
    nproc
} | tee "${BASELINE_DIR}/resources.txt"

# ------------------------------------------
# Security summary
# ------------------------------------------

section "SECURITY SUMMARY"

{
    echo "SELinux:"
    getenforce
    
    echo
    echo "firewalld:"
    if command -v firewall-cmd >/dev/null 2>&1; then
        sudo firewall-cmd --state
    else
        echo "not installed"
    fi
    
    echo
    echo "sshd:"
    systemctl is-active sshd || true
    
    echo
    echo "Failed systemd units:"
    systemctl --failed --no-legend --no-pager | wc -l
} | tee "${BASELINE_DIR}/security-summary.txt"

# ---------------------------------------------
# Manifest
# ---------------------------------------------

section "BASELINE MANIFEST"

{
    echo "CentOS Baseline"
    echo "==============="
    echo
    echo "Timestamp:    ${TIMESTAMP}"
    echo "Hostname:     $(hostname)"
    echo "Kernel:       $(uname -r)"
    echo "OS:"
    cat /etc/redhat-release
    
    echo
    echo "Files:"
    find "$BASELINE_DIR" -maxdepth 1 -type f -printf '%f\n' | sort
} | tee "${BASELINE_DIR}manifest.txt"

# -----------------------------------
# Finish
# -----------------------------------

section "COMPLETE"

echo "Baseline collection completed."
echo "Output directory:"
echo "${BASELINE_DIR}"

echo
echo "Files collected:"
find "$BASELINE_DIR" -maxdepth 1 -type f -printf ' %f\n' | sort

echo "This Script(Baseline collector) was made with LOVE by 0xTT-byte(Omar Fattouh)"
echo "omarfattouh.work@gmail.com"

exit 0