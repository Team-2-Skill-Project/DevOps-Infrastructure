import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { useLocalizedPath } from "@/utils/routes";
import {
  Rocket,
  Upload,
  Compass,
  ArrowRight,
  CheckCircle2,
} from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { useTranslation } from "react-i18next";

const TRUST_POINTS = [
  "Instant AI skill scoring",
  "No credit card required",
  "SOC-2 & ISO 27001 Certified",
];

export default function FinalCTASection() {
  const localizedPath = useLocalizedPath();
  const { t } = useTranslation("common");
  return (
    <section
      className="mx-auto w-full max-w-[1280px] px-6 py-16 md:px-10 lg:px-16 lg:py-24"
      id="get-started"
    >
      <motion.div
        initial={{ opacity: 0, y: 24 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true, amount: 0.3 }}
        transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
        className="relative flex w-full flex-col items-center overflow-hidden rounded-[24px] border border-border bg-surface p-8 text-center shadow-[0_12px_32px_rgba(27,28,26,0.06)] sm:p-12 lg:p-16"
      >
        <div className="pointer-events-none absolute -inset-e-20 -top-20 h-80 w-80 rounded-full bg-secondary/30 blur-3xl" />
        <div className="pointer-events-none absolute -bottom-20 -inset-s-20 h-80 w-80 rounded-full bg-primary/5 blur-3xl" />

        <div className="relative z-10 flex max-w-3xl flex-col items-center">
          <Badge
            variant="outline"
            className="mb-4 gap-1.5 rounded-full border-border bg-background px-4 py-1 text-[11px] font-semibold uppercase tracking-wider text-primary"
          >
            <Rocket className="h-[17px] w-[17px] text-secondary" />
            {t("home.cta.badge")}
          </Badge>

          <h2 className="mb-3 font-headline-xl text-[34px] font-bold leading-tight tracking-tight text-ink sm:text-[42px] lg:text-[48px]">
            {t("home.cta.title")}
          </h2>

          <p className="mb-8 max-w-2xl font-body-lg leading-relaxed text-muted">
            {t("home.cta.description")}
          </p>

          <div className="mb-8 flex w-full flex-col items-center justify-center gap-4 sm:w-auto sm:flex-row">
            <Button
              asChild
              size="lg"
              className="group w-full gap-2 rounded-xl bg-primary px-8 py-[14px] font-headline-sm text-[15px] font-semibold text-primary-foreground shadow-md hover:bg-primary/90 hover:shadow-lg sm:w-auto"
            >
              {/* TODO: point to the real upload-CV route */}
              <Link to={localizedPath("/upload-cv")}>
                <Upload className="h-5 w-5 transition-transform group-hover:-translate-y-0.5" />
                {t("home.cta.uploadCv")}
              </Link>
            </Button>

            <Button
              asChild
              variant="outline"
              size="lg"
              className="group w-full gap-2 rounded-xl border-border bg-surface px-8 py-[14px] font-headline-sm text-[15px] font-semibold text-primary shadow-sm hover:bg-background sm:w-auto"
            >
              <Link to={localizedPath("/dashboard/jobs")}>
                <Compass className="h-5 w-5 text-muted transition-colors group-hover:text-primary" />
                {t("home.cta.browseJobs")}
                <ArrowRight className="h-[18px] w-[18px] transition-transform group-hover:translate-x-1" />
              </Link>
            </Button>
          </div>

          <div className="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 font-body-sm text-[12px] text-muted">
            {TRUST_POINTS.map((point) => (
              <div key={point} className="flex items-center gap-1.5">
                <CheckCircle2 className="h-4 w-4 fill-success text-surface" />
                <span>{t(`home.cta.trust.${point}`)}</span>
              </div>
            ))}
          </div>
        </div>
      </motion.div>
    </section>
  );
}
