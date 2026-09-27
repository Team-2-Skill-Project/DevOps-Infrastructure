import { motion } from "framer-motion";
import { Button } from "@/components/ui/button";
import { useTranslation } from "react-i18next";

const TAGS = ["Frontend Dev", "Data Science", "Product Manager", "Remote"];

export default function HeroPopularSearches({ onSelect }) {
  const { t } = useTranslation("common");
  return (
    <motion.div
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      transition={{ duration: 0.6, delay: 0.4 }}
      className="mb-4 flex flex-wrap items-center justify-center gap-2 text-[12px] sm:text-[13px]"
    >
      <span className="font-medium text-primary-foreground/60">{t("home.hero.popular")}</span>
      {TAGS.map((tag) => (
        <Button
          key={tag}
          type="button"
          variant="outline"
          size="sm"
          onClick={() => onSelect(tag)}
          className="h-auto rounded-full border-primary-foreground/10 bg-primary-foreground/10 px-3 py-1 text-primary-foreground/90 backdrop-blur-md hover:bg-primary-foreground/20 hover:text-primary-foreground"
        >
          {tag}
        </Button>
      ))}
    </motion.div>
  );
}
