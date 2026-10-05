import { Info, MinusCircle, PlusCircle } from "lucide-react";
import { cn } from "@/lib/utils";

/** Pretty-print objects, pass primitives through as-is. */
function formatValue(value) {
  return typeof value === "object" && value !== null
    ? JSON.stringify(value, null, 2)
    : `"${value}"`;
}

/**
 * DiffViewer
 * Formatted previous-vs-new change comparison.
 *
 * - `variant="full"` (default): info summary, side-by-side JSON panes
 *   (stacked on mobile) and an optional field-level changes list.
 * - `variant="compact"`: single-line `field: "value"` boxes for drawers.
 */
export default function DiffViewer({
  variant = "full",
  label,
  field = "status",
  summary,
  previous,
  next,
  previousLabel,
  nextLabel,
  changes = [],
  stacked = false,
  className,
}) {
  const gridClass = stacked
    ? "grid grid-cols-1 gap-3.5"
    : "grid grid-cols-1 sm:grid-cols-2 gap-3.5";

  /* ---------- Compact (drawer) variant ---------- */
  if (variant === "compact") {
    return (
      <div className={className}>
        {label && (
          <div className="text-[11px] font-bold uppercase tracking-wider text-muted block mb-1">
            {label}
          </div>
        )}
        <div
          className={cn(
            "grid grid-cols-1 sm:grid-cols-2 gap-3 mt-1 font-mono text-xs",
            stacked && "grid-cols-1",
          )}
        >
          <div className="p-3 bg-error/10 border border-error/20 rounded-lg text-error">
            <div className="font-sans text-[10px] font-bold uppercase opacity-80 mb-1">
              {previousLabel ?? "Previous"}
            </div>
            {field}: {formatValue(previous)}
          </div>
          <div className="p-3 bg-success/10 border border-success/20 rounded-lg text-success">
            <div className="font-sans text-[10px] font-bold uppercase opacity-80 mb-1">
              {nextLabel ?? "New"}
            </div>
            {field}: {formatValue(next)}
          </div>
        </div>
      </div>
    );
  }

  /* ---------- Full (details page) variant ---------- */
  return (
    <div className={className}>
      {summary && (
        <div className="flex items-center gap-2 p-3 bg-primary/5 border border-primary/10 rounded-xl mb-4 text-xs sm:text-sm text-[#222831]">
          <Info className="w-4 h-4 text-primary shrink-0" />
          <span>{summary}</span>
        </div>
      )}

      <div className={gridClass}>
        <div className="border border-error/20 rounded-xl overflow-hidden">
          <div className="p-2.5 px-3.5 bg-error/10 text-error text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5">
            <MinusCircle className="w-3.5 h-3.5" />
            {previousLabel ?? "Previous Data"}
          </div>
          <pre className="p-3.5 bg-error/5 text-[#8a3a37] font-mono text-xs leading-relaxed overflow-x-auto whitespace-pre-wrap">
            {formatValue(previous)}
          </pre>
        </div>
        <div className="border border-success/25 rounded-xl overflow-hidden">
          <div className="p-2.5 px-3.5 bg-success/10 text-success text-[11px] font-bold uppercase tracking-wider flex items-center gap-1.5">
            <PlusCircle className="w-3.5 h-3.5" />
            {nextLabel ?? "New Data"}
          </div>
          <pre className="p-3.5 bg-success/5 text-[#3a5c44] font-mono text-xs leading-relaxed overflow-x-auto whitespace-pre-wrap">
            {formatValue(next)}
          </pre>
        </div>
      </div>

      {/* Field-level changes */}
      {changes.length > 0 && (
        <div className="mt-5 pt-4 border-t border-border">
          <h3 className="text-[11px] font-bold uppercase tracking-wider text-muted mb-3">
            Field-level changes
          </h3>
          <div className="space-y-2 text-xs sm:text-sm">
            {changes.map((change) => (
              <div
                key={change.field}
                className="grid grid-cols-1 sm:grid-cols-3 gap-1 py-2 border-b border-border/60"
              >
                <span className="font-semibold text-[#222831]">
                  {change.field}
                </span>
                <span className="font-mono text-error line-through">
                  {change.from}
                </span>
                <span className="font-mono text-success font-semibold">
                  {change.to}
                </span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
