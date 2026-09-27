import { AnimatePresence, motion } from "framer-motion";
import { Check, Edit3, Zap } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Textarea } from "@/components/ui/textarea";

export function JobCardSkeleton() {
  return (
    <div className="animate-pulse rounded-2xl border border-border bg-surface p-4 shadow-xs sm:p-6">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div className="flex min-w-0 items-start gap-3 sm:gap-4">
          <div className="mt-4 size-4 shrink-0 rounded bg-border" />
          <div className="size-13 shrink-0 rounded-xl bg-border" />
          <div className="min-w-0 flex-1 space-y-2">
            <div className="h-5 w-3/4 rounded bg-border" />
            <div className="h-3 w-full rounded bg-border/70" />
            <div className="h-3 w-2/3 rounded bg-border/70" />
          </div>
        </div>
        <div className="h-7 w-28 self-end rounded-full bg-border sm:self-auto" />
      </div>
      <div className="mt-5 flex flex-wrap gap-2">
        <div className="h-6 w-20 rounded-md bg-border/70" />
        <div className="h-6 w-24 rounded-md bg-border/70" />
        <div className="h-6 w-28 rounded-md bg-border/70" />
      </div>
      <div className="mt-5 space-y-2 border-t border-border pt-4">
        <div className="h-3 w-full rounded bg-border/60" />
        <div className="h-3 w-4/5 rounded bg-border/60" />
      </div>
    </div>
  );
}

