import { cn } from "@/lib/utils";

/**
 * Canonical status -> visual token map for admin job/listing statuses.
 */
const STATUS_VARIANTS = {
  active: "bg-success/10 text-success",
  draft: "bg-background text-[#44474E] border border-border",
  expired: "bg-[#B3271E]/10 text-[#B3271E]",
  closed: "bg-background text-muted border border-border",
};

const STATUS_DOTS = {
  active: "bg-success",
  draft: "bg-muted",
  expired: "bg-[#B3271E]",
  closed: "bg-muted",
};

/**
 * StatusBadge
 * Pill-style status indicator with an optional leading dot.
 * Reusable across any admin surface (jobs, users, companies, ...).
 */
export default function StatusBadge({
  status,
  label,
  variant,
  dotColor,
  withDot = true,
  className,
}) {
  const resolvedVariant =
    variant ?? STATUS_VARIANTS[status] ?? STATUS_VARIANTS.draft;
  const resolvedDot = dotColor ?? STATUS_DOTS[status] ?? "bg-muted";
  const text =
    label ?? (status ? status.charAt(0).toUpperCase() + status.slice(1) : "");

  return (
    <span
      className={cn(
        "inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold",
        resolvedVariant,
        className,
      )}
    >
      {withDot && (
        <span className={cn("w-1.5 h-1.5 rounded-full", resolvedDot)} />
      )}
      {text}
    </span>
  );
}
