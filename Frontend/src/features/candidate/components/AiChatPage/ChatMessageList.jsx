import { useRef, useEffect } from "react";
import { AnimatePresence } from "framer-motion";
import ChatEmptyState from "./ChatEmptyState";
import UserBubble from "./UserBubble";
import AssistantBubble from "./AssistantBubble";
import TypingIndicator from "./TypingIndicator";
import ErrorMessage from "./ErrorMessage";

/**
 * Scrollable list of chat messages.
 * Shows ChatEmptyState when there are no messages.
 *
 * @param {{
 *   messages: Array<{ id: string|number, role: "user"|"assistant", content: string, sources?: Array<{ icon: import("lucide-react").LucideIcon, label: string, href?: string }>, isSending?: boolean }>,
 *   isTyping?: boolean,
 *   hasError?: boolean,
 *   onRetry?: () => void,
 * }} props
 */
export default function ChatMessageList({
  messages = [],
  isTyping = false,
  hasError = false,
  onRetry,
}) {
  const scrollRef = useRef(null);

  // Auto-scroll to bottom on new messages
  useEffect(() => {
    const el = scrollRef.current;
    if (el) {
      el.scrollTo({ top: el.scrollHeight, behavior: "smooth" });
    }
  }, [messages.length, isTyping]);

  const isEmpty = messages.length === 0 && !isTyping && !hasError;

  return (
    <div
      ref={scrollRef}
      className="flex-1 overflow-y-auto px-4 py-4 space-y-4 scrollbar-thin"
    >
      {isEmpty ? (
        <ChatEmptyState />
      ) : (
        <AnimatePresence mode="popLayout" initial={false}>
          {messages.map((msg) =>
            msg.role === "user" ? (
              <UserBubble key={msg.id} isSending={msg.isSending}>
                {msg.content}
              </UserBubble>
            ) : (
              <AssistantBubble key={msg.id} sources={msg.sources}>
                {msg.content}
              </AssistantBubble>
            ),
          )}

          {isTyping && <TypingIndicator key="typing" />}

          {hasError && <ErrorMessage key="error" onRetry={onRetry} />}
        </AnimatePresence>
      )}
    </div>
  );
}
