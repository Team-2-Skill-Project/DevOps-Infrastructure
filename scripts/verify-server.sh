#!/usr/bin/env bash

set -uo pipefail

# ============================================================
# CentOS Server Verification
# ============================================================

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

HEALTH_CHECK="$PROJECT_ROOT/scripts/health-check.py"
SECURITY_AUDIT="$PROJECT_ROOT/scripts/security-audit.py"

REPORT_DIR="$PROJECT_ROOT/docs/operations/verification"
TIMESTAMP="$(date '+%Y-%m-%d_%H-%M-%S')"
REPORT_FILE="$REPORT_DIR/$TIMESTAMP.txt"

mkdir -p "$REPORT_DIR"

# ------------------------------------------------------------
# Validation
# ------------------------------------------------------------

if [[ ! -f "$HEALTH_CHECK" ]]; then
    echo "ERROR: health-check.py not found."
    exit 2
fi

if [[ ! -f "$SECURITY_AUDIT" ]]; then
    echo "ERROR: security-audit.py not found."
    exit 2
fi

# ------------------------------------------------------------
# Temporary workspace
# ------------------------------------------------------------

TMP_DIR="$(mktemp -d)"

trap 'rm -rf "$TMP_DIR"' EXIT

HEALTH_OUTPUT="$TMP_DIR/health.txt"
SECURITY_OUTPUT="$TMP_DIR/security.txt"

# ------------------------------------------------------------
# Run health check
# ------------------------------------------------------------

python3 "$HEALTH_CHECK" > "$HEALTH_OUTPUT" 2>&1
health_status=$?

# ------------------------------------------------------------
# Run security audit
# ------------------------------------------------------------

python3 "$SECURITY_AUDIT" > "$SECURITY_OUTPUT" 2>&1
security_status=$?

# ------------------------------------------------------------
# Security invariant checker
# ------------------------------------------------------------

check_invariant() {
    local name="$1"
    local expected="$2"
    local actual="$3"

    if [[ "$actual" == "$expected" ]]; then
        echo "[PASS] $name"
        return 0
    else
        echo "[FAIL] $name"
        echo "       Expected: $expected"
        echo "       Actual  : ${actual:-<empty>}"
        return 1
    fi
}

# ------------------------------------------------------------
# Collect invariant values
# ------------------------------------------------------------

permit_root_login="$(
    sudo sshd -T 2>/dev/null |
    awk '$1 == "permitrootlogin" {print $2; exit}'
)"

password_auth="$(
    sudo sshd -T 2>/dev/null |
    awk '$1 == "passwordauthentication" {print $2; exit}'
)"

pubkey_auth="$(
    sudo sshd -T 2>/dev/null |
    awk '$1 == "pubkeyauthentication" {print $2; exit}'
)"

selinux_state="$(getenforce 2>/dev/null || echo "unknown")"

firewalld_state="$(
    systemctl is-active firewalld 2>/dev/null ||
    echo "unknown"
)"

ssh_allowed="$(
    sudo firewall-cmd --query-service=ssh 2>/dev/null ||
    echo "unknown"
)"

auditd_state="$(
    systemctl is-active auditd 2>/dev/null ||
    echo "unknown"
)"

failed_services="$(
    systemctl --failed --no-legend --plain 2>/dev/null |
    grep -c . ||
    true
)"

# ------------------------------------------------------------
# Evaluate invariants
# ------------------------------------------------------------

INVARIANT_OUTPUT="$TMP_DIR/invariants.txt"

invariant_failures=0

{
    echo "===== SECURITY INVARIANTS ====="
    echo

    check_invariant \
        "SSH PermitRootLogin" \
        "no" \
        "$permit_root_login" ||
        ((invariant_failures++))

    check_invariant \
        "SSH PasswordAuthentication" \
        "no" \
        "$password_auth" ||
        ((invariant_failures++))

    check_invariant \
        "SSH PubkeyAuthentication" \
        "yes" \
        "$pubkey_auth" ||
        ((invariant_failures++))

    check_invariant \
        "SELinux" \
        "Enforcing" \
        "$selinux_state" ||
        ((invariant_failures++))

    check_invariant \
        "firewalld" \
        "active" \
        "$firewalld_state" ||
        ((invariant_failures++))

    check_invariant \
        "SSH firewall service" \
        "yes" \
        "$ssh_allowed" ||
        ((invariant_failures++))

    check_invariant \
        "auditd" \
        "active" \
        "$auditd_state" ||
        ((invariant_failures++))

    check_invariant \
        "Failed systemd services" \
        "0" \
        "$failed_services" ||
        ((invariant_failures++))

    echo
    echo "Invariant Failures: $invariant_failures"

} > "$INVARIANT_OUTPUT"

# ------------------------------------------------------------
# Determine overall status
#
# 0 = PASS
# 1 = WARNINGS / FINDINGS
# 2 = EXECUTION FAILURE
# ------------------------------------------------------------

if (( health_status >= 2 || security_status >= 2 )); then
    overall_status=2

elif (( health_status == 1 ||
        security_status == 1 ||
        invariant_failures > 0 )); then
    overall_status=1

else
    overall_status=0
fi

# ------------------------------------------------------------
# Generate consolidated report
# ------------------------------------------------------------

{
    echo "CentOS Server Verification Report"
    echo "=================================="
    echo
    echo "Timestamp : $(date --iso-8601=seconds)"
    echo "Hostname  : $(hostname)"
    echo "Kernel    : $(uname -r)"
    echo
    echo "Exit Code Contract:"
    echo "  0 = PASS"
    echo "  1 = WARNINGS / FINDINGS"
    echo "  2 = EXECUTION FAILURE"
    echo

    # --------------------------------------------------------
    # Health Check
    # --------------------------------------------------------

    echo "===== HEALTH CHECK ====="
    cat "$HEALTH_OUTPUT"
    echo
    echo "Health Check Exit Code: $health_status"
    echo

    # --------------------------------------------------------
    # Security Audit
    # --------------------------------------------------------

    echo "===== SECURITY AUDIT ====="
    cat "$SECURITY_OUTPUT"
    echo
    echo "Security Audit Exit Code: $security_status"
    echo

    # --------------------------------------------------------
    # Security Invariants
    # --------------------------------------------------------

    cat "$INVARIANT_OUTPUT"
    echo

    # --------------------------------------------------------
    # Overall Result
    # --------------------------------------------------------

    echo "===== OVERALL RESULT ====="

    case "$overall_status" in

        0)
            echo "PASS: Server verification successful."
            ;;

        1)
            echo "WARNING: Server verification completed with findings."
            ;;

        2)
            echo "CRITICAL: Verification execution failure."
            ;;

    esac

    echo
    echo "Overall Exit Code: $overall_status"
    echo
    echo "This Script/Tool was made by 0xTT-byte"

} | tee "$REPORT_FILE"

# ------------------------------------------------------------
# Final result
# ------------------------------------------------------------

echo "This Script/Tool was made by 0xTT-byte (omarfattouh.work@gmail.com)"
exit "$overall_status"