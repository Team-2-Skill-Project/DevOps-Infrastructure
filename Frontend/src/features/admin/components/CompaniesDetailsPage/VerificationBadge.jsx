import { cn } from "@/lib/utils";
import { VERIFICATION_STATUS, VERIFICATION_TONES } from "../../shared/companiesDetailsData";

/**
 * VerificationBadge
 * Compact status pill for the verification state. Renders the tone dot from
 * the shared config map instead of hard-coded per-state markup.
 */
export default function VerificationBadge({ status, label, className }) {
  const config = VERIFICATION_STATUS[status] ?? VERIFICATION_STATUS.pending;
  const tone = VERIFICATION_TONES[config.tone];

  return (
    <span
      className={cn(
        "inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[12.5px] font-bold",
        tone.badge,
        className,
      )}
    >
      <span className={cn("h-2 w-2 shrink-0 rounded-full", tone.dot)} />
      {label}
    </span>
  );
}