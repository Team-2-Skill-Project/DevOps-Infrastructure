import { motion } from "framer-motion";
import ActionCard from "./ActionCard";
import { containerVariants } from "../../shared/motionVariants";


export default function QuickActions({ actions = [], title = "Quick Actions" }) {
  return (
    <section className="space-y-3.5">
      <div className="flex items-center justify-between gap-3">
        <h2 className="text-base font-semibold tracking-tight text-[#222831]">
          {title}
        </h2>
      </div>
      <motion.div
        variants={containerVariants}
        initial="hidden"
        animate="visible"
        className="grid grid-cols-1 min-[480px]:grid-cols-2 min-[900px]:grid-cols-4 gap-3.5"
      >
        {actions.map((action) => (
          <ActionCard key={action.id} {...action} />
        ))}
      </motion.div>
    </section>
  );
}