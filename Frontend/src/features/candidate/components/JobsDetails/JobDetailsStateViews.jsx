import JobDetailsSkeleton from "@/components/layouts/skeleton/JobDetailsSkeleton";
import { Button } from "@/components/ui/button";
import { motion } from "framer-motion";
import { ArrowLeft, CircleAlert, RotateCcw, Search } from "lucide-react";

export default function JobDetailsStateViews({ pageState, onStateChange }) {
  return (
    <>
      {pageState === "loading" && <JobDetailsSkeleton />}

      {pageState === "not_found" && (
        <motion.div
          initial={{ opacity: 0, scale: 0.98, y: 12 }}
          animate={{ opacity: 1, scale: 1, y: 0 }}
          transition={{ duration: 0.3, ease: "easeOut" }}
          className="bg-surface border border-border rounded-2xl p-14 text-center shadow-sm my-6"
        >
          <div className="w-16 h-16 rounded-2xl bg-background border border-border flex items-center justify-center mx-auto mb-5 text-3xl">
            <Search className="h-7 w-7 text-secondary" />
          </div>
          <h2 className="font-dm-sans text-3xl font-bold text-primary mb-2">
            Job Post Not Found
          </h2>
          <p className="text-sm text-muted max-w-md mx-auto mb-7">
            This job posting may have been removed by the employer, or the link
            is incorrect. The source listing was last verified 2 hours ago.
          </p>
          <div className="flex items-center justify-center gap-3">
            <Button
              variant="ghost"
              onClick={() => onStateChange("loaded")}
              className="px-5 py-2.5 rounded-xl border border-border bg-surface text-primary text-xs font-bold hover:bg-background"
            >
              <ArrowLeft className="h-4 w-4" /> Back to Last Loaded Job
            </Button>
            <Button
              variant="ghost"
              onClick={() => onStateChange("retry")}
              className="px-5 py-2.5 rounded-xl bg-primary text-primary-foreground text-xs font-bold hover:bg-primary/90"
            >
              Browse Similar Roles
            </Button>
          </div>
        </motion.div>
      )}

      {pageState === "expired_stale" && (
        <motion.div
          initial={{ opacity: 0, y: -10 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.3, ease: "easeOut" }}
          className="mb-6 bg-secondary/10 border border-secondary/20 text-secondary rounded-2xl px-5 py-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3"
        >
          <div className="flex items-start gap-3 text-xs leading-relaxed">
            <CircleAlert className="h-5 w-5 shrink-0" />
            <div>
              <strong className="block text-sm mb-0.5">
                This job post has expired or is no longer accepting
                applications.
              </strong>
              Last verified 2 hours ago · Source listing may have been closed by
              TechNova Labs.
            </div>
          </div>
          <Button
            variant="ghost"
            onClick={() => onStateChange("loaded")}
            className="shrink-0 px-4 py-2 rounded-lg bg-surface border border-secondary/20 text-xs font-bold hover:bg-background"
          >
            View Snapshot Anyway
          </Button>
        </motion.div>
      )}

      {pageState === "error" && (
        <motion.div
          initial={{ opacity: 0, scale: 0.98, y: 12 }}
          animate={{ opacity: 1, scale: 1, y: 0 }}
          transition={{ duration: 0.3, ease: "easeOut" }}
          className="bg-surface border border-border rounded-2xl p-14 text-center shadow-sm my-6"
        >
          <div className="w-16 h-16 rounded-2xl bg-error/10 border border-error/20 flex items-center justify-center mx-auto mb-5 text-3xl">
            <CircleAlert className="h-7 w-7 text-error" />
          </div>
          <h2 className="font-dm-sans text-3xl font-bold text-primary mb-2">
            Unable to Load Job Details
          </h2>
          <p className="text-sm text-muted max-w-md mx-auto mb-2">
            A network error occurred while fetching this role. Your connection
            or our job API may be temporarily unavailable.
          </p>
          <p className="text-[11px] text-error font-bold mb-7">
            Error code: ERR_JOB_FETCH_TIMEOUT
          </p>
          <Button
            variant="ghost"
            onClick={() => onStateChange("retry")}
            className="px-6 py-2.5 rounded-xl bg-primary text-primary-foreground text-xs font-bold hover:bg-primary/90 inline-flex items-center gap-2"
          >
            <RotateCcw className="h-4 w-4" /> Retry Now
          </Button>
        </motion.div>
      )}

      {pageState === "retry" && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          transition={{ duration: 0.25 }}
          className="text-center py-20 space-y-4"
        >
          <div className="w-10 h-10 border-[3px] border-border border-t-primary rounded-full animate-spin mx-auto"></div>
          <p className="text-sm font-semibold text-primary">
            Retrying job connection...
          </p>
        </motion.div>
      )}
    </>
  );
}
