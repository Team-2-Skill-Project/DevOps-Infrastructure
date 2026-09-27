import { Check, Flag, GraduationCap, Plus, Sparkles } from "lucide-react";

export default function ApplicationDetailInsight() {
  return (
    <div className="bg-white border border-primary/15 rounded-3xl p-6 mb-5 shadow-sm relative overflow-hidden">
      <div className="absolute top-0 inset-e-0 w-40 h-40 bg-primary/5 rounded-full -me-16 -mt-16 pointer-events-none" />
      <div className="relative">
        <div className="flex items-center gap-2 mb-4">
          <div className="w-8 h-8 rounded-xl bg-primary text-white flex items-center justify-center shrink-0">
            <Sparkles className="h-4 w-4" />
          </div>
          <h2 className="font-display font-bold text-[15px]">
            AI Application Insight
          </h2>
        </div>

        <div className="mb-4">
          <div className="text-[11px] font-semibold text-muted mb-1.5">
            CURRENT STATUS EXPLANATION
          </div>
          <p className="text-[13px] text-[#44474E] leading-relaxed">
            Your application has been viewed by the hiring team and moved to
            Under Review. This typically means your profile passed the initial
            screening — no action is required from you right now.
          </p>
        </div>

        <div className="mb-4 border border-primary/20 bg-primary/5 rounded-xl p-3.5">
          <div className="text-[11px] font-semibold text-primary mb-1 flex items-center gap-1.5">
            <Flag className="h-4 w-4" />
            RECOMMENDED NEXT ACTION
          </div>
          <p className="text-[13px] text-[#44474E] leading-relaxed">
            Interviews for this role often start within a week of reaching Under
            Review. Use the time to close your System Design gap so you're ready
            if one is scheduled.
          </p>
        </div>

        <div className="mb-4">
          <div className="text-[11px] font-semibold text-muted mb-2">
            RELEVANT SKILL GAPS
          </div>
          <div className="flex flex-wrap gap-2">
            <a
              href="#gap-1"
              className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[12px] border border-border text-[#44474E] hover:border-primary hover:text-primary transition-colors"
            >
              <Plus className="h-3.5 w-3.5 text-warning" /> System Design
            </a>
            <a
              href="#gap-2"
              className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[12px] border border-border text-[#44474E] hover:border-primary hover:text-primary transition-colors"
            >
              <Plus className="h-3.5 w-3.5 text-warning" /> GraphQL
            </a>
          </div>
        </div>

        <div className="mb-4">
          <div className="text-[11px] font-semibold text-muted mb-2">
            INTERVIEW PREPARATION SUGGESTIONS
          </div>
          <ul className="space-y-1.5">
            <li className="flex items-start gap-2 text-[13px] text-[#44474E]">
              <Check className="h-4 w-4 text-success mt-0.5" />
              Review system design fundamentals — scaling, caching, trade-offs
            </li>
            <li className="flex items-start gap-2 text-[13px] text-[#44474E]">
              <Check className="h-4 w-4 text-success mt-0.5" />
              Practice explaining your Next.js migration project end-to-end
            </li>
          </ul>
        </div>

        <div>
          <div className="text-[11px] font-semibold text-muted mb-2">
            RELATED ROADMAP STEPS
          </div>
          <div className="space-y-2">
            <a
              href="#step-1"
              className="flex items-center gap-3 p-2.5 border border-border rounded-xl hover:bg-background transition-colors"
            >
              <GraduationCap className="h-4 w-4 text-primary" />
              <span className="text-[12.5px] font-medium flex-1">
                System Design Fundamentals — Phase 1
              </span>
              <span className="text-[11px] text-muted">75%</span>
            </a>
            <a
              href="#step-2"
              className="flex items-center gap-3 p-2.5 border border-border rounded-xl hover:bg-background transition-colors"
            >
              <GraduationCap className="h-4 w-4 text-primary" />
              <span className="text-[12.5px] font-medium flex-1">
                GraphQL in Practice — Phase 2
              </span>
              <span className="text-[11px] text-muted">10%</span>
            </a>
          </div>
        </div>
      </div>
    </div>
  );
}
