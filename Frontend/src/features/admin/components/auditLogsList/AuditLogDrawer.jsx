import { AnimatePresence, motion } from "framer-motion";
import { X } from "lucide-react";
import { Badge, ReadOnlyBanner } from "@/features/admin/shared";
import { DiffViewer } from "../auditLogDetails";

/** Small labeled field used inside the drawer grid. */
function DrawerField({ label, children }) {
  return (
    <div>
      <label className="text-[11px] font-bold uppercase tracking-wider text-muted block mb-1">
        {label}
      </label>
      <div className="font-medium">{children}</div>
    </div>
  );
}

/**
 * AuditLogDrawer
 * Animated preview drawer shown under the table when a row is selected.
 * Framer Motion (AnimatePresence + motion.div) drives the slide-in/out.
 */
export default function AuditLogDrawer({ log, onClose }) {
  return (
    <AnimatePresence>
      {log && (
        <motion.div
          initial={{ opacity: 0, y: 15 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: 15 }}
          className="mt-5 bg-white border border-border rounded-2xl shadow-sm overflow-hidden"
        >
          {/* Header */}
          <div className="flex items-center justify-between p-4 px-5 border-b border-border bg-[#f7f4ef]">
            <h2 className="font-semibold text-sm sm:text-base">
              Log Details · #{log.id}
            </h2>
            <button
              type="button"
              aria-label="Close log details"
              onClick={onClose}
              className="p-1 hover:bg-stone-200 rounded-lg text-muted"
            >
              <X className="w-4 h-4" />
            </button>
          </div>

          {/* Fields */}
          <div className="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
            <DrawerField label="Timestamp">{log.time} EEST</DrawerField>
            <DrawerField label="Actor">
              {log.actor} ({log.role})
            </DrawerField>
            <DrawerField label="Action">
              <Badge type={log.actionType} text={log.action} />
            </DrawerField>
            <DrawerField label="Entity">
              {log.entity} · <span className="font-mono">{log.entityId}</span>
            </DrawerField>
            <div className="col-span-full">
              <DiffViewer
                variant="compact"
                label="Value Change"
                field="status"
                previous={log.prev}
                next={log.new}
              />
            </div>
          </div>

          {/* Read-only footer */}
          <ReadOnlyBanner variant="strip">
            Audit logs are immutable read-only records. They cannot be edited
            or deleted.
          </ReadOnlyBanner>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
