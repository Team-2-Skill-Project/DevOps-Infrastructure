import * as React from "react";
import { useTranslation } from "react-i18next";
import { useParams, Link } from "react-router-dom";
import {
  ArrowLeft,
  SearchX,
  AlertTriangle,
  Clock,
  CheckCircle2,
  PencilLine,
  Building2,
  RefreshCw,
  ExternalLink,
  Save,
  MapPin,
  Briefcase,
  Award,
  Calendar,
  Lock,
  ChevronRight,
  Loader2,
} from "lucide-react";

import { Skeleton } from "@/components/ui/skeleton";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { cn } from "@/lib/utils";
import JobCard from "@/features/admin/components/JobDetailsPage/JobCard";

const LOCATION_OPTIONS = ["Remote", "Hybrid", "Onsite"];
const TYPE_OPTIONS = ["Full-time", "Part-time", "Contract", "Internship"];
const LEVEL_OPTIONS = [
  "Entry level",
  "Mid level",
  "Senior level",
  "Lead / Principal",
];
const STATUS_OPTIONS = ["Active", "Draft", "Closed"];

const STATUS_STYLES = {
  Active: "bg-success/10 text-success",
  Draft: "bg-muted/12 text-muted",
  Closed: "bg-error/10 text-error",
};

// TODO: replace with a real fetch, e.g. GET /admin/jobs/:jobId
function fetchJobDetails(jobId) {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      if (jobId === "missing") return resolve(null);
      if (jobId === "broken") return reject(new Error("Failed to load job"));
      resolve({
        id: jobId ?? "JOB-1042",
        title: "Senior Frontend Engineer",
        company: { name: "Vercel", initial: "V", verified: true },
        location: "Remote",
        type: "Full-time",
        level: "Senior level",
        status: "Active",
        publishedLabel: "Sep 15, 2026",
        publishedDaysAgo: 7,
        freshnessLabel: "Fresh · 3h ago",
        description:
          "We're looking for a Senior Frontend Engineer to help scale our design system and developer platform. You'll work closely with product and design to ship features used by millions of developers worldwide.",
        responsibilities: [
          "Build and maintain core UI components used across the platform",
          "Partner with design on the next version of the design system",
          "Mentor junior engineers and review pull requests",
        ],
        requiredSkills: ["React", "TypeScript", "Next.js"],
        preferredSkills: ["System Design", "GraphQL"],
        source: "Internal",
        applicationMethod: "Internal — via SkillMatch form",
        postedBy: "Vercel (verified company)",
        sourceUrl: "#",
      });
    }, 700);
  });
}

// TODO: replace with a real mutation, e.g. PATCH /admin/jobs/:jobId
function saveJobDetails(jobId, patch) {
  return new Promise((resolve) => setTimeout(() => resolve(patch), 1200));
}

function updateJobStatus(jobId, status) {
  return new Promise((resolve) => setTimeout(() => resolve(status), 600));
}

