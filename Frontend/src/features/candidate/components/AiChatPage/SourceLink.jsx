import { motion } from "framer-motion";
import { cn } from "@/lib/utils";

/**
 * Small reference chip for grounded AI responses.
 * Shows an icon + label linking to the source (job, skill, roadmap, etc.).
 *
 * @param {{
 *   icon: import("lucide-react").LucideIcon,
 *   label: string,
 *   href?: string,
 *   className?: string,
 * }} props
 */
export default function SourceLink({ icon: Icon, label, href = "#", className }) {
  return (
    <motion.a
      href={href}
      initial={{ opacity: 0, y: 6 }}
      animate={{ opacity: 1, y: 0 }}
      whileHover={{ scale: 1.04, backgroundColor: "var(--color-background)" }}
      whileTap={{ scale: 0.97 }}
      transition={{ type: "spring", stiffness: 400, damping: 22 }}
      className={cn(
        "inline-flex items-center gap-1.5 rounded-lg border border-border px-2.5 py-1",
        "text-[11px] font-semibold text-primary",
        "transition-colors hover:bg-background cursor-pointer",
        className,
      )}
    >
      {Icon && <Icon className="size-3.5 flex-shrink-0" />}
      {label}
    </motion.a>
  );
}
