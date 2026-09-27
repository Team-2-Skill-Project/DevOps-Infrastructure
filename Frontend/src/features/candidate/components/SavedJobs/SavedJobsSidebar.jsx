import { ChevronRight, Sparkles } from "lucide-react";
import { Button } from "@/components/ui/button";
import { PortfolioHealth } from "./SavedJobsStates";

export function AiAdvisor({ aiState, onStateChange, onToast }) {
  const content = {
    success: [
      "Bridge 2 Missing Skills for TechNova",
      "Completing the guided CI/CD & Automated Testing modules will increase your interview callback rate by an estimated 28%.",
      "Launch Personalized Learning Path",
    ],
    low: [
      "3 roles dropped below 50% stack overlap",
      "Requirements changed after employer updates. Weak matches are dimmed in the feed.",
      "Re-run Full Analysis",
    ],
    missing: [
      "Verification data incomplete",
      "Could not verify credentials from public repos. Scores derived from CV keywords only.",
      "Sync GitHub Profile",
    ],
    failure: [
      "Match model unavailable",
      "Skill-verification endpoint timed out. Fallback matcher active.",
      "Retry Connection",
    ],
  };
  if (aiState === "analyzing")
    return (
      <div className="rounded-2xl p-6 shadow-md bg-primary text-primary-foreground">
        <div className="flex items-center gap-2 text-[11px] uppercase font-bold mb-3">
          <span className="w-4 h-4 border-2 border-surface/30 border-t-secondary rounded-full animate-spin" />
          AI Match Gap Optimizer
        </div>
        <h4 className="font-dm-sans text-lg font-bold mb-3">
          Re-scoring bookmarks...
        </h4>
        <p className="text-[11px] text-surface/70">
          Cross-validating repo evidence against ATS requirements...
        </p>
      </div>
    );
  const [title, description, action] = content[aiState] || content.success;
  return (
    <div
      className={`rounded-2xl p-6 shadow-md ${aiState === "low" || aiState === "failure" ? "bg-ink text-surface" : "bg-primary text-primary-foreground"}`}
    >
      <div className="flex items-center gap-2 text-[11px] uppercase text-accent font-bold mb-3">
        <Sparkles className="w-4 h-4 text-secondary" />
        AI Match Gap Optimizer
      </div>
      <h4 className="font-dm-sans text-lg font-bold mb-2">{title}</h4>
      <p className="text-xs text-surface/80 leading-relaxed mb-4">
        {description}
      </p>
      <Button
        onClick={() => {
          onStateChange(
            aiState === "success" || aiState === "missing"
              ? "analyzing"
              : "analyzing",
          );
          onToast("AI Advisor", action);
        }}
        className="w-full bg-surface text-primary text-xs font-bold py-3 px-4 rounded-xl"
      >
        {action}
        <ChevronRight className="w-3.5 h-3.5" />
      </Button>
    </div>
  );
}

export default function SavedJobsSidebar({
  aiState,
  onAiStateChange,
  onToast,
}) {
  return (
    <div className="lg:col-span-4 space-y-5">
      <PortfolioHealth />
      <AiAdvisor
        aiState={aiState}
        onStateChange={onAiStateChange}
        onToast={onToast}
      />
      <div className="bg-surface border border-border rounded-2xl p-4 sm:p-6 shadow-xs">
        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
          <h4 className="font-dm-sans text-base font-bold text-primary">
            Target Preferences
          </h4>
          <Button className="text-xs font-semibold text-secondary">
            Edit Preferences
          </Button>
        </div>
        <div className="space-y-3 text-xs">
          {[
            ["Target Roles", "Flutter, Senior Frontend"],
            ["Location Preference", "Cairo, Egypt / Remote"],
            ["Work Arrangement", "Hybrid, Remote"],
            ["Job Type", "Full-time, Internship"],
          ].map(([label, value]) => (
            <div
              key={label}
              className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 py-1.5 border-b border-border"
            >
              <span className="text-muted">{label}</span>
              <span className="font-semibold text-primary">{value}</span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
