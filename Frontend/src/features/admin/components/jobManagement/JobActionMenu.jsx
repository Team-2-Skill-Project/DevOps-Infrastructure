import { useEffect, useRef } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { MoreVertical, Building, RotateCcw } from "lucide-react";

/**
 * JobActionMenu
 * Row-level "MoreVertical" dropdown with click-outside detection and
 * a Framer Motion enter/exit animation.
 */
export default function JobActionMenu({ job, isOpen, onToggle, onAction }) {
  const menuRef = useRef(null);

  // Close the menu when clicking anywhere outside of it.
  useEffect(() => {
    if (!isOpen) return undefined;

    function handleClickOutside(event) {
      if (menuRef.current && !menuRef.current.contains(event.target)) {
        onToggle(false);
      }
    }

    document.addEventListener("mousedown", handleClickOutside);
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [isOpen, onToggle]);

  return (
    <div className="relative" ref={menuRef}>
      <button
        type="button"
        onClick={() => onToggle(!isOpen)}
        className="w-8 h-8 rounded-full flex items-center justify-center text-muted hover:bg-background transition-colors"
      >
        <MoreVertical className="w-4 h-4" />
      </button>

      <AnimatePresence>
        {isOpen && (
          <motion.div
            initial={{ opacity: 0, scale: 0.95, y: -5 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            exit={{ opacity: 0, scale: 0.95, y: -5 }}
            transition={{ duration: 0.15 }}
            className="absolute right-0 top-9 z-20 bg-white border border-border rounded-xl shadow-lg py-1.5 w-48"
          >
            <button
              type="button"
              onClick={() =>
                onAction(`Opening ${job.company} company profile…`)
              }
              className="w-full flex items-center gap-2 px-3.5 py-2 text-[12.5px] text-[#44474E] hover:bg-background transition-colors text-left"
            >
              <Building className="w-4 h-4 text-muted" /> Open Company
            </button>
            <button
              type="button"
              onClick={() =>
                onAction(`Updating ${job.title}: Status or featured mode…`)
              }
              className="w-full flex items-center gap-2 px-3.5 py-2 text-[12.5px] text-[#44474E] hover:bg-background transition-colors text-left"
            >
              <RotateCcw className="w-4 h-4 text-muted" /> Update Job
            </button>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
