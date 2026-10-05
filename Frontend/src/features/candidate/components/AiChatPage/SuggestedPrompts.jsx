import { motion, AnimatePresence } from "framer-motion";
import { cn } from "@/lib/utils";
import { useTranslation } from "react-i18next";

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

const DEFAULT_PROMPT_KEYS = [
  "ui.chat.promptWeek",
  "ui.chat.promptWeakMatch",
  "ui.chat.promptSkill",
  "ui.chat.promptProfile",
  "ui.chat.promptSavedJob",
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
  prompts,
  visible = true,
  onSelect,
  className,
}) {
  const { t } = useTranslation("common");
  const displayedPrompts = prompts ?? DEFAULT_PROMPT_KEYS.map((key) => t(key));
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
            {displayedPrompts.map((prompt) => (
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
