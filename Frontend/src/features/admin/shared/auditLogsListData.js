/**
 * Static mock dataset + toolbar options for the Audit Logs list page.
 * Swap with an API/service result when wiring real data.
 */

/** Mock audit-log entries rendered as table rows and drawer previews. */
export const AUDIT_LOGS = [
  {
    id: "AL-94821",
    actor: "Sara Ahmed",
    role: "Super Admin",
    initials: "SA",
    avatarClass: "bg-primary",
    action: "Company Verified",
    actionType: "verified",
    entity: "Company",
    entityId: "CMP-1042",
    prev: "pending",
    new: "verified",
    ip: "185.23.41.12",
    time: "Sep 22, 2026 · 14:42",
    relative: "2 min ago",
  },
  {
    id: "AL-94818",
    actor: "Omar Hassan",
    role: "Super Admin",
    initials: "OHs",
    avatarClass: "bg-secondary",
    action: "Job Status Changed",
    actionType: "status",
    entity: "Job",
    entityId: "JOB-2841",
    prev: "open",
    new: "suspended",
    ip: "41.68.102.9",
    time: "Sep 22, 2026 · 14:18",
    relative: "26 min ago",
  },
];

/** Toolbar "All Actions" filter options. */
export const ACTION_FILTERS = [
  { value: "", label: "All Actions" },
  { value: "Company Verified", label: "Company Verified" },
  { value: "Company Updated", label: "Company Updated" },
  { value: "Company Rejected", label: "Company Rejected" },
  { value: "Job Created", label: "Job Created" },
  { value: "Job Updated", label: "Job Updated" },
  { value: "Job Status Changed", label: "Job Status Changed" },
  { value: "Admin Login", label: "Admin Login" },
];

/** Toolbar "All Entities" filter options. */
export const ENTITY_FILTERS = [
  { value: "", label: "All Entities" },
  { value: "Company", label: "Company" },
  { value: "Job", label: "Job" },
  { value: "User", label: "User" },
  { value: "Admin", label: "Admin" },
];

/** Toolbar "All Actors" filter options. */
export const ACTOR_FILTERS = [
  { value: "", label: "All Actors" },
  { value: "Sara Ahmed", label: "Sara Ahmed" },
  { value: "Omar Hassan", label: "Omar Hassan" },
  { value: "System", label: "System" },
];

/** Results-bar sort options. */
export const SORT_OPTIONS = [
  { value: "newest", label: "Newest first" },
  { value: "oldest", label: "Oldest first" },
  { value: "actor-az", label: "Actor A–Z" },
];

/** Active filter chips shown under the toolbar on first render. */
export const INITIAL_ACTIVE_FILTERS = [
  { id: "1", label: "Action: Job Status Changed" },
  { id: "2", label: "Last 7 days" },
];

/** "Showing X–Y of Z logs" summary. */
export const LOGS_RESULT_SUMMARY = { from: 1, to: 10, total: 248 };

/** Pagination state for the table footer. */
export const LOGS_PAGINATION = { page: 1, pageCount: 25 };
