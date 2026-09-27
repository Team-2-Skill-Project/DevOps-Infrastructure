import { motion } from "framer-motion";

export default function ApplicationLoadingState() {
  return (
    <motion.div
      key="loading"
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      exit={{ opacity: 0 }}
      className="space-y-5"
    >
      <div className="bg-white border border-border rounded-2xl p-6 shadow-sm">
        <div className="flex items-start gap-4">
          <div className="w-16 h-16 rounded-2xl bg-linear-to-r from-[#EFEDE8] via-[#F5F3EF] to-[#EFEDE8] bg-size-[400%_100%] animate-pulse shrink-0" />
          <div className="flex-1 space-y-2">
            <div className="h-5 w-56 rounded-lg bg-[#EFEDE8] animate-pulse" />
            <div className="h-3 w-36 rounded-lg bg-[#EFEDE8] animate-pulse" />
            <div className="h-3 w-28 rounded-lg bg-[#EFEDE8] animate-pulse" />
          </div>
          <div className="h-8 w-24 rounded-full bg-[#EFEDE8] animate-pulse" />
        </div>
      </div>
      <div className="bg-white border border-border rounded-2xl p-6 shadow-sm">
        <div className="h-4 w-40 rounded-lg bg-[#EFEDE8] animate-pulse mb-5" />
        <div className="space-y-3">
          <div className="h-3 w-full rounded-lg bg-[#EFEDE8] animate-pulse" />
          <div className="h-3 w-full rounded-lg bg-[#EFEDE8] animate-pulse" />
          <div className="h-3 w-3/4 rounded-lg bg-[#EFEDE8] animate-pulse" />
        </div>
      </div>
    </motion.div>
  );
}
