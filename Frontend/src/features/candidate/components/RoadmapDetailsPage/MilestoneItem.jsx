import { MILESTONE_STATUS_STYLES } from "@/constants/milestoneStatusStyles";
import { motion } from "framer-motion";

export default function MilestoneItem({ milestone, index = 0, isLast = false }) {
  const style = MILESTONE_STATUS_STYLES[milestone.status] ?? MILESTONE_STATUS_STYLES["not-started"];
  const Icon = style.icon;

  return (
    <motion.div
      initial={{ opacity: 0, x: -12 }}
      whileInView={{ opacity: 1, x: 0 }}
      viewport={{ once: true, amount: 0.3 }}
      transition={{ duration: 0.4, delay: index * 0.08, ease: [0.16, 1, 0.3, 1] }}
      className={`relative flex items-start gap-4 ${isLast ? "" : "pb-5"}`}
    >
      <div
        className={`z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full ${style.circleClass}`}
      >
        <Icon className="h-[18px] w-[18px]" />
      </div>
      <div className="flex-1 pt-1">
        <div className={`text-[13px] font-semibold ${style.textClass}`}>{milestone.title}</div>
        <div className="mt-0.5 text-[12px] text-muted">{milestone.subtitle}</div>
      </div>
    </motion.div>
  );
}
