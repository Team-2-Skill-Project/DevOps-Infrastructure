import * as React from "react";
import { useTranslation } from "react-i18next";
import { useParams, Link } from "react-router-dom";
import {
  ChevronRight,
  ArrowLeft,
  PencilLine,
  X,
  CheckCircle2,
  Loader2,
  ArrowLeftRight,
  Power,
  Ban,
  Trash2,
  FileText,
  AlertTriangle,
} from "lucide-react";

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
import { Section, Field, AppStatusBadge, RoleBadge, UserStatusBadge, STATUS_LABELS } from "@/features/admin/components/UsersDetailsPage/DetailSections";

// Note: this page is scoped to account/profile/activity data only — a
// user's AI mentorship conversations are a different feature/scope and
// are intentionally never surfaced here.

const EXPERIENCE_OPTIONS = ["Less than 1 year", "1–2 years", "3–5 years", "6–10 years", "10+ years"];

function initials(fullName) {
  return fullName.split(" ").map((p) => p[0]).slice(0, 2).join("").toUpperCase();
}

const StatusBadge = UserStatusBadge;

// TODO: replace with a real fetch, e.g. GET /admin/users/:userId
function fetchUserDetails(userId) {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      if (userId === "missing") {
        resolve(null); // simulates "not found"
        return;
      }
      if (userId === "broken") {
        reject(new Error("Failed to load user"));
        return;
      }
      resolve({
        id: userId ?? "USR-4821",
        fullName: "Maya Khaled",
        email: "maya.k@matchin.io",
        phone: "+20 101 234 8890",
        location: "Cairo, Egypt",
        role: "candidate",
        status: "active",
        authProvider: "Email / Password",
        emailVerified: true,
        language: "English",
        jobTitle: "Frontend Developer",
        experience: "3–5 years",
        bio: "Frontend developer focused on React and accessible UI. Looking for mid-to-senior roles in product teams across Cairo or remote.",
        skills: ["React", "TypeScript", "CSS / Tailwind", "Next.js", "Git", "Figma"],
        cv: {
          uploaded: true,
          fileName: "Maya_Khaled_CV.pdf",
          fileSize: "240 KB",
          uploadedAt: "Sep 10, 2026",
          lastParsed: "Sep 10, 2026 · Skills extracted",
        },
        stats: {
          applications: { value: 12, sub: "3 in review" },
          savedJobs: { value: 8, sub: "2 expired" },
          cvStatus: { value: "Uploaded", sub: "Updated 12 days ago" },
          profile: { value: "92% complete", sub: "Skills · Bio · Experience" },
        },
        applications: [
          { title: "Senior Frontend Engineer", company: "BrightPath Inc.", appliedOn: "Sep 20", status: "review" },
          { title: "React Developer", company: "Nexus Labs", appliedOn: "Sep 15", status: "submitted" },
          { title: "UI Engineer", company: "Horizon Digital", appliedOn: "Sep 8", status: "offer" },
          { title: "Frontend Intern", company: "Spark Labs", appliedOn: "Aug 28", status: "rejected" },
        ],
        savedJobs: { total: 8, open: 6, expired: 2, lastSaved: "Sep 21 · DevOps Engineer @ CloudStack" },
        dates: {
          created: { date: "Sep 18, 2026", sub: "4 days ago" },
          lastActivity: { date: "Sep 22, 2026", sub: "1 hr ago · Cairo" },
          lastLogin: { date: "Sep 22, 2026", sub: "09:14 EEST" },
        },
      });
    }, 700);
  });
}

// TODO: replace with a real mutation, e.g. PATCH /admin/users/:userId
function saveUserDetails(userId, patch) {
  return new Promise((resolve) => setTimeout(() => resolve(patch), 900));
}

