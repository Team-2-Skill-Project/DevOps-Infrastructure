import { Button } from "@/components/ui/button";
import { motion } from "framer-motion";
import {
  Check,
  CircleAlert,
  Compass,
  Rocket,
  RotateCcw,
  UserRound,
  X,
} from "lucide-react";
import JobDetailsHeader from "./JobDetailsHeader";
import JobDetailsSidebar from "./JobDetailsSidebar";

const responsibilities = [
  [
    "Frontend Architecture & Lifecycle",
    "Lead modular mission-critical dashboards with deterministic TanStack Query caching and memory-efficient streaming.",
  ],
  [
    "Design System Governance",
    "Evolve the internal Starlight UI library and enforce WCAG 2.1 AA accessibility standards.",
  ],
  [
    "Client Telemetry & Performance",
    "Audit browser frame rates, optimize canvas memory usage, and drive Web Vitals toward the top decile.",
  ],
  [
    "Peer Mentorship & Standards",
    "Conduct architectural reviews, facilitate frontend guilds, and uphold TypeScript strictness.",
  ],
];

const coreSkills = [
  "React 19+",
  "TypeScript (Strict Mode)",
  "Modern JavaScript (ESNext)",
  "RESTful & WebSocket APIs",
  "State Architecture (Zustand)",
];
const preferredSkills = [
  "Next.js / SSR",
  "Jest & Testing Library",
  "WebGL & Three.js",
];

export default function JobDetailsContent({
  isExpired,
  isBookmarked,
  matchState,
  isInRoadmap,
  onApply,
  onAnalysis,
  onShare,
  onToggleBookmark,
  onRoadmapToggle,
  onOpenMentor,
  onAnalyze,
  onSync,
  onRetry,
  onToast,
}) {
  return (
    <motion.div
      initial="hidden"
      animate="visible"
      variants={{
        hidden: {},
        visible: { transition: { staggerChildren: 0.08, delayChildren: 0.12 } },
      }}
      className="space-y-8"
    >
      <JobDetailsHeader
        isExpired={isExpired}
        isBookmarked={isBookmarked}
        onApply={onApply}
        onAnalysis={onAnalysis}
        onShare={onShare}
        onToggleBookmark={onToggleBookmark}
      />
      <motion.div
        variants={{ hidden: {}, visible: {} }}
        className="grid grid-cols-1 lg:grid-cols-12 gap-7"
      >
        <motion.div
          variants={{
            hidden: { opacity: 0, y: 16 },
            visible: { opacity: 1, y: 0 },
          }}
          transition={{ duration: 0.4, ease: "easeOut" }}
          className="lg:col-span-8 space-y-6"
        >
          <OverviewSection />
          <ResponsibilitiesSection />
          <RequirementsSection />
        </motion.div>
        <motion.div
          variants={{
            hidden: { opacity: 0, y: 16 },
            visible: { opacity: 1, y: 0 },
          }}
          transition={{ duration: 0.4, delay: 0.12, ease: "easeOut" }}
          className="lg:col-span-4 space-y-5"
        >
          <MatchCard
            matchState={matchState}
            isExpired={isExpired}
            isInRoadmap={isInRoadmap}
            onApply={onApply}
            onRoadmapToggle={onRoadmapToggle}
            onOpenMentor={onOpenMentor}
            onAnalyze={onAnalyze}
            onSync={onSync}
            onRetry={onRetry}
            onToast={onToast}
          />
          <JobDetailsSidebar />
        </motion.div>
      </motion.div>
    </motion.div>
  );
}

function OverviewSection() {
  return (
    <motion.section
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.15 }}
      transition={{ duration: 0.4, ease: "easeOut" }}
      className="bg-surface border border-border rounded-2xl p-7 shadow-xs"
    >
      <SectionHeading
        title="Architectural Scope & Mission"
        meta="Role Dossier #TN-882"
      />
      <p className="text-sm text-ink leading-relaxed mb-4">
        TechNova is scaling its unified AI operations console. As a Senior
        Frontend Developer, you will act as the principal engineering bridge
        between our design systems, distributed microservices backends, and
        low-latency visualization surfaces. You will architect predictable,
        high-throughput interfaces consumed by more than 180,000 analytical
        professionals daily.
      </p>
      <p className="text-sm text-ink leading-relaxed mb-6">
        We prioritize code ergonomics, deterministic state architectures,
        zero-cumulative layout shifts, and pixel-precise execution. You will own
        modern client rendering pipelines and drive our multi-tenant dashboard
        infrastructure with React 19 and custom WebGL telemetry widgets.
      </p>
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <Metric
          label="Render Budget"
          value="< 90ms"
          detail="Target interaction latency"
        />
        <Metric
          label="Stack Standard"
          value="React 19 + Vite"
          detail="Modern atomic design token system"
        />
        <Metric
          label="Product Squads"
          value="4 Squads"
          detail="Direct sync with UX Designers"
        />
      </div>
    </motion.section>
  );
}

