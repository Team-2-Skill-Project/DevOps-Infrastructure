import { motion } from "framer-motion";
import { Button } from "@/components/ui/button";
import { Card, CardContent } from "@/components/ui/card";
import { DEFAULT_COMPETENCY_RADAR } from "@/constants/competencyRadar";
import { useTranslation } from "react-i18next";

const RADIUS_OUTER = 50;
const RADIUS_INNER = 38;
const CIRCUMFERENCE_OUTER = 2 * Math.PI * RADIUS_OUTER;
const CIRCUMFERENCE_INNER = 2 * Math.PI * RADIUS_INNER;

const SKILL_COLOR_CLASSES = {
  primary: { dot: "bg-primary", value: "text-primary" },
  success: { dot: "bg-success", value: "text-success" },
  accent: { dot: "bg-accent", value: "text-accent" },
};

// Turns any 0-100 score into the matching stroke-dashoffset for a ring of
// the given circumference — this is what makes the meter work with
// whatever score comes back from the API, not just the two numbers that
// happened to be in the original mockup.
function ringOffset(circumference, percent) {
  const clamped = Math.min(Math.max(percent, 0), 100);
  return circumference * (1 - clamped / 100);
}

/**
 * data shape: { overallScore, secondaryScore, comparisonLabel, benchmarkLabel,
 * skills: [{ label, value, color: "primary"|"success"|"accent" }] }
 * Pass any number of skills — the list and the two rings are fully driven by data.
 */
export default function CompetencyRadarCard({
  data = DEFAULT_COMPETENCY_RADAR,
  onRecalibrate,
}) {
  const { t } = useTranslation("dashboard");
  const { overallScore, secondaryScore, comparisonLabel, benchmarkLabel, skills } = data;

  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.2 }}
      transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
    >
      <Card className="relative overflow-hidden rounded-2xl border-border/80 bg-surface p-6 shadow-sm">
        <CardContent className="p-0">
          <div className="mb-4 flex items-center justify-between">
            <div>
              <h2 className="font-serif text-[17px] font-bold text-primary">{t("overview.competency.title")}</h2>
              <p className="text-[12px] text-muted">{benchmarkLabel}</p>
            </div>
            <span className="font-mono text-[16px] font-bold text-primary">{overallScore}%</span>
          </div>

          {/* Radar meter */}
          <div className="relative my-3 flex items-center justify-center py-2">
            <svg className="h-48 w-48 -rotate-90 transform" viewBox="0 0 120 120">
              <circle
                cx="60"
                cy="60"
                r={RADIUS_OUTER}
                fill="none"
                stroke="var(--color-border)"
                strokeWidth="10"
              />
              <motion.circle
                cx="60"
                cy="60"
                r={RADIUS_OUTER}
                fill="none"
                stroke="var(--color-primary)"
                strokeWidth="10"
                strokeLinecap="round"
                strokeDasharray={CIRCUMFERENCE_OUTER}
                initial={{ strokeDashoffset: CIRCUMFERENCE_OUTER }}
                whileInView={{ strokeDashoffset: ringOffset(CIRCUMFERENCE_OUTER, overallScore) }}
                viewport={{ once: true }}
                transition={{ duration: 1, ease: [0.16, 1, 0.3, 1] }}
              />
              <motion.circle
                cx="60"
                cy="60"
                r={RADIUS_INNER}
                fill="none"
                stroke="var(--color-success)"
                strokeWidth="4"
                strokeLinecap="round"
                opacity="0.85"
                strokeDasharray={CIRCUMFERENCE_INNER}
                initial={{ strokeDashoffset: CIRCUMFERENCE_INNER }}
                whileInView={{
                  strokeDashoffset: ringOffset(CIRCUMFERENCE_INNER, secondaryScore),
                }}
                viewport={{ once: true }}
                transition={{ duration: 1, delay: 0.15, ease: [0.16, 1, 0.3, 1] }}
              />
            </svg>

            <div className="absolute inset-0 flex flex-col items-center justify-center text-center">
              <span className="font-serif text-[26px] font-bold leading-none text-primary">
                {overallScore}%
              </span>
              <span className="mt-1 text-[11px] font-bold uppercase tracking-wider text-muted">
                {t("overview.competency.profileFit")}
              </span>
              <span className="mt-0.5 text-[10px] font-semibold text-success">
                {comparisonLabel}
              </span>
            </div>
          </div>

          {/* Skill breakdown */}
          <div className="mt-2 space-y-2 border-t border-border/60 pt-3">
            {skills.map((skill) => {
              const colorClasses = SKILL_COLOR_CLASSES[skill.color] ?? SKILL_COLOR_CLASSES.primary;
              return (
                <div key={skill.label} className="flex items-center justify-between text-[12px]">
                  <span className="flex items-center gap-1.5 text-muted">
                    <span className={`h-2 w-2 rounded-full ${colorClasses.dot}`} />
                    {skill.label}
                  </span>
                  <span className={`font-bold ${colorClasses.value}`}>{skill.value}%</span>
                </div>
              );
            })}
          </div>

          <Button
            type="button"
            variant="outline"
            onClick={onRecalibrate}
            className="mt-4 w-full rounded-xl border-border/80 bg-background py-2 text-[12.5px] font-bold text-primary hover:bg-border/10"
          >
            {t("overview.competency.recalibrate")}
          </Button>
        </CardContent>
      </Card>
    </motion.div>
  );
}
