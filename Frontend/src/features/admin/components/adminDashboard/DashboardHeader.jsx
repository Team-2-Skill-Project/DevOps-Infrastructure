import { motion } from "framer-motion";
import { useTranslation } from "react-i18next";
import { RotateCw, Plus } from "lucide-react";
import { Button } from "@/components/ui/button";


export default function DashboardHeader({
  title,
  subtitle,
  onRefresh,
  onNewCompany,
}) {
  const { t } = useTranslation("dashboard");
  const headerTitle = title ?? t("pages.admin.overview.title");
  const headerSubtitle = subtitle ?? t("pages.admin.overview.subtitle");

  return (
    <motion.header
      initial={{ opacity: 0, y: -10 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4 }}
      className="flex flex-col sm:flex-row sm:items-center justify-between gap-4"
    >
      <div>
        <h1 className="text-2xl font-bold tracking-tight text-[#222831]">{headerTitle}</h1>
        <p className="text-sm text-muted mt-1">{headerSubtitle}</p>
      </div>
      <div className="flex items-center gap-2.5 flex-wrap">
        <Button
          variant="outline"
          onClick={onRefresh}
          className="h-10 px-4 rounded-xl text-[13.5px] font-semibold bg-white text-[#222831] border border-border hover:bg-[#f5f2ec] hover:border-[#d9d4cb] transition-all shadow-sm"
        >
          <RotateCw className="w-4 h-4 shrink-0" />
          Refresh
        </Button>
        <Button
          onClick={onNewCompany}
          className="h-10 px-4 rounded-xl text-[13.5px] font-semibold bg-primary text-white hover:bg-[#182a4a] transition-all shadow-sm"
        >
          <Plus className="w-4 h-4 shrink-0" />
          New Company
        </Button>
      </div>
    </motion.header>
  );
}