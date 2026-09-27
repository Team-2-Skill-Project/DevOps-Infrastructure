import { AnimatePresence, motion } from "framer-motion";
import { CheckCircle2, Sparkles } from "lucide-react";
import { Button } from "@/components/ui/button";

const ease = [0.16, 1, 0.3, 1];

/**
 * @typedef {Object} ExtractedSkill
 * @property {string} id
 * @property {string} name
 * @property {string} evidence
 * @property {boolean} confirmed
 */

/**
 * AI extraction review list + confirm actions.
 *
 * @param {{
 *   skills: ExtractedSkill[],
 *   onConfirmSkill: (id: string) => void,
 *   onConfirmAll: () => void,
 * }} props
 */
export default function CvExtractionReview({
  skills,
  onConfirmSkill,
  onConfirmAll,
}) {
  const allConfirmed = skills.length > 0 && skills.every((s) => s.confirmed);

  return (
    <motion.div
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay: 0.16, ease }}
      className="space-y-4"
    >
      <h3 className="flex items-center gap-2 text-base font-bold text-primary">
        <Sparkles className="h-5 w-5 text-accent" />
        Extraction Review
      </h3>

      <div className="space-y-3 rounded-xl border border-border bg-surface p-4">
        {skills.length === 0 ? (
          <p className="text-sm text-muted">
            No skills extracted yet. Upload a CV to get started.
          </p>
        ) : (
          <AnimatePresence mode="popLayout">
            {skills.map((skill, index) => (
              <SkillRow
                key={skill.id}
                skill={skill}
                index={index}
                onConfirm={() => onConfirmSkill(skill.id)}
              />
            ))}
          </AnimatePresence>
        )}
      </div>

      <div className="flex justify-end gap-3 pt-2">
        <Button
          type="button"
          onClick={onConfirmAll}
          disabled={allConfirmed || skills.length === 0}
          className="flex items-center gap-2 rounded-xl bg-success px-6 py-2.5 text-sm font-bold text-success-foreground hover:bg-success/90 disabled:opacity-50"
        >
          Confirm All Extracted Data
        </Button>
      </div>
    </motion.div>
  );
}

function SkillRow({ skill, index, onConfirm }) {
  return (
    <motion.div
      layout
      initial={{ opacity: 0, y: 10 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -6 }}
      transition={{ duration: 0.3, delay: index * 0.05, ease }}
      className="flex flex-col justify-between gap-2 rounded-lg bg-background p-3 text-sm md:flex-row md:items-center"
    >
      <div>
        <span className="font-bold text-ink">{skill.name}</span>
        <p className="text-xs text-muted">Evidence: {skill.evidence}</p>
      </div>

      <div className="flex items-center gap-2">
        <AnimatePresence mode="wait">
          {skill.confirmed ? (
            <motion.span
              key="confirmed"
              initial={{ opacity: 0, scale: 0.9 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.9 }}
              transition={{ duration: 0.18, ease }}
              className="flex items-center gap-1 text-xs font-bold text-success"
            >
              <CheckCircle2 className="h-4 w-4" />
              Confirmed
            </motion.span>
          ) : (
            <motion.div
              key="confirm-btn"
              initial={{ opacity: 0, scale: 0.9 }}
              animate={{ opacity: 1, scale: 1 }}
              exit={{ opacity: 0, scale: 0.9 }}
              transition={{ duration: 0.18, ease }}
            >
              <Button
                type="button"
                size="sm"
                onClick={onConfirm}
                className="rounded-lg bg-success px-3 py-1 text-xs font-bold text-success-foreground hover:bg-success/90"
              >
                Confirm Skill
              </Button>
            </motion.div>
          )}
        </AnimatePresence>
      </div>
    </motion.div>
  );
}
