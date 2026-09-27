import { motion } from "framer-motion";
import { AlertTriangle, RefreshCw } from "lucide-react";

export default function ApplicationErrorState({ onRetry }) {
  return (
    <motion.div
      key="error"
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -10 }}
      className="bg-white border border-border rounded-3xl py-20 px-6 text-center shadow-sm"
    >
      <div className="w-16 h-16 rounded-2xl bg-[#B3271E]/10 flex items-center justify-center mx-auto mb-5">
        <AlertTriangle className="h-7 w-7 text-[#B3271E]" />
      </div>
      <h2 className="font-display font-bold text-[20px] mb-2">
        Couldn't load this application
      </h2>
      <p className="text-[13.5px] text-muted max-w-sm mx-auto mb-7">
        Something went wrong on our end. Your data is safe — please try again.
      </p>
      <button
        onClick={onRetry}
        className="bg-primary text-white text-[13px] font-semibold px-6 h-11 rounded-full inline-flex items-center gap-2 shadow-sm hover:bg-[#0F2036] transition-colors"
      >
        <RefreshCw className="h-4 w-4" />
        Retry
      </button>
    </motion.div>
  );
}
