#!/usr/bin/env python3

"""
Centos - RHEL Server Health Check

Purpose:
    Perform a read-only operational health check.

Exit codes:
    0 = Healthy
    1 = Warnings
    2 = Critical failure / execution error

Environment variables:
    DISK_WARN=80
    DISK_CRIT=90
    MEMORY_WARN_MB=512
    MEMORY_CRIT_MB=256
    DNS_TEST_HOST=github.com
    HTTPS_TEST_HOST=github.com
    HTTPS_TEST_PORT=443
"""

from __future__ import annotations

import os
import shutil
import socket
import subprocess
import sys
from dataclasses import dataclass
from datetime import datetime, timezone
from pathlib import Path
from contextlib import redirect_stdout
import io 

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------

DISK_WARN = int(os.getenv("DISK_WARN", "80"))
DISK_CRIT = int(os.getenv("DISK_CRIT", "90"))

MEMORY_WARN_MB = int(os.getenv("MEMORY_WARN_MB", "512"))
MEMORY_CRIT_MB = int(os.getenv("MEMORY_CRIT_MB", "256"))

DNS_TEST_HOST = os.getenv("DNS_TEST_HOST", "github.com")
HTTPS_TEST_HOST = os.getenv("HTTPS_TEST_HOST", "github.com")
HTTPS_TEST_PORT = int(os.getenv("HTTPS_TEST_PORT", "443"))

COMMAND_TIMEOUT = 10


# ---------------------------------------------------------------------------
# Result model
# ---------------------------------------------------------------------------

@dataclass
class Result:
    status: str
    message: str


