import { ArrowRight } from "lucide-react";
import { cn } from "@/lib/utils";

/**
 * RelatedEntityCard
 * Interactive card linking to a related record (Job, Company, User, ...).
 * Renders icon tile, uppercase label, title, mono meta id and a sliding arrow.
 */
export default function RelatedEntityCard({
  href = "#",
  icon: Icon,
  iconClass = "bg-primary/10 text-primary",
  label,
  title,
  meta,
  onClick,
  className,
}) {
  return (
    <a
      href={href}
      onClick={(e) => {
        if (onClick) {
          e.preventDefault();
          onClick(e);
        }
      }}
      className={cn(
        "p-4 border border-border rounded-xl flex items-center gap-3.5 hover:border-primary/30 hover:shadow-md transition-all bg-white group",
        className,
      )}
    >
      <div
        className={cn(
          "w-10 h-10 rounded-xl flex items-center justify-center shrink-0",
          iconClass,
        )}
      >
        {Icon && <Icon className="w-5 h-5" />}
      </div>
      <div className="flex-1 min-w-0">
        <div className="text-[11px] font-bold uppercase tracking-wider text-muted">
          {label}
        </div>
        <div className="text-sm font-bold text-[#222831] truncate">{title}</div>
        <div className="text-xs font-mono text-muted">{meta}</div>
      </div>
      <ArrowRight className="w-4 h-4 text-primary group-hover:translate-x-1 transition-transform" />
    </a>
  );
}
