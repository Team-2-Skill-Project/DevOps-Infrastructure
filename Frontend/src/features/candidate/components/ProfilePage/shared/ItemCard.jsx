import React from "react";
import { Pencil, Trash2 } from "lucide-react";

export default function ItemCard({
  title,
  subtitle,
  date,
  badgeText,
  extraInfo,
  onEdit,
  onDelete,
  children,
}) {
  return (
    <div className="w-full bg-[#F8FAFC]/60 hover:bg-[#F8FAFC] transition-colors p-4 rounded-xl border border-slate-200/80 flex items-start justify-between gap-4">
      <div className="space-y-1 flex-1">
        <div className="flex items-center gap-2.5 flex-wrap">
          <h4 className="text-base font-bold text-slate-900">{title}</h4>
          {badgeText && (
            <span
              className={`text-[11px] font-semibold px-2.5 py-0.5 rounded-full ${
                badgeText === "Manually Added"
                  ? "bg-orange-100 text-orange-700"
                  : "bg-blue-100 text-blue-700"
              }`}
            >
              {badgeText}
            </span>
          )}
        </div>

        {(subtitle || date) && (
          <p className="text-xs text-slate-500 font-medium">
            {subtitle} {subtitle && date && "•"} {date}
          </p>
        )}

        {extraInfo && (
          <p className="text-xs text-slate-400 font-normal pt-0.5">
            {extraInfo}
          </p>
        )}

        {children}
      </div>

      <div className="flex items-center gap-1 shrink-0">
        {onEdit && (
          <button
            type="button"
            onClick={onEdit}
            className="flex items-center gap-1.5 bg-[#1E2B3E] hover:bg-[#111827] text-white text-xs font-semibold px-3 py-1.5 rounded-md transition-colors cursor-pointer"
          >
            <Pencil className="w-3.5 h-3.5" />
          </button>
        )}
        {onDelete && (
          <button
            type="button"
            onClick={onDelete}
            className="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors cursor-pointer"
            title="Delete"
          >
            <Trash2 className="w-4 h-4" />
          </button>
        )}
      </div>
    </div>
  );
}
