import { forwardRef } from "react";
import { cva } from "class-variance-authority";
import { motion } from "framer-motion";
import { cn } from "@/lib/utils";

const chatBubbleVariants = cva(
  "relative text-[13px] leading-relaxed max-w-[80%]",
  {
    variants: {
      variant: {
        user: [
          "bg-primary text-primary-foreground",
          "rounded-2xl rounded-tr-md",
          "px-4 py-2.5",
          "shadow-[0_2px_8px_rgba(31,54,92,0.15)]",
        ],
        assistant: [
          "bg-surface border border-border",
          "rounded-2xl rounded-tl-md",
          "px-4 py-3",
          "shadow-[0_1px_4px_rgba(0,0,0,0.04)]",
        ],
      },
    },
    defaultVariants: {
      variant: "assistant",
    },
  },
);

const bubbleAnimation = {
  initial: { opacity: 0, y: 14, scale: 0.96 },
  animate: { opacity: 1, y: 0, scale: 1 },
  transition: {
    type: "spring",
    stiffness: 380,
    damping: 24,
    mass: 0.8,
  },
};

const ChatBubble = forwardRef(
  ({ variant = "assistant", className, children, ...props }, ref) => {
    return (
      <motion.div
        ref={ref}
        {...bubbleAnimation}
        className={cn(
          "flex",
          variant === "user" ? "justify-end" : "justify-start",
        )}
      >
        <div
          className={cn(chatBubbleVariants({ variant }), className)}
          {...props}
        >
          {children}
        </div>
      </motion.div>
    );
  },
);

ChatBubble.displayName = "ChatBubble";

export { ChatBubble, chatBubbleVariants, bubbleAnimation };
