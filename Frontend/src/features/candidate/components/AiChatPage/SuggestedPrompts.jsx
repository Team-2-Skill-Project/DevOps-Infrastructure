import { motion, AnimatePresence } from "framer-motion";
import { cn } from "@/lib/utils";

const containerVariants = {
  hidden: { opacity: 0 },
  show: {
    opacity: 1,
    transition: { staggerChildren: 0.05 },
  },
  exit: {
    opacity: 0,
    transition: { duration: 0.2 },
  },
};

const chipVariants = {
  hidden: { opacity: 0, x: -10, scale: 0.9 },
  show: {
    opacity: 1,
    x: 0,
    scale: 1,
    transition: { type: "spring", stiffness: 400, damping: 22 },
  },
  exit: { opacity: 0, scale: 0.85, transition: { duration: 0.15 } },
};

const DEFAULT_PROMPTS = [
  "What should I focus on this week?",
  "Why am I a weak match for this job?",
  "Which skill should I learn first?",
  "How can I improve my profile for this role?",
  "Which saved job should I prioritize?",
];

/**
 * Horizontally scrollable suggested prompt chips.
 *
 * @param {{
 *   prompts?: string[],
 *   visible?: boolean,
 *   onSelect?: (prompt: string) => void,
 *   className?: string,
 * }} props
 */
export default function SuggestedPrompts({
  prompts = DEFAULT_PROMPTS,
  visible = true,
  onSelect,
  className,
}) {
  return (
    <AnimatePresence>
      {visible && (
        <motion.div
          variants={containerVariants}
          initial="hidden"
          animate="show"
          exit="exit"
          className={cn("flex-shrink-0 px-4 pt-3", className)}
        >
          <div className="flex gap-2 overflow-x-auto pb-1 scrollbar-none">
            {prompts.map((prompt) => (
              <motion.button
                key={prompt}
                variants={chipVariants}
                whileHover={{ scale: 1.03 }}
                whileTap={{ scale: 0.97 }}
                onClick={() => onSelect?.(prompt)}
                className={cn(
                  "flex-shrink-0 whitespace-nowrap rounded-lg border border-border",
                  "px-3 py-1.5 text-[12px] font-medium text-ink",
                  "transition-colors hover:bg-background hover:text-primary",
                  "cursor-pointer select-none",
                )}
              >
                {prompt}
              </motion.button>
            ))}
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
