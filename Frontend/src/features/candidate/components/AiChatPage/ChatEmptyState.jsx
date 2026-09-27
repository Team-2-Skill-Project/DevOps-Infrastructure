import { motion } from "framer-motion";
import { Bot } from "lucide-react";

/**
 * Shown when the conversation has no messages yet.
 * Encourages the user to ask their first question.
 */
export default function ChatEmptyState() {
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.9 }}
      animate={{ opacity: 1, scale: 1 }}
      transition={{ type: "spring", stiffness: 300, damping: 22, delay: 0.1 }}
      className="flex h-full flex-col items-center justify-center px-6 text-center"
    >
      {/* Icon */}
      <motion.div
        initial={{ scale: 0 }}
        animate={{ scale: 1 }}
        transition={{
          type: "spring",
          stiffness: 400,
          damping: 18,
          delay: 0.25,
        }}
        className="mb-4 flex size-14 items-center justify-center rounded-2xl bg-primary/10"
      >
        <Bot className="size-7 text-primary" />
      </motion.div>

      <h2 className="mb-1.5 font-dm-sans text-base font-bold text-ink">
        Ask your Mentor anything
      </h2>

      <p className="max-w-xs text-[13px] leading-relaxed text-muted">
        Grounded in your profile, CV, saved jobs, and roadmap — start with a
        suggestion below or type your own.
      </p>
    </motion.div>
  );
}
