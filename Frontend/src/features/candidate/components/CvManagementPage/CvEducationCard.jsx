import { motion } from "framer-motion";
import { GraduationCap, School, CheckCircle2 } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

const ease = [0.16, 1, 0.3, 1];

export default function CvEducationCard({
  educations = [],
  onConfirmEducation,
}) {
  if (!educations || educations.length === 0) {
    return (
      <div className="rounded-2xl border border-border bg-surface p-5 text-center text-sm text-muted">
        No education records detected in CV.
      </div>
    );
  }

  return (
    <motion.div
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.35, ease }}
      className="space-y-4 rounded-2xl border border-border bg-surface p-5 shadow-xs"
    >
      {/* Header */}
      <div className="flex items-center justify-between border-b border-border/70 pb-4">
        <div className="flex items-center gap-2">
          <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-accent/15 text-accent">
            <GraduationCap className="h-5 w-5" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h3 className="text-base font-bold text-primary">
                Education & Academics
              </h3>
              <Badge className="bg-accent/15 text-ink hover:bg-accent/15 border-0 text-[11px] font-semibold">
                {educations.length} Degree{educations.length > 1 ? "s" : ""}
              </Badge>
            </div>
            <p className="text-xs text-muted">
              Academic credentials, degrees, and university programs
            </p>
          </div>
        </div>
      </div>

      {/* List of Education */}
      <div className="space-y-3">
        {educations.map((edu, index) => (
          <motion.div
            key={edu.id || edu.institution + index}
            layout
            initial={{ opacity: 0, y: 8 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.25, delay: index * 0.05, ease }}
            className="rounded-xl border border-border/80 bg-background/80 p-4 transition-all hover:border-border hover:shadow-xs"
          >
            <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
              <div className="space-y-1">
                <div className="flex flex-wrap items-center gap-2">
                  <h4 className="text-sm font-bold text-ink">
                    {edu.degree}
                    {edu.field_of_study ? ` in ${edu.field_of_study}` : ""}
                  </h4>
                  <Badge className="bg-primary/5 text-primary border border-primary/20 text-[10px] font-semibold">
                    Degree
                  </Badge>
                </div>

                <div className="flex items-center gap-2 text-xs font-semibold text-secondary">
                  <School className="h-3.5 w-3.5" />
                  <span>{edu.institution}</span>
                </div>
              </div>

              <div>
                {edu.confirmed ? (
                  <span className="flex items-center gap-1 text-xs font-bold text-success">
                    <CheckCircle2 className="h-4 w-4" />
                    Confirmed
                  </span>
                ) : (
                  <Button
                    type="button"
                    size="sm"
                    onClick={() =>
                      onConfirmEducation &&
                      onConfirmEducation(edu.id || index)
                    }
                    className="rounded-lg bg-success px-3 py-1 text-xs font-bold text-success-foreground hover:bg-success/90"
                  >
                    Confirm
                  </Button>
                )}
              </div>
            </div>

            {edu.description && (
              <div className="mt-3 border-t border-border/60 pt-3">
                <p className="text-xs leading-relaxed text-ink/80 whitespace-pre-line">
                  {edu.description}
                </p>
              </div>
            )}
          </motion.div>
        ))}
      </div>
    </motion.div>
  );
}
