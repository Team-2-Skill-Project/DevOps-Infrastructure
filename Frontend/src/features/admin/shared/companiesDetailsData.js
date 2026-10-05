/**
 * Static mock dataset + visual config for the Company Details page.
 * Swap with an API/service result when wiring real data.
 */
import { Clock, CheckCircle2, XCircle } from "lucide-react";

/**
 * The three states a company verification can be in. Every status surface on
 * the page (banner, badge, switch) reads from this single map instead of
 * duplicating per-state JSX.
 */
export const VERIFICATION_STATUS = {
  pending: {
    icon: Clock,
    tone: "warning",
    labelKey: "status.pending",
    bannerKey: "banner.pending",
  },
  verified: {
    icon: CheckCircle2,
    tone: "success",
    labelKey: "status.verified",
    bannerKey: "banner.verified",
  },
  rejected: {
    icon: XCircle,
    tone: "error",
    labelKey: "status.rejected",
    bannerKey: "banner.rejected",
  },
};

export const DEFAULT_VERIFICATION_STATUS = "pending";

/** Per-tone Tailwind classes shared by the banner, badge and controls. */
export const VERIFICATION_TONES = {
  warning: {
    banner: "border-warning/30 bg-warning/10",
    icon: "bg-warning/15 text-warning",
    title: "text-warning",
    badge: "bg-warning/12 text-warning",
    dot: "bg-warning",
    control: "border-warning/40 text-warning hover:bg-warning/10",
  },
  success: {
    banner: "border-success/25 bg-success/10",
    icon: "bg-success/20 text-success",
    title: "text-success",
    badge: "bg-success/12 text-success",
    dot: "bg-success",
    control: "border-success/40 text-success hover:bg-success/10",
  },
  error: {
    banner: "border-error/25 bg-error/10",
    icon: "bg-error/15 text-error",
    title: "text-error",
    badge: "bg-error/12 text-error",
    dot: "bg-error",
    control: "border-error/35 text-error hover:bg-error/10",
  },
};

export const COMPANY = {
  id: "CMP-1098",
  name: "CloudStack",
  initials: "CS",
  colorScheme: "from-[#3d6b8a] to-[#274b63]",
  profile: {
    legalName: "CloudStack Technologies LLC",
    industry: "Cloud Infrastructure",
    size: "51–200 employees",
    website: "https://cloudstack.io",
    headquarters: "Cairo, Egypt",
    about:
      "CloudStack builds scalable cloud infrastructure tools for startups and mid-size companies across MENA. Founded in 2021, the team focuses on developer experience and reliable multi-region deployments.",
  },
  contact: {
    email: "ops@cloudstack.io",
    phone: "+20 100 555 0123",
    hrEmail: "careers@cloudstack.io",
    linkedin: "linkedin.com/company/cloudstack",
  },
  stats: [
    { label: "openJobs", value: "4", sub: "2 drafts" },
    { label: "totalApplications", value: "86", sub: "12 this week" },
    { label: "created", value: "Sep 22, 2026", sub: "Today", small: true },
    { label: "lastUpdated", value: "Sep 22, 2026", sub: "41 min ago", small: true },
  ],
  dates: [
    { label: "created", date: "Sep 22, 2026", sub: "11:30 EEST" },
    { label: "lastUpdated", date: "Sep 22, 2026", sub: "14:02 EEST · 41 min ago" },
    { label: "submitted", date: "Sep 22, 2026", sub: "14:02 EEST" },
  ],
};

export const COMPANY_JOBS = [
  {
    id: "JOB-2841",
    title: "Senior Backend Engineer",
    meta: ["Full-time", "Cairo · Hybrid", "24 applications"],
    status: "active",
    statusLabel: "Open",
  },
  {
    id: "JOB-2842",
    title: "DevOps Engineer",
    meta: ["Full-time", "Remote", "18 applications"],
    status: "active",
    statusLabel: "Open",
  },
  {
    id: "JOB-2843",
    title: "Product Designer",
    meta: ["Full-time", "Cairo · On-site", "31 applications"],
    status: "active",
    statusLabel: "Open",
  },
  {
    id: "JOB-2844",
    title: "Junior Frontend Developer",
    meta: ["Full-time", "Cairo", "13 applications"],
    status: "active",
    statusLabel: "Open",
  },
  {
    id: "JOB-2845",
    title: "Technical Writer",
    meta: ["Part-time", "Remote"],
    status: "draft",
    statusLabel: "Draft",
  },
  {
    id: "JOB-2846",
    title: "Office Manager",
    meta: ["Full-time", "Cairo"],
    status: "closed",
    statusLabel: "Closed",
  },
];

export const COMPANY_ACTIVITY = [
  {
    id: "ACT-1",
    tone: "warning",
    text: "Company submitted for verification review",
    meta: "Sep 22, 2026 · 14:02 · System",
  },
  {
    id: "ACT-2",
    tone: "primary",
    text: "Profile details updated (website, about)",
    meta: "Sep 22, 2026 · 13:48 · Company owner",
  },
  {
    id: "ACT-3",
    tone: "success",
    text: "Job posted: Senior Backend Engineer",
    meta: "Sep 22, 2026 · 12:15 · Company owner",
  },
  {
    id: "ACT-4",
    tone: "success",
    text: "Job posted: DevOps Engineer",
    meta: "Sep 22, 2026 · 12:10 · Company owner",
  },
  {
    id: "ACT-5",
    tone: "muted",
    text: "Company account created",
    meta: "Sep 22, 2026 · 11:30 · System",
  },
];

/** Timeline dot colors for the activity feed. */
export const ACTIVITY_DOTS = {
  primary: "bg-primary",
  success: "bg-success",
  warning: "bg-warning",
  error: "bg-error",
  muted: "bg-muted",
};