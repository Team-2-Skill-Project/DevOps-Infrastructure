import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  stages: [
    { duration: '30s', target: 20 },  // 1. Warm-up to 20 concurrent users
    { duration: '1m',  target: 50 },  // 2. Normal load: 50 concurrent users
    { duration: '1m',  target: 100 }, // 3. Heavy load: 100 concurrent users
    { duration: '30s', target: 150 }, // 4. Peak stress: 150 concurrent users
    { duration: '30s', target: 0 },   // 5. Cool-down ramp to 0
  ],
  thresholds: {
    http_req_failed: ['rate<0.05'],    // Less than 5% errors allowed
    http_req_duration: ['p(95)<500'],  // 95% of requests must complete under 500ms
  },
};

export default function () {
  // 1. Backend Health & DB/Redis Connectivity Probe
  const backendRes = http.get('http://skillmatch-backend:8000/api/health');
  check(backendRes, {
    'backend status is 200': (r) => r.status === 200,
  });

  // 2. AI FastAPI Engine Health Probe
  const aiRes = http.get('http://skillmatch-ai:8001/health');
  check(aiRes, {
    'ai status is 200': (r) => r.status === 200,
  });

  sleep(0.5); // 500ms think time between requests
}
