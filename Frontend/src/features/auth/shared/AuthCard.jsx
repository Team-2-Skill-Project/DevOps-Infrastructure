import { motion } from "framer-motion";
import { cn } from "@/lib/utils";

export default function AuthCard({ children, className }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 24 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.5, ease: "easeOut", delay: 0.15 }}
      className={cn(
        "flex min-h-0 flex-1 flex-col justify-center overflow-y-auto rounded-2xl border border-border bg-white px-9 py-7",
        "scrollbar-none [&::-webkit-scrollbar]:hidden max-h-fit",
        className,
      )}
    >
      {children}
    </motion.div>
  );
}
