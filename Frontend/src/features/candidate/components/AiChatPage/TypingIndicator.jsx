import { motion } from "framer-motion";

const dotVariants = {
  initial: { y: 0 },
  animate: {
    y: [0, -6, 0],
    transition: {
      duration: 0.6,
      repeat: Infinity,
      ease: "easeInOut",
    },
  },
};

const containerVariants = {
  initial: { opacity: 0, y: 14, scale: 0.96 },
  animate: {
    opacity: 1,
    y: 0,
    scale: 1,
    transition: {
      type: "spring",
      stiffness: 380,
      damping: 24,
      staggerChildren: 0.15,
    },
  },
  exit: {
    opacity: 0,
    scale: 0.9,
    transition: { duration: 0.2 },
  },
};

/**
 * Three bouncing dots in an assistant-styled bubble.
 * Shows while the AI is generating a response.
 */
export default function TypingIndicator() {
  return (
    <div className="flex justify-start">
      <motion.div
        variants={containerVariants}
        initial="initial"
        animate="animate"
        exit="exit"
        className="inline-flex items-center gap-1.5 rounded-2xl rounded-tl-md border border-border bg-surface px-5 py-4 shadow-[0_1px_4px_rgba(0,0,0,0.04)]"
      >
        {[0, 1, 2].map((i) => (
          <motion.span
            key={i}
            variants={dotVariants}
            className="block size-2 rounded-full bg-primary/50"
          />
        ))}
      </motion.div>
    </div>
  );
}
