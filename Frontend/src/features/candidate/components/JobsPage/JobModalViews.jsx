import { motion } from "framer-motion";
import { CircleAlert, CircleCheck, StickyNotes } from "lucide-react";
import Modal from "@/components/shared/Modal";
import Status from "@/components/shared/Status";
import JobApplicationModal from "./JobApplicationModal";

function Spinner() {
  return (
    <div className="h-10 w-10">
      <motion.div
        className="h-10 w-10 rounded-full border-[3px] border-border border-t-primary"
        animate={{ rotate: 360 }}
        transition={{ repeat: Infinity, duration: 0.8, ease: "linear" }}
      />
    </div>
  );
}

export default function JobModalViews({
  view,
  activeJob,
  form,
  onClose,
  onRetry,
  onSubmit,
}) {
  if (view === "form") {
    return (
      <JobApplicationModal
        job={activeJob}
        form={form}
        onClose={onClose}
        onSubmit={onSubmit}
      />
    );
  }

  if (view === "loading") {
    return (
      <Modal onClose={() => {}}>
        <Status
          icon={<Spinner />}
          title="Checking Application Status"
          subtitle="We are verifying if you have previously applied for this position and available submission routes."
          secondaryButton={{ label: "Cancel", disabled: true }}
          primaryButton={{ label: "Please wait", disabled: true }}
        />
      </Modal>
    );
  }

  if (view === "submitting") {
    return (
      <Modal onClose={() => {}}>
        <Status
          icon={<Spinner />}
          title="Submitting Your Application"
          subtitle="Please do not close this window — this will only take a moment."
          secondaryButton={{ label: "Cancel", disabled: true }}
          primaryButton={{ label: "Submitting", disabled: true }}
        />
      </Modal>
    );
  }

  if (view === "success") {
    return (
      <Modal onClose={onClose}>
        <Status
          icon={<CircleCheck className="h-7 w-7 text-success" />}
          iconClassName="border border-success/30 bg-success/10"
          title="Application Submitted!"
          subtitle={
            <>
              Your application for{" "}
              <strong className="text-primary">{activeJob.title}</strong> at{" "}
              {activeJob.company} has been successfully sent. You will receive
              updates as status changes.
            </>
          }
          secondaryButton={{ label: "Close", onClick: onClose }}
          primaryButton={{ label: "View My Applications" }}
        >
          <ApplicationSummary />
        </Status>
      </Modal>
    );
  }

  if (view === "duplicate") {
    return (
      <Modal onClose={onClose}>
        <Status
          icon={<StickyNotes className="text-[28px] text-warning" />}
          iconClassName="border border-warning/30 bg-warning/10"
          title="Already Applied"
          subtitle={
            <>
              You submitted an application for{" "}
              <strong className="text-primary">{activeJob.title}</strong> at{" "}
              {activeJob.company} on August 24, 2026. Multiple applications are
              not permitted.
            </>
          }
          secondaryButton={{ label: "Close", onClick: onClose }}
          primaryButton={{ label: "View Application" }}
        >
          <div className="mb-6 flex items-center justify-between rounded-xl border border-border bg-background/50 p-3 text-xs">
            <span className="inline-flex items-center gap-1.5 rounded-md bg-[#EFF3EF] px-2.5 py-1 text-[11px] font-bold text-success">
              <span className="h-1.5 w-1.5 rounded-full bg-success" />
              Under Review
            </span>
            <span className="text-muted">Last updated: Aug 27, 2026</span>
          </div>
        </Status>
      </Modal>
    );
  }

  if (view === "servererror") {
    return (
      <Modal onClose={onClose}>
        <Status
          icon={<CircleAlert className="h-7 w-7 text-error" />}
          iconClassName="border border-error/30 bg-error/10"
          title="Submission Failed"
          subtitle="A server connection error occurred. Your entered information was not lost — you can try submitting again."
          secondaryButton={{ label: "Cancel", onClick: onClose }}
          primaryButton={{ label: "Retry Submission", onClick: onRetry }}
        />
      </Modal>
    );
  }

  return null;
}

function ApplicationSummary() {
  return (
    <div className="mb-6 space-y-1.5 rounded-xl border border-border bg-background/50 p-3 text-left text-xs text-primary">
      <div className="flex justify-between">
        <span className="text-muted">Method:</span>
        <span className="font-bold">Internal</span>
      </div>
      <div className="flex justify-between">
        <span className="text-muted">Source:</span>
        <span className="font-bold">MatchIn Feed</span>
      </div>
      <div className="flex justify-between">
        <span className="text-muted">Time:</span>
        <span className="font-bold">Just now</span>
      </div>
    </div>
  );
}
