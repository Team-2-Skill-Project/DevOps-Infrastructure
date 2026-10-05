import { cn } from "@/lib/utils";

/**
 * SkeletonLoader
 * Base pulsing block. Pass full utility classes via `className`
 * (sizing, rounding, spacing) to shape it however you need.
 */
export default function SkeletonLoader({ className }) {
  return (
    <div className={cn("bg-stone-200 rounded-md animate-pulse", className)} />
  );
}

/**
 * SkeletonHeader
 * Page-header skeleton: a stack of text lines on the left, optional
 * action blocks on the right.
 *
 * @param {string[]} lines - full utility classes per line (e.g. "h-7 w-48").
 * @param {{ className?: string }[]} actions - action button placeholders.
 */
export function SkeletonHeader({
  lines = [],
  actions = [],
  actionClassName,
  className,
}) {
  return (
    <div
      className={cn(
        "flex flex-col md:flex-row md:items-start justify-between gap-4 mb-6",
        className,
      )}
    >
      <div className="space-y-2">
        {lines.map((line, index) => (
          <SkeletonLoader key={index} className={line} />
        ))}
      </div>
      {actions.length > 0 && (
        <div className={cn("flex gap-2.5", actionClassName)}>
          {actions.map((action, index) => (
            <SkeletonLoader
              key={index}
              className={cn("rounded-xl", action.className)}
            />
          ))}
        </div>
      )}
    </div>
  );
}

/**
 * SkeletonTable
 * Data-table card skeleton: tinted header bar + N placeholder rows.
 *
 * @param {number} rows - number of body row placeholders.
 */
export function SkeletonTable({ rows = 6, className }) {
  return (
    <div
      className={cn(
        "bg-white border border-border rounded-2xl overflow-hidden shadow-sm",
        className,
      )}
    >
      <div className="p-4 border-b border-border bg-[#f7f4ef] flex justify-between">
        <SkeletonLoader className="h-4 w-32" />
        <SkeletonLoader className="h-4 w-32" />
      </div>
      <div className="divide-y divide-border">
        {Array.from({ length: rows }).map((_, index) => (
          <div key={index} className="p-4 flex items-center justify-between gap-4">
            <SkeletonLoader className="h-8 w-28" />
            <div className="flex items-center gap-2">
              <SkeletonLoader className="w-8 h-8 rounded-full" />
              <SkeletonLoader className="h-8 w-24" />
            </div>
            <SkeletonLoader className="h-6 w-32 rounded-full" />
            <SkeletonLoader className="h-8 w-20" />
            <SkeletonLoader className="h-4 w-16" />
            <SkeletonLoader className="h-4 w-16" />
          </div>
        ))}
      </div>
    </div>
  );
}

/**
 * SkeletonCard
 * Detail-card placeholder(s) matching the white bordered card style.
 *
 * @param {number} count - number of card blocks to render.
 */
export function SkeletonCard({ count = 1, className }) {
  return (
    <div className={cn(count > 1 && "space-y-4", className)}>
      {Array.from({ length: count }).map((_, index) => (
        <div
          key={index}
          className="h-28 bg-white border border-border rounded-2xl p-4 animate-pulse"
        />
      ))}
    </div>
  );
}
