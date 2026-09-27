import { motion } from "framer-motion";
import { AlertCircle, RotateCcw } from "lucide-react";
import { Button } from "@/components/ui/button";

const shakeAnimation = {
  initial: { opacity: 0, x: 0, y: 14 },
  animate: {
    opacity: 1,
    y: 0,
    x: [0, -6, 6, -4, 4, -2, 2, 0],
    transition: {
      opacity: { duration: 0.3 },
      y: { type: "spring", stiffness: 380, damping: 24 },
      x: { duration: 0.5, delay: 0.15 },
    },
  },
  exit: {
    opacity: 0,
    scale: 0.9,
    transition: { duration: 0.2 },
  },
};

/**
 * Error state shown when the AI response fails.
 *
 * @param {{ onRetry?: () => void }} props
 */
export default function ErrorMessage({ onRetry }) {
  return (
    <div className="flex justify-start">
      <motion.div
        variants={shakeAnimation}
        initial="initial"
        animate="animate"
        exit="exit"
        className="max-w-[85%]"
      >
        <div className="rounded-2xl rounded-tl-md border border-error/30 bg-error/5 px-4 py-3">
          <p className="flex items-center gap-1.5 text-[13px] font-semibold text-error">
            <AlertCircle className="size-4" />
            Couldn&apos;t get a response
          </p>
          <p className="mt-1 text-[12px] text-muted">
            Something went wrong on our end. Your question wasn&apos;t lost.
          </p>
        </div>

        <Button
          variant="outline"
          size="sm"
          onClick={onRetry}
          className="mt-2 gap-1.5 text-[12px] font-semibold text-primary"
        >
          <RotateCcw className="size-3.5" />
          Retry
        </Button>
      </motion.div>
    </div>
  );
}
