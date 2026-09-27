import { motion } from "framer-motion";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import PipelineStatCard from "./PipelineStatCard";
import { PIPELINE_STATS } from "@/constants/pipelineStats";
import { useTranslation } from "react-i18next";

export default function PipelineOverviewCard() {
  const { t } = useTranslation("dashboard");
  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.2 }}
      transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
    >
      <Card className="rounded-2xl border-border/80 bg-surface p-6 shadow-sm">
        <CardContent className="p-0">
          <div className="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
            <div>
              <div className="flex items-center gap-2">
                <h2 className="font-serif text-[19px] font-bold text-primary">
                  {t("overview.pipeline.title")}
                </h2>
                <Badge className="gap-1.5 rounded-full border-success/25 bg-success/10 px-2 py-0.5 text-[11px] font-semibold text-success hover:bg-success/10">
                  <span className="h-1.5 w-1.5 rounded-full bg-success" />
                  {t("overview.pipeline.weeklyChange")}
                </Badge>
              </div>
              <p className="mt-0.5 text-[12.5px] text-muted">
                {t("overview.pipeline.description")}
              </p>
            </div>

           
          </div>

          <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
            {PIPELINE_STATS.map((stat, index) => (
              <PipelineStatCard key={stat.key} stat={stat} index={index} />
            ))}
          </div>
        </CardContent>
      </Card>
    </motion.div>
  );
}
