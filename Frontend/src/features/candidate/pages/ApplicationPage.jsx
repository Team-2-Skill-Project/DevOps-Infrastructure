import { useMemo, useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import ApplicationDetailsDialog from "../components/ApplicationPage/ApplicationDetailsDialog";
import ApplicationFilters from "../components/ApplicationPage/ApplicationFilters";
import ApplicationList from "../components/ApplicationPage/ApplicationList";
import ApplicationStateViews from "../components/ApplicationPage/ApplicationStateViews";
import ApplicationStats from "../components/ApplicationPage/ApplicationStats";
import ApplicationToast from "../components/ApplicationPage/ApplicationToast";
import { INITIAL_APPLICATIONS } from "@/constants/application";

export default function ApplicationTracker() {
  const [viewState, setViewState] = useState("loaded");
  const [applications, setApplications] = useState(INITIAL_APPLICATIONS);
  const [searchQuery, setSearchQuery] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [sortBy, setSortBy] = useState("recent");
  const [activeModalApp, setActiveModalApp] = useState(null);
  const [toastMessage, setToastMessage] = useState(null);

  const showToast = (message) => {
    setToastMessage(message);
    setTimeout(() => setToastMessage(null), 3000);
  };

  const handleWithdraw = (id) => {
    setApplications((previous) =>
      previous.map((app) =>
        app.id === id ? { ...app, status: "withdrawn" } : app,
      ),
    );
    showToast("Application marked as withdrawn.");
  };

  const filteredApplications = useMemo(() => {
    return applications
      .filter((app) => {
        const normalizedQuery = searchQuery.toLowerCase();
        const matchesQuery =
          app.title.toLowerCase().includes(normalizedQuery) ||
          app.company.toLowerCase().includes(normalizedQuery);
        const matchesStatus =
          statusFilter === "all" || app.status === statusFilter;
        return matchesQuery && matchesStatus;
      })
      .sort((first, second) => {
        if (sortBy === "match") return second.matchScore - first.matchScore;
        return new Date(second.appliedDate) - new Date(first.appliedDate);
      });
  }, [applications, searchQuery, statusFilter, sortBy]);

  const stats = useMemo(
    () => ({
      total: applications.length,
      underReview: applications.filter((app) => app.status === "under-review")
        .length,
      interview: applications.filter((app) => app.status === "interview")
        .length,
      offer: applications.filter((app) => app.status === "offer").length,
      rejected: applications.filter((app) => app.status === "rejected").length,
    }),
    [applications],
  );

  const clearFilters = () => {
    setSearchQuery("");
    setStatusFilter("all");
  };

  return (
    <div className="min-h-screen bg-background text-[#1B1C1A] font-inter antialiased selection:bg-primary selection:text-white pb-12">
      <main className="max-w-270 mx-auto px-4 sm:px-5 py-6 sm:py-8">
        <div className="mb-7">
          <h1 className="font-dm-sans font-extrabold text-2xl sm:text-[26px] tracking-tight text-[#1B1C1A]">
            Application Tracker
          </h1>
          <p className="text-[13.5px] text-muted mt-1">
            Every application you've sent, in one place — with its current stage
            and next step.
          </p>
        </div>

        <AnimatePresence mode="wait">
          {viewState !== "loaded" && (
            <ApplicationStateViews
              key={viewState}
              viewState={viewState}
              onLoaded={() => setViewState("loaded")}
            />
          )}

          {viewState === "loaded" && (
            <motion.div
              key="loaded"
              initial={{ opacity: 0 }}
              animate={{ opacity: 1 }}
              exit={{ opacity: 0 }}
              className="space-y-6"
            >
              <ApplicationStats stats={stats} />
              <ApplicationFilters
                searchQuery={searchQuery}
                onSearchChange={setSearchQuery}
                sortBy={sortBy}
                onSortChange={setSortBy}
                statusFilter={statusFilter}
                onStatusChange={setStatusFilter}
              />
              <ApplicationList
                applications={filteredApplications}
                onOpenDetails={(app) => {
                  setActiveModalApp(app);
                }}
                onWithdraw={handleWithdraw}
                onClearFilters={clearFilters}
              />
            </motion.div>
          )}
        </AnimatePresence>
      </main>

      <ApplicationDetailsDialog
        app={activeModalApp}
        onClose={() => setActiveModalApp(null)}
        onToast={showToast}
      />
      <ApplicationToast message={toastMessage} />
    </div>
  );
}
