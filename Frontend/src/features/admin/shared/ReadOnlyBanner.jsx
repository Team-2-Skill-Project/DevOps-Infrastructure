import { Lock } from "lucide-react";
import { cn } from "@/lib/utils";

const VARIANTS = {
  /** Full warning banner used at the top of detail pages. */
  banner:
    "flex items-start gap-2.5 p-3.5 px-4 bg-muted/10 border border-border rounded-xl mb-5 text-xs sm:text-sm text-muted leading-relaxed",
  /** Slim footer strip used inside cards/drawers. */
  strip:
    "flex items-center gap-2 px-5 py-2.5 bg-[#faf9f6] border-t border-border text-xs text-muted",
};

const ICON_SHAPES = {
  banner: "w-4 h-4 mt-0.5 opacity-75",
  strip: "w-3.5 h-3.5 opacity-70",
};

/**
 * ReadOnlyBanner
 * Standard warning banner for immutable/read-only system records.
 * `icon` accepts any Lucide component (defaults to Lock; pass KeyRound for a key).
 */
export default function ReadOnlyBanner({
  message,
  children,
  variant = "banner",
  icon: Icon = Lock,
  className,
}) {
  return (
    <div className={cn(VARIANTS[variant] ?? VARIANTS.banner, className)}>
      <Icon
        className={cn(
          "shrink-0",
          ICON_SHAPES[variant] ?? ICON_SHAPES.banner,
        )}
      />
      <span>{children ?? message}</span>
    </div>
  );
}
