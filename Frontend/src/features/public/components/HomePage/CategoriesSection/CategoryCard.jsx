import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { useLocalizedPath } from "@/utils/routes";
import { ArrowRight } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";

export default function CategoryCard({ category, index = 0 }) {
  const Icon = category.icon;
  const { t } = useTranslation("common");
  const localizedPath = useLocalizedPath();

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.2 }}
      transition={{ duration: 0.5, delay: index * 0.05, ease: [0.16, 1, 0.3, 1] }}
      whileHover={{ y: -3 }}
      className="h-full"
    >
      {/* TODO: point to the real category route/query param */}
      <Link to={localizedPath(`/dashboard/jobs?category=${category.id}`)} className="block h-full">
        <Card className="group h-full cursor-pointer rounded-2xl border-border bg-surface p-4 shadow-[0_2px_8px_rgba(0,0,0,0.02)] transition-all hover:border-primary/40 hover:shadow-md">
          <CardContent className="flex h-full flex-col justify-between p-0">
            <div className="mb-4 flex items-center justify-between">
              <div
                className={`flex h-11 w-11 items-center justify-center rounded-xl transition-transform group-hover:scale-105 ${category.iconClass}`}
              >
                <Icon className="h-6 w-6" />
              </div>
              <ArrowRight className="h-[18px] w-[18px] text-muted transition-colors group-hover:text-primary" />
            </div>

            <div>
              <h3 className="font-headline-sm text-[17px] font-semibold text-ink">
                {t(`home.categoryData.${category.id}.title`)}
              </h3>
              <p className="mt-1 font-body-sm text-[13px] text-muted">{category.openJobs}</p>
            </div>

            <div className="mt-4 flex items-center justify-between border-t border-border pt-2">
              <span className="font-headline-sm text-[12px] font-semibold text-primary transition-colors group-hover:text-secondary">
                {t("home.categories.viewRoles")}
              </span>
              <span className="font-body-sm text-[11px] text-muted">{t(`home.categoryData.${category.id}.tags`)}</span>
            </div>
          </CardContent>
        </Card>
      </Link>
    </motion.div>
  );
}
