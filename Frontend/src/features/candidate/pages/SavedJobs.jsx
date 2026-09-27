import React, { useEffect, useState } from "react";
import { AnimatePresence } from "framer-motion";
import { EmptySavedJobs } from "../components/SavedJobs/SavedJobsStates";
import JobCard, { JobCardSkeleton } from "../components/SavedJobs/JobCard";
import SavedJobsHeader from "../components/SavedJobs/SavedJobsHeader";
import SavedJobsSidebar from "../components/SavedJobs/SavedJobsSidebar";
import SavedJobsToolbar from "../components/SavedJobs/SavedJobsToolbar";
import ToastList from "../components/SavedJobs/ToastList";

const INITIAL_JOBS = [
  {
    id: "1",
    company: "TechNova Labs",
    logo: "TN",
    title: "Senior Flutter & Mobile Architect",
    badge: "Fresh • 2d ago",
    location: "Cairo, Egypt (Hybrid)",
    salary: "$3,800 - $4,800 / mo",
    verification: "Verified direct from TechNova ATS • Updated 4h ago",
    fitScore: 89,
    fitFraction: "8/9 Fit",
    fitLabel: "High Confidence Match",
    skills: ["Flutter", "Dart", "Bloc / Riverpod", "REST APIs"],
    extraSkillsCount: 3,
    description:
      "Lead the mobile architecture for TechNova's consumer AI console app.",
    responsibilities: [
      "Architect modular Flutter features",
      "Enforce automated testing coverage",
      "Own CI/CD mobile pipelines",
    ],
    source: "TechNova ATS",
    method: "Internal Fast Track",
    experience: "5+ Years",
    rationaleTitle: "AI Match Rationale & Evidence",
    rationaleSub: "Score derived from validated production projects",
    strength:
      "Strong Flutter Bloc Architecture and offline-first caching experience.",
    gap: "Missing: Automated Appium / Patrol UI testing.",
    gapActionText: "Bridge in 1.5h →",
    note: "Verified team values clean architecture & unit test coverage.",
    deadline: "Deadline: Oct 28 (14 days remaining)",
    status: "default",
    appliedText: "",
  },
  {
    id: "2",
    company: "PulseHealth Systems",
    logo: "PH",
    title: "Lead UI/UX Systems Engineer",
    badge: "Fresh • 1d ago",
    location: "Remote (Worldwide)",
    salary: "$4,200 - $5,400 / mo",
    verification: "Direct Verified Hire • Synchronized 2h ago",
    fitScore: 78,
    fitFraction: "7/9 Fit",
    fitLabel: "Direct Verified Hire",
    skills: ["React 19", "TypeScript", "Design Systems", "WCAG 2.1 AA"],
    extraSkillsCount: 0,
    description:
      "Own PulseHealth's Meridian design system used by six product squads.",
    responsibilities: [
      "Evolve design tokens",
      "Run accessibility audits",
      "Mentor product squads",
    ],
    source: "PulseHealth Portal",
    method: "Direct Verified Hire",
    experience: "Technical Screening",
    rationaleTitle: "AI Match Rationale: UI Accessibility & Systems",
    rationaleSub: "Strong alignment with design token standards",
    strength:
      "Demonstrated mastery in design token pipelines and React systems.",
    gap: "Highlight WCAG compliance case studies in CV.",
    gapActionText: "Instant Align Resume →",
    note: "",
    deadline: "Applied in Review • Sep 8 (Technical Screening Stage)",
    status: "in_review",
    appliedText: "Applied in Review • Sep 8 (Technical Screening Stage)",
  },
  {
    id: "3",
    company: "CloudScale Tech",
    logo: "CS",
    title: "Frontend Infrastructure Engineer",
    badge: "Expiring soon • 36h",
    location: "Cairo (On-site)",
    salary: "$3,200 - $4,000 / mo",
    verification: "",
    fitScore: 67,
    fitFraction: "6/9 Fit",
    fitLabel: "Direct Team Lead",
    skills: ["JavaScript", "Webpack to Vite", "CI/CD Pipelines"],
    extraSkillsCount: 0,
    description:
      "Drive CloudScale's frontend infrastructure and shared delivery pipelines.",
    responsibilities: [
      "Lead Webpack to Vite migration",
      "Build shared pipelines",
      "Optimize bundle budgets",
    ],
    source: "CloudScale Careers",
    method: "Direct Team Lead",
    experience: "Deadline: tomorrow 11:59 PM",
    rationaleTitle: "",
    rationaleSub: "",
    strength: "",
    gap: "",
    gapActionText: "",
    note: "",
    deadline: "Applications close tomorrow at 11:59 PM",
    status: "default",
    appliedText: "",
  },
];

