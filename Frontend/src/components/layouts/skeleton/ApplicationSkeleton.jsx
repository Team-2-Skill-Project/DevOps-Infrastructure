import { motion } from "framer-motion";

export default function ApplicationSkeleton() {
  return (
    <motion.div
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -10 }}
      className="space-y-6"
    >
      <div className="grid grid-cols-2 sm:grid-cols-5 gap-3">
        {[...Array(5)].map((_, index) => (
          <div
            key={index}
            className="h-24 rounded-2xl bg-linear-to-r from-[#EFEDE8] via-[#F5F3EF] to-[#EFEDE8] bg-size-[400%_100%] animate-pulse"
          />
        ))}
      </div>
      <div className="space-y-3">
        {[...Array(4)].map((_, index) => (
          <div
            key={index}
            className="h-20 rounded-2xl bg-linear-to-r from-[#EFEDE8] via-[#F5F3EF] to-[#EFEDE8] bg-size-[400%_100%] animate-pulse"
          />
        ))}
      </div>
    </motion.div>
  );
}
