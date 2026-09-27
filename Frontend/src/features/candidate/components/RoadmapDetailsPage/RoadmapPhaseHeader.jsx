import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { MessageSquare } from "lucide-react";
import { Button } from "@/components/ui/button";
import { DEFAULT_ROADMAP_PHASE } from "@/constants/roadmapPhaseMock";
import { useLocalizedPath } from "@/utils/routes";

/**
 * data shape: { phaseNumber, totalPhases, title, goal, completion,
 * tasksCompleted, totalTasks, mentorHref }
 * The ring and the bar both read `completion` — pass any 0-100 value.
 */
export default function RoadmapPhaseHeader({ data = DEFAULT_ROADMAP_PHASE }) {
  const localizedPath = useLocalizedPath();
  const { phaseNumber, totalPhases, title, goal, completion, tasksCompleted, totalTasks, mentorHref } =
    data;

  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.3 }}
      transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
      className="mb-5 rounded-3xl border border-border bg-surface p-6 shadow-sm"
    >
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="flex items-start gap-4">
          {/* Donut ring — CSS conic-gradient driven by `completion`, no custom .ring class needed */}
          <div
            className="relative h-16 w-16 shrink-0 rounded-full"
            style={{
              background: `conic-gradient(var(--color-success) ${completion}%, var(--color-border) 0)`,
            }}
          >
            <div className="absolute inset-[6px] flex items-center justify-center rounded-full bg-surface">
              <span className="font-display text-[13px] font-extrabold text-success">
                {completion}%
              </span>
            </div>
          </div>

          <div>
            <div className="mb-1.5 text-[11px] font-semibold text-secondary">
              PHASE {phaseNumber} OF {totalPhases}
            </div>
            <h1 className="mb-2 font-display text-[22px] font-extrabold leading-tight text-ink">
              {title}
            </h1>
            <p className="max-w-lg text-[13px] leading-relaxed text-muted">
              <span className="font-semibold text-ink">Goal:</span> {goal}
            </p>
          </div>
        </div>

        {/* TODO: point to the real AI mentor chat route */}
        <Button
          asChild
          className="h-10 shrink-0 gap-2 rounded-full bg-primary px-5 text-[13px] font-semibold text-primary-foreground shadow-sm hover:bg-primary/90"
        >
          <Link to={localizedPath(mentorHref)}>
            <MessageSquare className="h-4 w-4" />
            Ask Mentor
          </Link>
        </Button>
      </div>

      <div className="mt-5 flex items-center gap-4 border-t border-border pt-5">
        <div className="flex-1">
          <div className="mb-1.5 flex items-center justify-between">
            <span className="text-[11px] font-semibold text-muted">COMPLETION</span>
            <span className="text-[13px] font-bold text-primary">{completion}%</span>
          </div>
          <div className="h-2.5 w-full overflow-hidden rounded-full bg-background">
            <motion.div
              initial={{ width: 0 }}
              whileInView={{ width: `${completion}%` }}
              viewport={{ once: true }}
              transition={{ duration: 0.8, ease: [0.16, 1, 0.3, 1] }}
              className="h-full rounded-full bg-gradient-to-r from-success to-success/70"
            />
          </div>
        </div>
        <div className="shrink-0 text-[12px] font-medium text-muted">
          {tasksCompleted} of {totalTasks} tasks complete
        </div>
      </div>
    </motion.div>
  );
}
