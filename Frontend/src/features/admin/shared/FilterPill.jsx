import { cn } from "@/lib/utils";

/**
 * FilterPill
 * Selectable pill button used for status/source filters.
 */
export default function FilterPill({
  active = false,
  label,
  onClick,
  className,
}) {
  return (
    <button
      type="button"
      onClick={onClick}
      className={cn(
        "shrink-0 px-3.5 h-8 rounded-full border text-[12px] font-semibold transition-colors",
        active
          ? "bg-primary text-white border-primary"
          : "bg-white text-[#44474E] border-border hover:bg-background",
        className,
      )}
    >
      {label}
    </button>
  );
}
