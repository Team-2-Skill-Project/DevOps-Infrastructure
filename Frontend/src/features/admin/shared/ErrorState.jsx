import { motion } from "framer-motion";
import { AlertCircle, RefreshCw } from "lucide-react";

/**
 * ErrorState
 * Centered error card with an icon, message and a retry action.
 */
export default function ErrorState({
  title = "Something went wrong",
  description = "Something went wrong on our end. Please try again.",
  retryLabel = "Retry",
  onRetry,
  className,
}) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      className={`bg-white border border-border rounded-3xl py-20 px-6 text-center shadow-sm ${
        className ?? ""
      }`}
    >
      <div className="w-16 h-16 rounded-2xl bg-[#B3271E]/10 flex items-center justify-center mx-auto mb-5 text-[#B3271E]">
        <AlertCircle className="w-7 h-7" />
      </div>
      <h2 className="font-bold text-[20px] mb-2 text-[#1B1C1A]">{title}</h2>
      <p className="text-[13.5px] text-muted max-w-sm mx-auto mb-7">
        {description}
      </p>
      {onRetry && (
        <button
          type="button"
          onClick={onRetry}
          className="bg-primary hover:bg-[#0F2036] text-white text-[13px] font-semibold px-6 h-11 rounded-full inline-flex items-center gap-2 shadow-sm transition-colors"
        >
          <RefreshCw className="w-4 h-4" /> {retryLabel}
        </button>
      )}
    </motion.div>
  );
}
