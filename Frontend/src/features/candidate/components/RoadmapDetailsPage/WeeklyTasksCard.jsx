import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import TaskRow from "./TaskRow";
import TaskDetailModal from "./TaskDetailModal";
import { WEEKLY_TASKS } from "@/constants/weeklyTasksMock";

export default function WeeklyTasksCard({ initialTasks = WEEKLY_TASKS }) {
  const [tasks, setTasks] = useState(initialTasks);
  const [openTaskId, setOpenTaskId] = useState(null);

  const toggleTask = (id) => {
    setTasks((prev) =>
      prev.map((t) =>
        t.id === id
          ? {
              ...t,
              done: !t.done,
              // TODO: use the real completion date from the backend
              completedDate: !t.done ? "Today" : null,
            }
          : t
      )
    );
  };

  const activeTask = tasks.find((t) => t.id === openTaskId) ?? null;

  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.3 }}
      transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
      className="rounded-3xl border border-border bg-surface p-6 shadow-sm"
    >
      <h2 className="mb-4 font-display text-[15px] font-bold text-ink">
        This phase&apos;s weekly actions
      </h2>

      <div className="space-y-2.5">
        {tasks.map((task, index) => (
          <TaskRow
            key={task.id}
            task={task}
            index={index}
            onToggle={toggleTask}
            onOpen={setOpenTaskId}
          />
        ))}
      </div>

      <AnimatePresence>
        {activeTask && (
          <TaskDetailModal
            task={activeTask}
            onClose={() => setOpenTaskId(null)}
            onToggle={toggleTask}
          />
        )}
      </AnimatePresence>
    </motion.div>
  );
}
