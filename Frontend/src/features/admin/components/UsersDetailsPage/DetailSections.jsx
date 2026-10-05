import { cn } from "@/lib/utils";
import { RoleBadge, UserStatusBadge } from "@/features/admin/shared";

const APP_STATUS_STYLES = { submitted: "bg-primary/10 text-primary", review: "bg-warning/10 text-warning", offer: "bg-success/10 text-success", rejected: "bg-error/10 text-error" };
const APP_STATUS_LABELS = { submitted: "Submitted", review: "In Review", offer: "Offer", rejected: "Rejected" };
const STATUS_LABELS = { active: "Active", inactive: "Inactive", suspended: "Suspended" };

export function Section({ title, hint, action, children, className }) {
  return <div className="mb-4.5 overflow-hidden rounded-2xl border border-border bg-surface shadow-[0_2px_8px_rgba(0,0,0,0.03)]"><div className="flex items-center justify-between gap-3 border-b border-border px-5 py-3.5"><h2 className="text-[14.5px] font-bold tracking-tight text-ink">{title}</h2>{hint && <span className="text-[12.5px] text-muted">{hint}</span>}{action && <button type="button" onClick={action.onClick} className="text-[12.5px] font-semibold text-muted hover:text-ink">{action.label}</button>}</div><div className={className ?? "p-5"}>{children}</div></div>;
}

export function Field({ label, value, mono, muted, full, valueClassName }) {
  return <div className={cn(full && "col-span-2 max-[560px]:col-span-1")}><label className="mb-1 block text-[11.5px] font-bold uppercase tracking-wide text-muted">{label}</label><div className={cn("text-[14.5px] font-medium text-ink", mono && "font-mono text-[13.5px]", muted && "font-normal text-muted", valueClassName)}>{value}</div></div>;
}

export function AppStatusBadge({ status }) { return <span className={cn("inline-flex h-[22px] items-center rounded-full px-2 text-[11px] font-bold", APP_STATUS_STYLES[status])}>{APP_STATUS_LABELS[status]}</span>; }

export { RoleBadge, UserStatusBadge, STATUS_LABELS };