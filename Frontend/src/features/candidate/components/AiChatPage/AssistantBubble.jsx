import { motion } from "framer-motion";
import { ChatBubble } from "./ChatBubble";
import SourceLink from "./SourceLink";

const linksContainer = {
  hidden: { opacity: 0 },
  show: {
    opacity: 1,
    transition: { staggerChildren: 0.08, delayChildren: 0.2 },
  },
};

const linkItem = {
  hidden: { opacity: 0, y: 6 },
  show: { opacity: 1, y: 0 },
};

/**
 * Assistant (AI) response bubble with optional grounded source links.
 *
 * @param {{
 *   children: React.ReactNode,
 *   sources?: Array<{ icon: import("lucide-react").LucideIcon, label: string, href?: string }>,
 * }} props
 */
export default function AssistantBubble({ children, sources }) {
  return (
    <div className="flex justify-start">
      <div className="max-w-[85%]">
        <ChatBubble variant="assistant">
          <p className="text-[13px] leading-relaxed text-muted">{children}</p>
        </ChatBubble>

        {sources?.length > 0 && (
          <motion.div
            variants={linksContainer}
            initial="hidden"
            animate="show"
            className="mt-2 flex flex-wrap gap-2"
          >
            {sources.map((source) => (
              <motion.div key={source.label} variants={linkItem}>
                <SourceLink
                  icon={source.icon}
                  label={source.label}
                  href={source.href}
                />
              </motion.div>
            ))}
          </motion.div>
        )}
      </div>
    </div>
  );
}
