import { motion, AnimatePresence } from "framer-motion";
import { Info } from "lucide-react";
import { useTranslation } from "react-i18next";

/**
 * Warning banner shown when the user has no target role set.
 *
 * @param {{ visible?: boolean }} props
 */
export default function MissingContextBanner({ visible = false }) {
  const { t } = useTranslation("common");
  return (
    <AnimatePresence>
      {visible && (
        <motion.div
          initial={{ opacity: 0, height: 0, marginTop: 0 }}
          animate={{ opacity: 1, height: "auto", marginTop: 16 }}
          exit={{ opacity: 0, height: 0, marginTop: 0 }}
          transition={{ type: "spring", stiffness: 350, damping: 28 }}
          className="overflow-hidden px-4"
        >
          <div className="flex items-start gap-2.5 rounded-xl border border-warning/30 bg-warning/10 px-3.5 py-3 text-[12px] text-muted">
            <Info className="mt-0.5 size-4 flex-shrink-0 text-warning" />
            <div>
              <span className="font-semibold text-ink">
                {t("ui.chat.noTarget")}
              </span>{" "}
              {t("ui.chat.generalHelp")}{" "}
              <a href="#" className="font-semibold text-primary">
                {t("ui.chat.setTarget")}
              </a>{" "}
              {t("ui.chat.orOpenSaved")}
            </div>
          </div>
        </motion.div>
      )}
    </AnimatePresence>
  );
}
