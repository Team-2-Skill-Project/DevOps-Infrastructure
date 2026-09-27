import { CheckCircle2 } from "lucide-react";
import { AnimatePresence, motion } from "framer-motion";

export default function ApplicationToast({ message }) {
  return (
    <AnimatePresence>
      {message && (
        <motion.div
          initial={{ opacity: 0, y: 20 }}
          animate={{ opacity: 1, y: 0 }}
          exit={{ opacity: 0, y: 20 }}
          className="fixed bottom-4 inset-s-4 inset-e-4 sm:inset-s-auto sm:bottom-6 sm:inset-e-6 z-50 bg-[#0F2036] text-white px-4 py-3 rounded-2xl shadow-xl flex items-center justify-center gap-2 text-xs font-medium text-center border border-white/10"
        >
          <CheckCircle2 className="w-4 h-4 text-success" />
          {message}
        </motion.div>
      )}
    </AnimatePresence>
  );
}
