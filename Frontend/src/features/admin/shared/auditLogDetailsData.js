/**
 * Static mock dataset for the Audit Log Details page.
 * Swap with an API/service result when wiring real data.
 */
import { Briefcase, Building2 } from "lucide-react";

/** The audit log record rendered on the details page. */
export const AUDIT_LOG_DETAIL = {
  id: "AL-94821",
  action: "Job Status Changed",
  actionType: "status",
  actor: {
    name: "Omar Hassan",
    role: "Super Admin",
    code: "ADM-002",
    initials: "OH",
    avatarClass: "bg-secondary",
  },
  timestamp: "Sep 22, 2026 · 14:18:07 EEST",
  relative: "26 min ago",
  entity: {
    type: "Job",
    id: "JOB-2841",
    title: "Senior Frontend Engineer",
    href: "#",
  },
  /** Human-readable change summary shown in the info banner. */
  change: { field: "status", from: "open", to: "suspended" },
  /** Previous/new JSON payloads rendered by the DiffViewer. */
  previousData: {
    status: "open",
    visibility: "public",
    updatedAt: "2026-09-20T11:02:00Z",
  },
  nextData: {
    status: "suspended",
    visibility: "hidden",
    updatedAt: "2026-09-22T14:18:07Z",
  },
  /** Field-level change rows under the diff panes. */
  fieldChanges: [
    { field: "status", from: "open", to: "suspended" },
    { field: "visibility", from: "public", to: "hidden" },
  ],
};

/** Linked-entity cards rendered in the "Related Entities" section. */
export const RELATED_ENTITIES = [
  {
    id: "related-job",
    href: "#",
    icon: Briefcase,
    iconClass: "bg-warning/15 text-warning",
    label: "Related Job",
    title: "Senior Frontend Engineer",
    meta: "JOB-2841",
  },
  {
    id: "related-company",
    href: "#",
    icon: Building2,
    iconClass: "bg-primary/10 text-primary",
    label: "Related Company",
    title: "BrightPath Inc.",
    meta: "CMP-0881",
  },
];
