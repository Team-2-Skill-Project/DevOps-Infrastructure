import { useState, useCallback } from "react";
import { motion } from "framer-motion";
import { Briefcase, BarChart3, CheckCircle2, Map } from "lucide-react";
import { cn } from "@/lib/utils";

import ChatHeader from "../components/AiChatPage/ChatHeader";
import MissingContextBanner from "../components/AiChatPage/MissingContextBanner";
import ChatMessageList from "../components/AiChatPage/ChatMessageList";
import SuggestedPrompts from "../components/AiChatPage/SuggestedPrompts";
import ChatInput from "../components/AiChatPage/ChatInput";

/* ─── Demo data ─── */
const DEMO_MESSAGES = [
  {
    id: 1,
    role: "user",
    content: "Why am I a weak match for the Vercel frontend role?",
  },
  {
    id: 2,
    role: "assistant",
    content:
      "Your match score for that role is 98%, so you're actually a strong candidate overall. The one gap pulling it down is System Design — it's listed as a core requirement and isn't confirmed on your profile yet. Everything else (React, TypeScript, Next.js) lines up well.",
    sources: [
      {
        icon: Briefcase,
        label: "Senior Frontend Engineer · Vercel",
        href: "#",
      },
      { icon: BarChart3, label: "Skill gap: System Design", href: "#" },
    ],
  },
  {
    id: 3,
    role: "user",
    content: "What should I focus on this week?",
  },
  {
    id: 4,
    role: "assistant",
    content:
      "You're 75% through Phase 1 of your roadmap — one task left. Finishing it closes out the System Design foundations before you move into GraphQL and testing next week.",
    sources: [
      {
        icon: CheckCircle2,
        label: "Task: Design a rate limiter",
        href: "#",
      },
      { icon: Map, label: "Phase 1: Foundations", href: "#" },
    ],
  },
];

let nextId = 100;

/**
 * Main AI Chat panel.
 * Drop this into a page/layout as a self-contained section.
 *
 * @param {{
 *   showMissingContext?: boolean,
 *   className?: string,
 * }} props
 */
export default function AiChat({ showMissingContext = false, className }) {
  const [messages, setMessages] = useState(DEMO_MESSAGES);
  const [inputValue, setInputValue] = useState("");
  const [isTyping, setIsTyping] = useState(false);
  const [hasError, setHasError] = useState(false);

  const handleSend = useCallback(
    (text) => {
      if (!text.trim()) return;

      // Add user message
      const userMsg = { id: nextId++, role: "user", content: text };
      setMessages((prev) => [...prev, userMsg]);
      setInputValue("");
      setIsTyping(true);
      setHasError(false);

      // Simulate AI response
      setTimeout(() => {
        setIsTyping(false);
        setMessages((prev) => [
          ...prev,
          {
            id: nextId++,
            role: "assistant",
            content: `Thanks for asking! Here's some AI-grounded guidance based on your profile and saved jobs. This is a demo response for: "${text}"`,
            sources: [
              {
                icon: Briefcase,
                label: "Related job match",
                href: "#",
              },
            ],
          },
        ]);
      }, 2000);
    },
    [],
  );

  const handleRetry = useCallback(() => {
    setHasError(false);
    setIsTyping(true);
    setTimeout(() => {
      setIsTyping(false);
      setMessages((prev) => [
        ...prev,
        {
          id: nextId++,
          role: "assistant",
          content: "Here's the response after retrying. Everything looks good now!",
        },
      ]);
    }, 1500);
  }, []);

  const handlePromptSelect = useCallback(
    (prompt) => {
      handleSend(prompt);
    },
    [handleSend],
  );

  const showPrompts = messages.length === 0;

  return (
    <motion.section
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ type: "spring", stiffness: 300, damping: 26, delay: 0.05 }}
      className={cn(
        "flex min-h-0 flex-1 flex-col overflow-hidden",
        "rounded-2xl border border-border bg-surface",
        "shadow-[0_4px_24px_rgba(0,0,0,0.04)]",
        className,
      )}
    >
      <ChatHeader />

      <MissingContextBanner visible={showMissingContext} />

      <ChatMessageList
        messages={messages}
        isTyping={isTyping}
        hasError={hasError}
        onRetry={handleRetry}
      />

      <SuggestedPrompts
        visible={showPrompts || messages.length <= 2}
        onSelect={handlePromptSelect}
      />

      <ChatInput
        value={inputValue}
        onChange={setInputValue}
        onSend={handleSend}
        disabled={isTyping}
      />
    </motion.section>
  );
}
