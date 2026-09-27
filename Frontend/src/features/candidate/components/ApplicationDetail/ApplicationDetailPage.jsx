import React, { useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import ApplicationLoadingState from "./ApplicationLoadingState";
import ApplicationNotFoundState from "./ApplicationNotFoundState";
import ApplicationErrorState from "./ApplicationErrorState";
import ApplicationDetailBanner from "./ApplicationDetailBanner";
import ApplicationDetailHero from "./ApplicationDetailHero";
import ApplicationDetailInsight from "./ApplicationDetailInsight";
import ApplicationDetailInfoGrid from "./ApplicationDetailInfoGrid";
import ApplicationDetailTimeline from "./ApplicationDetailTimeline";
import ApplicationDetailNotes from "./ApplicationDetailNotes";

export default function ApplicationDetailPage() {
  const [viewState, setViewState] = useState("loaded");
  const [showNoteForm, setShowNoteForm] = useState(false);
  const [noteText, setNoteText] = useState("");
  const [notes, setNotes] = useState([
    {
      id: 1,
      text: "Recruiter mentioned the team is hiring for two openings — followed up asking about the tech stack.",
      date: "Aug 27, 2026",
    },
    {
      id: 2,
      text: "Tailored resume to highlight design-systems work before applying.",
      date: "Aug 24, 2026",
    },
  ]);

  const handleSaveNote = () => {
    if (!noteText.trim()) return;
    setNotes([
      {
        id: Date.now(),
        text: noteText.trim(),
        date: "Just now",
      },
      ...notes,
    ]);
    setNoteText("");
    setShowNoteForm(false);
  };

  const handleWithdraw = () => {
    if (window.confirm("Withdraw this application? This can't be undone.")) {
      setViewState("withdrawn");
    }
  };

  const isBannerState = ["expired", "withdrawn", "closed"].includes(viewState);

  return (
    <div className="font-sans text-[#1B1C1A] min-h-screen bg-linear-to-b from-background via-[#F5F2EC] to-background bg-size-[100%_260px] bg-no-repeat">
      <main className="max-w-225 mx-auto px-5 py-8">
        <AnimatePresence mode="wait">
          {viewState === "loading" && <ApplicationLoadingState />}
          {viewState === "notfound" && (
            <ApplicationNotFoundState onBack={() => setViewState("loaded")} />
          )}
          {viewState === "error" && (
            <ApplicationErrorState onRetry={() => setViewState("loaded")} />
          )}

          {(viewState === "loaded" || isBannerState) && (
            <motion.div
              key="loaded"
              initial={{ opacity: 0, y: 15 }}
              animate={{ opacity: 1, y: 0 }}
              exit={{ opacity: 0, y: -15 }}
              transition={{ duration: 0.25 }}
            >
              <ApplicationDetailBanner viewState={viewState} />

              <ApplicationDetailHero
                isBannerState={isBannerState}
                setShowNoteForm={setShowNoteForm}
                handleWithdraw={handleWithdraw}
              />

              <ApplicationDetailInsight />
              <ApplicationDetailInfoGrid />
              <ApplicationDetailTimeline
                viewState={viewState}
                isBannerState={isBannerState}
              />

              {!isBannerState && (
                <>
                  <div className="bg-white border border-border rounded-2xl p-6 mb-5 shadow-sm">
                    <h2 className="font-display font-bold text-[15px] mb-4">
                      Interview details
                    </h2>
                    <div className="grid sm:grid-cols-2 gap-3 text-[13px] mb-4">
                      <div className="flex justify-between border-b border-border pb-2">
                        <span className="text-muted">Date &amp; time</span>
                        <span className="font-medium">Not yet scheduled</span>
                      </div>
                      <div className="flex justify-between border-b border-border pb-2">
                        <span className="text-muted">Format</span>
                        <span className="font-medium">Video call</span>
                      </div>
                      <div className="flex justify-between border-b border-border pb-2">
                        <span className="text-muted">Round</span>
                        <span className="font-medium">1 of 2</span>
                      </div>
                      <div className="flex justify-between border-b border-border pb-2">
                        <span className="text-muted">Interviewer</span>
                        <span className="font-medium">To be confirmed</span>
                      </div>
                    </div>
                    <a
                      href="#prep"
                      className="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-warning"
                    >
                      <span className="material-symbols-outlined text-[16px]">
                        mic
                      </span>
                      Prepare for this interview
                      <span className="text-[9px] font-bold bg-warning/15 rounded-full px-1.5 py-0.5">
                        PHASE 2
                      </span>
                    </a>
                  </div>

                  <div className="bg-white border border-border rounded-2xl p-6 mb-5 shadow-sm">
                    <h2 className="font-display font-bold text-[15px] mb-4">
                      Assessment details
                    </h2>
                    <div className="grid sm:grid-cols-2 gap-3 text-[13px]">
                      <div className="flex justify-between border-b border-border pb-2">
                        <span className="text-muted">Type</span>
                        <span className="font-medium">
                          Take-home coding task
                        </span>
                      </div>
                      <div className="flex justify-between border-b border-border pb-2">
                        <span className="text-muted">Due</span>
                        <span className="font-medium">Sep 12, 2026</span>
                      </div>
                    </div>
                  </div>
                </>
              )}

              <ApplicationDetailNotes
                notes={notes}
                showNoteForm={showNoteForm}
                noteText={noteText}
                setShowNoteForm={setShowNoteForm}
                setNoteText={setNoteText}
                handleSaveNote={handleSaveNote}
              />
            </motion.div>
          )}
        </AnimatePresence>
      </main>
    </div>
  );
}
