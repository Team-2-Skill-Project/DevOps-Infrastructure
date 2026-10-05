import { motion } from "framer-motion";
import { Languages, CheckCircle2 } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

const ease = [0.16, 1, 0.3, 1];

export default function CvLanguagesCard({
  languages = [],
  onConfirmLanguage,
}) {
  if (!languages || languages.length === 0) return null;

  return (
    <motion.div
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.35, ease }}
      className="space-y-4 rounded-2xl border border-border bg-surface p-5 shadow-xs"
    >
      {/* Header */}
      <div className="flex items-center justify-between border-b border-border/70 pb-4">
        <div className="flex items-center gap-2">
          <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <Languages className="h-5 w-5" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h3 className="text-base font-bold text-primary">
                Languages & Proficiency
              </h3>
              <Badge className="bg-primary/10 text-primary hover:bg-primary/10 border-0 text-[11px] font-semibold">
                {languages.length} Language{languages.length > 1 ? "s" : ""}
              </Badge>
            </div>
            <p className="text-xs text-muted">
              Spoken and written language competencies detected in CV
            </p>
          </div>
        </div>
      </div>

      {/* Grid of Languages */}
      <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3">
        {languages.map((lang, index) => (
          <motion.div
            key={lang.id || lang.language + index}
            layout
            initial={{ opacity: 0, scale: 0.96 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.25, delay: index * 0.04, ease }}
            className="flex items-center justify-between rounded-xl border border-border/80 bg-background/80 p-3.5"
          >
            <div>
              <span className="font-bold text-ink text-sm block">
                {lang.language}
              </span>
              <span className="inline-block mt-1 text-[11px] font-semibold text-secondary">
                {lang.proficiency || "Proficient"}
              </span>
            </div>

            <div>
              {lang.confirmed ? (
                <span className="flex items-center gap-1 text-xs font-bold text-success">
                  <CheckCircle2 className="h-4 w-4" />
                </span>
              ) : (
                <Button
                  type="button"
                  size="sm"
                  variant="outline"
                  onClick={() =>
                    onConfirmLanguage &&
                    onConfirmLanguage(lang.id || index)
                  }
                  className="h-7 rounded-lg border-border text-[11px] font-bold text-primary hover:bg-primary hover:text-white"
                >
                  Confirm
                </Button>
              )}
            </div>
          </motion.div>
        ))}
      </div>
    </motion.div>
  );
}
