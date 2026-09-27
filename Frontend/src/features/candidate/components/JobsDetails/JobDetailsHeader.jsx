import { motion } from "framer-motion";
import {
  ArrowRight,
  Bookmark,
  BriefcaseBusiness,
  MapPin,
  Share2,
  Zap,
} from "lucide-react";
import { Button } from "@/components/ui/button";

export default function JobDetailsHeader({
  isExpired,
  isBookmarked,
  onApply,
  onAnalysis,
  onShare,
  onToggleBookmark,
}) {
  return (
    <motion.div
      initial={{ opacity: 0, y: -18 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.45, ease: "easeOut" }}
      className="bg-surface border border-border rounded-2xl p-8 mb-8 shadow-xs"
    >
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div className="flex items-start gap-5">
          <div className="w-16 h-16 rounded-2xl bg-primary text-primary-foreground flex items-center justify-center font-dm-sans text-2xl font-bold shrink-0 shadow-sm border-2 border-border relative overflow-hidden">
            <span className="relative z-10">TN</span>
            <div className="absolute bottom-0 inset-x-0 h-1 bg-secondary"></div>
          </div>
          <div>
            <div className="flex items-center gap-2.5 mb-2 flex-wrap">
              <span className="text-sm font-semibold text-muted">
                TechNova Labs
              </span>
              <span className="bg-secondary/10 text-secondary border border-secondary/20 text-[11px] font-semibold px-2 py-0.5 rounded-md flex items-center gap-1.5">
                <span className="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                Fresh · Published 3 days ago
              </span>
            </div>
            <h1 className="font-dm-sans text-3xl md:text-4xl text-primary font-bold tracking-tight mb-3">
              Senior Frontend Developer
            </h1>
            <div className="flex flex-wrap items-center gap-4 text-xs text-muted">
              <span className="flex items-center gap-1.5 font-medium">
                <MapPin className="h-3.5 w-3.5" />
                Cairo, Egypt (Maadi & Remote)
              </span>
              <span className="text-border">•</span>
              <span className="flex items-center gap-1.5 font-medium">
                <BriefcaseBusiness className="h-3.5 w-3.5" />
                Full-time · Senior / Lead
              </span>
              <span className="text-border">•</span>
              <span className="font-medium">5+ Years Exp</span>
            </div>
          </div>
        </div>

        <div className="bg-background border border-border p-4 rounded-xl min-w-70 shrink-0 text-right md:text-left">
          <span className="text-[11px] uppercase tracking-wider font-semibold text-muted block mb-0.5">
            Estimated Compensation
          </span>
          <div className="text-2xl font-bold text-primary tracking-tight mb-1">
            $3,200 – $4,500{" "}
            <span className="text-xs font-normal text-muted">/ month net</span>
          </div>
          <div className="text-[11px] text-muted flex items-center gap-2 mb-3">
            <span>0.15% Equity Option</span>
            <span>•</span>
            <span>Annual Tech Stipend</span>
          </div>
          <div className="pt-2.5 border-t border-border flex items-center gap-2">
            <Button
              variant="ghost"
              onClick={onShare}
              aria-label="Share job"
              title="Share job"
              className="h-10 w-10 rounded-lg border border-border bg-surface p-0 text-primary hover:bg-background"
            >
              <Share2 className="h-4 w-4" />
            </Button>
            <Button
              variant="ghost"
              onClick={onToggleBookmark}
              aria-label={isBookmarked ? "Remove saved job" : "Save job"}
              title={isBookmarked ? "Remove saved job" : "Save job"}
              className={`h-10 w-10 rounded-lg border p-0 ${isBookmarked ? "border-secondary/20 bg-secondary/10 text-secondary" : "border-border bg-surface text-primary hover:bg-background"}`}
            >
              <Bookmark
                className="h-4 w-4"
                fill={isBookmarked ? "currentColor" : "none"}
              />
            </Button>
            <Button
              variant="ghost"
              onClick={onApply}
              disabled={isExpired}
              className={`flex-1 bg-primary hover:bg-primary/90 text-primary-foreground text-xs font-bold py-2.5 px-3 rounded-lg shadow-xs transition-colors flex items-center justify-center gap-1.5 group ${isExpired ? "opacity-45 pointer-events-none" : ""}`}
            >
              <span className="w-1.5 h-1.5 rounded-full bg-secondary"></span>
              Apply Now <ArrowRight className="h-4 w-4" />
            </Button>
            <Button
              variant="ghost"
              onClick={onAnalysis}
              className="bg-surface hover:bg-background text-primary border border-border text-xs font-semibold py-2.5 px-3 rounded-lg transition-colors flex items-center gap-1"
            >
              <Zap className="h-3.5 w-3.5 text-secondary" />
              Analysis
            </Button>
          </div>
        </div>
      </div>
    </motion.div>
  );
}
