import { motion } from "framer-motion";

/**
 * EmptyState
 * Generic centered empty-state card: icon, title, description, optional extra.
 */
export default function EmptyState({
  icon,
  title,
  description,
  children,
  className,
}) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      className={`bg-white border border-border rounded-3xl py-20 px-6 text-center shadow-sm ${
        className ?? ""
      }`}
    >
      {icon && (
        <div className="w-16 h-16 rounded-2xl bg-background border border-border flex items-center justify-center mx-auto mb-5 text-muted">
          {icon}
        </div>
      )}
      {title && (
        <h2 className="font-bold text-[20px] mb-2 text-[#1B1C1A]">{title}</h2>
      )}
      {description && (
        <p className="text-[13.5px] text-muted max-w-sm mx-auto">
          {description}
        </p>
      )}
      {children}
    </motion.div>
  );
}
