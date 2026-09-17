#!/usr/bin/env python3

"""_summary_
    Read only security posture assessment for the Centos server
    
    Exit codes:
        0 - No findings 
        1 - findings detected 
        2 - Auditor execution failure
    
    The auditor never modifies the system 
    """
    
from __future__ import annotations

import os 
import re
import shutil
import socket 
import subprocess
import sys
from dataclasses import dataclass
from enum import IntEnum 
from pathlib import Path 
from typing import Sequence 
from contextlib import redirect_stdout
from datetime import datetime
import io

# Configuration 

COMMAND_TIMEOUT = 10 

AUDIT_PATHS = (
    "/etc",
    "/usr",
    "/var",
)

MAX_FINDINGS_DISPLAY = 20 


# Severity 

class Severity(IntEnum):
    INFO = 0
    LOW = 1 
    MEDIUM = 2
    HIGH = 3
    CRITICAL = 4
    
SEVERITY_NAMES = {
   Severity.INFO: "INFO",
        Severity.LOW: "LOW",
        Severity.MEDIUM: "MEDIUM",
        Severity.HIGH: "HIGH",
        Severity.CRITICAL: "CRITICAL",
    }
    
# Finding 

@dataclass(frozen=True)
class Finding:
    finding_id: str
    severity: Severity
    title: str
    evidence: str 
    remediation: str
    
# Auditor 

