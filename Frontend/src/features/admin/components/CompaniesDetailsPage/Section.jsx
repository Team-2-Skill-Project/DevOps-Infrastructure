import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { cn } from "@/lib/utils";

/**
 * Section
 * Titled panel used for every block on the company details page. Wraps the
 * shadcn `Card` primitives and normalises the admin surface look
 * (`bg-surface` + `border-border`), with an optional right-hand action node.
 */
export default function Section({
  title,
  hint,
  action,
  bodyClassName,
  className,
  children,
}) {
  return (
    <Card
      className={cn(
        "gap-0 overflow-hidden rounded-2xl border-border bg-surface py-0 shadow-sm",
        className,
      )}
    >
      <CardHeader className="flex flex-row items-center justify-between gap-3 border-b border-border px-5 py-3.5">
        <CardTitle className="text-[15px] font-bold text-ink">{title}</CardTitle>
        {(hint || action) && (
          <div className="flex items-center gap-3">
            {hint && <span className="text-xs text-muted">{hint}</span>}
            {action}
          </div>
        )}
      </CardHeader>
      <CardContent className={cn("p-5", bodyClassName)}>{children}</CardContent>
    </Card>
  );
}