function ResponsibilitiesSection() {
  return (
    <motion.section
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.15 }}
      transition={{ duration: 0.4, ease: "easeOut" }}
      className="bg-surface border border-border rounded-2xl p-7 shadow-xs"
    >
      <h2 className="font-dm-sans text-2xl font-bold text-primary mb-5">
        Core Responsibilities & Deliverables
      </h2>
      <div className="space-y-4 text-sm text-ink">
        {responsibilities.map(([title, description], index) => (
          <div
            key={title}
            className="flex items-start gap-3.5 p-3.5 rounded-xl bg-background border border-border"
          >
            <div className="w-6 h-6 rounded-lg bg-primary text-primary-foreground flex items-center justify-center text-xs font-bold shrink-0 mt-0.5 border-t border-secondary">
              {index + 1}
            </div>
            <div>
              <strong className="text-primary block mb-0.5 font-semibold">
                {title}:
              </strong>
              {description}
            </div>
          </div>
        ))}
      </div>
    </motion.section>
  );
}

function RequirementsSection() {
  return (
    <motion.section
      initial={{ opacity: 0, y: 16 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.15 }}
      transition={{ duration: 0.4, ease: "easeOut" }}
      className="bg-surface border border-border rounded-2xl p-7 shadow-xs"
    >
      <div className="flex items-center justify-between mb-4">
        <h2 className="font-dm-sans text-2xl font-bold text-primary">
          Technical Requirements Matrix
        </h2>
        <span className="text-xs font-semibold text-primary bg-background border border-border px-2.5 py-1 rounded-lg">
          6 of 6 Verified
        </span>
      </div>
      <SkillGroup title="Mandatory Core Stack" skills={coreSkills} primary />
      <SkillGroup
        title="Complementary & Preferred Skills"
        skills={["Tailwind CSS", ...preferredSkills]}
      />
    </motion.section>
  );
}

function MatchCard({
  matchState,
  isExpired,
  isInRoadmap,
  onApply,
  onRoadmapToggle,
  onOpenMentor,
  onAnalyze,
  onSync,
  onRetry,
  onToast,
}) {
  const status = {
    success: [
      "High Confidence (94%)",
      "text-secondary bg-secondary/10 border-secondary/20",
    ],
    low_confidence: [
      "Low Confidence (58%)",
      "text-warning bg-warning/10 border-warning/20",
    ],
    ai_failure: ["Service Offline", "text-error bg-error/10 border-error/20"],
  }[matchState] || [
    matchState === "analyzing"
      ? "Analyzing..."
      : matchState === "missing_context"
        ? "Needs Sync"
        : "Not Analyzed",
    "text-muted bg-muted/10 border-border",
  ];

  return (
    <section className="bg-surface border border-border rounded-2xl p-6 shadow-sm relative overflow-hidden">
      <div className="absolute top-0 inset-s-0 inset-e-0 h-1.5 bg-secondary" />
      <div className="flex items-center justify-between mb-3 pt-1">
        <span className="inline-flex items-center gap-1.5 text-xs font-bold text-primary uppercase tracking-wider">
          <span className="w-2 h-2 rounded-full bg-secondary" />
          AI Match Analysis
        </span>
        <span
          className={`text-xs font-semibold px-2.5 py-0.5 rounded-md border ${status[1]}`}
        >
          {status[0]}
        </span>
      </div>
      <div className="flex items-center gap-1.5 text-[11px] text-muted mb-4 pb-2.5 border-b border-border">
        Sourced directly from TechNova Career Portal · Verified 2h ago
      </div>
      {matchState === "not_analyzed" && <EmptyMatch onAnalyze={onAnalyze} />}
      {matchState === "analyzing" && <AnalyzingMatch />}
      {matchState === "success" && (
        <SuccessMatch
          isExpired={isExpired}
          isInRoadmap={isInRoadmap}
          onApply={onApply}
          onRoadmapToggle={onRoadmapToggle}
          onOpenMentor={onOpenMentor}
          onToast={onToast}
        />
      )}
      {matchState === "low_confidence" && (
        <LowConfidenceMatch onSync={onSync} onOpenMentor={onOpenMentor} />
      )}
      {matchState === "missing_context" && (
        <MissingContextMatch onSync={onSync} onAnalyze={onAnalyze} />
      )}
      {matchState === "ai_failure" && (
        <FailureMatch onApply={onApply} onRetry={onRetry} />
      )}
    </section>
  );
}

