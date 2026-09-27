export default function SavedJobsHeader({ jobsCount }) {
  return (
    <div className="bg-surface border border-border rounded-2xl p-5 sm:p-6 lg:p-7 mb-8 shadow-[0_2px_12px_-4px_rgba(31,54,92,0.04)] flex flex-col lg:flex-row lg:items-center justify-between gap-5 lg:gap-6">
      <div className="order-1 max-w-2xl min-w-0">
        <div className="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold uppercase tracking-wider text-muted mb-2">
          <span>Candidate Workspace</span>
          <span className="text-border">•</span>
          <span className="text-secondary font-bold">
            Bookmarked Opportunities
          </span>
        </div>
        <h1 className="font-dm-sans text-2xl sm:text-3xl md:text-4xl text-primary font-bold tracking-tight mb-2 wrap-break-word">
          Saved Opportunities
        </h1>
        <p className="text-sm text-muted leading-relaxed wrap-break-word">
          Manage, annotate, and accelerate positions tailored to your
          engineering background. AI continuously tracks requirement changes and
          deadline windows.
        </p>
      </div>
      <div className="order-2 grid w-full grid-cols-3 gap-1.5 sm:gap-3 bg-background border border-border p-2.5 sm:p-3 rounded-xl lg:w-auto lg:min-w-85">
        <Stat value={jobsCount} label="Saved Roles" />
        <Stat value="5" label="High Fit" />
        <Stat value="2" label="Expiring" last />
      </div>
    </div>
  );
}

function Stat({ value, label, last = false }) {
  return (
    <div
      className={`px-3 py-2 text-center ${last ? "" : "border-r border-border"}`}
    >
      <span className="block text-xl font-bold text-primary">{value}</span>
      <span className="text-[11px] font-semibold text-muted uppercase tracking-wide">
        {label}
      </span>
    </div>
  );
}
