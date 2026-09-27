import { motion } from "framer-motion";
import { Loader2 } from "lucide-react";
import { ChatBubble } from "./ChatBubble";

/**
 * User chat message bubble.
 *
 * @param {{
 *   children: React.ReactNode,
 *   isSending?: boolean,
 * }} props
 */
export default function UserBubble({ children, isSending = false }) {
  return (
    <ChatBubble variant="user">
      <span>{children}</span>

      {isSending && (
        <motion.span
          initial={{ opacity: 0, scale: 0 }}
          animate={{ opacity: 1, scale: 1 }}
          className="ms-2 inline-flex flex-shrink-0"
        >
          <Loader2 className="size-3.5 animate-spin text-primary-foreground/60" />
        </motion.span>
      )}
    </ChatBubble>
  );
}
