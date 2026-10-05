import { cn } from "@/lib/utils";

/**
 * InitialsAvatar
 * Renders a company/user initials avatar. Supports an explicit Tailwind
 * gradient `colorScheme` ("from-... to-...") or the neutral "canvas" style.
 */
export default function InitialsAvatar({
  initials,
  colorScheme = "canvas",
  className,
}) {
  const isCanvas = colorScheme === "canvas";

  return (
    <div
      className={cn(
        "w-10 h-10 rounded-xl flex items-center justify-center font-bold text-[13px] shrink-0",
        isCanvas
          ? "bg-background border border-border text-muted"
          : `bg-gradient-to-br ${colorScheme} text-white`,
        className,
      )}
    >
      {initials}
    </div>
  );
}