function EmptyMatch({ onAnalyze }) {
  return (
    <div className="text-center py-6">
      <Compass className="mx-auto mb-4 h-8 w-8 text-secondary" />
      <h3 className="font-dm-sans text-xl font-bold text-primary mb-1.5">
        No Match Analysis Yet
      </h3>
      <p className="text-xs text-muted leading-relaxed mb-5">
        Run the AI engine to compare your verified profile against TechNova's
        core requirements.
      </p>
      <Button
        variant="ghost"
        onClick={onAnalyze}
        className="px-5 py-2.5 rounded-xl bg-primary text-primary-foreground text-xs font-bold hover:bg-primary/90"
      >
        Analyze Match <Rocket className="h-4 w-4" />
      </Button>
    </div>
  );
}
function AnalyzingMatch() {
  return (
    <div className="space-y-3 py-2">
      <div className="flex items-center gap-2 text-[11px] text-muted">
        <div className="w-3.5 h-3.5 border-2 border-border border-t-secondary rounded-full animate-spin" />
        Evaluating profile commits & stack depth...
      </div>
      <div className="h-3 w-full animate-pulse rounded-lg bg-border" />
      <div className="h-3 w-5/6 animate-pulse rounded-lg bg-border" />
      <div className="h-16 w-full animate-pulse rounded-lg bg-border" />
    </div>
  );
}
function SuccessMatch({
  isExpired,
  isInRoadmap,
  onApply,
  onRoadmapToggle,
  onOpenMentor,
  onToast,
}) {
  return (
    <>
      <div className="flex items-baseline justify-between mb-2">
        <h3 className="font-dm-sans text-2xl font-bold text-primary">
          Strong Match
        </h3>
        <span className="text-sm font-bold text-secondary">7 of 9 Matched</span>
      </div>
      <div className="w-full bg-border h-2.5 rounded-full overflow-hidden mb-3 p-0.5 flex">
        <div
          className="bg-primary h-full rounded-l-full"
          style={{ width: "78%" }}
        />
        <div
          className="bg-secondary h-full rounded-r-full"
          style={{ width: "16%" }}
        />
      </div>
      <div className="bg-background border border-border rounded-xl p-3 mb-4 text-xs text-muted">
        Calculated from Alex's verified profile: production repositories, React
        19 mastery, strict TypeScript, and multi-tenant UI architecture
        experience.
      </div>
      <Button
        variant="ghost"
        onClick={onApply}
        disabled={isExpired}
        className="w-full bg-primary text-primary-foreground font-bold py-3.5 rounded-xl text-sm mb-3"
      >
        Apply with MatchIn Profile
      </Button>
      <div className="grid grid-cols-2 gap-2 text-xs">
        <Button
          variant="ghost"
          onClick={onRoadmapToggle}
          className="py-2.5 px-3 rounded-xl border border-border bg-background text-primary font-semibold"
        >
          {isInRoadmap ? (
            <>
              <Check className="h-4 w-4" /> Added to Roadmap
            </>
          ) : (
            "Add to Roadmap"
          )}
        </Button>
        <Button
          variant="ghost"
          onClick={onOpenMentor}
          className="py-2.5 px-3 rounded-xl border border-border bg-background text-primary font-semibold"
        >
          Ask AI Mentor
        </Button>
      </div>
      <Button
        variant="ghost"
        onClick={() =>
          onToast("Pathway opened: Jest & Monorepo CI/CD for TechNova")
        }
        className="w-full mt-3 text-xs bg-primary text-primary-foreground"
      >
        Start Guided Path <Rocket className="h-4 w-4" />
      </Button>
    </>
  );
}
function LowConfidenceMatch({ onSync, onOpenMentor }) {
  return (
    <>
      <h3 className="font-dm-sans text-2xl font-bold text-primary mb-2">
        Partial Match
      </h3>
      <div className="bg-warning/10 border border-warning/20 rounded-xl p-3 mb-4 text-xs text-muted">
        Core React identified, but micro-frontend and automated testing
        verification data are missing.
      </div>
      <div className="flex flex-wrap gap-1.5 mb-4">
        {["Jest Testing", "CI/CD Pipelines", "WebGL"].map((item) => (
          <span
            key={item}
            className="bg-secondary/10 text-secondary border border-secondary/20 text-xs font-semibold px-2.5 py-1 rounded-md"
          >
            <X className="h-3.5 w-3.5" /> {item}
          </span>
        ))}
      </div>
      <div className="grid grid-cols-2 gap-2">
        <Button
          variant="ghost"
          onClick={onSync}
          className="py-2.5 rounded-xl border border-border bg-background text-primary font-semibold"
        >
          Sync GitHub
        </Button>
        <Button
          variant="ghost"
          onClick={onOpenMentor}
          className="py-2.5 rounded-xl border border-border bg-background text-primary font-semibold"
        >
          Ask AI Mentor
        </Button>
      </div>
    </>
  );
}
function MissingContextMatch({ onSync, onAnalyze }) {
  return (
    <div className="text-center py-4">
      <UserRound className="mx-auto mb-4 h-8 w-8 text-secondary" />
      <h3 className="font-dm-sans text-xl font-bold text-primary mb-1.5">
        Profile Data Incomplete
      </h3>
      <p className="text-xs text-muted mb-4">
        Only <strong className="text-primary">2 of 9 requirements</strong> could
        be matched.
      </p>
      <Button
        variant="ghost"
        onClick={onSync}
        className="w-full bg-primary text-primary-foreground font-bold py-3 rounded-xl text-xs mb-2"
      >
        Sync GitHub Profile
      </Button>
      <Button
        variant="ghost"
        onClick={onAnalyze}
        className="w-full py-2.5 rounded-xl border border-border bg-background text-primary font-semibold text-xs"
      >
        Re-run with Limited Data
      </Button>
    </div>
  );
}
function FailureMatch({ onApply, onRetry }) {
  return (
    <div className="text-center py-6">
      <CircleAlert className="mx-auto mb-4 h-8 w-8 text-error" />
      <h3 className="font-dm-sans text-xl font-bold text-primary mb-1.5">
        Match Calculation Paused
      </h3>
      <p className="text-xs text-muted mb-6">
        The vector embedding endpoint timed out while scoring your profile.
      </p>
      <div className="grid grid-cols-2 gap-2">
        <Button
          variant="ghost"
          onClick={onApply}
          className="py-2.5 rounded-xl border border-border bg-background text-primary font-semibold"
        >
          Apply Anyway
        </Button>
        <Button
          variant="ghost"
          onClick={onRetry}
          className="py-2.5 rounded-xl bg-primary text-primary-foreground font-bold"
        >
          <RotateCcw className="h-4 w-4" /> Retry Analysis
        </Button>
      </div>
    </div>
  );
}
function SectionHeading({ title, meta }) {
  return (
    <div className="flex items-center justify-between mb-4 pb-3 border-b border-border">
      <h2 className="font-dm-sans text-2xl font-bold text-primary">{title}</h2>
      <span className="text-xs text-muted">{meta}</span>
    </div>
  );
}
function Metric({ label, value, detail }) {
  return (
    <div className="bg-background border border-border p-4 rounded-xl">
      <span className="text-xs text-muted block mb-1 font-medium">{label}</span>
      <span className="font-bold text-primary text-lg">{value}</span>
      <span className="text-[11px] text-muted block mt-0.5">{detail}</span>
    </div>
  );
}
function SkillGroup({ title, skills, primary = false }) {
  return (
    <div className="pt-3 first:pt-0">
      <span className="text-xs font-bold uppercase tracking-wider text-muted block mb-2.5">
        {title}
      </span>
      <div className="flex flex-wrap gap-2">
        {skills.map((skill) => (
          <span
            key={skill}
            className={`inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg ${primary ? "bg-primary text-primary-foreground" : "bg-background border border-border text-muted"}`}
          >
            {primary && <Check className="h-3.5 w-3.5 text-secondary" />}
            {skill}
          </span>
        ))}
      </div>
    </div>
  );
}
