import { useState, useCallback, useEffect } from "react";
import { motion } from "framer-motion";
import { Briefcase, BarChart3, CheckCircle2, Map } from "lucide-react";
import { cn } from "@/lib/utils";
import { useTranslation } from "react-i18next";

import ChatHeader from "../components/AiChatPage/ChatHeader";
import MissingContextBanner from "../components/AiChatPage/MissingContextBanner";
import ChatMessageList from "../components/AiChatPage/ChatMessageList";
import SuggestedPrompts from "../components/AiChatPage/SuggestedPrompts";
import ChatInput from "../components/AiChatPage/ChatInput";

/* ─── Demo data ─── */
const createDemoMessages = (t) => [
  {
    id: 1,
    role: "user",
    content: t("ui.chat.demoQuestion"),
  },
  {
    id: 2,
    role: "assistant",
    content: t("ui.chat.demoAnswer"),
    sources: [
      {
        icon: Briefcase,
        label: t("ui.chat.sourceJob"),
        href: "#",
      },
      { icon: BarChart3, label: t("ui.chat.sourceSkillGap"), href: "#" },
    ],
  },
  {
    id: 3,
    role: "user",
    content: t("ui.chat.demoQuestionWeek"),
  },
  {
    id: 4,
    role: "assistant",
    content: t("ui.chat.demoAnswerWeek"),
    sources: [
      {
        icon: CheckCircle2,
        label: t("ui.chat.sourceTask"),
        href: "#",
      },
      { icon: Map, label: t("ui.chat.sourcePhase"), href: "#" },
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
  const { t, i18n } = useTranslation("common");
  const [messages, setMessages] = useState(() => createDemoMessages(t));
  const [inputValue, setInputValue] = useState("");
  const [isTyping, setIsTyping] = useState(false);
  const [hasError, setHasError] = useState(false);

  useEffect(() => {
    const refreshDemoMessages = () => {
      setMessages((previous) => [
        ...createDemoMessages(i18n.t.bind(i18n)),
        ...previous.filter((message) => message.id >= 100),
      ]);
    };
    i18n.on("languageChanged", refreshDemoMessages);
    return () => i18n.off("languageChanged", refreshDemoMessages);
  }, [i18n]);

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
            content: t("ui.chat.responsePrefix", { question: text }),
            sources: [
              {
                icon: Briefcase,
                label: t("ui.chat.relatedJob"),
                href: "#",
              },
            ],
          },
        ]);
      }, 2000);
    },
    [t],
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
          content: t("ui.chat.retryResponse"),
        },
      ]);
    }, 1500);
  }, [t]);

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