export function UsersDetailsPage() {
  const { t } = useTranslation("dashboard");
  const { userId } = useParams();

  const [pageState, setPageState] = React.useState("loading"); // loading | loaded | not-found | error
  const [user, setUser] = React.useState(null);

  const [isEditing, setIsEditing] = React.useState(false);
  const [form, setForm] = React.useState(null);
  const [saveState, setSaveState] = React.useState("idle"); // idle | saving | success | error

  const load = React.useCallback(() => {
    setPageState("loading");
    fetchUserDetails(userId)
      .then((data) => {
        if (!data) {
          setPageState("not-found");
          return;
        }
        setUser(data);
        setPageState("loaded");
      })
      .catch(() => setPageState("error"));
  }, [userId]);

  React.useEffect(() => {
    load();
  }, [load]);

  const startEditing = () => {
    setForm({
      fullName: user.fullName,
      email: user.email,
      phone: user.phone,
      location: user.location,
      jobTitle: user.jobTitle,
      experience: user.experience,
      bio: user.bio,
    });
    setIsEditing(true);
    setSaveState("idle");
  };

  const cancelEditing = () => {
    setIsEditing(false);
    setForm(null);
    setSaveState("idle");
  };

  const handleFieldChange = (name, value) => {
    setForm((prev) => ({ ...prev, [name]: value }));
  };

  const handleSave = async () => {
    setSaveState("saving");
    try {
      const patch = await saveUserDetails(user.id, form);
      setUser((prev) => ({ ...prev, ...patch }));
      setIsEditing(false);
      setSaveState("success");
      setTimeout(() => setSaveState("idle"), 2500);
    } catch {
      setSaveState("error");
    }
  };

  const handleChangeRole = () => {
    // TODO: open a real role-change modal; cycling through roles for now.
    const order = ["candidate", "recruiter", "admin"];
    const next = order[(order.indexOf(user.role) + 1) % order.length];
    setUser((prev) => ({ ...prev, role: next }));
  };

  const handleToggleActive = () => {
    // TODO: confirm + call the real activate/deactivate endpoint.
    setUser((prev) => ({ ...prev, status: prev.status === "active" ? "inactive" : "active" }));
  };

  const handleDelete = () => {
    // TODO: confirm + call the real delete endpoint, then route back to the users list.
    if (window.confirm(`Delete ${user.fullName}? This can't be undone.`)) {
      setPageState("not-found");
    }
  };

  // ---------- Loading ----------
  if (pageState === "loading") {
    return (
      <div className="flex min-h-screen items-center justify-center bg-background">
        <div className="flex items-center gap-2.5 text-muted">
          <Loader2 className="h-5 w-5 animate-spin" />
          <span className="text-sm font-medium">Loading user…</span>
        </div>
      </div>
    );
  }

  // ---------- Not found ----------
  if (pageState === "not-found") {
    return (
      <div className="flex min-h-screen items-center justify-center bg-background px-6">
        <div className="text-center">
          <div className="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-muted/15 text-muted">
            <AlertTriangle className="h-5 w-5" />
          </div>
          <h1 className="mb-1 text-lg font-bold text-ink">{t("pages.admin.userNotFound")}</h1>
          <p className="mb-5 text-sm text-muted">This user may have been deleted or the link is incorrect.</p>
          <Button asChild className="rounded-xl bg-primary text-primary-foreground hover:bg-primary/90">
            <Link to="/admin/users">Back to Users</Link>
          </Button>
        </div>
      </div>
    );
  }

  // ---------- Error ----------
  if (pageState === "error") {
    return (
      <div className="flex min-h-screen items-center justify-center bg-background px-6">
        <div className="text-center">
          <div className="mx-auto mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-error/10 text-error">
            <AlertTriangle className="h-5 w-5" />
          </div>
          <h1 className="mb-1 text-lg font-bold text-ink">{t("pages.admin.userLoadFailed")}</h1>
          <p className="mb-5 text-sm text-muted">Something went wrong. Please try again.</p>
          <Button onClick={load} className="rounded-xl bg-primary text-primary-foreground hover:bg-primary/90">
            Retry
          </Button>
        </div>
      </div>
    );
  }

  // ---------- Loaded ----------
  return (
    <div className="min-h-screen bg-background px-7 py-8">
      <div className="mx-auto max-w-[1100px]">
        {/* Breadcrumb */}
        <div className="mb-3.5 flex flex-wrap items-center gap-1.5 text-[13px] text-muted">
          <Link to="/admin/users" className="hover:text-primary">Users</Link>
          <ChevronRight className="h-3.5 w-3.5 opacity-55" />
          <span>{user.id}</span>
        </div>

        {/* Success / error toasts for saving */}
        {saveState === "success" && (
          <div className="mb-4 flex items-center gap-2 rounded-xl bg-success/10 px-4 py-2.5 text-[13px] font-semibold text-success">
            <CheckCircle2 className="h-4 w-4" />
            Changes saved
          </div>
        )}
        {saveState === "error" && (
          <div className="mb-4 flex items-center justify-between gap-3 rounded-xl bg-error/10 px-4 py-2.5 text-[13px] font-semibold text-error">
            <span className="flex items-center gap-2"><AlertTriangle className="h-4 w-4" /> Couldn't save changes</span>
            <button type="button" onClick={handleSave} className="underline">Try again</button>
          </div>
        )}

        {/* Header */}
        <div className="mb-5 flex flex-wrap items-start justify-between gap-4">
          <div className="flex items-center gap-4">
            <div className="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-primary text-xl font-bold text-primary-foreground">
              {initials(user.fullName)}
            </div>
            <div>
              <h1 className="text-[22px] font-bold tracking-tight text-ink">{user.fullName}</h1>
              <div className="mt-1 flex flex-wrap items-center gap-2 text-[13.5px] text-muted">
                <span className="font-mono text-[13px]">{user.id}</span>
                <span className="h-[3px] w-[3px] rounded-full bg-border" />
                <RoleBadge role={user.role} />
                <span className="h-[3px] w-[3px] rounded-full bg-border" />
                <span>{user.email}</span>
              </div>
            </div>
          </div>

          <div className="flex flex-wrap gap-2.5">
            <Button variant="ghost" asChild className="h-10 gap-2 rounded-xl text-muted hover:text-ink">
              <Link to="/admin/users">
                <ArrowLeft className="h-4 w-4" />
                Back
              </Link>
            </Button>
            {isEditing ? (
              <Button variant="outline" onClick={cancelEditing} className="h-10 gap-2 rounded-xl border-border text-ink">
                <X className="h-4 w-4" />
                Cancel
              </Button>
            ) : (
              <Button variant="outline" onClick={startEditing} className="h-10 gap-2 rounded-xl border-border text-ink">
                <PencilLine className="h-4 w-4" />
                Edit User
              </Button>
            )}
          </div>
        </div>

        {/* Status banner */}
        <div
          className={cn(
            "mb-5.5 flex flex-wrap items-center justify-between gap-4 rounded-2xl border px-4.5 py-3.5",
            user.status === "active" && "bg-success/10 border-success/25",
            user.status === "inactive" && "bg-muted/10 border-muted/20",
            user.status === "suspended" && "bg-error/10 border-error/25"
          )}
        >
          <div className="flex items-center gap-3">
            <div
              className={cn(
                "flex h-10 w-10 shrink-0 items-center justify-center rounded-xl",
                user.status === "active" && "bg-success/20 text-success",
                user.status === "inactive" && "bg-muted/15 text-muted",
                user.status === "suspended" && "bg-error/15 text-error"
              )}
            >
              <CheckCircle2 className="h-5 w-5" />
            </div>
            <div>
              <div
                className={cn(
                  "text-[14.5px] font-bold",
                  user.status === "active" && "text-success",
                  user.status === "inactive" && "text-muted",
                  user.status === "suspended" && "text-error"
                )}
              >
                Account {STATUS_LABELS[user.status]}
              </div>
              <div className="mt-0.5 text-[13px] text-muted">
                Last activity · {user.dates.lastActivity.sub}
              </div>
            </div>
          </div>

          <div className="flex flex-wrap gap-2">
            <Button size="sm" variant="outline" onClick={handleChangeRole} className="h-[34px] gap-1.5 rounded-lg border-border text-[12.5px] text-ink">
              <ArrowLeftRight className="h-3.5 w-3.5" />
              Change Role
            </Button>
            <Button
              size="sm"
              variant="outline"
              onClick={handleToggleActive}
              className={cn(
                "h-[34px] gap-1.5 rounded-lg text-[12.5px]",
                user.status === "active"
                  ? "border-error/35 text-error hover:border-error hover:bg-error/10"
                  : "border-success/35 text-success hover:border-success hover:bg-success/10"
              )}
            >
              {user.status === "active" ? <Ban className="h-3.5 w-3.5" /> : <Power className="h-3.5 w-3.5" />}
              {user.status === "active" ? "Deactivate" : "Activate"}
            </Button>
          </div>
        </div>

        {/* Overview stats */}
        <div className="mb-5.5 grid grid-cols-4 gap-3.5 max-[800px]:grid-cols-2 max-[440px]:grid-cols-1">
          {Object.entries(user.stats).map(([key, stat]) => (
            <div key={key} className="rounded-2xl border border-border bg-surface p-4.5 shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
              <div className="text-[12.5px] font-medium capitalize text-muted">
                {key === "cvStatus" ? "CV Status" : key === "savedJobs" ? "Saved Jobs" : key}
              </div>
              <div className="mt-1 text-[22px] font-bold tracking-tight text-ink">{stat.value}</div>
              <div className="mt-0.5 text-xs text-muted">{stat.sub}</div>
            </div>
          ))}
        </div>

        <div className="grid grid-cols-[1fr_320px] items-start gap-5 max-[900px]:grid-cols-1">
          {/* LEFT */}
          <div>
            {/* Personal Information */}
            <Section title={t("pages.admin.userDetails.personalInformation")} hint="Identity & contact">
              <div className="grid grid-cols-2 gap-x-6 gap-y-4.5 max-[560px]:grid-cols-1">
                {isEditing ? (
                  <>
                    <div>
                      <label className="mb-1 block text-[11.5px] font-bold uppercase tracking-wide text-muted">Full Name</label>
                      <Input value={form.fullName} onChange={(e) => handleFieldChange("fullName", e.target.value)} className="h-10 rounded-lg border-border" />
                    </div>
                    <div>
                      <label className="mb-1 block text-[11.5px] font-bold uppercase tracking-wide text-muted">Email</label>
                      <Input type="email" value={form.email} onChange={(e) => handleFieldChange("email", e.target.value)} className="h-10 rounded-lg border-border" />
                    </div>
                    <div>
                      <label className="mb-1 block text-[11.5px] font-bold uppercase tracking-wide text-muted">Phone</label>
                      <Input value={form.phone} onChange={(e) => handleFieldChange("phone", e.target.value)} className="h-10 rounded-lg border-border" />
                    </div>
                    <div>
                      <label className="mb-1 block text-[11.5px] font-bold uppercase tracking-wide text-muted">Location</label>
                      <Input value={form.location} onChange={(e) => handleFieldChange("location", e.target.value)} className="h-10 rounded-lg border-border" />
                    </div>
                  </>
                ) : (
                  <>
                    <Field label="Full Name" value={user.fullName} />
                    <Field label="Email" value={<a href={`mailto:${user.email}`} className="font-semibold text-primary hover:underline">{user.email}</a>} />
                    <Field label="Phone" value={user.phone} />
                    <Field label="Location" value={user.location} />
                  </>
                )}
              </div>
            </Section>

            {/* Account Information */}
            <Section title={t("pages.admin.userDetails.accountInformation")}>
              <div className="grid grid-cols-2 gap-x-6 gap-y-4.5 max-[560px]:grid-cols-1">
                <Field label="User ID" value={user.id} mono />
                <Field label="Role" value={<RoleBadge role={user.role} />} />
                <Field label="Account Status" value={<StatusBadge status={user.status} />} />
                <Field label="Auth Provider" value={user.authProvider} muted />
                <Field label="Email Verified" value={user.emailVerified ? "Yes" : "No"} valueClassName={user.emailVerified ? "text-success" : "text-error"} />
                <Field label="Language" value={user.language} muted />
              </div>
            </Section>

            {/* Career Profile Summary */}
            <Section title={t("pages.admin.userDetails.careerProfile")}>
              <div className="grid grid-cols-2 gap-x-6 gap-y-4.5 max-[560px]:grid-cols-1">
                {isEditing ? (
                  <>
                    <div>
                      <label className="mb-1 block text-[11.5px] font-bold uppercase tracking-wide text-muted">Job Title</label>
                      <Input value={form.jobTitle} onChange={(e) => handleFieldChange("jobTitle", e.target.value)} className="h-10 rounded-lg border-border" />
                    </div>
                    <div>
                      <label className="mb-1 block text-[11.5px] font-bold uppercase tracking-wide text-muted">Experience</label>
                      <Select value={form.experience} onValueChange={(v) => handleFieldChange("experience", v)}>
                        <SelectTrigger className="h-10 rounded-lg border-border"><SelectValue /></SelectTrigger>
                        <SelectContent>
                          {EXPERIENCE_OPTIONS.map((opt) => (
                            <SelectItem key={opt} value={opt}>{opt}</SelectItem>
                          ))}
                        </SelectContent>
                      </Select>
                    </div>
                    <div className="col-span-2 max-[560px]:col-span-1">
                      <label className="mb-1 block text-[11.5px] font-bold uppercase tracking-wide text-muted">Bio</label>
                      <Textarea value={form.bio} onChange={(e) => handleFieldChange("bio", e.target.value)} className="rounded-lg border-border" />
                    </div>
                  </>
                ) : (
                  <>
                    <Field label="Job Title" value={user.jobTitle} />
                    <Field label="Experience" value={user.experience} />
                    <Field label="Bio" full value={<p className="leading-[1.55] text-muted">{user.bio}</p>} />
                  </>
                )}
                <Field
                  full
                  label="Skills"
                  value={
                    <div className="mt-1 flex flex-wrap gap-2">
                      {user.skills.map((skill) => (
                        <span key={skill} className="inline-flex h-[26px] items-center rounded-full bg-primary/8 px-2.5 text-[12.5px] font-medium text-primary">
                          {skill}
                        </span>
                      ))}
                    </div>
                  }
                />
              </div>
            </Section>

            {/* CV Status */}
            <Section title={t("pages.admin.userDetails.cvStatus")}>
              <div className="grid grid-cols-2 gap-x-6 gap-y-4.5 max-[560px]:grid-cols-1">
                <Field
                  label="Status"
                  value={
                    <span className={cn("inline-flex h-[26px] items-center gap-1.5 rounded-full px-2.5 text-xs font-bold", user.cv.uploaded ? "bg-success/12 text-success" : "bg-warning/12 text-warning")}>
                      <CheckCircle2 className="h-3.5 w-3.5" />
                      {user.cv.uploaded ? "Uploaded" : "Missing"}
                    </span>
                  }
                />
                <Field label="File" value={user.cv.fileName ? `${user.cv.fileName} · ${user.cv.fileSize}` : "—"} muted />
                <Field label="Uploaded" value={user.cv.uploadedAt ?? "—"} muted />
                <Field label="Last parsed" value={user.cv.lastParsed ?? "—"} muted />
              </div>
              <div className="mt-4">
                <Button variant="outline" size="sm" asChild className="h-[34px] gap-1.5 rounded-lg border-border">
                  <Link to="#">
                    <FileText className="h-3.5 w-3.5" />
                    View CV / Profile
                  </Link>
                </Button>
              </div>
            </Section>

            {/* Applications Summary */}
            <Section title={t("pages.admin.userDetails.applications")} action={{ label: "View all →", onClick: () => {} }} className="px-5 pb-1 pt-1">
              {user.applications.map((app, i) => (
                <div key={i} className="flex items-center justify-between gap-3 border-b border-border py-3 last:border-b-0">
                  <div>
                    <div className="text-[13.5px] font-semibold text-ink">{app.title}</div>
                    <div className="mt-0.5 text-xs text-muted">{app.company} · Applied {app.appliedOn}</div>
                  </div>
                  <AppStatusBadge status={app.status} />
                </div>
              ))}
            </Section>

            {/* Saved Jobs Summary */}
            <Section title={t("pages.admin.userDetails.savedJobs")} className="px-5 pb-1 pt-1">
              <div className="flex items-center justify-between gap-3 border-b border-border py-3">
                <span className="text-[13.5px] font-medium text-ink">Total saved</span>
                <span className="text-sm font-bold text-ink">{user.savedJobs.total}</span>
              </div>
              <div className="flex items-center justify-between gap-3 border-b border-border py-3">
                <span className="text-[13.5px] font-medium text-ink">Still open</span>
                <span className="text-sm font-bold text-ink">{user.savedJobs.open}</span>
              </div>
              <div className="flex items-center justify-between gap-3 border-b border-border py-3">
                <span className="text-[13.5px] font-medium text-ink">Expired / closed</span>
                <span className="text-sm font-bold text-ink">{user.savedJobs.expired}</span>
              </div>
              <div className="flex items-center justify-between gap-3 py-3">
                <span className="text-[13.5px] font-medium text-ink">Last saved</span>
                <span className="text-[13.5px] font-medium text-muted">{user.savedJobs.lastSaved}</span>
              </div>
            </Section>
          </div>

          {/* SIDEBAR */}
          <div className="sticky top-6">
            <Section title={t("pages.admin.userDetails.roleStatus")}>
              <div className="mb-3.5">
                <div className="mb-1.5 text-[11.5px] font-bold uppercase tracking-wide text-muted">Role</div>
                <RoleBadge role={user.role} />
              </div>
              <div className="mb-4">
                <div className="mb-1.5 text-[11.5px] font-bold uppercase tracking-wide text-muted">Status</div>
                <StatusBadge status={user.status} />
              </div>
              <div className="flex flex-col gap-2">
                <Button variant="outline" onClick={handleChangeRole} className="h-10 w-full justify-center gap-2 rounded-xl border-border text-ink">
                  <ArrowLeftRight className="h-4 w-4" />
                  Change Role
                </Button>
                <Button
                  variant="outline"
                  onClick={handleToggleActive}
                  className={cn(
                    "h-10 w-full justify-center gap-2 rounded-xl",
                    user.status === "active"
                      ? "border-error/35 text-error hover:border-error hover:bg-error/10"
                      : "border-success/35 text-success hover:border-success hover:bg-success/10"
                  )}
                >
                  {user.status === "active" ? <Ban className="h-4 w-4" /> : <Power className="h-4 w-4" />}
                  {user.status === "active" ? "Deactivate" : "Activate"}
                </Button>
                <Button variant="outline" onClick={handleDelete} className="h-10 w-full justify-center gap-2 rounded-xl border-error/35 text-error hover:border-error hover:bg-error/10">
                  <Trash2 className="h-4 w-4" />
                  Delete User
                </Button>
              </div>
            </Section>

            <Section title={t("pages.admin.userDetails.dates")} className="px-5 pb-1.5 pt-1.5">
              {[
                ["Created", user.dates.created],
                ["Last Activity", user.dates.lastActivity],
                ["Last Login", user.dates.lastLogin],
              ].map(([label, d]) => (
                <div key={label} className="flex items-start justify-between gap-3 border-b border-border py-2.5 text-[13.5px] last:border-b-0">
                  <span className="text-muted">{label}</span>
                  <div className="text-right font-semibold text-ink">
                    {d.date}
                    <small className="mt-0.5 block text-xs font-normal text-muted">{d.sub}</small>
                  </div>
                </div>
              ))}
            </Section>

            <Section title={t("pages.admin.userDetails.quickLinks")}>
              <div className="flex flex-col gap-2">
                <Button variant="outline" asChild className="h-10 w-full justify-center gap-2 rounded-xl border-border text-ink">
                  <Link to="#"><FileText className="h-4 w-4" />View Applications</Link>
                </Button>
                <Button variant="outline" asChild className="h-10 w-full justify-center gap-2 rounded-xl border-border text-ink">
                  <Link to="#"><FileText className="h-4 w-4" />View CV / Profile</Link>
                </Button>
              </div>
            </Section>
          </div>
        </div>

        {/* Footer */}
        <div className="mt-2 flex flex-wrap items-center justify-between gap-3 pt-2">
          <Button variant="ghost" asChild className="h-10 gap-2 rounded-xl text-muted hover:text-ink">
            <Link to="/admin/users">
              <ArrowLeft className="h-4 w-4" />
              Back to Users
            </Link>
          </Button>

          <div className="flex flex-wrap gap-2.5">
            {isEditing ? (
              <>
                <Button variant="outline" onClick={cancelEditing} className="h-10 rounded-xl border-border text-ink">
                  Cancel
                </Button>
                <Button
                  onClick={handleSave}
                  disabled={saveState === "saving"}
                  className="h-10 gap-2 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 disabled:opacity-60"
                >
                  {saveState === "saving" ? <Loader2 className="h-4 w-4 animate-spin" /> : <CheckCircle2 className="h-4 w-4" />}
                  {saveState === "saving" ? "Saving…" : "Save Changes"}
                </Button>
              </>
            ) : (
              <Button variant="outline" onClick={startEditing} className="h-10 rounded-xl border-border text-ink">
                Edit User
              </Button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
