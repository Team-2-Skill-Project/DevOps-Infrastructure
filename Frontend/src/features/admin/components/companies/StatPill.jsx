import { motion } from "framer-motion";

export default function StatPill({ stat, index = 0 }) {
  const Icon = stat.icon;

  return (
    <motion.div
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay: index * 0.06, ease: [0.16, 1, 0.3, 1] }}
      className="flex items-center gap-3.5 rounded-2xl border border-border bg-surface p-4 shadow-sm"
    >
      <div
        className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ${stat.iconClass}`}
      >
        <Icon className="h-5 w-5" />
      </div>
      <div>
        <div className="text-[22px] font-bold leading-tight tracking-tight text-ink">
          {stat.value}
        </div>
        <div className="mt-0.5 text-[12.5px] font-medium text-muted">{stat.label}</div>
      </div>
    </motion.div>
  );
}
