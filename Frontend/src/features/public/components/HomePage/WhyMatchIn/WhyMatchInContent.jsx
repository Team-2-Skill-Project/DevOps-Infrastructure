import { motion } from "framer-motion";
import { ArrowRight, BadgeCheck, Zap, Gauge, Lock } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { useTranslation } from "react-i18next";

const BENEFITS = [
  {
    icon: BadgeCheck,
    iconClass: "bg-success/15 text-success",
    key: "verifiedJobs",
  },
  {
    icon: Zap,
    iconClass: "bg-secondary/15 text-secondary",
    key: "aiMatching",
  },
  {
    icon: Gauge,
    iconClass: "bg-accent/10 text-accent",
    key: "fastResults",
  },
  {
    icon: Lock,
    iconClass: "bg-primary/10 text-primary",
    key: "privacyFirst",
  },
];

export default function WhyMatchInContent() {
  const { t } = useTranslation("common");

  return (
    <div className="flex flex-col gap-4 lg:col-span-6">
      <motion.div
        initial={{ opacity: 0, y: 12 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.5 }}
      >
        <Badge
          variant="outline"
          className="w-fit self-start rounded-full border-border bg-surface px-4 py-1 text-[11px] font-semibold uppercase tracking-wider text-secondary"
        >
          {t("home.benefits.badge")}
        </Badge>
      </motion.div>

      <motion.h2
        initial={{ opacity: 0, y: 16 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.6, delay: 0.1 }}
        className="font-headline-xl text-[34px] font-bold tracking-tight text-ink sm:text-[42px]"
      >
        {t("home.benefits.title")}
      </motion.h2>

      <motion.p
        initial={{ opacity: 0, y: 16 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.6, delay: 0.15 }}
        className="font-body-lg leading-relaxed text-muted"
      >
        {t("home.benefits.description")}
      </motion.p>

      <div className="grid grid-cols-1 gap-4 pt-1 sm:grid-cols-2">
        {BENEFITS.map((benefit, i) => (
          <motion.div
            key={benefit.title}
            initial={{ opacity: 0, y: 16 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.2 + i * 0.08 }}
            className="flex gap-3"
          >
            <div
              className={`flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${benefit.iconClass}`}
            >
              <benefit.icon className="h-[18px] w-[18px]" />
            </div>
            <div className="flex flex-col">
              <h4 className="font-headline-sm text-[15px] font-bold text-ink">{t(`home.benefitData.${benefit.key}.title`)}</h4>
              <p className="mt-1 font-body-sm text-[13px] leading-relaxed text-muted">
                {t(`home.benefitData.${benefit.key}.description`)}
              </p>
            </div>
          </motion.div>
        ))}
      </div>

      <motion.div
        initial={{ opacity: 0, y: 12 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.5, delay: 0.55 }}
        className="pt-2"
      >
        <Button
          asChild
          className="group gap-2 rounded-xl bg-primary px-8 py-3 font-headline-sm text-[14px] font-semibold text-primary-foreground shadow-sm hover:bg-primary/90"
        >
          <motion.button whileTap={{ scale: 0.97 }} whileHover={{ scale: 1.01 }}>
            {t("home.benefits.button")}
            <ArrowRight className="h-[17px] w-[17px] transition-transform group-hover:translate-x-1" />
          </motion.button>
        </Button>
      </motion.div>
    </div>
  );
}
