import { motion } from "framer-motion";
import { useTranslation } from "react-i18next";

export default function HeroHeadline() {
  const { t } = useTranslation("common");
  return (
    <motion.div
      initial={{ opacity: 0, y: 16 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.7, delay: 0.1, ease: [0.16, 1, 0.3, 1] }}
      className="max-w-4xl mx-auto mb-5"
    >
      <h1 className="font-headline-xl text-[38px] sm:text-[50px] lg:text-[62px] font-bold text-primary-foreground tracking-tight leading-[1.12]">
        {t("home.hero.headlinePrefix")}{" "}
        <span className="relative inline-block">
          <motion.span
            className="font-black bg-gradient-to-r from-accent via-secondary to-accent bg-[length:200%_auto] bg-clip-text text-transparent"
            animate={{ backgroundPositionX: ["0%", "100%", "0%"] }}
            transition={{ duration: 4, repeat: Infinity, ease: "linear" }}
          >
            {t("home.hero.headlineHighlight")}
          </motion.span>
          <svg
            className="absolute -bottom-2.5 inset-s-0 w-full overflow-visible"
            viewBox="0 0 260 14"
            fill="none"
          >
            <motion.path
              d="M2 11C65 2.5 195 2.5 258 11"
              className="text-accent"
              stroke="currentColor"
              strokeWidth="4"
              strokeLinecap="round"
              initial={{ pathLength: 0 }}
              animate={{ pathLength: 1 }}
              transition={{ duration: 0.9, delay: 0.6, ease: "easeInOut" }}
            />
          </svg>
        </span>
      </h1>

      <p className="font-body-lg text-primary-foreground/80 leading-relaxed max-w-2xl text-[16px] sm:text-[18px] mt-5 mx-auto">
        {t("home.hero.description")}
      </p>
    </motion.div>
  );
}
