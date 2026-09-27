import { motion } from "framer-motion";
import { Badge } from "@/components/ui/badge";
import CategoryCard from "./CategoryCard";
import AIMentorCard from "../AIMentorCard";
import { CATEGORIES } from "@/constants/categories";
import { useTranslation } from "react-i18next";


export default function CategoriesSection() {
  const { t } = useTranslation("common");
  return (
    <section id="categories" className="mx-auto w-full scroll-mt-20 max-w-[1280px] px-6 py-16 md:px-10 lg:px-16 lg:py-24">
      <motion.div
        initial={{ opacity: 0, y: 16 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.6 }}
        className="mx-auto mb-12 flex max-w-2xl flex-col items-center gap-2 text-center"
      >
        <Badge
          variant="outline"
          className="w-fit self-center rounded-full border-border bg-surface px-4 py-1 text-[11px] font-semibold uppercase tracking-wider text-primary"
        >
          {t("home.categories.badge")}
        </Badge>
        <h2 className="font-headline-xl text-[34px] font-bold tracking-tight text-ink sm:text-[42px]">
          {t("home.categories.title")}
        </h2>
        <p className="font-body-lg text-muted">
          {t("home.categories.description")}
        </p>
      </motion.div>

      <div className="mb-12 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {CATEGORIES.map((category, index) => (
          <CategoryCard key={category.id} category={category} index={index} />
        ))}
      </div>

      <AIMentorCard />
    </section>
  );
}
