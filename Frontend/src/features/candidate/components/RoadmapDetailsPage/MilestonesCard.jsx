import { motion } from "framer-motion";
import MilestoneItem from "./MilestoneItem";
import { MILESTONES } from "@/constants/milestonesMock";

export default function MilestonesCard({ milestones = MILESTONES }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.3 }}
      transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
      className="mb-5 rounded-3xl border border-border bg-surface p-6 shadow-sm"
    >
      <h2 className="mb-4 font-display text-[15px] font-bold text-ink">
        Milestones
      </h2>

      <div className="relative ps-1">
        <div className="absolute bottom-3 inset-s-[15px] top-3 w-px bg-border" />
        {milestones.map((milestone, index) => (
          <MilestoneItem
            key={milestone.id}
            milestone={milestone}
            index={index}
            isLast={index === milestones.length - 1}
          />
        ))}
      </div>
    </motion.div>
  );
}
