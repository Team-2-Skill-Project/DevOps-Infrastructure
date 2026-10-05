import {
  Building2,
  Clock,
  Briefcase,
  FileCheck,
  Users,
  CheckCircle2,
  UserPlus,
  XCircle,
  Lock,
  LogOut,
  Trash2,
  Sliders,
} from "lucide-react";


export const overviewStats = [
  {
    id: "companies",
    icon: Building2,
    iconClass: "bg-primary/10 text-primary",
    value: "148",
    label: "Companies",
    sublabel: "132 active · 16 inactive",
    trend: { type: "up", label: "+12%" },
  },
  {
    id: "pending-companies",
    icon: Clock,
    iconClass: "bg-secondary/12 text-secondary",
    value: "23",
    label: "Pending Companies",
    sublabel: "7 submitted today",
    trend: { type: "neutral", label: "Needs review" },
  },
  {
    id: "jobs",
    icon: Briefcase,
    iconClass: "bg-warning/14 text-warning",
    value: "1,284",
    label: "Jobs",
    sublabel: "942 open · 342 closed",
    trend: { type: "up", label: "+8%" },
  },
  {
    id: "applications",
    icon: FileCheck,
    iconClass: "bg-success/12 text-success",
    value: "9,417",
    label: "Applications",
    sublabel: "312 this week",
    trend: { type: "up", label: "+24%" },
  },
  {
    id: "users",
    icon: Users,
    iconClass: "bg-muted/12 text-muted",
    value: "6,892",
    label: "Users",
    sublabel: "Candidates + Recruiters",
    trend: { type: "up", label: "+5%" },
  },
];

/**
 * Quick action tiles rendered dynamically by <ActionCard />.
 */
export const quickActions = [
  {
    id: "manage-companies",
    icon: Building2,
    iconClass: "bg-primary/10 text-primary",
    title: "Manage Companies",
    description: "View, edit & suspend company accounts",
    href: "#",
  },
  {
    id: "review-pending",
    icon: CheckCircle2,
    iconClass: "bg-secondary/12 text-secondary",
    title: "Review Pending",
    description: "Approve or reject new company requests",
    href: "#",
  },
  {
    id: "manage-jobs",
    icon: Briefcase,
    iconClass: "bg-warning/14 text-warning",
    title: "Manage Jobs",
    description: "Moderate listings & featured positions",
    href: "#",
  },
  {
    id: "audit-logs",
    icon: FileCheck,
    iconClass: "bg-success/12 text-success",
    title: "View Audit Logs",
    description: "Track admin actions & system events",
    href: "#",
  },
];

/**
 * A list item's title is a list of segments so we can mix plain text
 * with inline links and emphasised words while keeping data declarative.
 *   - string                      -> plain text
 *   - { text, href }              -> anchor link
 *   - { text, strong: true }      -> <strong>
 * Meta entries describe the row beneath the title:
 *   - { type: "badge", label, className }
 *   - { type: "dot" }
 *   - { type: "text", text }
 */
export const recentActivity = [
  {
    id: "activity-1",
    icon: CheckCircle2,
    iconClass: "bg-success/12 text-success",
    title: ["Company ", { text: "Nexus Labs", href: "#" }, " was approved"],
    meta: [
      { type: "badge", label: "Approved", className: "bg-success/12 text-success" },
      { type: "dot" },
      { type: "text", text: "2 min ago" },
      { type: "dot" },
      { type: "text", text: "by Admin · Sara" },
    ],
  },
  {
    id: "activity-2",
    icon: Briefcase,
    iconClass: "bg-warning/14 text-warning",
    title: ["New job posted: ", { text: "Senior Frontend Engineer", href: "#" }],
    meta: [
      { type: "badge", label: "Job", className: "bg-primary/10 text-primary" },
      { type: "dot" },
      { type: "text", text: "18 min ago" },
      { type: "dot" },
      { type: "text", text: "by BrightPath Inc." },
    ],
  },
  {
    id: "activity-3",
    icon: Clock,
    iconClass: "bg-secondary/12 text-secondary",
    title: ["Company ", { text: "CloudStack", href: "#" }, " submitted for review"],
    meta: [
      { type: "badge", label: "Pending", className: "bg-warning/14 text-warning" },
      { type: "dot" },
      { type: "text", text: "41 min ago" },
    ],
  },
  {
    id: "activity-4",
    icon: UserPlus,
    iconClass: "bg-primary/10 text-primary",
    title: ["New user registered: ", { text: "maya.k@matchin.io", href: "#" }],
    meta: [
      { type: "badge", label: "User", className: "bg-muted/10 text-muted" },
      { type: "dot" },
      { type: "text", text: "1 hr ago" },
    ],
  },
  {
    id: "activity-5",
    icon: XCircle,
    iconClass: "bg-secondary/12 text-secondary",
    title: ["Job ", { text: "Junior Data Analyst", href: "#" }, " was suspended"],
    meta: [
      { type: "badge", label: "Suspended", className: "bg-secondary/12 text-secondary" },
      { type: "dot" },
      { type: "text", text: "2 hr ago" },
      { type: "dot" },
      { type: "text", text: "by Admin · Omar" },
    ],
  },
];

export const auditLogs = [
  {
    id: "audit-1",
    icon: Lock,
    iconClass: "bg-primary/10 text-primary",
    title: ["Role updated for ", { text: "recruiter@brightpath.com", href: "#" }],
    meta: [
      { type: "badge", label: "Permission", className: "bg-primary/10 text-primary" },
      { type: "dot" },
      { type: "text", text: "5 min ago" },
      { type: "dot" },
      { type: "text", text: "Admin · Sara" },
    ],
  },
  {
    id: "audit-2",
    icon: CheckCircle2,
    iconClass: "bg-success/12 text-success",
    title: ["Company status changed → ", { text: "Active", strong: true }],
    meta: [
      { type: "badge", label: "Status", className: "bg-success/12 text-success" },
      { type: "dot" },
      { type: "text", text: "12 min ago" },
      { type: "dot" },
      { type: "text", text: "Admin · Sara" },
    ],
  },
  {
    id: "audit-3",
    icon: LogOut,
    iconClass: "bg-muted/10 text-muted",
    title: ["Admin login from 185.23.xx.xx"],
    meta: [
      { type: "badge", label: "Auth", className: "bg-muted/10 text-muted" },
      { type: "dot" },
      { type: "text", text: "28 min ago" },
      { type: "dot" },
      { type: "text", text: "Omar · Cairo" },
    ],
  },
  {
    id: "audit-4",
    icon: Trash2,
    iconClass: "bg-secondary/12 text-secondary",
    title: ["Job listing deleted: ", { text: "Intern Marketing", href: "#" }],
    meta: [
      { type: "badge", label: "Delete", className: "bg-secondary/12 text-secondary" },
      { type: "dot" },
      { type: "text", text: "1 hr ago" },
      { type: "dot" },
      { type: "text", text: "Admin · Omar" },
    ],
  },
  {
    id: "audit-5",
    icon: Sliders,
    iconClass: "bg-secondary/12 text-secondary",
    title: ["Settings updated: Application rate limits"],
    meta: [
      { type: "badge", label: "Config", className: "bg-warning/14 text-warning" },
      { type: "dot" },
      { type: "text", text: "3 hr ago" },
      { type: "dot" },
      { type: "text", text: "Admin · Sara" },
    ],
  },
];