class HealthCheck:
    def __init__(self) -> None:
        self.results: list[Result] = []

    def pass_(self, message: str) -> None:
        self.results.append(Result("PASS", message))
        print(f"[PASS] {message}")

    def warn(self, message: str) -> None:
        self.results.append(Result("WARN", message))
        print(f"[WARN] {message}")

    def fail(self, message: str) -> None:
        self.results.append(Result("FAIL", message))
        print(f"[FAIL] {message}")

    def info(self, message: str) -> None:
        print(f"[INFO] {message}")

    def section(self, name: str) -> None:
        print(f"\n### {name}")

    # -----------------------------------------------------------------------
    # Command execution
    # -----------------------------------------------------------------------

    @staticmethod
    def command_exists(command: str) -> bool:
        return shutil.which(command) is not None

    @staticmethod
    def run_command(
        command: list[str],
        timeout: int = COMMAND_TIMEOUT,
    ) -> subprocess.CompletedProcess[str] | None:

        try:
            return subprocess.run(
                command,
                capture_output=True,
                text=True,
                timeout=timeout,
                check=False,
            )
        except (subprocess.TimeoutExpired, OSError):
            return None

    # -----------------------------------------------------------------------
    # CPU / Load
    # -----------------------------------------------------------------------

    def check_load(self) -> None:
        self.section("SYSTEM")

        try:
            load_1, _, _ = os.getloadavg()
            cpu_count = os.cpu_count() or 1
            load_per_cpu = load_1 / cpu_count

            self.info(f"Load average (1m): {load_1:.2f}")
            self.info(f"CPU count         : {cpu_count}")
            self.info(f"Load per CPU      : {load_per_cpu:.2f}")

            if load_per_cpu < 1.0:
                self.pass_("CPU load is within normal range")
            elif load_per_cpu < 2.0:
                self.warn("CPU load is elevated")
            else:
                self.fail("CPU load is critically elevated")

        except OSError as exc:
            self.fail(f"Unable to read system load: {exc}")

    # -----------------------------------------------------------------------
    # Memory
    # -----------------------------------------------------------------------

    def check_memory(self) -> None:
        self.section("MEMORY")

        result = self.run_command(["free", "-m"])

        if result is None or result.returncode != 0:
            self.fail("Unable to read memory information")
            return

        available_mb = None

        for line in result.stdout.splitlines():
            if line.startswith("Mem:"):
                fields = line.split()

                # free output:
                # Mem: total used free shared buff/cache available
                if len(fields) >= 7:
                    available_mb = int(fields[6])
                break

        if available_mb is None:
            self.fail("Unable to determine available memory")
            return

        self.info(f"Available memory: {available_mb} MB")

        if available_mb <= MEMORY_CRIT_MB:
            self.fail(
                f"Critically low available memory: {available_mb} MB"
            )
        elif available_mb <= MEMORY_WARN_MB:
            self.warn(
                f"Low available memory: {available_mb} MB"
            )
        else:
            self.pass_("Available memory is healthy")

    # -----------------------------------------------------------------------
    # Disk
    # -----------------------------------------------------------------------

    def check_disk(self) -> None:
        self.section("STORAGE")

        try:
            usage = shutil.disk_usage("/")
            used_percent = (usage.used / usage.total) * 100

            self.info(f"Root filesystem usage: {used_percent:.1f}%")

            if used_percent >= DISK_CRIT:
                self.fail(
                    f"Root filesystem critically full: {used_percent:.1f}%"
                )
            elif used_percent >= DISK_WARN:
                self.warn(
                    f"Root filesystem usage is high: {used_percent:.1f}%"
                )
            else:
                self.pass_("Root filesystem usage is healthy")

        except OSError as exc:
            self.fail(f"Unable to inspect root filesystem: {exc}")

    # -----------------------------------------------------------------------
    # systemd
    # -----------------------------------------------------------------------

    def check_systemd(self) -> None:
        self.section("SYSTEMD")

        if not self.command_exists("systemctl"):
            self.fail("systemctl is unavailable")
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

        if result is None or result.returncode != 0:
            self.warn("Unable to query failed systemd units")
            return

        failed_units = [
            line for line in result.stdout.splitlines()
            if line.strip()
        ]

        count = len(failed_units)

        self.info(f"Failed systemd units: {count}")

        if count == 0:
            self.pass_("No failed systemd units")
        else:
            self.fail(f"{count} failed systemd unit(s)")

    # -----------------------------------------------------------------------
    # SSH
    # -----------------------------------------------------------------------

    def check_sshd(self) -> None:
        self.section("SSH")

        if not self.command_exists("systemctl"):
            self.warn("systemctl unavailable")
            return

        enabled = self.run_command(
            ["systemctl", "is-enabled", "sshd"]
        )

        if enabled and enabled.returncode == 0:
            self.info("sshd is enabled")
        else:
            self.warn("sshd is not enabled")

        active = self.run_command(
            ["systemctl", "is-active", "sshd"]
        )

        if active and active.returncode == 0:
            self.pass_("sshd is running")
        else:
            self.fail("sshd is not running")

    # -----------------------------------------------------------------------
    # Firewall
    # -----------------------------------------------------------------------

    def check_firewall(self) -> None:
        self.section("FIREWALL")

        if not self.command_exists("firewall-cmd"):
            self.warn("firewall-cmd is not installed")
            return

        result = self.run_command(["firewall-cmd", "--state"])

        if result and result.returncode == 0:
            if result.stdout.strip() == "running":
                self.pass_("firewalld is running")
            else:
                self.fail("firewalld is not running")
        else:
            self.fail("Unable to determine firewalld state")

    # -----------------------------------------------------------------------
    # SELinux
    # -----------------------------------------------------------------------

    def check_selinux(self) -> None:
        self.section("SELINUX")

        if not self.command_exists("getenforce"):
            self.warn("SELinux utilities are unavailable")
            return

        result = self.run_command(["getenforce"])

        if result is None or result.returncode != 0:
            self.warn("Unable to determine SELinux state")
            return

        state = result.stdout.strip()

        self.info(f"SELinux mode: {state}")

        if state == "Enforcing":
            self.pass_("SELinux is enforcing")
        elif state == "Permissive":
            self.warn("SELinux is permissive")
        elif state == "Disabled":
            self.fail("SELinux is disabled")
        else:
            self.warn(f"Unknown SELinux state: {state}")

    # -----------------------------------------------------------------------
    # Network
    # -----------------------------------------------------------------------

    def check_network(self) -> None:
        self.section("NETWORK")

        if not self.command_exists("ip"):
            self.fail("ip command is unavailable")
            return

        result = self.run_command(["ip", "route", "show", "default"])

        if result and result.returncode == 0:
            if any(
                line.startswith("default")
                for line in result.stdout.splitlines()
            ):
                self.pass_("Default network route exists")
            else:
                self.fail("No default network route found")
        else:
            self.fail("Unable to inspect routing table")

    # -----------------------------------------------------------------------
    # DNS
    # -----------------------------------------------------------------------

    def check_dns(self) -> None:
        self.section("DNS")

        try:
            addresses = socket.getaddrinfo(
                DNS_TEST_HOST,
                None,
                type=socket.SOCK_STREAM,
            )

            unique_addresses = {
                address[4][0]
                for address in addresses
            }

            if unique_addresses:
                self.pass_(
                    f"DNS resolution works: {DNS_TEST_HOST}"
                )
                self.info(
                    f"Resolved addresses: {', '.join(sorted(unique_addresses))}"
                )
            else:
                self.fail(
                    f"DNS returned no addresses: {DNS_TEST_HOST}"
                )

        except socket.gaierror:
            self.fail(
                f"DNS resolution failed: {DNS_TEST_HOST}"
            )

    # -----------------------------------------------------------------------
    # HTTPS connectivity
    # -----------------------------------------------------------------------

    def check_https(self) -> None:
        self.section("CONNECTIVITY")

        try:
            with socket.create_connection(
                (HTTPS_TEST_HOST, HTTPS_TEST_PORT),
                timeout=5,
            ):
                self.pass_(
                    "Outbound TCP connectivity works: "
                    f"{HTTPS_TEST_HOST}:{HTTPS_TEST_PORT}"
                )

        except OSError:
            self.warn(
                "Unable to establish outbound TCP connectivity: "
                f"{HTTPS_TEST_HOST}:{HTTPS_TEST_PORT}"
            )

    # -----------------------------------------------------------------------
    # Time synchronization
    # -----------------------------------------------------------------------

    def check_time(self) -> None:
        self.section("TIME")

        if not self.command_exists("timedatectl"):
            self.warn("timedatectl is unavailable")
            return

        result = self.run_command(
            [
                "timedatectl",
                "show",
                "--property=NTPSynchronized",
                "--value",
            ]
        )

        if result is None or result.returncode != 0:
            self.warn("Unable to determine NTP synchronization")
            return

        synchronized = result.stdout.strip()

        if synchronized == "yes":
            self.pass_("System clock is NTP synchronized")
        else:
            self.warn(
                "System clock is not confirmed NTP synchronized"
            )

    # -----------------------------------------------------------------------
    # Summary
    # -----------------------------------------------------------------------

    def summary(self) -> int:
        self.section("SUMMARY")

        counts = {
            "PASS": sum(r.status == "PASS" for r in self.results),
            "WARN": sum(r.status == "WARN" for r in self.results),
            "FAIL": sum(r.status == "FAIL" for r in self.results),
        }

        print(f"PASS : {counts['PASS']}")
        print(f"WARN : {counts['WARN']}")
        print(f"FAIL : {counts['FAIL']}")
        print()

        if counts["FAIL"] > 0:
            print("STATUS: CRITICAL")
            return 2

        if counts["WARN"] > 0:
            print("STATUS: WARNING")
            return 1

        print("STATUS: HEALTHY")
        return 0

    # -----------------------------------------------------------------------
    # Run
    # -----------------------------------------------------------------------

    def run(self) -> int:
        self.check_load()
        self.check_memory()
        self.check_disk()
        self.check_systemd()
        self.check_sshd()
        self.check_firewall()
        self.check_selinux()
        self.check_network()
        self.check_dns()
        self.check_https()
        self.check_time()

        return self.summary()


