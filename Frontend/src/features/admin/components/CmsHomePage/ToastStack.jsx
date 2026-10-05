import { AnimatePresence, motion } from "framer-motion";
import { cn } from "@/lib/utils";

export default function ToastStack({ toasts, onDismiss, ease }) {
  return <div className="fixed bottom-5 right-5 z-50 space-y-2"><AnimatePresence>{toasts.map((toast) => <motion.div key={toast.id} initial={{ opacity: 0, y: 12, scale: 0.96 }} animate={{ opacity: 1, y: 0, scale: 1 }} exit={{ opacity: 0, y: 8, scale: 0.96 }} transition={{ duration: 0.2, ease }} className={cn("rounded-lg px-4 py-2 text-sm text-white shadow-lg", toast.kind === "err" && "bg-error", toast.kind === "warn" && "bg-warning", toast.kind === "ok" && "bg-success")} onClick={() => onDismiss(toast.id)}>{toast.msg}</motion.div>)}</AnimatePresence></div>;
}