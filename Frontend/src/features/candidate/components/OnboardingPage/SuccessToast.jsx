import { motion, AnimatePresence } from "framer-motion";
import { CheckCircle2 } from "lucide-react";

export default function SuccessToast({ show, label }) {
  return (
    <AnimatePresence>
      {show && (
        <motion.div
          initial={{ opacity: 0, y: -8, scale: 0.96 }}
          animate={{ opacity: 1, y: 0, scale: 1 }}
          exit={{ opacity: 0, y: -8, scale: 0.96 }}
          className="mb-4 flex items-center gap-2 rounded-lg bg-success/10 px-3 py-2 text-[12px] font-semibold text-success"
        >
          <CheckCircle2 className="h-4 w-4" />
          Saved: &quot;{label}&quot;
        </motion.div>
      )}
    </AnimatePresence>
  );
}
