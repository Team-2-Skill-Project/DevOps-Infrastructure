import React from "react";
import { cn } from "@/lib/utils";

export default function PageHeader({
  title,
  description,
  badge,
  actions,
  breadcrumbs,
  className = "",
  children,
}) {
  return (
    <div
      className={cn(
        "mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end",
        className,
      )}
    >
      <div className="flex max-w-2xl flex-col">
        {breadcrumbs && <div className="mb-2">{breadcrumbs}</div>}
        {badge && <div className="mb-2 flex items-center gap-1.5">{badge}</div>}
        {title && (
          <h1 className="font-dm-sans text-2xl font-bold tracking-tight text-primary sm:text-3xl">
            {title}
          </h1>
        )}
        {description && (
          <p className="mt-1 text-sm text-muted leading-relaxed">
            {description}
          </p>
        )}
        {children}
      </div>

      {actions && (
        <div className="flex shrink-0 items-center gap-2.5">{actions}</div>
      )}
    </div>
  );
}
