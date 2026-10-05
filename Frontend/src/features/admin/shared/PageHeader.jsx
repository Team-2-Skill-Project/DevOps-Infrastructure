import { cn } from "@/lib/utils";

/**
 * PageHeader
 * Reusable admin page header: title, subtitle, meta/sync-status row and
 * primary action buttons. Optionally renders breadcrumbs above the block.
 *
 * - `subtitle` accepts a string (styled paragraph) or a node (custom styling).
 * - `meta` renders in the small muted row (e.g. "Read-only · Last synced").
 */
export default function PageHeader({
  title,
  subtitle,
  meta,
  actions,
  breadcrumbs,
  className,
  children,
}) {
  return (
    <>
      {breadcrumbs}
      <div
        className={cn(
          "flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6",
          className,
        )}
      >
        <div className="min-w-0">
          <h1 className="text-2xl font-bold tracking-tight">{title}</h1>
          {typeof subtitle === "string" ? (
            <p className="text-sm text-muted mt-1">{subtitle}</p>
          ) : (
            subtitle && <div className="mt-1">{subtitle}</div>
          )}
          {meta && (
            <div className="flex items-center gap-2 text-xs text-muted mt-1.5">
              {meta}
            </div>
          )}
          {children}
        </div>
        {actions && <div className="flex gap-2.5">{actions}</div>}
      </div>
    </>
  );
}
