import { motion } from "framer-motion";
import { cn } from "@/lib/utils";

/**
 * DetailCard
 * Animated section card used across the audit-log details page:
 * motion entrance (with optional stagger delay), header title + hint
 * and a padded body for arbitrary content.
 */
export default function DetailCard({
  title,
  hint,
  delay = 0,
  bodyClassName,
  className,
  children,
}) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ delay }}
      className={cn(
        "bg-white border border-border rounded-2xl shadow-sm mb-4 overflow-hidden",
        className,
      )}
    >
      <div className="p-3.5 px-5 border-b border-border flex justify-between items-center">
        <h2 className="text-sm font-bold tracking-tight">{title}</h2>
        {hint && <span className="text-xs text-muted">{hint}</span>}
      </div>
      <div className={cn("p-5", bodyClassName)}>{children}</div>
    </motion.div>
  );
}