export function JobDetailsPage() {
  const { t } = useTranslation("dashboard");
  const { jobId } = useParams();

  const [pageState, setPageState] = React.useState("loading"); // loading | loaded | not-found | error
  const [job, setJob] = React.useState(null);

  const [isEditing, setIsEditing] = React.useState(false);
  const [form, setForm] = React.useState(null);
  const [titleError, setTitleError] = React.useState(false);
  const [saveState, setSaveState] = React.useState("idle"); // idle | saving | success | error

  const [headerStatusMenuOpen, setHeaderStatusMenuOpen] = React.useState(false);
  const [cardStatusMenuOpen, setCardStatusMenuOpen] = React.useState(false);
  const [statusSaving, setStatusSaving] = React.useState(false);

  const load = React.useCallback(() => {
    setPageState("loading");
    fetchJobDetails(jobId)
      .then((data) => {
        if (!data) {
          setPageState("not-found");
          return;
        }
        setJob(data);
        setPageState("loaded");
      })
      .catch(() => setPageState("error"));
  }, [jobId]);

  React.useEffect(() => {
    load();
  }, [load]);

  const isStale = job && job.publishedDaysAgo >= 30;

  const startEditing = () => {
    setForm({
      title: job.title,
      location: job.location,
      type: job.type,
      level: job.level,
      description: job.description,
      responsibilities: job.responsibilities.join("\n"),
      requiredSkills: job.requiredSkills.join(", "),
      preferredSkills: job.preferredSkills.join(", "),
    });
    setTitleError(false);
    setSaveState("idle");
    setIsEditing(true);
  };

  const cancelEditing = () => {
    setIsEditing(false);
    setForm(null);
    setTitleError(false);
    setSaveState("idle");
  };

  const setField = (name, value) =>
    setForm((prev) => ({ ...prev, [name]: value }));

  const handleSave = async () => {
    if (!form.title.trim()) {
      setTitleError(true);
      setSaveState("error");
      window.scrollTo({ top: 0, behavior: "smooth" });
      return;
    }
    setTitleError(false);
    setSaveState("saving");

    try {
      const patch = await saveJobDetails(job.id, {
        title: form.title.trim(),
        location: form.location,
        type: form.type,
        level: form.level,
        description: form.description,
        responsibilities: form.responsibilities
          .split("\n")
          .map((s) => s.trim())
          .filter(Boolean),
        requiredSkills: form.requiredSkills
          .split(",")
          .map((s) => s.trim())
          .filter(Boolean),
        preferredSkills: form.preferredSkills
          .split(",")
          .map((s) => s.trim())
          .filter(Boolean),
      });
      setJob((prev) => ({ ...prev, ...patch }));
      setIsEditing(false);
      setSaveState("success");
      window.scrollTo({ top: 0, behavior: "smooth" });
    } catch {
      setSaveState("error");
    }
  };

  const handleUpdateStatus = async (newStatus) => {
    setStatusSaving(true);
    const status = await updateJobStatus(job.id, newStatus);
    setJob((prev) => ({ ...prev, status }));
    setStatusSaving(false);
    setHeaderStatusMenuOpen(false);
    setCardStatusMenuOpen(false);
  };

  // ---------- Loading ----------
  if (pageState === "loading") {
    return (
      <div className="min-h-screen bg-background px-5 py-8">
        <div className="mx-auto max-w-[900px]">
          <JobCard>
            <div className="flex items-start gap-4">
              <Skeleton className="h-16 w-16 shrink-0 rounded-2xl" />
              <div className="flex-1">
                <Skeleton className="mb-3 h-5 w-56" />
                <Skeleton className="mb-2 h-3 w-36" />
                <Skeleton className="h-3 w-28" />
              </div>
            </div>
          </JobCard>
          <JobCard>
            <Skeleton className="mb-5 h-4 w-32" />
            <div className="space-y-3">
              <Skeleton className="h-3 w-full" />
              <Skeleton className="h-3 w-full" />
              <Skeleton className="h-3 w-3/4" />
            </div>
          </JobCard>
        </div>
      </div>
    );
  }

  // ---------- Not found ----------
  if (pageState === "not-found") {
    return (
      <div className="min-h-screen bg-background px-5 py-8">
        <div className="mx-auto max-w-[900px]">
          <JobCard className="py-20 text-center">
            <div className="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-border bg-background">
              <SearchX className="h-7 w-7 text-muted" />
            </div>
            <h2 className="mb-2 text-xl font-bold text-ink">{t("pages.admin.jobNotFound")}</h2>
            <p className="mx-auto mb-7 max-w-sm text-[13.5px] text-muted">
              This job may have been deleted, or the link you followed is no
              longer valid.
            </p>
            <Button
              asChild
              className="h-11 gap-2 rounded-full bg-primary px-6 text-primary-foreground hover:bg-primary/90"
            >
              <Link to="/admin/jobs">Return to Jobs</Link>
            </Button>
          </JobCard>
        </div>
      </div>
    );
  }

  // ---------- Error ----------
  if (pageState === "error") {
    return (
      <div className="min-h-screen bg-background px-5 py-8">
        <div className="mx-auto max-w-[900px]">
          <JobCard className="py-20 text-center">
            <div className="mx-auto mb-5 flex h-16 w-16 items-center justify-center rounded-2xl bg-error/10">
              <AlertTriangle className="h-7 w-7 text-error" />
            </div>
            <h2 className="mb-2 text-xl font-bold text-ink">
              {t("pages.admin.jobDetails.loadFailed")}
            </h2>
            <p className="mx-auto mb-7 max-w-sm text-[13.5px] text-muted">
              Something went wrong on our end. Please try again.
            </p>
            <Button
              onClick={load}
              className="h-11 gap-2 rounded-full bg-primary px-6 text-primary-foreground hover:bg-primary/90"
            >
              <RefreshCw className="h-4 w-4" />
              Retry
            </Button>
          </JobCard>
        </div>
      </div>
    );
  }

  // ---------- Loaded ----------
  return (
    <div className="min-h-screen bg-background">

      <main className="mx-auto  px-5 py-8">
        {/* Stale banner */}
        {isStale && !isEditing && (
          <div className="mb-5 flex items-start gap-3 rounded-2xl border border-warning/25 bg-warning/10 px-5 py-4 text-warning shadow-sm">
            <Clock className="mt-0.5 h-5 w-5 shrink-0" />
            <div className="flex-1">
              <div className="text-[13.5px] font-bold">
                This listing is stale — last published {job.publishedDaysAgo}{" "}
                days ago
              </div>
              <div className="mt-0.5 text-[12.5px] opacity-85">
                Consider updating its status or reaching out to the source
                before candidates keep seeing it.
              </div>
            </div>
          </div>
        )}

        {/* Success banner */}
        {saveState === "success" && (
          <div className="mb-5 flex items-center gap-3 rounded-2xl border border-success/25 bg-success/10 px-5 py-4 text-success shadow-sm">
            <CheckCircle2 className="h-5 w-5 shrink-0" />
            <div className="flex-1 text-[13.5px] font-semibold">
              Changes saved successfully.
            </div>
            <button
              type="button"
              onClick={() => setSaveState("idle")}
              className="text-xs font-semibold opacity-70 hover:opacity-100"
            >
              Dismiss
            </button>
          </div>
        )}

        {/* Validation banner */}
        {isEditing && titleError && (
          <div className="mb-5 flex items-start gap-3 rounded-2xl border border-error/25 bg-error/10 px-5 py-4 text-error shadow-sm">
            <AlertTriangle className="mt-0.5 h-5 w-5 shrink-0" />
            <div className="flex-1 text-[13.5px] font-semibold">
              Please fix the highlighted fields before saving.
            </div>
          </div>
        )}

        {/* Job Header */}
        <JobCard>
          <div className="flex flex-col gap-4 sm:flex-row sm:items-start">
            <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-primary to-primary/70 shadow-sm">
              <span className="font-display text-[22px] font-extrabold text-white">
                {job.company.initial}
              </span>
            </div>

            <div className="min-w-0 flex-1">
              {isEditing ? (
                <>
                  <Input
                    value={form.title}
                    onChange={(e) => setField("title", e.target.value)}
                    className={cn(
                      "mb-1 h-11 rounded-xl border-border font-display text-base font-bold",
                      titleError && "border-error",
                    )}
                  />
                  {titleError && (
                    <p className="mb-1 text-[11.5px] font-semibold text-error">
                      Job title is required.
                    </p>
                  )}
                </>
              ) : (
                <h1 className="mb-1 font-display text-[21px] font-extrabold leading-tight text-ink">
                  {job.title}
                </h1>
              )}

              <button
                type="button"
                className="mt-1 flex items-center gap-1 text-[13px] text-muted hover:text-primary"
              >
                {job.company.name}
                <ExternalLink className="h-3.5 w-3.5" />
              </button>

              <div className="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1.5 text-xs text-muted">
                {isEditing ? (
                  <Select
                    value={form.location}
                    onValueChange={(v) => setField("location", v)}
                  >
                    <SelectTrigger className="h-8 w-auto rounded-lg border-border px-2 text-xs">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {LOCATION_OPTIONS.map((o) => (
                        <SelectItem key={o} value={o}>
                          {o}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                ) : (
                  <span className="flex items-center gap-1">
                    <MapPin className="h-3.5 w-3.5" />
                    {job.location}
                  </span>
                )}

                {isEditing ? (
                  <Select
                    value={form.type}
                    onValueChange={(v) => setField("type", v)}
                  >
                    <SelectTrigger className="h-8 w-auto rounded-lg border-border px-2 text-xs">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {TYPE_OPTIONS.map((o) => (
                        <SelectItem key={o} value={o}>
                          {o}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                ) : (
                  <span className="flex items-center gap-1">
                    <Briefcase className="h-3.5 w-3.5" />
                    {job.type}
                  </span>
                )}

                {isEditing ? (
                  <Select
                    value={form.level}
                    onValueChange={(v) => setField("level", v)}
                  >
                    <SelectTrigger className="h-8 w-auto rounded-lg border-border px-2 text-xs">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      {LEVEL_OPTIONS.map((o) => (
                        <SelectItem key={o} value={o}>
                          {o}
                        </SelectItem>
                      ))}
                    </SelectContent>
                  </Select>
                ) : (
                  <span className="flex items-center gap-1">
                    <Award className="h-3.5 w-3.5" />
                    {job.level}
                  </span>
                )}
              </div>
            </div>

            <div className="flex shrink-0 flex-col items-end gap-2">
              <span
                className={cn(
                  "inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold",
                  STATUS_STYLES[job.status],
                )}
              >
                <span className="h-1.5 w-1.5 rounded-full bg-current" />
                {job.status}
              </span>
            </div>
          </div>

          {/* Publication date + freshness — preserved */}
          <div className="mt-4 flex flex-wrap items-center gap-2 border-t border-border pt-4">
            <span className="inline-flex items-center gap-1.5 rounded-full border border-border bg-background px-2.5 py-1 text-[11px] font-semibold text-ink/80">
              <Calendar className="h-3.5 w-3.5" />
              Published {job.publishedLabel}
            </span>
            <span className="inline-flex items-center gap-1.5 rounded-full bg-success/10 px-2.5 py-1 text-[11px] font-semibold text-success">
              ● {job.freshnessLabel}
            </span>
            {isEditing && (
              <span className="ml-1 flex items-center gap-1 text-[11px] text-muted">
                <Lock className="h-3.5 w-3.5" />
                Preserved from source — not editable
              </span>
            )}
          </div>

          {/* Actions */}
          {isEditing ? (
            <div className="mt-5 flex flex-wrap items-center gap-2 border-t border-border pt-5">
              <Button
                onClick={handleSave}
                disabled={saveState === "saving"}
                className="h-10 gap-1.5 rounded-full bg-primary px-5 text-[12.5px] font-semibold text-primary-foreground hover:bg-primary/90 disabled:opacity-70"
              >
                <Save className="h-4 w-4" />
                Save Changes
              </Button>
              <Button
                variant="outline"
                onClick={cancelEditing}
                disabled={saveState === "saving"}
                className="h-10 rounded-full border-border px-4 text-[12.5px] font-semibold text-ink"
              >
                Cancel
              </Button>
              {saveState === "saving" && (
                <span className="flex items-center gap-2 text-[13px] font-medium text-ink/80">
                  <Loader2 className="h-4 w-4 animate-spin" />
                  Saving changes…
                </span>
              )}
            </div>
          ) : (
            <div className="mt-5 flex flex-wrap items-center gap-2 border-t border-border pt-5">
              <Button
                onClick={startEditing}
                className="h-10 gap-1.5 rounded-full bg-primary px-4 text-[12.5px] font-semibold text-primary-foreground hover:bg-primary/90"
              >
                <PencilLine className="h-4 w-4" />
                Edit Job
              </Button>
              <Button
                variant="outline"
                asChild
                className="h-10 gap-1.5 rounded-full border-border px-4 text-[12.5px] font-semibold text-ink"
              >
                <Link to={`/admin/companies/${job.company.name}`}>
                  <Building2 className="h-4 w-4" />
                  View Company
                </Link>
              </Button>
              <div className="relative">
                <Button
                  variant="outline"
                  onClick={() => setHeaderStatusMenuOpen((v) => !v)}
                  className="h-10 gap-1.5 rounded-full border-border px-4 text-[12.5px] font-semibold text-ink"
                >
                  <RefreshCw className="h-4 w-4" />
                  Update Status
                </Button>
                {headerStatusMenuOpen && (
                  <div className="absolute left-0 top-11 z-10 w-40 rounded-xl border border-border bg-surface p-1.5 shadow-md">
                    {STATUS_OPTIONS.map((opt) => (
                      <button
                        key={opt}
                        type="button"
                        disabled={statusSaving}
                        onClick={() => handleUpdateStatus(opt)}
                        className={cn(
                          "flex w-full items-center justify-between rounded-lg px-2.5 py-2 text-left text-[12.5px] font-medium text-ink hover:bg-background disabled:opacity-50",
                          job.status === opt && "font-bold text-primary",
                        )}
                      >
                        {opt}
                        {statusSaving && job.status !== opt && (
                          <Loader2 className="h-3.5 w-3.5 animate-spin" />
                        )}
                      </button>
                    ))}
                  </div>
                )}
              </div>
              <a
                href={job.sourceUrl}
                target="_blank"
                rel="noreferrer"
                className="flex h-10 items-center gap-1.5 rounded-full border border-border px-4 text-[12.5px] font-semibold text-ink hover:bg-background"
              >
                <ExternalLink className="h-4 w-4" />
                Open Source
              </a>
              <Button
                variant="ghost"
                asChild
                className="ml-auto h-10 gap-1.5 rounded-full px-4 text-[12.5px] font-semibold text-muted hover:text-ink"
              >
                <Link to="/admin/jobs">
                  <ArrowLeft className="h-4 w-4" />
                  Return to Jobs
                </Link>
              </Button>
            </div>
          )}
        </JobCard>

        {/* Description */}
        <JobCard>
          <h2 className="mb-3 font-display text-[15px] font-bold text-ink">
            {t("pages.admin.jobDetails.description")}
          </h2>
          {isEditing ? (
            <Textarea
              value={form.description}
              onChange={(e) => setField("description", e.target.value)}
              rows={4}
              className="resize-none rounded-xl border-border text-[13px]"
            />
          ) : (
            <p className="text-[13px] leading-relaxed text-ink/80">
              {job.description}
            </p>
          )}
        </JobCard>

        {/* Responsibilities */}
        <JobCard>
          <h2 className="mb-3 font-display text-[15px] font-bold text-ink">
            {t("pages.admin.jobDetails.responsibilities")}
          </h2>
          {isEditing ? (
            <>
              <Textarea
                value={form.responsibilities}
                onChange={(e) => setField("responsibilities", e.target.value)}
                rows={4}
                className="resize-none rounded-xl border-border text-[13px]"
              />
              <p className="mt-1.5 text-[11px] text-muted">
                One item per line.
              </p>
            </>
          ) : (
            <ul className="space-y-1.5">
              {job.responsibilities.map((item, i) => (
                <li
                  key={i}
                  className="flex items-start gap-2 text-[13px] text-ink/80"
                >
                  <ChevronRight className="mt-0.5 h-[15px] w-[15px] shrink-0 text-primary" />
                  {item}
                </li>
              ))}
            </ul>
          )}
        </JobCard>

        {/* Required Skills */}
        <JobCard>
          <h2 className="mb-3 font-display text-[15px] font-bold text-ink">
            {t("pages.admin.jobDetails.requiredSkills")}
          </h2>
          {isEditing ? (
            <>
              <Input
                value={form.requiredSkills}
                onChange={(e) => setField("requiredSkills", e.target.value)}
                className="h-11 rounded-xl border-border text-[13px]"
              />
              <p className="mt-1.5 text-[11px] text-muted">Comma-separated.</p>
            </>
          ) : (
            <div className="flex flex-wrap gap-2">
              {job.requiredSkills.map((skill) => (
                <span
                  key={skill}
                  className="rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary"
                >
                  {skill}
                </span>
              ))}
            </div>
          )}
        </JobCard>

        {/* Preferred Skills */}
        <JobCard>
          <h2 className="mb-3 font-display text-[15px] font-bold text-ink">
            {t("pages.admin.jobDetails.preferredSkills")}
          </h2>
          {isEditing ? (
            <>
              <Input
                value={form.preferredSkills}
                onChange={(e) => setField("preferredSkills", e.target.value)}
                className="h-11 rounded-xl border-border text-[13px]"
              />
              <p className="mt-1.5 text-[11px] text-muted">Comma-separated.</p>
            </>
          ) : (
            <div className="flex flex-wrap gap-2">
              {job.preferredSkills.map((skill) => (
                <span
                  key={skill}
                  className="rounded-full border border-border px-2.5 py-1 text-xs font-medium text-ink/80"
                >
                  {skill}
                </span>
              ))}
            </div>
          )}
        </JobCard>

        {/* Source & application method — preserved, never editable */}
        <JobCard>
          <div className="mb-4 flex items-center gap-2">
            <h2 className="font-display text-[15px] font-bold text-ink">
              {t("pages.admin.jobDetails.sourceAndMethod")}
            </h2>
            <Lock
              className="h-[15px] w-[15px] text-muted"
              title="Preserved — not editable"
            />
          </div>
          <div className="grid gap-3 text-[13px] sm:grid-cols-2">
            <div className="flex justify-between border-b border-border pb-2">
              <span className="text-muted">Source</span>
              <span className="rounded-full bg-primary/10 px-2 py-0.5 text-xs font-medium text-primary">
                {job.source}
              </span>
            </div>
            <div className="flex justify-between border-b border-border pb-2">
              <span className="text-muted">Application method</span>
              <span className="font-medium text-ink">
                {job.applicationMethod}
              </span>
            </div>
            <div className="flex justify-between border-b border-border pb-2">
              <span className="text-muted">Posted by</span>
              <span className="font-medium text-ink">{job.postedBy}</span>
            </div>
            <div className="flex justify-between border-b border-border pb-2">
              <span className="text-muted">Original listing</span>
              <a
                href={job.sourceUrl}
                target="_blank"
                rel="noreferrer"
                className="flex items-center gap-1 font-medium text-primary"
              >
                Open Source
                <ExternalLink className="h-[13px] w-[13px]" />
              </a>
            </div>
          </div>
        </JobCard>

        {/* Job Status */}
        <JobCard className="mb-0">
          <h2 className="mb-4 font-display text-[15px] font-bold text-ink">
            {t("pages.admin.jobDetails.status")}
          </h2>
          <div className="flex items-center justify-between gap-3">
            <div>
              <span
                className={cn(
                  "inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-semibold",
                  STATUS_STYLES[job.status],
                )}
              >
                <span className="h-1.5 w-1.5 rounded-full bg-current" />
                {job.status}
              </span>
              <p className="mt-2 text-xs text-muted">
                {job.status === "Active" &&
                  "Visible to candidates in search and recommendations."}
                {job.status === "Draft" && "Not visible to candidates yet."}
                {job.status === "Closed" && "No longer accepting applications."}
              </p>
            </div>
            <div className="relative">
              <Button
                variant="outline"
                onClick={() => setCardStatusMenuOpen((v) => !v)}
                className="h-9 rounded-full border-border px-3.5 text-xs font-semibold text-ink"
              >
                Update Status
              </Button>
              {cardStatusMenuOpen && (
                <div className="absolute right-0 top-10 z-10 w-40 rounded-xl border border-border bg-surface p-1.5 shadow-md">
                  {STATUS_OPTIONS.map((opt) => (
                    <button
                      key={opt}
                      type="button"
                      disabled={statusSaving}
                      onClick={() => handleUpdateStatus(opt)}
                      className={cn(
                        "flex w-full items-center justify-between rounded-lg px-2.5 py-2 text-left text-[12.5px] font-medium text-ink hover:bg-background disabled:opacity-50",
                        job.status === opt && "font-bold text-primary",
                      )}
                    >
                      {opt}
                    </button>
                  ))}
                </div>
              )}
            </div>
          </div>
        </JobCard>
      </main>
    </div>
  );
}
