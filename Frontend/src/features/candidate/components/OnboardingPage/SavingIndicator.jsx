import { motion, AnimatePresence } from "framer-motion";
import { Loader2 } from "lucide-react";

export default function SavingIndicator({ show }) {
  return (
    <AnimatePresence>
      {show && (
        <motion.div
          initial={{ opacity: 0, y: -8 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: -8 }}
          className="mb-4 flex items-center gap-2 rounded-lg bg-background px-3 py-2 text-[12px] font-medium text-muted"
        >
          <Loader2 className="h-3.5 w-3.5 animate-spin" />
          Saving changes to server...
        </motion.div>
      )}
    </AnimatePresence>
  );
}
