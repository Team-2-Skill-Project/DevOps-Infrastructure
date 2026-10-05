import { cn } from "@/lib/utils";

const ROLE_STYLES = {
  candidate: "bg-success/10 text-success border-success/20",
  recruiter: "bg-primary/10 text-primary border-primary/15",
  admin: "bg-secondary/10 text-secondary border-secondary/20",
};

const ROLE_LABELS = { candidate: "Candidate", recruiter: "Recruiter", admin: "Admin" };

const STATUS_STYLES = {
  active: "bg-success/10 text-success",
  inactive: "bg-muted/15 text-muted",
  suspended: "bg-error/10 text-error",
};

const STATUS_DOT = { active: "bg-success", inactive: "bg-muted", suspended: "bg-error" };
const STATUS_LABELS = { active: "Active", inactive: "Inactive", suspended: "Suspended" };

export function RoleBadge({ role }) {
  return (
    <span className={cn("inline-flex h-[26px] items-center whitespace-nowrap rounded-full border px-2.5 text-xs font-bold", ROLE_STYLES[role])}>
      {ROLE_LABELS[role]}
    </span>
  );
}

export function UserStatusBadge({ status }) {
  return (
    <span className={cn("inline-flex h-[26px] items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 text-xs font-bold", STATUS_STYLES[status])}>
      <span className={cn("h-1.5 w-1.5 rounded-full", STATUS_DOT[status])} />
      {STATUS_LABELS[status]}
    </span>
  );
}

export { ROLE_LABELS, STATUS_LABELS };