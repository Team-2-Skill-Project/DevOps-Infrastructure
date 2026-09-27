import { AnimatePresence, motion } from "framer-motion";
import { AlertCircle, CheckCircle2, Loader2 } from "lucide-react";
import { cn } from "@/lib/utils";

/**
 * Status banner for page-level feedback.
 *
 * @param {{
 *   status: "loading" | "error" | "success" | null,
 *   text?: string,
 *   className?: string,
 * }} props
 */
export default function ActionBanner({ status = null, text, className }) {
  const config = {
    loading: {
      icon: Loader2,
      iconClass: "animate-spin",
      container: "bg-primary/10 border-primary/30 text-primary",
      defaultText: "Processing… please wait.",
    },
    error: {
      icon: AlertCircle,
      iconClass: "",
      container: "bg-error/10 border-error/30 text-error",
      defaultText: "Something went wrong. Please try again.",
    },
    success: {
      icon: CheckCircle2,
      iconClass: "",
      container: "bg-success/10 border-success/30 text-success",
      defaultText: "Done successfully.",
    },
  };

  const active = status ? config[status] : null;
  const message = text ?? active?.defaultText;
  const Icon = active?.icon;

  return (
    <AnimatePresence mode="wait">
      {status && active && (
        <motion.div
          key={status + (message ?? "")}
          role="status"
          initial={{ opacity: 0, y: -8, height: 0 }}
          animate={{ opacity: 1, y: 0, height: "auto" }}
          exit={{ opacity: 0, y: -8, height: 0 }}
          transition={{ duration: 0.22, ease: [0.16, 1, 0.3, 1] }}
          className={cn(
            "flex items-center gap-2 overflow-hidden rounded-xl border p-4 text-sm font-bold",
            active.container,
            className,
          )}
        >
          <Icon className={cn("h-5 w-5 shrink-0", active.iconClass)} />
          <span>{message}</span>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
