import { motion } from "framer-motion";
import { BadgeCheck, Zap, Award } from "lucide-react";
import { Badge } from "@/components/ui/badge";

const COMPETENCIES = ["Distributed Systems", "Go & Rust"];

export default function PhilosophyVisual() {
  return (
    // pb-28 reserves room for the floating candidate card below, which sits
    // at -bottom-4 (i.e. partly outside the image frame). Without this the
    // card overlaps whatever comes after this section.
    <div className="relative pb-28 lg:col-span-6">
      <div className="pointer-events-none absolute -inset-s-10 -top-10 h-72 w-72 rounded-full bg-accent/10 blur-3xl" />

      <motion.div
        initial={{ opacity: 0, scale: 0.96 }}
        whileInView={{ opacity: 1, scale: 1 }}
        viewport={{ once: true, amount: 0.3 }}
        transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
        className="group relative overflow-hidden rounded-2xl border border-border bg-surface p-2 shadow-[0_14px_34px_rgba(0,0,0,0.08)] transition-all duration-300 hover:shadow-xl"
      >
        <div className="relative aspect-[4/3] overflow-hidden rounded-xl">
          <img
            alt="MatchIn Verified Evaluation Workspace"
            src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=1800&h=600&fit=crop&auto=format"
            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-primary/60 via-transparent to-transparent" />
        </div>
      </motion.div>

      {/* Live telemetry pill */}
      <motion.div
        initial={{ opacity: 0, y: -10 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.5, delay: 0.3 }}
        className="absolute -top-3 inset-e-3 flex items-center gap-2 rounded-full border border-border bg-surface/95 px-3.5 py-1.5 shadow-md backdrop-blur-md sm:inset-e-6"
      >
        <span className="relative flex h-2 w-2">
          <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-success opacity-75" />
          <span className="relative inline-flex h-2 w-2 rounded-full bg-success" />
        </span>
        <span className="font-headline-sm text-[12px] font-semibold tracking-wide text-primary">
          Live Evaluation Engine
        </span>
      </motion.div>

      {/* Floating candidate card */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.6, delay: 0.4, ease: [0.16, 1, 0.3, 1] }}
        whileHover={{ y: -4 }}
        className="absolute -bottom-4 inset-s-2 inset-e-2 flex max-w-sm flex-col gap-2.5 rounded-2xl border border-border bg-surface/95 p-4 shadow-[0_12px_32px_rgba(27,28,26,0.12)] backdrop-blur-xl sm:inset-s-6 sm:inset-e-auto"
      >
        <div className="flex items-center justify-between gap-2">
          <div className="flex items-center gap-2.5">
            <div className="flex h-10 w-10 items-center justify-center rounded-full border border-border bg-primary/10 font-headline-sm text-[15px] font-bold text-primary">
              AL
            </div>
            <div className="flex flex-col">
              <span className="flex items-center gap-1 font-headline-sm text-[13px] font-bold text-ink">
                Alex Lin
                <BadgeCheck className="h-[15px] w-[15px] fill-success text-surface" />
              </span>
              <span className="font-body-sm text-[11px] text-muted">
                Staff Systems Architect
              </span>
            </div>
          </div>
          <Badge className="gap-1 rounded-full bg-success/10 px-2 py-0.5 text-[11px] font-bold text-success hover:bg-success/10">
            <Zap className="h-[13px] w-[13px]" />
            98.8% Fit
          </Badge>
        </div>

        <div className="flex items-center gap-1.5 border-t border-border/60 pt-1">
          {COMPETENCIES.map((c) => (
            <Badge
              key={c}
              variant="outline"
              className="rounded-full border-border bg-background px-2 py-0.5 text-[11px] font-medium text-muted"
            >
              {c}
            </Badge>
          ))}
          <Badge className="flex items-center gap-0.5 rounded-full bg-accent/10 px-2 py-0.5 text-[11px] font-semibold text-accent hover:bg-accent/10">
            <Award className="h-3 w-3" />
            Top 1%
          </Badge>
        </div>
      </motion.div>
    </div>
  );
}
