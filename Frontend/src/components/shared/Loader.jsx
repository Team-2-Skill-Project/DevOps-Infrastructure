import { motion } from "framer-motion"
export function Loader() {
  return (
    <div className="flex items-center justify-center py-10">
      <motion.div
        className="size-8 rounded-full border-2 border-primary border-t-transparent"
        animate={{ rotate: 360 }}
        transition={{ repeat: Infinity, duration: 0.8, ease: "linear" }}
      />
    </div>
  )
}
