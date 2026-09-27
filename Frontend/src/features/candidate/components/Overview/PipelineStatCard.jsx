import { motion } from "framer-motion";
import { useTranslation } from "react-i18next";

export default function PipelineStatCard({ stat, index = 0 }) {
  const Icon = stat.icon;
  const { t } = useTranslation("dashboard");

  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.3 }}
      transition={{ duration: 0.4, delay: index * 0.08, ease: [0.16, 1, 0.3, 1] }}
      whileHover={{ y: -2 }}
      className="group rounded-xl border border-border/70 bg-background p-4 transition-all hover:border-border"
    >
      <div className="mb-2 flex items-center justify-between text-muted">
        <span className="text-[11px] font-bold uppercase tracking-wider">
          {t(`overview.pipeline.${stat.key === "in-review" ? "inReview" : stat.key}`)}
        </span>
        <Icon className={`h-4 w-4 ${stat.iconClass}`} />
      </div>

      <div className="flex items-baseline gap-2">
        <div className={`font-serif text-[28px] font-bold leading-none ${stat.valueClass}`}>
          {stat.value}
        </div>
        <span className={`text-[11px] font-semibold ${stat.suffixClass}`}>
          {stat.key === "interview"
            ? `${t("overview.pipeline.round")} 3`
            : t(`overview.pipeline.${stat.suffix}`)}
        </span>
      </div>

    </motion.div>
  );
}