def main() -> int:
    print("========================================")
    print(" RedOps RHEL Server Health Check")
    print("========================================")
    print(f"Host      : {os.uname().nodename}")
    print(
        "Timestamp : "
        f"{datetime.now(timezone.utc).isoformat()}"
    )
    print("Mode      : READ ONLY")

    checker = HealthCheck()

    try:
        return checker.run()
    except KeyboardInterrupt:
        print("\n[WARN] Interrupted by user", file=sys.stderr)
        return 2
    except Exception as exc:
        print(
            f"[ERROR] Unexpected error: {exc}",
            file=sys.stderr,
        )
        return 2

def run_with_report() -> int:
    """
    Run the health check, display the output 
    and save it to a TXT report 
    """
    
    report_dir = (
        Path(__file__).resolve().parent.parent\
            / "docs"
            / "operations"
            / "health"
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
        + "Server (RHEL) Health Check Report\n"
        + "=" * 60 
        + "\n"
        + f"Generated : {datetime.now().isoformat(timespec='seconds')}\n"
        + f"Exit Code : {exit_code}\n"
        + "\n"
        + output
    )
    
    report_file.write_text(
        report_content,
        encoding="utf-8"
    )   
    
    print(f"\n[INFO] Report saved to: {report_file}")
    
    return exit_code


if __name__ == "__main__":
    sys.exit(run_with_report())