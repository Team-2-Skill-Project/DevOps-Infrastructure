import { Eye } from "lucide-react";
import { cn } from "@/lib/utils";
import { Badge } from "@/features/admin/shared";

/**
 * AuditLogRow
 * Renders a single audit-log table row: timestamp + relative time, actor
 * avatar/name, action badge, entity, previous/new values and IP/meta.
 * Clicking the row (or the eye button) opens the detail preview.
 */
export default function AuditLogRow({ log, onSelect }) {
  return (
    <tr
      onClick={() => onSelect?.(log)}
      className="hover:bg-[#faf9f6] cursor-pointer transition-colors"
    >
      {/* Timestamp */}
      <td className="py-3.5 px-4">
        <div className="font-medium text-[#222831]">{log.time}</div>
        <div className="text-xs text-muted">{log.relative}</div>
      </td>

      {/* Actor */}
      <td className="py-3.5 px-4">
        <div className="flex items-center gap-2.5">
          <div
            className={cn(
              "w-8 h-8 rounded-full text-white flex items-center justify-center font-bold text-xs",
              log.avatarClass ?? "bg-primary",
            )}
          >
            {log.initials}
          </div>
          <div>
            <div className="font-semibold text-[#222831]">{log.actor}</div>
            <div className="text-xs text-muted">{log.role}</div>
          </div>
        </div>
      </td>

      {/* Action */}
      <td className="py-3.5 px-4">
        <Badge type={log.actionType} text={log.action} />
      </td>

      {/* Entity */}
      <td className="py-3.5 px-4">
        <div className="font-semibold text-[#222831]">{log.entity}</div>
        <div className="text-xs font-mono text-muted">{log.entityId}</div>
      </td>

      {/* Previous / New */}
      <td className="py-3.5 px-4 font-mono text-xs text-muted line-through">
        {log.prev}
      </td>
      <td className="py-3.5 px-4 font-mono text-xs text-success font-semibold">
        {log.new}
      </td>

      {/* IP / Meta */}
      <td className="py-3.5 px-4 font-mono text-xs text-muted">{log.ip}</td>

      {/* Preview action */}
      <td className="py-3.5 px-4 text-right">
        <button
          type="button"
          title="Preview log"
          onClick={(e) => {
            e.stopPropagation();
            onSelect?.(log);
          }}
          className="p-1.5 rounded-lg hover:bg-primary/10 text-muted hover:text-primary transition-colors"
        >
          <Eye className="w-4 h-4" />
        </button>
      </td>
    </tr>
  );
}
