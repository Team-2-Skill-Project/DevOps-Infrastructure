import { motion } from "framer-motion";
import { SearchX } from "lucide-react";

export default function ApplicationNotFoundState({ onBack }) {
  return (
    <motion.div
      key="notfound"
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -10 }}
      className="bg-white border border-border rounded-3xl py-20 px-6 text-center shadow-sm"
    >
      <div className="w-16 h-16 rounded-2xl bg-background border border-border flex items-center justify-center mx-auto mb-5">
        <SearchX className="h-7 w-7 text-muted" />
      </div>
      <h2 className="font-display font-bold text-[20px] mb-2">
        Application not found
      </h2>
      <p className="text-[13.5px] text-muted max-w-sm mx-auto mb-7">
        This application may have been removed, or the link you followed is no
        longer valid.
      </p>
      <button
        onClick={onBack}
        className="bg-primary text-white text-[13px] font-semibold px-6 h-11 rounded-full inline-flex items-center gap-2 shadow-sm hover:bg-[#0F2036] transition-colors"
      >
        Back to Applications
      </button>
    </motion.div>
  );
}