export default function JobCard({
  job,
  aiState,
  selected,
  expanded,
  editingNote,
  noteValue,
  onSelect,
  onExpand,
  onApply,
  onStartEdit,
  onSaveNote,
  onCancelNote,
  onNoteChange,
  onAction,
}) {
  const isDimmed = aiState === "low" && job.fitScore < 75;

  return (
    <motion.div
      layout
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: isDimmed ? 0.45 : 1, y: 0 }}
      exit={{ opacity: 0, scale: 0.95 }}
      className={`bg-surface border border-border rounded-2xl p-4 sm:p-6 shadow-xs group relative ${isDimmed ? "filter grayscale-[0.4]" : ""}`}
    >
      <div className="flex flex-col lg:flex-row items-start justify-between gap-4 mb-4">
        <div className="flex items-start gap-3 sm:gap-4 min-w-0 w-full">
          <Checkbox
            checked={selected}
            onCheckedChange={onSelect}
            className="mt-4"
          />
          <div className="w-13 h-13 rounded-xl bg-background border border-border flex items-center justify-center p-2.5 text-primary font-bold text-lg shrink-0">
            {job.logo}
          </div>
          <div className="min-w-0">
            <div className="flex items-center gap-2 mb-1 flex-wrap">
              <h3 className="font-dm-sans text-lg sm:text-xl font-bold text-primary wrap-break-word">
                {job.title}
              </h3>
              <span className="bg-background text-accent border border-border text-[11px] font-semibold px-2 py-0.5 rounded-md">
                {job.badge}
              </span>
            </div>
            <div className="flex items-center gap-2 sm:gap-3 text-xs text-muted flex-wrap">
              <span className="font-semibold text-primary">{job.company}</span>
              <span>•</span>
              <span>{job.location}</span>
              <span>•</span>
              <span className="font-medium text-primary">{job.salary}</span>
              {job.verification && (
                <span className="text-[11px] bg-background border border-border px-2 py-0.5 rounded wrap-break-word">
                  {job.verification}
                </span>
              )}
            </div>
          </div>
        </div>
        <div className="text-left lg:text-right shrink-0 self-end lg:self-auto">
          <div className="inline-flex items-center gap-1.5 bg-primary text-primary-foreground px-3 py-1 rounded-full text-xs font-semibold">
            <Check className="w-3.5 h-3.5" /> {job.fitFraction} ({job.fitScore}
            %)
          </div>
          <span className="block text-[10px] text-muted mt-1">
            {job.fitLabel}
          </span>
        </div>
      </div>

      <div className="flex flex-wrap items-center gap-1.5 mb-4">
        {job.skills.map((skill) => (
          <span
            key={skill}
            className="bg-background border border-border text-primary text-xs font-semibold px-2.5 py-1 rounded-md"
          >
            {skill}
          </span>
        ))}
        {job.extraSkillsCount > 0 && (
          <span className="text-muted text-xs px-2 py-1 rounded-md">
            +{job.extraSkillsCount} bonus skills
          </span>
        )}
      </div>

      <AnimatePresence>
        {expanded && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: "auto", opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            className="overflow-hidden border border-border rounded-xl bg-background p-4 text-xs space-y-3 mb-4"
          >
            <div>
              <strong className="text-primary block mb-1">Description</strong>
              <p className="text-muted wrap-break-word">{job.description}</p>
            </div>
            <div>
              <strong className="text-primary block mb-1">
                Key Responsibilities
              </strong>
              <ul className="text-muted list-disc ps-4">
                {job.responsibilities.map((item) => (
                  <li key={item}>{item}</li>
                ))}
              </ul>
            </div>
            <div className="flex flex-wrap gap-2 pt-2 border-t border-border">
              <span className="text-muted">Source: {job.source}</span>
              <span className="text-muted">Method: {job.method}</span>
              <span className="text-muted">Experience: {job.experience}</span>
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {job.rationaleTitle && (
        <div className="bg-background border border-border rounded-xl p-3.5 mb-4 text-xs">
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 border-b border-border pb-2">
            <strong className="text-primary wrap-break-word">
              {job.rationaleTitle}
            </strong>
            <span className="text-muted wrap-break-word">
              {job.rationaleSub}
            </span>
          </div>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-2 pt-2">
            <p className="bg-surface p-2.5 text-muted wrap-break-word">
              {job.strength}
            </p>
            <div className="bg-surface p-2.5 flex flex-col sm:flex-row sm:items-center gap-2 min-w-0">
              <p className="text-muted wrap-break-word">{job.gap}</p>
              {job.gapActionText && (
                <Button
                  onClick={() => onAction(job)}
                  className="text-[11px] text-secondary shrink-0"
                >
                  {job.gapActionText}
                </Button>
              )}
            </div>
          </div>
        </div>
      )}

      {editingNote ? (
        <div className="border border-border rounded-xl p-3 mb-4 bg-surface">
          <Textarea
            rows={2}
            value={noteValue}
            onChange={(event) => onNoteChange(event.target.value)}
            className="min-h-20 w-full resize-none text-xs leading-relaxed whitespace-normal"
            placeholder="Write a private note or salary target..."
          />
          <div className="flex flex-wrap justify-end gap-2 mt-2">
            <Button onClick={onCancelNote}>Cancel</Button>
            <Button onClick={() => onSaveNote(job.id)}>Save Note</Button>
          </div>
        </div>
      ) : job.note ? (
        <div className="bg-background border border-border rounded-xl p-3 mb-4 text-xs flex items-center justify-between gap-2">
          <span className="min-w-0 flex-1 truncate">
            <Edit3 className="w-3.5 h-3.5 inline me-2" />
            <strong>Candidate Note:</strong> {job.note}
          </span>
          <Button
            onClick={() => onStartEdit(job)}
            className="text-[11px] text-secondary shrink-0"
          >
            Edit
          </Button>
        </div>
      ) : (
        <Button
          onClick={() => onStartEdit(job)}
          className="h-auto min-h-14 w-full items-start justify-between gap-3 whitespace-normal border border-dashed border-border rounded-xl p-3 mb-4 text-left text-xs text-muted"
        >
          <span className="min-w-0 flex-1 italic wrap-break-word">
            + Add a private note or salary target for this position...
          </span>
          <span className="shrink-0 self-end text-secondary sm:self-center">
            Add Note
          </span>
        </Button>
      )}

      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between pt-3 border-t border-border text-xs gap-3">
        <span
          className={`wrap-break-word ${job.status === "applied" ? "text-success font-semibold" : "text-muted"}`}
        >
          {job.status === "applied" ? job.appliedText : job.deadline}
        </span>
        <div className="flex w-full sm:w-auto flex-wrap gap-2.5">
          <Button
            onClick={onExpand}
            className="flex-1 sm:flex-none border border-border bg-background text-primary"
          >
            {expanded ? "Hide Details" : "View Details"}
          </Button>
          {job.status === "applied" ? (
            <Button
              disabled
              className="flex-1 sm:flex-none bg-success text-success-foreground"
            >
              <Check className="w-3.5 h-3.5" />
              Applied
            </Button>
          ) : (
            <Button
              onClick={() => onApply(job.id)}
              className="flex-1 sm:flex-none bg-secondary text-secondary-foreground"
            >
              <Zap className="w-3.5 h-3.5" />
              Apply Fast Track
            </Button>
          )}
        </div>
      </div>
    </motion.div>
  );
}
