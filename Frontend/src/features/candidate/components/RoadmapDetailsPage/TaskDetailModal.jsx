import { Link } from "react-router-dom";
import { useLocalizedPath } from "@/utils/routes";
import { X, MessageSquare, CheckCircle2, Circle } from "lucide-react";
import { Button } from "@/components/ui/button";
import Modal from "@/components/shared/Modal";
import { useTranslation } from "react-i18next";

export default function TaskDetailModal({ task, onClose, onToggle }) {
  const { t } = useTranslation("common");
  const localizedPath = useLocalizedPath();

  return (
    <Modal onClose={onClose} maxWidth="max-w-[520px]" className="max-h-[88vh] overflow-y-auto">
      <div className="flex items-start justify-between border-b border-border p-6">
        <div>
          <div className="mb-1 text-[11px] font-semibold text-secondary">
            {t("ui.roadmapTask.targetSkill", { skill: task.targetSkill.toUpperCase() })}
          </div>
          <h2 className="font-display text-[17px] font-bold text-ink">{task.title}</h2>
        </div>
        <button
          type="button"
          onClick={onClose}
          className="flex h-9 w-9 shrink-0 items-center justify-center rounded-full transition-colors hover:bg-background"
        >
          <X className="h-[18px] w-[18px]" />
        </button>
      </div>

      <div className="space-y-5 p-6">
        <div>
          <div className="mb-1.5 text-[11px] font-semibold text-muted">{t("ui.roadmapTask.description")}</div>
          <p className="text-[13px] leading-relaxed text-muted">{task.description}</p>
        </div>

        <div className="rounded-2xl border border-primary/15 bg-primary/5 p-4">
          <div className="mb-1.5 text-[11px] font-semibold text-primary">
            {t("ui.roadmapTask.why")}
          </div>
          <p className="text-[13px] leading-relaxed text-muted">{task.why}</p>
        </div>

        <div>
          <div className="mb-1.5 text-[11px] font-semibold text-muted">{t("ui.roadmapTask.outcome")}</div>
          <p className="text-[13px] leading-relaxed text-muted">{task.outcome}</p>
        </div>

        <div>
          <div className="mb-1.5 text-[11px] font-semibold text-muted">{t("ui.roadmapTask.completionState")}</div>
          <div className="flex items-center gap-2">
            {task.done ? (
              <span className="inline-flex items-center gap-1.5 rounded-full bg-success/10 px-3 py-1.5 text-[12px] font-semibold text-success">
                <CheckCircle2 className="h-[14px] w-[14px]" />
                {t("ui.roadmapTask.completed")}
              </span>
            ) : (
              <span className="inline-flex items-center gap-1.5 rounded-full border border-border bg-background px-3 py-1.5 text-[12px] font-semibold text-muted">
                <Circle className="h-[14px] w-[14px]" />
                {t("ui.roadmapTask.notCompleted")}
              </span>
            )}
          </div>
        </div>
      </div>

      <div className="sticky bottom-0 flex items-center gap-3 rounded-b-3xl border-t border-border bg-surface p-6">
        {/* TODO: point to the real AI mentor chat route */}
        <Button
          asChild
          variant="outline"
          className="h-11 gap-1.5 rounded-full border-border px-4 text-[13px] font-semibold text-ink hover:bg-background"
        >
          <Link to={localizedPath("/dashboard/ai-chat")}>
            <MessageSquare className="h-4 w-4" />
            {t("ui.roadmapTask.askMentor")}
          </Link>
        </Button>

        <Button
          type="button"
          variant="outline"
          onClick={() => onToggle(task.id)}
          className="h-11 flex-1 rounded-full border-border text-[13px] font-semibold text-muted hover:bg-background"
        >
          {task.done ? t("ui.roadmapTask.undo") : t("ui.roadmapTask.markComplete")}
        </Button>
      </div>
    </Modal>
  );
}
