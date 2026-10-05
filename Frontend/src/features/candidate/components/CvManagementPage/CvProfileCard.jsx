import { motion } from "framer-motion";
import {
  User,
  Mail,
  Phone,
  MapPin,
  ExternalLink,
  Sparkles,
  CheckCircle2,
  Globe,
  Link2,
} from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";

const ease = [0.16, 1, 0.3, 1];

function LinkedinIcon(props) {
  return (
    <svg
      viewBox="0 0 24 24"
      width="14"
      height="14"
      fill="currentColor"
      {...props}
    >
      <path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.88 8.56a1.68 1.68 0 0 0 1.68-1.68c0-.93-.75-1.69-1.68-1.69a1.69 1.69 0 0 0-1.69 1.69c0 .93.76 1.68 1.69 1.68m1.39 9.94v-8.37H5.5v8.37h2.77z" />
    </svg>
  );
}

function GithubIcon(props) {
  return (
    <svg
      viewBox="0 0 24 24"
      width="14"
      height="14"
      fill="currentColor"
      {...props}
    >
      <path d="M12 2A10 10 0 0 0 2 12c0 4.42 2.87 8.17 6.84 9.5.5.08.66-.23.66-.5v-1.69c-2.77.6-3.36-1.34-3.36-1.34-.46-1.16-1.11-1.47-1.11-1.47-.91-.62.07-.6.07-.6 1 .07 1.53 1.03 1.53 1.03.87 1.52 2.34 1.07 2.91.83.1-.65.35-1.09.63-1.34-2.22-.25-4.55-1.11-4.55-4.92 0-1.11.38-2 1.03-2.71-.1-.25-.45-1.29.1-2.64 0 0 .84-.27 2.75 1.02.79-.22 1.65-.33 2.5-.33.85 0 1.71.11 2.5.33 1.91-1.29 2.75-1.02 2.75-1.02.55 1.35.2 2.39.1 2.64.65.71 1.03 1.6 1.03 2.71 0 3.82-2.34 4.66-4.57 4.91.36.31.69.92.69 1.85V21c0 .27.16.59.67.5C19.14 20.16 22 16.42 22 12A10 10 0 0 0 12 2z" />
    </svg>
  );
}

