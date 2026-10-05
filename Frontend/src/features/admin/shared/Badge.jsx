import {
  CheckCircle2,
  Edit3,
  XCircle,
  Plus,
  RefreshCcw,
  LogOut,
  Lock,
} from "lucide-react";
import { cn } from "@/lib/utils";

/**
 * Canonical action-type -> { color tokens, Lucide icon } map.
 * Colors follow the app palette (success/primary/error/warning/secondary/muted).
 */
const BADGE_TYPES = {
  verified: { className: "bg-success/10 text-success", icon: CheckCircle2 },
  updated: { className: "bg-primary/10 text-primary", icon: Edit3 },
  rejected: { className: "bg-error/10 text-error", icon: XCircle },
  created: { className: "bg-warning/15 text-[#a07e1a]", icon: Plus },
  status: { className: "bg-secondary/12 text-secondary", icon: RefreshCcw },
  login: { className: "bg-muted/12 text-muted", icon: LogOut },
  readonly: { className: "bg-muted/10 text-muted", icon: Lock },
};

/**
 * Badge
 * Generic icon badge for audit-log action statuses (verified, updated,
 * rejected, created, status changed, login, read-only, ...).
 * Pass `icon` to override the mapped glyph and `className` for extra styles.
 */
export default function Badge({ type = "login", text, icon, className }) {
  const config = BADGE_TYPES[type] ?? BADGE_TYPES.login;
  const Icon = icon ?? config.icon;

  return (
    <span
      className={cn(
        "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap",
        config.className,
        className,
      )}
    >
      {Icon && <Icon className="w-3.5 h-3.5" />}
      {text}
    </span>
  );
}
