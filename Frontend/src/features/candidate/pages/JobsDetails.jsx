import { useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { useForm } from "react-hook-form";
import JobDetailsContent from "../components/JobsDetails/JobDetailsContent";
import JobDetailsModals from "../components/JobsDetails/JobDetailsModals";
import JobDetailsStateViews from "../components/JobsDetails/JobDetailsStateViews";
import JobModalViews from "../components/JobsPage/JobModalViews";

const ACTIVE_JOB = {
  title: "Senior Frontend Developer",
  company: "TechNova Labs",
};

export default function JobDetailsPage() {
  const [pageState, setPageState] = useState("loaded");
  const [matchState, setMatchState] = useState("success");
  const [isBookmarked, setIsBookmarked] = useState(false);
  const [isInRoadmap, setIsInRoadmap] = useState(false);
  const [activeModal, setActiveModal] = useState(null);
  const [applyStep, setApplyStep] = useState("form");
  const [toast, setToast] = useState(null);
  const [mentorMessages, setMentorMessages] = useState([
    {
      sender: "mentor",
      text: "I've reviewed this role's requirements. Your React 19 and TypeScript depth are strong selling points, but TechNova's hiring bar emphasizes testing culture. Want a focused prep plan?",
    },
  ]);
  const applicationForm = useForm({
    defaultValues: { cv: "resume-2026.pdf", authorized: "", startDate: "" },
  });

  function showToast(message) {
    setToast(message);
    setTimeout(() => setToast(null), 3500);
  }

  function handlePageState(stateKey) {
    const matchStates = [
      "not_analyzed",
      "analyzing",
      "success",
      "low_confidence",
      "missing_context",
      "ai_failure",
    ];
    if (matchStates.includes(stateKey)) {
      setPageState("loaded");
      setMatchState(stateKey);
      if (stateKey === "analyzing") {
        setTimeout(() => setMatchState("success"), 2500);
      }
      return;
    }
    if (stateKey === "retry") {
      setPageState("retry");
      setTimeout(() => {
        setPageState("loaded");
        setMatchState("success");
      }, 1800);
      return;
    }
    setPageState(stateKey);
  }

  function openApplication() {
    applicationForm.reset();
    setApplyStep("form");
    setActiveModal("apply");
  }

  function submitApplication() {
    setApplyStep("submitting");
    setTimeout(() => setApplyStep("success"), 1500);
  }

  function askMentor(question) {
    setMentorMessages((messages) => [
      ...messages,
      { sender: "user", text: question },
      {
        sender: "mentor",
        text: "Great question — based on TN-882, emphasize your multi-tenant dashboard work and be ready for a live React 19 + testing exercise. Want a mock interview drill?",
      },
    ]);
  }

  return (
    <div className="min-h-screen bg-background font-sans text-ink antialiased">
      <main className="mx-auto w-full max-w-340 flex-1 px-6 py-8">
        <JobDetailsStateViews
          pageState={pageState}
          onStateChange={handlePageState}
        />
        {(pageState === "loaded" || pageState === "expired_stale") && (
          <>
            <JobDetailsContent
              isExpired={pageState === "expired_stale"}
              isBookmarked={isBookmarked}
              matchState={matchState}
              isInRoadmap={isInRoadmap}
              onApply={openApplication}
              onAnalysis={() =>
                showToast(
                  "Full market compensation analysis loaded for Cairo Tech Center",
                )
              }
              onShare={() => showToast("Job link copied to clipboard!")}
              onToggleBookmark={() => setIsBookmarked((value) => !value)}
              onRoadmapToggle={() => setIsInRoadmap((value) => !value)}
              onOpenMentor={() => setActiveModal("mentor")}
              onAnalyze={() => handlePageState("analyzing")}
              onSync={() =>
                showToast("Connecting GitHub & portfolio analyzer...")
              }
              onRetry={() => handlePageState("retry")}
              onToast={showToast}
            />
          </>
        )}
      </main>

      {activeModal === "apply" && (
        <JobModalViews
          view={applyStep}
          activeJob={ACTIVE_JOB}
          form={applicationForm}
          onClose={() => setActiveModal(null)}
          onRetry={() => setApplyStep("form")}
          onSubmit={submitApplication}
        />
      )}
      <JobDetailsModals
        activeModal={activeModal}
        onClose={() => setActiveModal(null)}
        mentorMessages={mentorMessages}
        onAskMentorQuestion={askMentor}
      />

      <AnimatePresence>
        {toast && (
          <motion.div
            initial={{ opacity: 0, y: 16, scale: 0.96 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, y: 8, scale: 0.96 }}
            transition={{ duration: 0.2, ease: "easeOut" }}
            className="fixed bottom-6 inset-e-6 z-50 rounded-xl border border-border bg-primary px-4 py-3 text-primary-foreground shadow-lg"
          >
            <p className="text-xs font-medium">{toast}</p>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}
