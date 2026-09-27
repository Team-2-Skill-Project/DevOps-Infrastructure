import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import {
  ArrowRight,
  Terminal,
  Palette,
  Activity,
  BadgeCheck,
  Star,
  Bookmark,
  Hourglass,
} from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { useLocalizedPath } from "@/utils/routes";
import { useTranslation } from "react-i18next";

const RECOMMENDED_JOBS = [
  {
    id: "rec-1",
    title: "Senior Frontend Engineer",
    badgeText: "Top Match",
    company: "Vercel",
    location: "Remote (Global)",
    salary: "$145K – $175K + Equity",
    matchScore: 98,
    matchTone: "success", // success | accent
    icon: Terminal,
    iconTheme: "gradient", // gradient | neutral
    tags: ["Next.js", "TypeScript", "Edge Computing"],
  },
  {
    id: "rec-2",
    title: "Lead Frontend Architect",
    badgeText: "Direct Referral",
    company: "Stripe",
    location: "San Francisco, CA / Hybrid",
    salary: "$190K – $220K",
    matchScore: 95,
    matchTone: "success",
    icon: Palette,
    iconTheme: "neutral",
    tags: ["Design Systems", "React 19", "Fintech"],
  },
  {
    id: "rec-3",
    title: "Frontend Developer (UI/UX Systems)",
    badgeText: null,
    company: "PulseHealth",
    location: "Remote (US/EU)",
    salary: "$130K – $155K",
    matchScore: 89,
    matchTone: "accent",
    icon: Activity,
    iconTheme: "neutral",
    tags: ["Tailwind CSS", "Accessibility", "Figma Tokens"],
  },
];

export default function RecommendedJobsCard({
  jobs = RECOMMENDED_JOBS,
  isLoading = false,
  onApply,
  onBookmark,
}) {
  const localizedPath = useLocalizedPath();
  const { t } = useTranslation("dashboard");
  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.2 }}
      transition={{ duration: 0.5, delay: 0.15, ease: [0.16, 1, 0.3, 1] }}
    >
      <Card className="rounded-2xl border-border/80 bg-surface p-6 shadow-sm " id="recommended-jobs-card">
        <CardContent className="p-0">
          {/* Header */}
          <div className="mb-4 flex flex-col justify-between gap-2 sm:flex-row sm:items-center">
            <div className="flex items-center gap-2.5">
              <h2 className="font-serif text-[18px] font-bold text-primary">
                {t("overview.recommended.title")}
              </h2>
              <Badge
                variant="outline"
                className="gap-1 rounded-full border-primary/20 bg-primary/10 px-2 py-0.5 text-[10.5px] font-bold text-primary"
              >
                {t("overview.recommended.aiMatched")}
              </Badge>
            </div>
            <Link
              to={localizedPath("/dashboard/jobs")}
              className="flex items-center gap-1 text-[13px] font-semibold text-primary transition-colors hover:underline"
            >
              {t("overview.recommended.viewAll")}
              <ArrowRight className="h-3.5 w-3.5" />
            </Link>
          </div>

          {/* Content / List or Placeholder */}
          {isLoading ? (
            <div className="flex flex-col items-center justify-center py-8 text-center">
              <Hourglass className="mb-2 h-7 w-7 text-muted/70 animate-pulse" />
              <p className="max-w-xs text-[13px] text-muted">
                {t("overview.recommended.loading")}
              </p>
            </div>
          ) : (
            <div className="space-y-3">
              {jobs.map((job, index) => {
                const IconComponent = job.icon;
                const isSuccess = job.matchTone === "success";

                return (
                  <motion.div
                    key={job.id}
                    initial={{ opacity: 0, y: 12 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    viewport={{ once: true }}
                    transition={{
                      duration: 0.4,
                      delay: index * 0.08,
                      ease: [0.16, 1, 0.3, 1],
                    }}
                    whileHover={{ y: -2 }}
                    className="group cursor-pointer rounded-xl border border-border/80 bg-surface p-4 shadow-xs transition-all hover:border-primary/40 hover:bg-background/60"
                  >
                    {/* Top Row: Icon, Titles, and Match Percentage */}
                    <div className="mb-2.5 flex items-start justify-between gap-3">
                      <div className="flex min-w-0 items-center gap-3.5">
                        {/* Company / Role Icon */}
                        <div
                          className={
                            job.iconTheme === "gradient"
                              ? "flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-primary to-primary/80 text-primary-foreground shadow-sm"
                              : "flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-border bg-background text-primary"
                          }
                        >
                          <IconComponent className="h-[22px] w-[22px]" />
                        </div>

                        {/* Title & Metadata */}
                        <div className="min-w-0">
                          <div className="flex flex-wrap items-center gap-2">
                            <h3 className="truncate text-[14.5px] font-bold text-ink transition-colors group-hover:text-primary">
                              {job.title}
                            </h3>
                            {job.badgeText && (
                              <Badge
                                variant="outline"
                                className="rounded border-success/25 bg-success/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-success"
                              >
                                {job.badgeText}
                              </Badge>
                            )}
                          </div>
                          <p className="mt-0.5 text-[12px] text-muted">
                            {job.company} · {job.location} · {job.salary}
                          </p>
                        </div>
                      </div>

                      {/* Match Score Radar */}
                      <div className="flex shrink-0 flex-col items-end">
                        <div
                          className={`flex items-center gap-1 rounded-lg border px-2.5 py-1 text-[13px] font-bold ${
                            isSuccess
                              ? "border-success/25 bg-success/10 text-success"
                              : "border-accent/25 bg-accent/10 text-accent"
                          }`}
                        >
                          {isSuccess ? (
                            <BadgeCheck className="h-3.5 w-3.5" />
                          ) : (
                            <Star className="h-3.5 w-3.5 fill-accent" />
                          )}
                          <span>{job.matchScore}%</span>
                        </div>
                        <span className="mt-1 text-[10px] font-medium text-muted/80">
                          {t("overview.recommended.radarAlignment")}
                        </span>
                      </div>
                    </div>

                    {/* Bottom Row: Tags & Quick Actions */}
                    <div className="flex flex-wrap items-center justify-between gap-2 border-t border-border/40 pt-2">
                      <div className="flex flex-wrap items-center gap-1.5">
                        {job.tags.map((tag) => (
                          <span
                            key={tag}
                            className="rounded-md border border-border/60 bg-background px-2 py-0.5 text-[11px] font-medium text-muted"
                          >
                            {tag}
                          </span>
                        ))}
                      </div>

                      <div className="flex items-center gap-2">
                        <Button
                          type="button"
                          variant="ghost"
                          size="icon"
                          onClick={(e) => {
                            e.stopPropagation();
                            onBookmark?.(job);
                          }}
                          className="h-8 w-8 rounded-lg text-muted transition-colors hover:bg-background hover:text-primary"
                          title={t("overview.recommended.bookmark")}
                        >
                          <Bookmark className="h-4 w-4" />
                        </Button>

                        <Button
                          type="button"
                          size="sm"
                          onClick={(e) => {
                            e.stopPropagation();
                            onApply?.(job);
                          }}
                          className="h-7 rounded-lg bg-primary px-3 text-[12px] font-semibold text-primary-foreground shadow-xs transition-colors hover:bg-primary/90"
                        >
                          {t("overview.recommended.apply")}
                        </Button>
                      </div>
                    </div>
                  </motion.div>
                );
              })}
            </div>
          )}
        </CardContent>
      </Card>
    </motion.div>
  );
}
