import { cn } from "@/lib/utils";

/**
 * FreshnessBadge
 * Small pill conveying how recently a job was published.
 *
 * Rules (preserved from the original monolith):
 *  - < 1 day old           -> green "fresh" tone
 *  - <= 7 days old         -> amber tone
 *  - draft & older         -> neutral canvas tone (no dot)
 *  - otherwise (stale)     -> red tone
 */
export default function FreshnessBadge({ label, days = 0, status, className }) {
  let tone;
  let withDot = true;

  if (days < 1) {
    tone = "bg-success/10 text-success";
  } else if (days <= 7) {
    tone = "bg-[#C88A26]/10 text-[#C88A26]";
  } else if (status === "draft") {
    tone = "bg-background text-muted border border-border";
    withDot = false;
  } else {
    tone = "bg-[#B3271E]/10 text-[#B3271E]";
  }

  return (
    <span
      className={cn(
        "inline-flex items-center gap-1 w-fit px-2 py-0.5 rounded-full text-[10.5px] font-semibold",
        tone,
        className,
      )}
    >
      {withDot && "● "}
      {label}
    </span>
  );
}