class SecurityAuditor:
    
    def __init__(self) -> None:
        self.findings: list[Finding] = []
        self.errors: list[str] = []
        
    # Output helpers:
    
    @staticmethod
    def section(title: str) -> None:
        print()
        print(f"### {title}")
        
    @staticmethod
    def info(message: str) -> None:
        print(f"[INFO] {message}")
        
    @staticmethod
    def pass_(message: str) -> None:
        print(f"[PASS] {message}")
        
    @staticmethod
    def warn(message: str) -> None:
        print(f"[WARN] {message}")
        
    # Finding management
    
    def finding(
        self,
        finding_id: str,
        severity: Severity,
        title: str,
        evidence: str,
        remediation: str,
    ) -> None:
        finding = Finding(
            finding_id = finding_id,
            severity=severity,
            title=title,
            evidence=evidence,
            remediation=remediation,
        )

        self.findings.append(finding)
        
        severity_name = SEVERITY_NAMES[severity]
        
        print()
        print(
            f"[{severity_name}]"
            f"{finding_id} - {title}"
        )
        print(f"    Evidence    : {evidence}")
        print(f"    Remediation : {remediation}")
        
    # Command execution 
    @staticmethod
    def command_exists(command: str) -> bool:
        return shutil.which(command) is not None
    
    @staticmethod
    def run_command(
        command: Sequence[str],
        timeout: int= COMMAND_TIMEOUT,
    ) -> subprocess.CompletedProcess[str] | None:
        
        try:
            return subprocess.run(
                command,
                capture_output=True,
                text=True,
                timeout=timeout,
                check=False,
            )
        except (
            subprocess.TimeoutExpired,
            OSError,
        ):
            return None
        
    # SELinux
    
    def audit_selinux(self) -> None:
        self.section("SELINUX")
        
        if not self.command_exists("getenforce"):
            self.finding(
                "SEC-SELINUX-001",
                Severity.HIGH,
                "SELinux tooling unavailable",
                "getenforce command was not found",
                "Install the SELinux management utils",
            )
            return 
        
        result = self.run_command(["getenforce"])
        
        if result is None or result.returncode !=0:
            self.finding(
                "SEC-SELINUX-002",
                Severity.HIGH,
                "Unable to determine SELinux state",
                "getenforce failed",
                "Investigate SELinux config and tooling",
            )
            return
        
        state = result.stdout.strip()
        
        self.info(f"Mode: {state}")
        
        if state == "Enforcing":
            self.pass_("SELinux is enforcing")
            
        elif state == "Permissive":
            self.finding(
                "SEC-SELINUX-003",
                Severity.HIGH,
                "SELinux is permissive",
                "SELinux enforcement is disabled",
                "Set SELinux to Enforcing after validating policy compatibility.",
            )
            
        elif state == "Disabled":
            self.finding(
                "SEC-SELINUX-004",
                Severity.CRITICAL,
                "SELinux is disabled",
                "SELinux enforcement is completely disabled",
                "Enable SELinux and reboot after validating the system policy.",
            )
            
        else: 
            self.finding(
                "SEC-SELINUX-005",
                Severity.MEDIUM,
                "Unknown SELinux state",
                state,
                "Investigate the SELinux config",
            )
    
    # SSH
    
    def audit_ssh(self) -> None:
        
        self.section("SSH")
        
        if not self.command_exists("sshd"):
            self.finding(
                "SEC-SSH-001",
                Severity.MEDIUM,
                "OpenSSH server not installed",
                "sshd command was not found",
                "Install OpenSSH server if this host is intended to provide SSH access.",
            )
            return

        result = self.run_command(["sshd", "-T"])

        if result is None or result.returncode != 0:
            self.finding(
                "SEC-SSH-002",
                Severity.HIGH,
                "Unable to inspect effective SSH configuration",
                "sshd -T failed",
                "Validate the SSH configuration with sshd -t.",
            )
            return

        config = {}

        for line in result.stdout.splitlines():
            parts = line.split(maxsplit=1)

            if len(parts) == 2:
                config[parts[0].lower()] = parts[1].strip()

        permit_root = config.get("permitrootlogin", "unknown")
        password_auth = config.get(
            "passwordauthentication",
            "unknown",
        )
        empty_passwords = config.get(
            "permitemptypasswords",
            "unknown",
        )
        pubkey_auth = config.get(
            "pubkeyauthentication",
            "unknown",
        )

        self.info(f"PermitRootLogin       : {permit_root}")
        self.info(f"PasswordAuthentication: {password_auth}")
        self.info(f"PermitEmptyPasswords  : {empty_passwords}")
        self.info(f"PubkeyAuthentication  : {pubkey_auth}")

        # Root login ---------------------------------------------------------

        if permit_root == "no":
            self.pass_("Direct root SSH login is disabled")

        else:
            self.finding(
                "SEC-SSH-003",
                Severity.HIGH,
                "Direct root SSH login is enabled",
                f"PermitRootLogin {permit_root}",
                "Disable direct root SSH login and use privileged escalation through an individual account.",
            )

        # Empty passwords ----------------------------------------------------

        if empty_passwords == "no":
            self.pass_("Empty SSH passwords are disabled")

        else:
            self.finding(
                "SEC-SSH-004",
                Severity.CRITICAL,
                "Empty SSH passwords are permitted",
                f"PermitEmptyPasswords {empty_passwords}",
                "Set PermitEmptyPasswords no.",
            )

        # Password authentication --------------------------------------------

        if password_auth == "no":
            self.pass_("SSH password authentication is disabled")

        else:
            self.finding(
                "SEC-SSH-005",
                Severity.MEDIUM,
                "SSH password authentication is enabled",
                f"PasswordAuthentication {password_auth}",
                "Prefer SSH public-key authentication where operationally appropriate.",
            )

        # Public key authentication ------------------------------------------

        if pubkey_auth == "yes":
            self.pass_("SSH public-key authentication is enabled")

        else:
            self.finding(
                "SEC-SSH-006",
                Severity.MEDIUM,
                "SSH public-key authentication is disabled",
                f"PubkeyAuthentication {pubkey_auth}",
                "Enable public-key authentication for administrative access.",
            )
            
    # Firewall 
    
    def audit_firewall(self) -> None:
        
        self.section("FIREWALL")
        
        if not self.command_exists("firewall-cmd"):
            self.finding(
                "SEC-FW-001",
                Severity.HIGH,
                "firewalld tooling unavailable",
                "firewall-cmd was not found",
                "Install and configure firewalld if it is the selected host firewall.",
            )
            return

        result = self.run_command(["firewall-cmd", "--state"])

        if result is None or result.returncode != 0:
            self.finding(
                "SEC-FW-002",
                Severity.HIGH,
                "Unable to determine firewall state",
                "firewall-cmd --state failed",
                "Investigate firewalld configuration.",
            )
            return

        state = result.stdout.strip()

        if state != "running":
            self.finding(
                "SEC-FW-003",
                Severity.HIGH,
                "Host firewall is not running",
                f"firewalld state: {state}",
                "Enable and configure the host firewall.",
            )
            return

        self.pass_("firewalld is running")

        # Active zones -------------------------------------------------------

        zones = self.run_command(
            ["firewall-cmd", "--get-active-zones"]
        )

        if zones and zones.returncode == 0:
            self.info("Active zones:")
            for line in zones.stdout.splitlines():
                print(f"        {line}")

        # Exposed services ---------------------------------------------------

        services = self.run_command(
            ["firewall-cmd", "--get-services"]
        )

        if services and services.returncode == 0:
            self.info("Available firewall services detected")

    # =========================================================================
    # Listening sockets
    # =========================================================================

    def audit_listening_sockets(self) -> None:

        self.section("NETWORK EXPOSURE")

        if not self.command_exists("ss"):
            self.finding(
                "SEC-NET-001",
                Severity.MEDIUM,
                "Socket inspection unavailable",
                "ss command was not found",
                "Install iproute utilities.",
            )
            return

        result = self.run_command(
            ["ss", "-H", "-lntup"]
        )

        if result is None or result.returncode != 0:
            self.finding(
                "SEC-NET-002",
                Severity.MEDIUM,
                "Unable to inspect listening sockets",
                "ss failed",
                "Investigate the network inspection environment.",
            )
            return

        lines = [
            line.strip()
            for line in result.stdout.splitlines()
            if line.strip()
        ]

        self.info(f"Listening sockets: {len(lines)}")

        for line in lines:
            print(f"        {line}")

    # =========================================================================
    # UID 0 accounts
    # =========================================================================

    def audit_uid_zero(self) -> None:

        self.section("PRIVILEGED ACCOUNTS")

        try:
            users = []

            with Path("/etc/passwd").open(
                encoding="utf-8"
            ) as passwd:

                for line in passwd:
                    fields = line.rstrip("\n").split(":")

                    if len(fields) >= 7 and fields[2] == "0":
                        users.append(fields[0])

        except OSError as exc:
            self.finding(
                "SEC-USER-001",
                Severity.HIGH,
                "Unable to inspect UID 0 accounts",
                str(exc),
                "Verify access to /etc/passwd.",
            )
            return

        self.info(
            f"UID 0 accounts: {', '.join(users)}"
        )

        if users == ["root"]:
            self.pass_("Only root has UID 0")

        else:
            self.finding(
                "SEC-USER-002",
                Severity.HIGH,
                "Additional UID 0 account detected",
                ", ".join(users),
                "Review every UID 0 account and remove unnecessary privileged identities.",
            )

    # =========================================================================
    # Sudo / wheel membership
    # =========================================================================

    def audit_privileged_groups(self) -> None:

        self.section("PRIVILEGED GROUPS")

        group_file = Path("/etc/group")

        try:
            lines = group_file.read_text(
                encoding="utf-8"
            ).splitlines()

        except OSError as exc:
            self.finding(
                "SEC-USER-003",
                Severity.MEDIUM,
                "Unable to inspect privileged groups",
                str(exc),
                "Verify access to /etc/group.",
            )
            return

        privileged_groups = {
            "wheel",
            "sudo",
        }

        for line in lines:

            fields = line.split(":")

            if len(fields) < 4:
                continue

            group_name = fields[0]

            if group_name not in privileged_groups:
                continue

            members = [
                member
                for member in fields[3].split(",")
                if member
            ]

            self.info(
                f"{group_name}: "
                f"{', '.join(members) if members else 'no direct members'}"
            )

    # =========================================================================
    # Password policy
    # =========================================================================

    def audit_password_policy(self) -> None:

        self.section("PASSWORD POLICY")

        login_defs = Path("/etc/login.defs")

        if not login_defs.exists():
            self.finding(
                "SEC-PASS-001",
                Severity.MEDIUM,
                "login.defs unavailable",
                "/etc/login.defs does not exist",
                "Verify the system's account policy configuration.",
            )
            return

        settings: dict[str, str] = {}

        try:
            for line in login_defs.read_text(
                encoding="utf-8"
            ).splitlines():

                line = line.strip()

                if not line or line.startswith("#"):
                    continue

                match = re.match(
                    r"^(\S+)\s+(\S+)",
                    line,
                )

                if match:
                    settings[match.group(1)] = match.group(2)

        except OSError as exc:
            self.finding(
                "SEC-PASS-002",
                Severity.MEDIUM,
                "Unable to read password policy",
                str(exc),
                "Verify /etc/login.defs permissions.",
            )
            return

        for key in (
            "PASS_MAX_DAYS",
            "PASS_MIN_DAYS",
            "PASS_WARN_AGE",
        ):
            value = settings.get(key, "not configured")
            self.info(f"{key}: {value}")

    # =========================================================================
    # World writable files
    # =========================================================================

    def audit_world_writable(self) -> None:

        self.section("FILESYSTEM PERMISSIONS")

        if not self.command_exists("find"):
            self.finding(
                "SEC-FS-001",
                Severity.MEDIUM,
                "Filesystem permission inspection unavailable",
                "find command was not found",
                "Install standard filesystem utilities.",
            )
            return

        command = [
            "find",
            *AUDIT_PATHS,
            "-xdev",
            "-type",
            "f",
            "-perm",
            "-0002",
            "-print",
        ]

        result = self.run_command(
            command,
            timeout=30,
        )

        if result is None:
            self.finding(
                "SEC-FS-002",
                Severity.MEDIUM,
                "World-writable file audit failed",
                "find command did not complete",
                "Investigate filesystem accessibility and command execution.",
            )
            return

        files = [
            line.strip()
            for line in result.stdout.splitlines()
            if line.strip()
        ]

        self.info(
            f"World-writable files: {len(files)}"
        )

        if not files:
            self.pass_("No world-writable files found in audited paths")
            return

        for path in files[:MAX_FINDINGS_DISPLAY]:
            print(f"        {path}")

        self.finding(
            "SEC-FS-003",
            Severity.MEDIUM,
            "World-writable files detected",
            f"{len(files)} file(s) found",
            "Review each file and remove unnecessary world-write permissions.",
        )

    # =========================================================================
    # SUID / SGID inventory
    # =========================================================================

    def audit_special_permissions(self) -> None:

        self.section("SPECIAL PERMISSIONS")

        if not self.command_exists("find"):
            self.warn("find unavailable")
            return

        suid_result = self.run_command(
            [
                "find",
                "/",
                "-xdev",
                "-type",
                "f",
                "-perm",
                "-4000",
                "-print",
            ],
            timeout=30,
        )

        sgid_result = self.run_command(
            [
                "find",
                "/",
                "-xdev",
                "-type",
                "f",
                "-perm",
                "-2000",
                "-print",
            ],
            timeout=30,
        )

        suid = (
            suid_result.stdout.splitlines()
            if suid_result
            else []
        )

        sgid = (
            sgid_result.stdout.splitlines()
            if sgid_result
            else []
        )

        self.info(f"SUID files: {len(suid)}")
        self.info(f"SGID files: {len(sgid)}")

        # Inventory only.
        #
        # Presence of SUID/SGID is not automatically a vulnerability.
        self.pass_("SUID/SGID inventory collected")

    # =========================================================================
    # Auditd
    # =========================================================================

    def audit_auditd(self) -> None:

        self.section("AUDITING")

        if not self.command_exists("systemctl"):
            self.warn("systemctl unavailable")
            return

        result = self.run_command(
            ["systemctl", "is-active", "auditd"]
        )

        if result and result.returncode == 0:
            self.pass_("auditd is running")
        else:
            self.finding(
                "SEC-AUDIT-001",
                Severity.MEDIUM,
                "auditd is not running",
                "systemctl reports auditd inactive",
                "Enable and start auditd according to the server logging policy.",
            )

        # AVC events ---------------------------------------------------------

        if not self.command_exists("ausearch"):
            self.info("ausearch unavailable; AVC analysis skipped")
            return

        avc = self.run_command(
            [
                "ausearch",
                "-m",
                "AVC",
                "-ts",
                "today",
            ],
            timeout=15,
        )

        if avc is None:
            self.warn("Unable to inspect today's AVC events")
            return

        avc_lines = [
            line
            for line in avc.stdout.splitlines()
            if "type=AVC" in line
        ]

        self.info(
            f"SELinux AVC events today: {len(avc_lines)}"
        )

        if not avc_lines:
            self.pass_("No SELinux AVC events detected today")

    # =========================================================================
    # Failed systemd units
    # =========================================================================

    def audit_failed_services(self) -> None:

        self.section("SYSTEM SERVICES")

        if not self.command_exists("systemctl"):
            self.warn("systemctl unavailable")
            return

        result = self.run_command(
            [
                "systemctl",
                "list-units",
                "--state=failed",
                "--no-legend",
                "--no-pager",
            ]
        )

        if result is None:
            self.warn("Unable to inspect systemd")
            return

        failed = [
            line
            for line in result.stdout.splitlines()
            if line.strip()
        ]

        self.info(
            f"Failed systemd units: {len(failed)}"
        )

        if not failed:
            self.pass_("No failed systemd units")
            return

        for unit in failed:
            print(f"        {unit}")

        self.finding(
            "SEC-SVC-001",
            Severity.MEDIUM,
            "Failed systemd units detected",
            f"{len(failed)} failed unit(s)",
            "Investigate and remediate failed services.",
        )

    # =========================================================================
    # Summary
    # =========================================================================

    def summary(self) -> int:

        self.section("SUMMARY")

        counts = {
            severity: sum(
                finding.severity == severity
                for finding in self.findings
            )
            for severity in Severity
        }

        print(f"CRITICAL : {counts[Severity.CRITICAL]}")
        print(f"HIGH     : {counts[Severity.HIGH]}")
        print(f"MEDIUM   : {counts[Severity.MEDIUM]}")
        print(f"LOW      : {counts[Severity.LOW]}")
        print(f"INFO     : {counts[Severity.INFO]}")

        print()

        if self.errors:
            print("Execution errors:")
            for error in self.errors:
                print(f"  - {error}")

            print()
            print("STATUS: AUDIT ERROR")
            return 2

        if counts[Severity.CRITICAL] > 0:
            print("STATUS: CRITICAL FINDINGS")
            return 1

        if counts[Severity.HIGH] > 0:
            print("STATUS: HIGH FINDINGS")
            return 1

        if self.findings:
            print("STATUS: FINDINGS PRESENT")
            return 1

        print("STATUS: NO FINDINGS")
        return 0

    # =========================================================================
    # Run
    # =========================================================================

    def run(self) -> int:

        self.audit_selinux()
        self.audit_ssh()
        self.audit_firewall()
        self.audit_listening_sockets()
        self.audit_uid_zero()
        self.audit_privileged_groups()
        self.audit_password_policy()
        self.audit_world_writable()
        self.audit_special_permissions()
        self.audit_auditd()
        self.audit_failed_services()

        return self.summary()

