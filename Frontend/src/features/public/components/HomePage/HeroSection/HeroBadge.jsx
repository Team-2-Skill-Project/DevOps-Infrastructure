import { motion } from "framer-motion";
import { Sparkles } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { useTranslation } from "react-i18next";

export default function HeroBadge() {
  const { t } = useTranslation("common");
  return (
    <motion.div
      initial={{ opacity: 0, y: -12 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1] }}
      className="mb-6"
    >
      <Badge
        variant="outline"
        className="cursor-default gap-1.5 rounded-full border-primary-foreground/20 bg-primary-foreground/10 px-4 py-1.5 text-[12px] font-medium tracking-wide text-primary-foreground/90 backdrop-blur-xl transition-all duration-300 hover:bg-primary-foreground/[0.16] sm:text-[13px]"
      >
        <span className="relative flex h-2 w-2">
          <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-accent opacity-75" />
          <span className="relative inline-flex h-2 w-2 rounded-full bg-accent" />
        </span>
        <Sparkles className="h-3.5 w-3.5 text-accent" />
        {t("home.hero.badge")}
      </Badge>
    </motion.div>
  );
}
