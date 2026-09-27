import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle2, X } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function ToastList({ toasts, onRemove }) {
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
              <div className="font-bold text-xs mb-0.5">{toast.title}</div>
              <div className="text-primary-foreground/80 text-[11px] leading-relaxed">
                {toast.message}
              </div>
            </div>
            <Button
              onClick={() => onRemove(toast.id)}
              className="text-primary-foreground/60 text-xs font-semibold ps-1"
            >
              <X className="w-3.5 h-3.5" />
            </Button>
          </motion.div>
        ))}
      </AnimatePresence>
    </div>
  );
}