export default function SavedJobsDashboard() {
  const [jobs, setJobs] = useState(INITIAL_JOBS);
  const [isLoading, setIsLoading] = useState(true);
  const [selectedIds, setSelectedIds] = useState([]);
  const [expandedJobId, setExpandedJobId] = useState(null);
  const [editingNoteId, setEditingNoteId] = useState(null);
  const [noteInputValue, setNoteInputValue] = useState("");
  const [aiState, setAiState] = useState("success");
  const [toasts, setToasts] = useState([]);

  useEffect(() => {
    if (aiState !== "analyzing") return undefined;
    const timer = setTimeout(() => setAiState("success"), 2500);
    return () => clearTimeout(timer);
  }, [aiState]);

  useEffect(() => {
    const timer = setTimeout(() => setIsLoading(false), 700);
    return () => clearTimeout(timer);
  }, []);

  const triggerToast = (title, message) => {
    const id = Date.now();
    setToasts((current) => [...current, { id, title, message }]);
    setTimeout(
      () => setToasts((current) => current.filter((toast) => toast.id !== id)),
      3500,
    );
  };

  const applyToJobs = (ids) => {
    setJobs((current) =>
      current.map((job) =>
        ids.includes(job.id)
          ? {
              ...job,
              status: "applied",
              appliedText: `Applied just now • ${job.company} will review within 48h`,
            }
          : job,
      ),
    );
  };

  const handleBatchApply = () => {
    if (!selectedIds.length)
      return triggerToast(
        "Selection Required",
        "Select at least one role to apply.",
      );
    applyToJobs(selectedIds);
    triggerToast(
      "Batch Fast Track",
      `Successfully applied to ${selectedIds.length} position(s).`,
    );
  };

  const handleArchive = () => {
    if (!selectedIds.length)
      return triggerToast(
        "Selection Required",
        "Select at least one role to archive.",
      );
    setJobs((current) =>
      current.filter((job) => !selectedIds.includes(job.id)),
    );
    setSelectedIds([]);
    triggerToast("Roles Archived", "Selected roles moved to your archive.");
  };

  const saveNote = (id) => {
    setJobs((current) =>
      current.map((job) =>
        job.id === id ? { ...job, note: noteInputValue.trim() } : job,
      ),
    );
    setEditingNoteId(null);
  };

  return (
    <div className="min-h-screen bg-background text-ink font-[#Plus_Jakarta_Sans,sans-serif] flex flex-col antialiased">
      <main className="flex-1 max-w-340 w-full mx-auto px-4 sm:px-6 py-6 sm:py-8">
        <SavedJobsHeader jobsCount={jobs.length} />
        <SavedJobsToolbar jobsCount={jobs.length} />

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-7">
          <div className="lg:col-span-8 space-y-4">
            {isLoading ? (
              <div className="space-y-4" aria-label="Loading saved jobs">
                {[1, 2, 3].map((skeleton) => (
                  <JobCardSkeleton key={skeleton} />
                ))}
              </div>
            ) : jobs.length ? (
              <AnimatePresence>
                {jobs.map((job) => (
                  <JobCard
                    key={job.id}
                    job={job}
                    aiState={aiState}
                    selected={selectedIds.includes(job.id)}
                    expanded={expandedJobId === job.id}
                    editingNote={editingNoteId === job.id}
                    noteValue={noteInputValue}
                    onSelect={() =>
                      setSelectedIds((current) =>
                        current.includes(job.id)
                          ? current.filter((id) => id !== job.id)
                          : [...current, job.id],
                      )
                    }
                    onExpand={() =>
                      setExpandedJobId(expandedJobId === job.id ? null : job.id)
                    }
                    onApply={(id) => applyToJobs([id])}
                    onStartEdit={(selectedJob) => {
                      setEditingNoteId(selectedJob.id);
                      setNoteInputValue(selectedJob.note || "");
                    }}
                    onSaveNote={saveNote}
                    onCancelNote={() => setEditingNoteId(null)}
                    onNoteChange={setNoteInputValue}
                    onAction={(selectedJob) =>
                      triggerToast(
                        "Action Triggered",
                        `Initiated action for ${selectedJob.company}`,
                      )
                    }
                  />
                ))}
              </AnimatePresence>
            ) : (
              <EmptySavedJobs />
            )}
          </div>
          <SavedJobsSidebar
            aiState={aiState}
            onAiStateChange={setAiState}
            onToast={triggerToast}
          />
        </div>
      </main>
      <ToastList
        toasts={toasts}
        onRemove={(id) =>
          setToasts((current) => current.filter((toast) => toast.id !== id))
        }
      />
    </div>
  );
}
