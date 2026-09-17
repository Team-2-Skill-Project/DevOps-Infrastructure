#!/usr/bin/env bash

set -u

# Centos - Day 2 Hardening Baseline
# Collects the current RHEL security/system state.
# This script is READ-ONLY.

TIMESTAMP="$(date '+%Y%m%d_%H%M%S')"
OUTPUT_DIR="docs/operations/security/day-2"
OUTPUT_FILE="${OUTPUT_DIR}/baseline_${TIMESTAMP}.txt"

mkdir -p "$OUTPUT_DIR"

exec > >(tee "$OUTPUT_FILE") 2>&1

echo "=========================================="
echo " CentOS - Hardening Baseline"
echo "=========================================="
echo "Timestamp: $(date)"
echo

echo "=========================================="
echo " SYSTEM"
echo "=========================================="
hostnamectl
echo

echo "Kernel:"
uname -r
echo

echo "OS:"
cat /etc/os-release
echo


echo "=========================================="
echo " CURRENT USER"
echo "=========================================="
echo "User: $(whoami)"
echo

id
echo


echo "=========================================="
echo " USERS"
echo "=========================================="
getent passwd
echo


echo "=========================================="
echo " GROUPS"
echo "=========================================="
getent group
echo


echo "=========================================="
echo " SUDO"
echo "=========================================="
echo "Sudo validation:"
sudo visudo -c
echo

echo "Current user's sudo privileges:"
sudo -l
echo

echo "Sudo configuration:"
sudo ls -la /etc/sudoers.d/
echo


echo "=========================================="
echo " SSH EFFECTIVE CONFIGURATION"
echo "=========================================="
sudo sshd -T | grep -E \
'permitrootlogin|passwordauthentication|pubkeyauthentication|maxauthtries|x11forwarding|allowusers|allowgroups'
echo

echo "SSH service:"
sudo systemctl status sshd --no-pager
echo


echo "=========================================="
echo " FIREWALL"
echo "=========================================="
echo "firewalld status:"
sudo systemctl status firewalld --no-pager
echo

echo "firewalld state:"
sudo firewall-cmd --state
echo

echo "Active zones:"
sudo firewall-cmd --get-active-zones
echo

echo "Firewall configuration:"
sudo firewall-cmd --list-all
echo

echo "Allowed services:"
sudo firewall-cmd --list-services
echo

echo "Allowed ports:"
sudo firewall-cmd --list-ports
echo


echo "=========================================="
echo " SELINUX"
echo "=========================================="
echo "SELinux mode:"
getenforce
echo

echo "SELinux status:"
sestatus
echo

echo "Recent AVC denials:"
sudo ausearch -m AVC -ts recent 2>/dev/null || true
echo


echo "=========================================="
echo " SYSTEMD SERVICES"
echo "=========================================="
echo "Failed services:"
systemctl --failed --no-pager
echo

echo "Enabled services:"
systemctl list-unit-files --type=service --state=enabled --no-pager
echo

echo "Running services:"
systemctl --type=service --state=running --no-pager
echo


echo "=========================================="
echo " NETWORK LISTENING SOCKETS"
echo "=========================================="
sudo ss -lntup
echo


echo "=========================================="
echo " PACKAGE / UPDATE STATE"
echo "=========================================="
echo "DNF history:"
sudo dnf history
echo

echo "Update information summary:"
sudo dnf updateinfo summary || true
echo

echo "Security updates:"
sudo dnf updateinfo list --security || true
echo


echo "=========================================="
echo " BASELINE COMPLETE"
echo "=========================================="
echo "Evidence saved to:"
echo "$OUTPUT_FILE"