import { cn } from "@/lib/utils";

const SOURCE_VARIANTS = {
  internal: "bg-primary/10 text-primary",
  external: "bg-[#9C432A]/10 text-[#9C432A]",
};

/**
 * SourceBadge
 * Small pill indicating where a job originates (Internal vs External source).
 */
export default function SourceBadge({ source, label, className }) {
  return (
    <span
      className={cn(
        "inline-flex items-center gap-1 w-fit px-2 py-0.5 rounded-full text-[10.5px] font-semibold",
        SOURCE_VARIANTS[source] ?? SOURCE_VARIANTS.internal,
        className,
      )}
    >
      {label}
    </span>
  );
}
