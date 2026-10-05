import { Fragment } from "react";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";


function TitleSegment({ segment }) {
  if (typeof segment === "string") {
    return <Fragment>{segment}</Fragment>;
  }

  if (segment?.href) {
    return (
      <a href={segment.href} className="text-primary font-semibold hover:underline">
        {segment.text}
      </a>
    );
  }

  if (segment?.strong) {
    return <strong className="font-semibold">{segment.text}</strong>;
  }

  return <Fragment>{segment?.text}</Fragment>;
}

/**
 * Renders a single meta token beneath the title (badge, dot or plain text).
 */
function MetaItem({ item }) {
  if (item.type === "dot") {
    return <span className="w-0.75 h-0.75 rounded-full bg-border" />;
  }

  if (item.type === "badge") {
    return (
      <Badge
        className={cn(
          "inline-flex items-center h-5.5 px-2 rounded-full text-[11px] font-semibold border-transparent",
          item.className
        )}
      >
        {item.label}
      </Badge>
    );
  }

  return <span>{item.text}</span>;
}

/**
 * Shared list row used by both RecentActivity and RecentAuditLogs.
 */
export default function ListItem({ icon: Icon, iconClass, title, meta }) {
  return (
    <div className="flex items-start gap-3.5 p-[14px_18px] hover:bg-[#faf9f6] transition-colors">
      <div
        className={cn(
          "w-9 h-9 rounded-xl grid place-items-center shrink-0 mt-0.5",
          iconClass
        )}
      >
        <Icon className="w-4 h-4" />
      </div>
      <div className="flex-1 min-w-0">
        <div className="text-[13.5px] font-medium text-[#222831] leading-snug">
          {title.map((segment, index) => (
            <TitleSegment key={index} segment={segment} />
          ))}
        </div>
        <div className="text-[12px] text-muted mt-1 flex items-center gap-1.5 flex-wrap">
          {meta.map((item, index) => (
            <MetaItem key={index} item={item} />
          ))}
        </div>
      </div>
    </div>
  );
}