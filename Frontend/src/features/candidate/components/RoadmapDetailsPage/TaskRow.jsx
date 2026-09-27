import { motion } from "framer-motion";
import { Check } from "lucide-react";

export default function TaskRow({ task, index = 0, onToggle, onOpen }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 10 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.3 }}
      transition={{ duration: 0.4, delay: index * 0.05, ease: [0.16, 1, 0.3, 1] }}
      className="flex items-center gap-3 rounded-2xl border border-border p-3.5 transition-colors"
    >
      <button
        type="button"
        onClick={(e) => {
          e.stopPropagation();
          onToggle(task.id);
        }}
        aria-pressed={task.done}
        title={task.done ? "Mark as not done" : "Mark as done"}
        className={`flex h-6 w-6 shrink-0 items-center justify-center rounded-full border-2 transition-colors ${
          task.done
            ? "border-primary bg-primary text-primary-foreground"
            : "border-border bg-transparent"
        }`}
      >
        {task.done && <Check className="h-[14px] w-[14px]" />}
      </button>

      <div className="min-w-0 flex-1 cursor-pointer" onClick={() => onOpen(task.id)}>
        <div className="text-[13.5px] font-semibold text-ink">{task.title}</div>
        <div className="mt-0.5 text-[11px] text-muted">
          Target skill: {task.targetSkill} ·{" "}
          {task.done ? `Done ${task.completedDate}` : "Not started"}
        </div>
      </div>

      <button
        type="button"
        onClick={() => onOpen(task.id)}
        className="shrink-0 px-2 text-[12px] font-semibold text-primary"
      >
        Open
      </button>
    </motion.div>
  );
}
