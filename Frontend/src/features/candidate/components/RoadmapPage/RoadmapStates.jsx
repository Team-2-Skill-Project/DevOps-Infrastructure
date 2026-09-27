import { motion } from "framer-motion";
import { 
  UserRoundX, Upload, UserRoundCheck, 
  Loader2, AlertCircle, RotateCcw, 
  Sparkles, ArrowRight, User, LineChart, Map 
} from "lucide-react";
import { Button } from "@/components/ui/button";

export function EmptyProfileState({ onUploadCv, onCompleteProfile }) {
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }}
      className="rounded-2xl border border-border bg-surface px-6 py-20 text-center shadow-sm"
    >
      <div className="mx-auto mb-5 flex size-14 items-center justify-center rounded-xl border border-border bg-background">
        <UserRoundX className="size-[26px] text-muted" />
      </div>
      <h2 className="mb-2 font-display text-[19px] font-bold text-ink">We need a bit more profile info first</h2>
      <p className="mx-auto mb-6 max-w-sm text-[13px] text-muted">
        A roadmap compares your current skills to a target role — complete your profile and upload a CV so we have something to compare against.
      </p>
      <div className="flex justify-center gap-3">
        <Button variant="outline" onClick={onUploadCv} className="h-10 px-5 text-[13px]">Upload CV</Button>
        <Button onClick={onCompleteProfile} className="h-10 px-5 text-[13px]">Complete profile</Button>
      </div>
    </motion.div>
  );
}

export function NoRoadmapState({ onGenerate }) {
  return (
    <motion.div
      initial={{ opacity: 0, y: 10 }} animate={{ opacity: 1, y: 0 }}
      className="rounded-2xl border border-border bg-surface p-8 shadow-sm"
    >
      <div className="mb-2 text-[11px] font-semibold text-secondary uppercase tracking-wide">Career Roadmap</div>
      <h1 className="mb-2 font-display text-[24px] font-bold text-ink">Build your path to your next role</h1>
      <p className="mb-7 max-w-lg text-[13px] text-muted">
        Pick a target role — or a job you've saved — and we'll compare it against your current profile to generate a phased plan with weekly tasks.
      </p>

      <div className="mb-6">
        <label className="mb-2 block text-[12px] font-semibold text-muted">Target role</label>
        <select className="h-11 w-full rounded-[10px] border border-border bg-transparent px-3 text-[13px] outline-none transition-colors focus:border-primary sm:w-96">
          <option>Senior Frontend Engineer</option>
          <option>Frontend Architect</option>
          <option>Full-Stack Engineer</option>
          <option value="job">Use a saved job — Senior Frontend Engineer @ Vercel</option>
        </select>
      </div>

      <div className="mb-7 flex flex-wrap items-center gap-4 text-[12px] text-muted sm:gap-6">
        <span className="flex items-center gap-1.5"><User className="size-4 text-primary" /> Current profile</span>
        <ArrowRight className="size-3.5 text-border" />
        <span className="flex items-center gap-1.5"><LineChart className="size-4 text-primary" /> Skill gap analysis</span>
        <ArrowRight className="size-3.5 text-border" />
        <span className="flex items-center gap-1.5"><Map className="size-4 text-primary" /> Phased roadmap</span>
      </div>

      <Button onClick={onGenerate} className="h-11 gap-2 px-6 text-[14px]">
        <Sparkles className="size-4" /> Generate Roadmap
      </Button>
    </motion.div>
  );
}

export function GeneratingState() {
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }} exit={{ opacity: 0, scale: 0.9 }}
      className="rounded-2xl border border-border bg-surface px-6 py-16 text-center shadow-sm"
    >
      <motion.div animate={{ rotate: 360 }} transition={{ duration: 1.2, repeat: Infinity, ease: "linear" }} className="mx-auto mb-5 flex size-10 items-center justify-center">
        <Loader2 className="size-10 text-primary" />
      </motion.div>
      <h2 className="mb-2 font-display text-[17px] font-bold text-ink">Building your roadmap</h2>
      <p className="mx-auto max-w-sm text-[13px] text-muted">
        Comparing your profile against Senior Frontend Engineer requirements and prioritizing skill gaps — this takes a few seconds.
      </p>
    </motion.div>
  );
}

export function ErrorState({ onRetry }) {
  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.95 }} animate={{ opacity: 1, scale: 1 }}
      className="rounded-2xl border border-border bg-surface px-6 py-20 text-center shadow-sm"
    >
      <div className="mx-auto mb-5 flex size-14 items-center justify-center rounded-xl border border-error/30 bg-error/10">
        <AlertCircle className="size-[26px] text-error" />
      </div>
      <h2 className="mb-2 font-display text-[19px] font-bold text-ink">Couldn't generate your roadmap</h2>
      <p className="mx-auto mb-6 max-w-sm text-[13px] text-muted">
        Something went wrong on our end. Nothing was saved — you can try again.
      </p>
      <Button onClick={onRetry} className="h-10 gap-2 px-5 text-[13px]">
        <RotateCcw className="size-4" /> Try again
      </Button>
    </motion.div>
  );
}
