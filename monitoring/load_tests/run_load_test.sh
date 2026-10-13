#!/bin/bash
set -e

echo "=========================================================="
echo "    SkillMatch Capacity & Load Test (Powered by k6)       "
echo "=========================================================="
echo "Simulating user traffic: 20 -> 50 -> 100 -> 150 users"
echo "Target: skillmatch-backend:8000 & skillmatch-ai:8001"
echo "Live Telemetry: http://localhost:3000 (Grafana Dashboard)"
echo "----------------------------------------------------------"

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

docker run --rm -i \
  -v "${SCRIPT_DIR}:/scripts" \
  --network skillmatch-net \
  grafana/k6 run /scripts/capacity_test.js
