import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import { useLocalizedPath } from "@/utils/routes";
import { BarChart3, Wallet, Check, ArrowRight, MoveRight } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { useTranslation } from "react-i18next";

const FEATURE_TILES = [
  {
    icon: BarChart3,
    iconClass: "bg-primary/10 text-primary",
    key: "telemetry",
  },
  {
    icon: Wallet,
    iconClass: "bg-secondary/20 text-secondary",
    key: "compensation",
  },
];

const BENEFITS = [
  "accuracy",
  "response",
  "privacy",
];

export default function PhilosophyContent() {
  const localizedPath = useLocalizedPath();
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
          className="w-fit gap-2 self-start rounded-full border-border bg-surface px-4 py-1 text-[11px] font-bold uppercase tracking-wider text-secondary shadow-sm"
        >
          {t("home.approach.badge")}
        </Badge>
      </motion.div>

      <motion.h2
        initial={{ opacity: 0, y: 16 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.6, delay: 0.1 }}
        className="font-headline-lg text-[32px] font-bold leading-tight tracking-tight text-ink sm:text-[40px]"
      >
        {t("home.approach.title")}
      </motion.h2>

      <motion.p
        initial={{ opacity: 0, y: 16 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.6, delay: 0.15 }}
        className="font-body-lg leading-relaxed text-muted"
      >
        {t("home.approach.description")}
      </motion.p>

      <div className="grid grid-cols-1 gap-4 pt-1 sm:grid-cols-2">
        {FEATURE_TILES.map((tile, i) => (
          <motion.div
            key={tile.title}
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.5, delay: 0.2 + i * 0.1 }}
          >
            <Card className="group h-full rounded-2xl border-border bg-surface p-4 shadow-[0_2px_8px_rgba(0,0,0,0.03)] transition-colors hover:border-primary/40">
              <CardContent className="flex flex-col gap-1.5 p-0">
                <div
                  className={`flex h-9 w-9 items-center justify-center rounded-xl transition-transform group-hover:scale-105 ${tile.iconClass}`}
                >
                  <tile.icon className="h-5 w-5" />
                </div>
                <h3 className="mt-1 font-headline-sm text-[15px] font-bold text-ink">
                  {t(`home.approach.tiles.${tile.key}.title`)}
                </h3>
                <p className="font-body-sm text-[13px] leading-relaxed text-muted">
                  {t(`home.approach.tiles.${tile.key}.description`)}
                </p>
              </CardContent>
            </Card>
          </motion.div>
        ))}
      </div>

      <div className="flex flex-col gap-2.5 pt-1">
        {BENEFITS.map((benefit, i) => (
          <motion.div
            key={benefit}
            initial={{ opacity: 0, x: -12 }}
            whileInView={{ opacity: 1, x: 0 }}
            viewport={{ once: true }}
            transition={{ duration: 0.4, delay: 0.4 + i * 0.08 }}
            className="flex items-center gap-3"
          >
            <span className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-success/15 text-success">
              <Check className="h-3.5 w-3.5" />
            </span>
            <span className="font-body-md text-[14px] font-medium text-ink">
              {t(`home.approach.benefits.${benefit}`)}
            </span>
          </motion.div>
        ))}
      </div>

      <motion.div
        initial={{ opacity: 0, y: 12 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.5, delay: 0.6 }}
        className="flex flex-wrap items-center gap-4 pt-2"
      >
        <Button
          asChild
          className="group gap-2 rounded-xl bg-primary px-8 py-3 font-headline-sm text-[14px] font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 hover:shadow-md"
        >
          <motion.button
            whileTap={{ scale: 0.97 }}
            whileHover={{ scale: 1.01 }}
          >
            <span>{t("home.approach.discover")}</span>
            <ArrowRight className="h-[17px] w-[17px] transition-transform group-hover:translate-x-1" />
          </motion.button>
        </Button>

        <Button
          asChild
          variant="link"
          className="group gap-1 p-0 font-headline-sm text-[14px] font-semibold text-muted hover:text-primary"
        >
          {/* TODO: point to the real methodology route */}
          <Link to={localizedPath("/methodology")}>
            <span>{t("home.approach.methodology")}</span>
            <MoveRight className="h-4 w-4 transition-transform group-hover:translate-x-1" />
          </Link>
        </Button>
      </motion.div>
    </div>
  );
}
