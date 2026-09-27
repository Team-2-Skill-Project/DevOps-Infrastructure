import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { Brain, Bot, ArrowRight } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { useLocalizedPath } from "@/utils/routes";
import { useTranslation } from "react-i18next";

export default function AIMentorCard() {
  const localizedPath = useLocalizedPath();
  const { t } = useTranslation("common");
  const previewChips = [
    t("home.aiMentor.chips.mockInterviews"),
    t("home.aiMentor.chips.resumeGapAnalysis"),
    t("home.aiMentor.chips.skillRoadmap"),
    t("home.aiMentor.chips.salaryBenchmarking"),
  ];

  return (
    <motion.div
      initial={{ opacity: 0, y: 24 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.2 }}
      transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
      className="relative flex w-full flex-col items-center justify-between gap-8 overflow-hidden rounded-[20px] border border-primary-foreground/10 bg-gradient-to-br from-primary via-primary to-primary/90 p-8 text-primary-foreground shadow-[0_16px_36px_rgba(5,32,69,0.18)] lg:flex-row lg:p-12"
    >
      <div className="pointer-events-none absolute -bottom-20 -inset-e-20 h-96 w-96 rounded-full bg-secondary/15 blur-3xl" />

      <div className="z-10 flex max-w-xl flex-col gap-3">
        <Badge
          variant="outline"
          className="w-fit gap-1.5 self-start rounded-full border-primary-foreground/15 bg-primary-foreground/10 px-4 py-1 text-[11px] font-semibold uppercase tracking-wider text-accent backdrop-blur-md"
        >
          <Brain className="h-[18px] w-[18px]" />
          {t("home.aiMentor.badge")}
        </Badge>

        <h3 className="font-headline-xl text-[32px] font-bold tracking-tight text-primary-foreground sm:text-[40px]">
          {t("home.aiMentor.title")}
        </h3>

        <p className="font-body-lg leading-relaxed text-primary-foreground/80">
          {t("home.aiMentor.description")}
        </p>

        <div className="flex flex-wrap gap-2 pt-1">
          {previewChips.map((chip) => (
            <Badge
              key={chip}
              variant="outline"
              className="cursor-pointer rounded-lg border-primary-foreground/10 bg-primary-foreground/10 px-3 py-1.5 font-body-sm text-[12px] text-primary-foreground/90 backdrop-blur-sm transition-colors hover:bg-primary-foreground/20"
            >
              {chip}
            </Badge>
          ))}
        </div>
      </div>

      <div className="z-10 flex w-full flex-col items-center gap-3 sm:items-start lg:w-auto lg:items-end">
        <Button
          asChild
          className="group w-full gap-2 rounded-xl bg-surface px-8 py-6 font-headline-sm text-[14px] font-bold text-primary shadow-md hover:bg-background sm:w-auto"
        >
          {/* TODO: point to the real AI mentor route */}
          <Link to={localizedPath("/dashboard/ai-chat")}>
            <Bot className="h-5 w-5 text-secondary transition-transform group-hover:rotate-12" />
            {t("home.aiMentor.action")}
            <ArrowRight className="h-[17px] w-[17px] text-primary" />
          </Link>
        </Button>
        <span className="text-center font-body-sm text-[12px] text-primary-foreground/70 lg:text-right">
          {t("home.aiMentor.availability")}
        </span>
      </div>
    </motion.div>
  );
}
