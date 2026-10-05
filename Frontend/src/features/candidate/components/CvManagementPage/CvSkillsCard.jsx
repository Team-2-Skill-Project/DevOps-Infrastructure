import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import {
  Sparkles,
  CheckCircle2,
  Search,
  Quote,
  SlidersHorizontal,
  ChevronDown,
  ChevronUp,
} from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

const ease = [0.16, 1, 0.3, 1];

export default function CvSkillsCard({ skills = [], onConfirmSkill }) {
  const [searchQuery, setSearchQuery] = useState("");
  const [filterType, setFilterType] = useState("all"); // 'all', 'high', 'moderate'

  const filteredSkills = skills.filter((skill) => {
    const matchesSearch = skill.name
      ?.toLowerCase()
      .includes(searchQuery.toLowerCase());
    if (!matchesSearch) return false;

    const conf = typeof skill.confidence === "number" ? skill.confidence : 0.8;
    if (filterType === "high") return conf >= 0.8;
    if (filterType === "moderate") return conf < 0.8;
    return true;
  });

  const highConfCount = skills.filter(
    (s) => (typeof s.confidence === "number" ? s.confidence : 0.8) >= 0.8,
  ).length;
  const moderateConfCount = skills.length - highConfCount;

  return (
    <motion.div
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.35, ease }}
      className="space-y-4 rounded-2xl border border-border bg-surface p-5 shadow-xs"
    >
      {/* Header */}
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-border/70 pb-4">
        <div className="flex items-center gap-2">
          <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-accent/15 text-accent">
            <Sparkles className="h-5 w-5" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h3 className="text-base font-bold text-primary">
                Extracted Skills & Competencies
              </h3>
              <Badge className="bg-accent/15 text-ink hover:bg-accent/15 border-0 text-[11px] font-semibold">
                {skills.length} Skills
              </Badge>
            </div>
            <p className="text-xs text-muted">
              Skills identified with AI confidence scores and document evidence citations
            </p>
          </div>
        </div>

        {/* Filter Chips */}
        <div className="flex flex-wrap items-center gap-1.5 text-xs">
          <button
            type="button"
            onClick={() => setFilterType("all")}
            className={`rounded-lg px-2.5 py-1 text-xs font-semibold transition-colors cursor-pointer ${
              filterType === "all"
                ? "bg-primary text-primary-foreground"
                : "bg-background text-muted hover:text-ink"
            }`}
          >
            All ({skills.length})
          </button>
          <button
            type="button"
            onClick={() => setFilterType("high")}
            className={`rounded-lg px-2.5 py-1 text-xs font-semibold transition-colors cursor-pointer ${
              filterType === "high"
                ? "bg-success text-success-foreground"
                : "bg-background text-muted hover:text-ink"
            }`}
          >
            High Confidence ({highConfCount})
          </button>
          <button
            type="button"
            onClick={() => setFilterType("moderate")}
            className={`rounded-lg px-2.5 py-1 text-xs font-semibold transition-colors cursor-pointer ${
              filterType === "moderate"
                ? "bg-warning text-warning-foreground"
                : "bg-background text-muted hover:text-ink"
            }`}
          >
            Moderate ({moderateConfCount})
          </button>
        </div>
      </div>

      {/* Search Bar */}
      <div className="relative">
        <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
        <input
          type="text"
          value={searchQuery}
          onChange={(e) => setSearchQuery(e.target.value)}
          placeholder="Search extracted skills (e.g. Sales, CRM, Operations)..."
          className="w-full rounded-xl border border-border/80 bg-background/80 py-2 pl-9 pr-4 text-xs font-medium text-ink placeholder:text-muted focus:border-primary focus:bg-surface focus:outline-none transition-all"
        />
      </div>

      {/* Skills List */}
      <div className="space-y-2.5">
        {filteredSkills.length === 0 ? (
          <div className="rounded-xl border border-dashed border-border py-8 text-center text-xs text-muted">
            No skills matching your criteria.
          </div>
        ) : (
          <AnimatePresence mode="popLayout">
            {filteredSkills.map((skill, index) => (
              <SkillCardItem
                key={skill.skill_id || skill.id || skill.name + index}
                skill={skill}
                index={index}
                onConfirm={() =>
                  onConfirmSkill &&
                  onConfirmSkill(skill.skill_id || skill.id || index)
                }
              />
            ))}
          </AnimatePresence>
        )}
      </div>
    </motion.div>
  );
}

