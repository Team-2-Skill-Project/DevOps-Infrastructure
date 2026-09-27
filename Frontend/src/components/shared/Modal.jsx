import { motion } from "framer-motion";

const backdropVariants = {
  hidden: { opacity: 0 },
  visible: { opacity: 1 },
};

const panelVariants = {
  hidden: { opacity: 0, y: 16, scale: 0.97 },
  visible: { opacity: 1, y: 0, scale: 1 },
  exit: { opacity: 0, y: 12, scale: 0.97 },
};

export default function Modal({
  children,
  onClose,
  maxWidth = "max-w-[440px]",
  className = "",
}) {
  return (
    <motion.div
      className="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4 backdrop-blur-sm"
      variants={backdropVariants}
      initial="hidden"
      animate="visible"
      exit="hidden"
      onClick={onClose}
    >
      <motion.div
        className={`w-full rounded-2xl border border-border bg-white shadow-lg ${maxWidth} ${className}`}
        variants={panelVariants}
        transition={{ duration: 0.2, ease: "easeOut" }}
        onClick={(event) => event.stopPropagation()}
      >
        {children}
      </motion.div>
    </motion.div>
  );
}
