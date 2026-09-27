import React from "react";
import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle2, X } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function Toast({ message, onClose, variant = "default" }) {
  return (
    <AnimatePresence>
      {message && (
        <motion.div
          initial={{ opacity: 0, y: 16, scale: 0.96 }}
          animate={{ opacity: 1, y: 0, scale: 1 }}
          exit={{ opacity: 0, y: 16, scale: 0.96 }}
          transition={{ duration: 0.2, ease: "easeOut" }}
          className="fixed bottom-4 inset-s-4 inset-e-4 sm:inset-s-auto sm:bottom-6 sm:inset-e-6 z-50 rounded-xl border border-border bg-primary px-4 py-3 text-primary-foreground shadow-lg flex items-center justify-between gap-3 text-xs font-medium"
        >
          <div className="flex items-center gap-2">
            <CheckCircle2 className="h-4 w-4 shrink-0 text-success" />
            <span>{typeof message === "string" ? message : message.text}</span>
          </div>
          {onClose && (
            <Button
              type="button"
              variant="ghost"
              size="icon-xs"
              onClick={onClose}
              className="text-primary-foreground/70 hover:text-primary-foreground"
            >
              <X className="h-3.5 w-3.5" />
            </Button>
          )}
        </motion.div>
      )}
    </AnimatePresence>
  );
}

export function ToastList({ toasts = [], onRemove }) {
  return (
    <div className="fixed bottom-4 inset-s-4 inset-e-4 sm:bottom-6 sm:inset-s-auto sm:inset-e-6 z-50 flex flex-col gap-2 pointer-events-none">
      <AnimatePresence>
        {toasts.map((toast) => (
          <motion.div
            key={toast.id}
            initial={{ opacity: 0, y: 10, scale: 0.95 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 10, scale: 0.95 }}
            className="pointer-events-auto w-full sm:min-w-80 sm:max-w-md bg-primary text-primary-foreground border border-border shadow-lg rounded-xl p-4 flex items-start gap-3 text-xs"
          >
            <CheckCircle2 className="w-4 h-4 text-secondary shrink-0 mt-0.5" />
            <div className="flex-1">
              {toast.title && (
                <div className="font-bold text-xs mb-0.5">{toast.title}</div>
              )}
              <div className="text-primary-foreground/80 text-[11px] leading-relaxed">
                {toast.message}
              </div>
            </div>
            {onRemove && (
              <button
                type="button"
                onClick={() => onRemove(toast.id)}
                className="text-primary-foreground/60 hover:text-primary-foreground text-xs font-semibold ps-1 cursor-pointer"
              >
                <X className="w-3.5 h-3.5" />
              </button>
            )}
          </motion.div>
        ))}
      </AnimatePresence>
    </div>
  );
}
