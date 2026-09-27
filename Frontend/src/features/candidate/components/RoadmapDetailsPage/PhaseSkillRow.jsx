import { PRIORITY_STYLES } from "@/constants/skillPriorityStyles";
import { motion } from "framer-motion";

export default function PhaseSkillRow({ skill, index = 0 }) {
  const Icon = skill.icon;
  const priority = PRIORITY_STYLES[skill.priority] ?? PRIORITY_STYLES.medium;
  const PriorityIcon = priority.icon;

  return (
    <motion.div
      initial={{ opacity: 0, x: -12 }}
      whileInView={{ opacity: 1, x: 0 }}
      viewport={{ once: true, amount: 0.3 }}
      transition={{ duration: 0.4, delay: index * 0.06, ease: [0.16, 1, 0.3, 1] }}
      className="flex items-center justify-between rounded-2xl border border-border px-4 py-3 transition-colors hover:bg-background"
    >
      <div className="flex items-center gap-3">
        <div className={`flex h-9 w-9 items-center justify-center rounded-xl ${skill.iconClass}`}>
          <Icon className="h-[17px] w-[17px]" />
        </div>
        <span className="text-[13px] font-semibold text-ink">{skill.label}</span>
      </div>

      <span
        className={`inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold ${priority.className}`}
      >
        <PriorityIcon className="h-3 w-3" />
        {priority.label}
      </span>
    </motion.div>
  );
}
