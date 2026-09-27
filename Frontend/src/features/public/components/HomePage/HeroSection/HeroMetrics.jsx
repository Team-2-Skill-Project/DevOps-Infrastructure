import { motion } from "framer-motion";
import { Card, CardContent } from "@/components/ui/card";
import { Separator } from "@/components/ui/separator";
import { useCountUp } from "../../../hooks/useCountUp";
import { useTranslation } from "react-i18next";

const METRICS = [
  {
    target: 180,
    suffix: "K+",
    labelKey: "home.metrics.activeSeekers",
    color: "text-primary-foreground",
  },
  {
    target: 4200,
    suffix: "+",
    labelKey: "home.metrics.partnerCompanies",
    color: "text-accent",
  },
  { target: 93, suffix: "%", labelKey: "home.metrics.matchAccuracy", color: "text-success" },
  {
    target: 2.4,
    suffix: "×",
    decimals: 1,
    labelKey: "home.metrics.fasterHiring",
    color: "text-secondary",
  },
];

function Metric({ target, suffix, decimals = 0, labelKey, color }) {
  const { t } = useTranslation("common");
  const { ref, value } = useCountUp(target, { decimals });
  const display = decimals
    ? value.toFixed(decimals)
    : Math.floor(value).toLocaleString();

  return (
    <div ref={ref} className="flex flex-col items-center px-3">
      <div
        className={`font-headline-xl text-[24px] font-bold leading-none tracking-tight sm:text-[30px] ${color}`}
      >
        {display}
        {suffix}
      </div>
      <span className="mt-1 text-[12px] font-medium text-primary-foreground/70">
        {t(labelKey)}
      </span>
    </div>
  );
}

export default function HeroMetrics() {
  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.7, delay: 0.5, ease: [0.16, 1, 0.3, 1] }}
      className="mb-8 w-full max-w-4xl"
    >
      <Card className="rounded-2xl border-primary-foreground/15 bg-primary-foreground/[0.07] shadow-2xl backdrop-blur-xl">
        <CardContent className="flex flex-wrap items-center justify-around gap-4 p-4 text-center sm:p-5">
          {METRICS.map((m, i) => (
            <div key={m.labelKey} className="flex items-center gap-4">
              <Metric {...m} />
              {i < METRICS.length - 1 && (
                <Separator
                  orientation="vertical"
                  className="hidden h-8 w-px bg-primary-foreground/15 sm:block"
                />
              )}
            </div>
          ))}
        </CardContent>
      </Card>
    </motion.div>
  );
}
