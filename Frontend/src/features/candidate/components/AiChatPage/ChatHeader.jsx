import { motion } from "framer-motion";
import { Bot } from "lucide-react";

/**
 * Header bar for the AI Chat panel.
 * Shows the bot avatar, name, and a short description.
 */
export default function ChatHeader() {
  return (
    <motion.div
      initial={{ opacity: 0, y: -8 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ type: "spring", stiffness: 350, damping: 26 }}
      className="flex flex-shrink-0 items-center gap-3 border-b border-border bg-surface/80 p-4 backdrop-blur-md"
    >
      {/* Avatar */}
      <div className="flex size-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary to-primary/80 shadow-[0_2px_8px_rgba(31,54,92,0.2)]">
        <Bot className="size-[18px] text-primary-foreground" />
      </div>

      {/* Info */}
      <div className="min-w-0">
        <div className="text-[13px] font-semibold text-ink">
          AI Career Mentor
        </div>
        <div className="text-[11px] text-muted">
          Answers using your profile, CV, jobs, and roadmap
        </div>
      </div>

      {/* Online indicator */}
      <div className="ms-auto flex items-center gap-1.5">
        <span className="relative flex size-2">
          <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-success opacity-75" />
          <span className="relative inline-flex size-2 rounded-full bg-success" />
        </span>
        <span className="text-[11px] font-medium text-success">Online</span>
      </div>
    </motion.div>
  );
}
