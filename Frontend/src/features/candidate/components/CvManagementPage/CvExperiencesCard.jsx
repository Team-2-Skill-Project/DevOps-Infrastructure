import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
  Briefcase,
  Calendar,
  Building2,
  CheckCircle2,
  ChevronDown,
  ChevronUp,
} from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

const ease = [0.16, 1, 0.3, 1];

function formatYearMonth(val) {
  if (!val) return "";
  const parts = val.split("-");
  if (parts.length >= 2) {
    const year = parts[0];
    const monthIndex = parseInt(parts[1], 10) - 1;
    const months = [
      "Jan",
      "Feb",
      "Mar",
      "Apr",
      "May",
      "Jun",
      "Jul",
      "Aug",
      "Sep",
      "Oct",
      "Nov",
      "Dec",
    ];
    if (months[monthIndex]) {
      return `${months[monthIndex]} ${year}`;
    }
  }
  return val;
}

export default function CvExperiencesCard({
  experiences = [],
  onConfirmExperience,
}) {
  if (!experiences || experiences.length === 0) {
    return (
      <div className="rounded-2xl border border-border bg-surface p-5 text-center text-sm text-muted">
        No work experience detected in CV.
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
          <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
            <Briefcase className="h-5 w-5" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h3 className="text-base font-bold text-primary">
                Work Experience
              </h3>
              <Badge className="bg-secondary/10 text-secondary hover:bg-secondary/10 border-0 text-[11px] font-semibold">
                {experiences.length} Position{experiences.length > 1 ? "s" : ""}
              </Badge>
            </div>
            <p className="text-xs text-muted">
              Employment history, roles, responsibilities, and key achievements
            </p>
          </div>
        </div>
      </div>

      {/* List of Experiences */}
      <div className="space-y-3">
        {experiences.map((exp, index) => (
          <ExperienceItem
            key={exp.id || exp.company_name + index}
            experience={exp}
            index={index}
            onConfirm={() =>
              onConfirmExperience && onConfirmExperience(exp.id || index)
            }
          />
        ))}
      </div>
    </motion.div>
  );
}

function ExperienceItem({ experience, index, onConfirm }) {
  const [isExpanded, setIsExpanded] = useState(true);

  const startFormatted = formatYearMonth(experience.start_date);
  const endFormatted = experience.is_current
    ? "Present"
    : formatYearMonth(experience.end_date) || "End Date Unspecified";
  const dateRange = startFormatted
    ? `${startFormatted} — ${endFormatted}`
    : endFormatted;

  return (
    <motion.div
      layout
      initial={{ opacity: 0, y: 8 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.25, delay: index * 0.05, ease }}
      className="rounded-xl border border-border/80 bg-background/80 p-4 transition-all hover:border-border hover:shadow-xs"
    >
      {/* Top Header of the item */}
      <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div className="space-y-1">
          <div className="flex flex-wrap items-center gap-2">
            <h4 className="text-sm font-bold text-ink">
              {experience.job_title}
            </h4>
            {experience.is_current && (
              <Badge className="bg-success/15 text-success hover:bg-success/15 border-0 text-[10px] font-bold px-2 py-0.5">
                Current Role
              </Badge>
            )}
            {experience.employment_type && (
              <Badge className="bg-primary/5 text-primary hover:bg-primary/5 border border-primary/20 text-[10px] font-semibold capitalize px-2 py-0.5">
                {experience.employment_type}
              </Badge>
            )}
          </div>

          <div className="flex flex-wrap items-center gap-3 text-xs text-muted">
            <span className="flex items-center gap-1 font-semibold text-secondary">
              <Building2 className="h-3.5 w-3.5" />
              {experience.company_name}
            </span>
            <span className="flex items-center gap-1">
              <Calendar className="h-3.5 w-3.5 text-muted/70" />
              {dateRange}
            </span>
          </div>
        </div>

        <div className="flex items-center gap-2 self-end sm:self-start">
          {experience.confirmed ? (
            <span className="flex items-center gap-1 text-xs font-bold text-success">
              <CheckCircle2 className="h-4 w-4" />
              Confirmed
            </span>
          ) : (
            <Button
              type="button"
              size="sm"
              onClick={onConfirm}
              className="rounded-lg bg-success px-3 py-1 text-xs font-bold text-success-foreground hover:bg-success/90"
            >
              Confirm
            </Button>
          )}

          <button
            type="button"
            onClick={() => setIsExpanded((prev) => !prev)}
            className="flex h-7 w-7 items-center justify-center rounded-lg text-muted hover:bg-muted/10 hover:text-ink cursor-pointer"
            title={isExpanded ? "Collapse" : "Expand"}
          >
            {isExpanded ? (
              <ChevronUp className="h-4 w-4" />
            ) : (
              <ChevronDown className="h-4 w-4" />
            )}
          </button>
        </div>
      </div>

      {/* Description Content */}
      <AnimatePresence initial={false}>
        {isExpanded && experience.description && (
          <motion.div
            initial={{ opacity: 0, height: 0 }}
            animate={{ opacity: 1, height: "auto" }}
            exit={{ opacity: 0, height: 0 }}
            transition={{ duration: 0.2 }}
            className="mt-3 overflow-hidden border-t border-border/60 pt-3"
          >
            <p className="text-xs leading-relaxed text-ink/80 whitespace-pre-line">
              {experience.description}
            </p>

            {/* Technologies used if present */}
            {experience.technologies && experience.technologies.length > 0 && (
              <div className="mt-3 flex flex-wrap items-center gap-1.5">
                <span className="text-[11px] font-semibold text-muted">
                  Technologies:
                </span>
                {experience.technologies.map((tech, i) => (
                  <span
                    key={i}
                    className="rounded-md bg-surface px-2 py-0.5 text-[11px] font-medium text-primary border border-border"
                  >
                    {tech}
                  </span>
                ))}
              </div>
            )}
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  );
}
