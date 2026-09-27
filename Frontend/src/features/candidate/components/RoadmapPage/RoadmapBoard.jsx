import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { 
  Loader2, RefreshCw, ArrowRightLeft, MessageSquare, 
  Check, ChevronDown, Flag 
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";

/* ====================================================================================
 * Internal Sub-Components (Kept in this file to reduce component count)
 * ==================================================================================== */

function TaskRow({ label, phase, done, onToggle, date }) {
  return (
    <div className={cn("flex items-center gap-3 rounded-lg border border-border p-2.5 transition-colors", done && "bg-background/50")}>
      <button 
        onClick={() => onToggle?.(!done)}
        className={cn(
          "flex size-5 flex-shrink-0 items-center justify-center rounded-[5px] border transition-all cursor-pointer",
          done ? "border-success bg-success" : "border-border hover:border-primary/50"
        )}
      >
        <motion.span initial={false} animate={{ scale: done ? 1 : 0, opacity: done ? 1 : 0 }}>
          <Check className="size-3 text-white" />
        </motion.span>
      </button>
      <span className={cn("flex-1 text-[13px] transition-all", done && "text-muted line-through")}>{label}</span>
      {(phase || date) && <span className="flex-shrink-0 text-[11px] text-muted">{phase || date}</span>}
    </div>
  );
}

function SkillGapItem({ name, reason, priority }) {
  const config = {
    high: { bg: "bg-error/10 text-error", label: "HIGH" },
    medium: { bg: "bg-warning/10 text-warning", label: "MEDIUM" },
  }[priority];

  return (
    <div className="flex items-start gap-3 rounded-xl border border-border p-3.5">
      <Badge variant="outline" className={cn("mt-0.5 flex-shrink-0 rounded-md border-transparent px-2 py-0.5 text-[10px] font-bold", config.bg)}>
        {config.label}
      </Badge>
      <div className="min-w-0 flex-1">
        <div className="text-[13px] font-semibold text-ink">{name}</div>
        <div className="mt-0.5 text-[12px] text-muted leading-relaxed">{reason}</div>
      </div>
    </div>
  );
}

function PhaseAccordion({ number, title, progress, colorClass, milestones, tasks }) {
  const [isOpen, setIsOpen] = useState(number === 1);
  return (
    <div className={cn("overflow-hidden rounded-xl border border-border", isOpen ? "bg-surface" : "bg-transparent")}>
      <button onClick={() => setIsOpen(!isOpen)} className="flex w-full items-center gap-3 p-4 text-left cursor-pointer">
        <div className={cn("flex size-8 flex-shrink-0 items-center justify-center rounded-full text-[12px] font-bold", colorClass.icon)}>
          {number}
        </div>
        <div className="min-w-0 flex-1">
          <div className="text-[13px] font-semibold text-ink">{title}</div>
          <div className="mt-1.5 h-1.5 w-full max-w-xs overflow-hidden rounded-full bg-background">
            <div className={cn("h-full rounded-full transition-all", colorClass.bar)} style={{ width: `${progress}%` }} />
          </div>
        </div>
        <span className="flex-shrink-0 text-[11px] font-semibold text-muted">{progress}%</span>
        <motion.div animate={{ rotate: isOpen ? 180 : 0 }} className="flex-shrink-0 text-muted">
          <ChevronDown className="size-[18px]" />
        </motion.div>
      </button>

      <AnimatePresence initial={false}>
        {isOpen && (
          <motion.div initial={{ height: 0, opacity: 0 }} animate={{ height: "auto", opacity: 1 }} exit={{ height: 0, opacity: 0 }} className="overflow-hidden">
            <div className="px-4 pb-4">
              {milestones.map((m, i) => (
                <div key={i} className="mb-3 mt-1 flex items-center gap-2 text-[12px] font-semibold text-muted">
                  <Flag className="size-4 text-warning" /> Milestone: {m}
                </div>
              ))}
              <div className="space-y-2">
                {tasks.map((t, i) => (
                  <TaskRow key={i} label={t.label} done={t.done} date={t.date} />
                ))}
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

/* ====================================================================================
 * Main Roadmap Board Component
 * ==================================================================================== */

export default function RoadmapBoard({ isUpdating, onRefresh, onChangeRole }) {
  const [showCompleted, setShowCompleted] = useState(false);

  return (
    <motion.div initial={{ opacity: 0, y: 15 }} animate={{ opacity: 1, y: 0 }} className="w-full">
      
      {/* Updating Banner */}
      <AnimatePresence>
        {isUpdating && (
          <motion.div initial={{ opacity: 0, height: 0, marginBottom: 0 }} animate={{ opacity: 1, height: "auto", marginBottom: 20 }} exit={{ opacity: 0, height: 0, marginBottom: 0 }} className="overflow-hidden">
            <div className="flex items-center gap-2 rounded-xl border border-primary/20 bg-primary/5 px-4 py-3 text-[13px] font-semibold text-primary">
              <Loader2 className="size-4 animate-spin" /> Recalculating your roadmap — your profile or target role changed.
            </div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Target Role Header */}
      <div className="mb-5 rounded-2xl border border-border bg-surface p-6 shadow-sm">
        <div className="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
          <div>
            <div className="mb-1 text-[11px] font-semibold text-secondary uppercase">Target Role</div>
            <h1 className="font-display text-[20px] font-bold text-ink">Senior Frontend Engineer</h1>
            <div className="mt-1 text-[12px] text-muted">Based on your saved job at Vercel · Generated Sep 1, 2026</div>
          </div>
          <div className="flex flex-wrap gap-2">
            <Button variant="outline" size="sm" onClick={onRefresh} className="h-9 gap-1.5"><RefreshCw className="size-4" /> Refresh</Button>
            <Button variant="outline" size="sm" onClick={onChangeRole} className="h-9 gap-1.5"><ArrowRightLeft className="size-4" /> Change target role</Button>
            <Button size="sm" className="h-9 gap-1.5"><MessageSquare className="size-4" /> Ask Mentor</Button>
          </div>
        </div>
      </div>

      {/* Stats Cards */}
      <div className="mb-5 grid gap-5 sm:grid-cols-2">
        <div className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
          <div className="mb-3 text-[11px] font-semibold text-muted uppercase">Current Baseline</div>
          <div className="mb-1 text-[13px] font-semibold text-ink">Mid-level Frontend Developer</div>
          <p className="mb-3 text-[12px] leading-relaxed text-muted">Based on your CV and confirmed skills: React, TypeScript, Next.js, REST APIs, Git.</p>
          <div className="text-[11px] text-muted">3–5 years experience · Last CV update Aug 24, 2026</div>
        </div>
        <div className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
          <div className="mb-3 flex items-center justify-between">
            <div className="text-[11px] font-semibold text-muted uppercase">Overall Progress</div>
            <span className="text-[13px] font-bold text-primary">34%</span>
          </div>
          <div className="mb-3 h-2 w-full overflow-hidden rounded-full bg-background">
            <div className="h-full rounded-full bg-primary" style={{ width: "34%" }}></div>
          </div>
          <div className="grid grid-cols-3 gap-2 text-center">
            <div><div className="font-display text-[15px] font-bold">3</div><div className="text-[10px] text-muted">Phases</div></div>
            <div><div className="font-display text-[15px] font-bold">7/22</div><div className="text-[10px] text-muted">Tasks done</div></div>
            <div><div className="font-display text-[15px] font-bold">~5 wks</div><div className="text-[10px] text-muted">Est. remaining</div></div>
          </div>
        </div>
      </div>

      {/* Priority Skill Gaps */}
      <div className="mb-5 rounded-2xl border border-border bg-surface p-6 shadow-sm">
        <h2 className="mb-1 font-display text-[15px] font-bold">Priority skill gaps</h2>
        <p className="mb-4 text-[12px] text-muted">Ranked by impact on your target role — reasons are generated by the roadmap engine from job requirements and your profile.</p>
        <div className="space-y-3">
          <SkillGapItem priority="high" name="System Design" reason="Appears as a core requirement in 9 of 10 roles you've saved, and isn't yet confirmed on your profile." />
          <SkillGapItem priority="medium" name="GraphQL" reason="Requested in the target job's tech stack; you have related API experience (REST) but no confirmed GraphQL usage." />
          <SkillGapItem priority="medium" name="Automated Testing (Jest)" reason="Common requirement at senior level; not yet reflected in your CV or confirmed skills." />
        </div>
      </div>

      {/* Roadmap Phases */}
      <div className="mb-5 rounded-2xl border border-border bg-surface p-6 shadow-sm">
        <h2 className="mb-4 font-display text-[15px] font-bold">Roadmap phases</h2>
        <div className="space-y-3">
          <PhaseAccordion 
            number={1} title="Foundations — System Design Basics" progress={75}
            colorClass={{ icon: "bg-success text-white", bar: "bg-success" }}
            milestones={['Complete "System Design Interview" course']}
            tasks={[{ label: 'Watch "Scaling Web Applications" module', done: true, date: 'Done Aug 30' }, { label: 'Design a URL-shortener system on paper', done: false }]}
          />
          <PhaseAccordion 
            number={2} title="Applied Skills — GraphQL & Testing" progress={15}
            colorClass={{ icon: "border-2 border-border text-muted", bar: "bg-warning" }}
            milestones={['Ship one project using GraphQL + Jest']}
            tasks={[{ label: 'Complete "GraphQL Fundamentals" module', done: false }, { label: 'Write unit tests for an existing project with Jest', done: false }]}
          />
          <PhaseAccordion 
            number={3} title="Interview Ready — Mock Interviews & Portfolio" progress={0}
            colorClass={{ icon: "border-2 border-border text-muted", bar: "bg-border" }}
            milestones={['Pass 2 AI mock interviews at "Senior" level']}
            tasks={[{ label: 'Schedule first AI mock interview', done: false }]}
          />
        </div>
      </div>

      {/* Weekly Tasks */}
      <div className="mb-5 rounded-2xl border border-border bg-surface p-6 shadow-sm">
        <div className="mb-4 flex items-center justify-between">
          <h2 className="font-display text-[15px] font-bold">This week's tasks</h2>
          <span className="text-[12px] text-muted">2 of 4 done</span>
        </div>
        <div className="space-y-2">
          <TaskRow label='Watch "Scaling Web Applications" module' phase="Phase 1" done={true} />
          <TaskRow label='Design a URL-shortener system on paper' phase="Phase 1" done={true} />
          <TaskRow label='Complete "GraphQL Fundamentals" module' phase="Phase 2" done={false} />
          <TaskRow label='Write unit tests for an existing project with Jest' phase="Phase 2" done={false} />
        </div>
        <p className="mt-3 text-[11px] text-muted">Tap a checkbox to mark complete — tap again to undo.</p>
      </div>

      {/* Completed Tasks */}
      <div className="rounded-2xl border border-border bg-surface p-6 shadow-sm">
        <button onClick={() => setShowCompleted(!showCompleted)} className="flex w-full items-center justify-between cursor-pointer">
          <h2 className="font-display text-[15px] font-bold">Completed tasks <span className="font-normal text-muted">(7)</span></h2>
          <motion.div animate={{ rotate: showCompleted ? 180 : 0 }} className="text-muted"><ChevronDown className="size-[18px]" /></motion.div>
        </button>
        <AnimatePresence>
          {showCompleted && (
            <motion.div initial={{ height: 0, opacity: 0 }} animate={{ height: "auto", opacity: 1 }} exit={{ height: 0, opacity: 0 }} className="overflow-hidden">
              <div className="mt-4 space-y-2">
                <TaskRow label="Confirmed React & TypeScript proficiency from CV" date="Aug 24" done={true} />
                <TaskRow label="Set up learning schedule (5 hrs/week)" date="Aug 25" done={true} />
              </div>
            </motion.div>
          )}
        </AnimatePresence>
      </div>

    </motion.div>
  );
}