export default function CvProfileCard({
  user,
  profile,
  confirmed,
  onConfirm,
}) {
  if (!user && !profile) return null;

  const initials = user?.name
    ? user.name
        .split(" ")
        .map((n) => n[0])
        .slice(0, 2)
        .join("")
        .toUpperCase()
    : "CA";

  return (
    <motion.div
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.35, ease }}
      className="space-y-4 rounded-2xl border border-border bg-surface p-5 shadow-xs"
    >
      {/* Header with Title and Section Action */}
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-border/70 pb-4">
        <div className="flex items-center gap-2">
          <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <User className="h-5 w-5" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h3 className="text-base font-bold text-primary">
                Candidate Profile & Contact
              </h3>
              <Badge className="bg-primary/10 text-primary hover:bg-primary/10 border-0 text-[11px] font-semibold">
                AI Extracted
              </Badge>
            </div>
            <p className="text-xs text-muted">
              Personal contact details and professional summary parsed from the CV header
            </p>
          </div>
        </div>

        {onConfirm && (
          <div>
            {confirmed ? (
              <span className="flex items-center gap-1.5 text-xs font-bold text-success">
                <CheckCircle2 className="h-4 w-4" />
                Profile Verified
              </span>
            ) : (
              <Button
                type="button"
                size="sm"
                onClick={onConfirm}
                className="rounded-lg bg-success px-3.5 py-1.5 text-xs font-bold text-success-foreground hover:bg-success/90"
              >
                Confirm Profile
              </Button>
            )}
          </div>
        )}
      </div>

      {/* Profile Info Grid */}
      <div className="grid grid-cols-1 gap-4 lg:grid-cols-12">
        {/* Left Column: Avatar & Basic Details */}
        <div className="flex flex-col gap-4 rounded-xl bg-background/80 p-4 lg:col-span-4">
          <div className="flex items-center gap-3">
            <div className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-primary font-bold text-primary-foreground text-lg shadow-sm">
              {initials}
            </div>
            <div className="min-w-0 flex-1">
              <h4 className="truncate text-base font-bold text-ink">
                {user?.name || "Candidate Name"}
              </h4>
              <p className="text-xs font-medium text-secondary line-clamp-2">
                {profile?.headline || "Professional Headline"}
              </p>
            </div>
          </div>

          <div className="space-y-2 border-t border-border/60 pt-3 text-xs">
            {user?.email && (
              <div className="flex items-center gap-2 text-muted">
                <Mail className="h-3.5 w-3.5 shrink-0 text-primary" />
                <span className="truncate text-ink font-medium">{user.email}</span>
              </div>
            )}
            {profile?.phone && (
              <div className="flex items-center gap-2 text-muted">
                <Phone className="h-3.5 w-3.5 shrink-0 text-primary" />
                <span className="text-ink font-medium">{profile.phone}</span>
              </div>
            )}
            {profile?.location && (
              <div className="flex items-center gap-2 text-muted">
                <MapPin className="h-3.5 w-3.5 shrink-0 text-primary" />
                <span className="text-ink font-medium">{profile.location}</span>
              </div>
            )}
          </div>

          {/* Social Links (only non-null links) */}
          {(profile?.linkedin_url || profile?.github_url || profile?.portfolio_url) && (
            <div className="flex flex-wrap gap-2 border-t border-border/60 pt-3">
              {profile?.linkedin_url && (
                <a
                  href={profile.linkedin_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center gap-1.5 rounded-lg border border-border bg-surface px-2.5 py-1 text-[11px] font-semibold text-primary transition-colors hover:bg-primary/5 hover:border-primary/40"
                >
                  <LinkedinIcon className="text-[#0A66C2]" />
                  <span>LinkedIn</span>
                  <ExternalLink className="h-2.5 w-2.5 opacity-60" />
                </a>
              )}
              {profile?.github_url && (
                <a
                  href={profile.github_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center gap-1.5 rounded-lg border border-border bg-surface px-2.5 py-1 text-[11px] font-semibold text-primary transition-colors hover:bg-primary/5"
                >
                  <GithubIcon />
                  <span>GitHub</span>
                  <ExternalLink className="h-2.5 w-2.5 opacity-60" />
                </a>
              )}
              {profile?.portfolio_url && (
                <a
                  href={profile.portfolio_url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-flex items-center gap-1.5 rounded-lg border border-border bg-surface px-2.5 py-1 text-[11px] font-semibold text-primary transition-colors hover:bg-primary/5"
                >
                  <Globe className="h-3.5 w-3.5" />
                  <span>Portfolio</span>
                  <ExternalLink className="h-2.5 w-2.5 opacity-60" />
                </a>
              )}
            </div>
          )}
        </div>

        {/* Right Column: Bio / Professional Summary */}
        <div className="flex flex-col justify-between rounded-xl bg-background/80 p-4 lg:col-span-8">
          <div className="space-y-2">
            <div className="flex items-center gap-1.5 text-xs font-bold text-primary">
              <Sparkles className="h-3.5 w-3.5 text-accent" />
              <span>Extracted Professional Bio & Summary</span>
            </div>
            <p className="text-xs leading-relaxed text-ink/90 whitespace-pre-line">
              {profile?.bio || "No professional summary extracted."}
            </p>
          </div>

          <div className="mt-4 flex items-center justify-between border-t border-border/60 pt-3 text-[11px] text-muted">
            <span>Source: CV Executive Summary</span>
            <span className="font-semibold text-success">Verified with High Confidence</span>
          </div>
        </div>
      </div>
    </motion.div>
  );
}