# Saving into a report txt file 

def run_with_report() -> int:
    """
    Run the security audit, display the output,
    and save it to a txt report 
    """

    report_dir = (
        Path(__file__).resolve().parent.parent 
        / "docs"
        / "operations"
        / "security"
    )
    
    report_dir.mkdir(parents=True, exist_ok=True)
    
    timestamp = datetime.now().strftime("%Y-%m-%d_%H-%M-%S")
    report_file = report_dir / f"{timestamp}.txt"
    
    buffer = io.StringIO()
    
    with redirect_stdout(buffer):
        exit_code = main()
    
    output = buffer.getvalue()
    
    # Keep normal terminal output 
    print(output, end="")
    
    report_content = (
        "=" * 60
        + "\n"
        + "CentOS Security Audit Report\n"
        + "=" * 60
        + "\n"
        + f"Generated : {datetime.now().isoformat(timespec='seconds')}\n"
        + f"Exit Code : {exit_code}\n"
        + "\n"
        + output
    )
    
    report_file.write_text(
        report_content, 
        encoding="utf-8",
    )

    print(f"\n[INFO] Report saved to: {report_file}")
    
    return exit_code

    
# ============================================================================
# Main
# ============================================================================

def main() -> int:

    print("=" * 56)
    print(" CentOS Security Audit")
    print("=" * 56)

    print(f"Host      : {socket.gethostname()}")
    print(f"UID       : {os.getuid()}")
    print("Mode      : READ ONLY")

    auditor = SecurityAuditor()

    try:
        return auditor.run()

    except KeyboardInterrupt:
        print("\n[WARN] Audit interrupted by user.")
        return 2

    except Exception as exc:
        print(
            f"\n[ERROR] Unexpected auditor failure: {exc}",
            file=sys.stderr,
        )
        return 2


if __name__ == "__main__":
    sys.exit(run_with_report())