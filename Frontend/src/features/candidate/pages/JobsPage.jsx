import { useState } from "react";
import { useForm } from "react-hook-form";
import { AnimatePresence } from "framer-motion";
import JobCard from "@/components/shared/JobCard";
import { JOBS } from "@/constants/jobsMock";
import JobFilters from "../components/JobsPage/JobFilters";
import JobModalViews from "../components/JobsPage/JobModalViews";

export default function JobsPage() {
  const [view, setView] = useState("none");
  const [filtersOpen, setFiltersOpen] = useState(false);
  const [activeJob, setActiveJob] = useState(JOBS[0]);
  const form = useForm({
    defaultValues: {
      cv: "",
      authorized: "",
      startDate: "",
    },
  });

  function openApply(job) {
    setActiveJob(job);
    form.reset();
    setView("loading");
    setTimeout(() => setView("form"), 900);
  }

  function closeModal() {
    setView("none");
  }

  function attemptSubmit() {
    setView("submitting");
    setTimeout(() => setView("success"), 1500);
  }

  return (
    <div className="flex min-h-screen flex-col bg-background font-sans text--primary antialiased">
      <main className="mx-auto w-full max-w-7xl flex-1 space-y-6 px-4 pb-16 pt-8 md:px-8">
        <JobFilters
          open={filtersOpen}
          onToggle={() => setFiltersOpen((value) => !value)}
        />
        <section className="space-y-4">
          <div className="flex items-center justify-between">
            <h2 className="text-xl font-bold text-primary">
              Recommended Opportunities
            </h2>
            <span className="text-xs text-muted">
              Showing {JOBS.length} of 140 jobs
            </span>
          </div>
          <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            {JOBS.map((job, index) => (
              <JobCard
                key={job.id}
                job={job}
                index={index}
                onApply={openApply}
              />
            ))}
          </div>
        </section>
      </main>

      <AnimatePresence>
        {view !== "none" && (
          <JobModalViews
            view={view}
            activeJob={activeJob}
            form={form}
            onClose={closeModal}
            onRetry={() => setView("form")}
            onSubmit={attemptSubmit}
          />
        )}
      </AnimatePresence>
    </div>
  );
}