function SkillCardItem({ skill, index, onConfirm }) {
  const [showAllEvidence, setShowAllEvidence] = useState(false);

  // Confidence normalization (e.g. 0.95 -> 95%, 95 -> 95%)
  const confValue =
    typeof skill.confidence === "number"
      ? skill.confidence <= 1
        ? Math.round(skill.confidence * 100)
        : Math.round(skill.confidence)
      : null;

  const isHighConfidence = confValue ? confValue >= 80 : true;

  // Evidence normalization (array of objects or simple string)
  let evidenceList = [];
  if (Array.isArray(skill.evidence)) {
    evidenceList = skill.evidence;
  } else if (typeof skill.evidence === "string") {
    evidenceList = [{ text: skill.evidence, section: "CV Content" }];
  }

  const primaryEvidence = evidenceList[0];
  const extraEvidence = evidenceList.slice(1);

  return (
    <motion.div
      layout
      initial={{ opacity: 0, y: 8 }}
      animate={{ opacity: 1, y: 0 }}
      exit={{ opacity: 0, y: -6 }}
      transition={{ duration: 0.25, delay: Math.min(index * 0.02, 0.2), ease }}
      className="rounded-xl border border-border/70 bg-background/80 p-3.5 transition-all hover:border-border hover:shadow-xs"
    >
      <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
        <div className="space-y-1.5 flex-1">
          <div className="flex flex-wrap items-center gap-2">
            <span className="text-sm font-bold text-ink">{skill.name}</span>

            {confValue !== null && (
              <Badge
                className={`border-0 text-[10px] font-bold px-2 py-0.5 ${
                  isHighConfidence
                    ? "bg-success/15 text-success hover:bg-success/15"
                    : "bg-warning/15 text-warning hover:bg-warning/15"
                }`}
              >
                {confValue}% Confidence
              </Badge>
            )}

            {skill.proficiency && (
              <Badge className="bg-primary/5 text-primary border border-primary/20 text-[10px] font-semibold px-2 py-0.5">
                {skill.proficiency}
              </Badge>
            )}

            {skill.years_of_experience && (
              <span className="text-[11px] text-muted font-medium">
                {skill.years_of_experience} yr
                {skill.years_of_experience > 1 ? "s" : ""} exp
              </span>
            )}
          </div>

          {/* Primary Evidence */}
          {primaryEvidence && (
            <div className="flex items-start gap-1.5 text-xs text-muted">
              <Quote className="h-3 w-3 shrink-0 text-accent mt-0.5" />
              <p className="line-clamp-2 italic text-ink/80 text-[11px]">
                "{primaryEvidence.text}"
                {primaryEvidence.section && (
                  <span className="ml-1.5 not-italic font-semibold text-secondary uppercase text-[10px]">
                    [{primaryEvidence.section}]
                  </span>
                )}
              </p>
            </div>
          )}

          {/* Expandable Extra Evidence if multiple */}
          {extraEvidence.length > 0 && (
            <div>
              <button
                type="button"
                onClick={() => setShowAllEvidence((prev) => !prev)}
                className="inline-flex items-center gap-1 text-[11px] font-semibold text-primary hover:underline cursor-pointer pt-0.5"
              >
                <span>
                  {showAllEvidence
                    ? "Hide extra citations"
                    : `+${extraEvidence.length} more evidence citation${
                        extraEvidence.length > 1 ? "s" : ""
                      }`}
                </span>
                {showAllEvidence ? (
                  <ChevronUp className="h-3 w-3" />
                ) : (
                  <ChevronDown className="h-3 w-3" />
                )}
              </button>

              <AnimatePresence>
                {showAllEvidence && (
                  <motion.div
                    initial={{ opacity: 0, height: 0 }}
                    animate={{ opacity: 1, height: "auto" }}
                    exit={{ opacity: 0, height: 0 }}
                    className="mt-2 space-y-1.5 border-t border-border/50 pt-2"
                  >
                    {extraEvidence.map((ev, i) => (
                      <div
                        key={i}
                        className="flex items-start gap-1.5 text-[11px] text-muted italic"
                      >
                        <span className="text-accent">•</span>
                        <p className="text-ink/80">
                          "{ev.text}"
                          {ev.section && (
                            <span className="ml-1 not-italic font-semibold text-secondary text-[9px] uppercase">
                              [{ev.section}]
                            </span>
                          )}
                        </p>
                      </div>
                    ))}
                  </motion.div>
                )}
              </AnimatePresence>
            </div>
          )}
        </div>

        {/* Action Button */}
        <div className="flex items-center gap-2 shrink-0 self-end sm:self-start">
          {skill.confirmed ? (
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
              Confirm Skill
            </Button>
          )}
        </div>
      </div>
    </motion.div>
  );
}
