import * as React from "react";
import { Upload, FileText, Loader2, CheckCircle2 } from "lucide-react";

import { Button } from "@/components/ui/button";
import { cn } from "@/lib/utils";
import { cvFileSchema } from "@/features/auth/schema/cv-schema";
import { useState } from "react";
import { useRef } from "react";
import { useTranslation } from "react-i18next";

function formatSize(bytes) {
  return `${(bytes / 1024).toFixed(0)} KB`;
}

function fileKindLabel(file) {
  if (file.type.includes("pdf")) return "PDF";
  if (file.name.toLowerCase().endsWith(".docx")) return "DOCX";
  return "DOC";
}

/**
 * CvUploadStep — step 3 of 4: "Upload your CV"
 * onComplete(file) — called when "Complete Profile" is pressed with a valid file
 * onSkip()         — called when "Browse Jobs" is pressed (skip profile setup for now)
 */
export function CvUploadStep({ onComplete, onSkip }) {
  const { t } = useTranslation("common");
  const inputRef =useRef(null);
  const [isDragging, setIsDragging] = useState(false);
  const [file, setFile] = useState(null);
  const [error, setError] = useState(null);
  const [status, setStatus] = useState("idle"); // "idle" | "reading" | "ready"

  const handleFile = (candidate) => {
    if (!candidate) return;
    const result = cvFileSchema.safeParse(candidate);
    if (!result.success) {
      setError(result.error.issues[0]?.message ?? "Invalid file");
      return;
    }
    setError(null);
    setFile(candidate);
    setStatus("reading");

    // TODO: replace with a real upload call once the file lands on the server.
    setTimeout(() => setStatus("ready"), 1400);
  };

  const handleDrop = (e) => {
    e.preventDefault();
    setIsDragging(false);
    handleFile(e.dataTransfer.files?.[0]);
  };

  const handleRemove = (e) => {
    e.stopPropagation();
    setFile(null);
    setStatus("idle");
    setError(null);
    if (inputRef.current) inputRef.current.value = "";
  };

  return (
    <div>
      <div className="mb-1 text-[11px] font-semibold text-secondary">
        {t("auth.register.step", { current: 3 })}
      </div>
      <h1 className="mb-1 font-[DM_Sans] text-[23px] font-bold leading-7 tracking-tight text-ink">
        {t("auth.register.uploadTitle")}
      </h1>
      <p className="mb-5 text-[13.5px] text-muted">
        {t("auth.register.uploadDescription")}
      </p>

      <div className="mb-2 rounded-2xl border border-border bg-surface p-3.5">
        <div className="mb-1 flex items-center gap-1.5 text-xs font-bold text-ink/70">
          <Upload className="h-3.5 w-3.5 text-primary" />
          {t("auth.register.uploadLabel")}
        </div>
        <p className="mb-3.5 text-[12.5px] leading-tight text-muted">
          {t("auth.register.uploadHelp")}
        </p>

        {!file && (
          <div
            onDragOver={(e) => {
              e.preventDefault();
              setIsDragging(true);
            }}
            onDragLeave={() => setIsDragging(false)}
            onDrop={handleDrop}
            onClick={() => inputRef.current?.click()}
            className={cn(
              "cursor-pointer rounded-2xl border-[1.5px] border-dashed border-border bg-surface p-4 text-center transition-colors",
              isDragging && "border-primary bg-primary/5",
            )}
          >
            <div className="mx-auto mb-2.5 flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-background">
              <Upload className="h-4 w-4 text-primary" />
            </div>
            <p className="mb-0.5 font-[DM_Sans] text-sm font-semibold text-ink">
              {t("auth.register.drop")}{" "}
              <span className="text-primary">{t("auth.register.browse")}</span>
            </p>
            <p className="text-[11.5px] text-muted">{t("auth.register.fileLimit")}</p>
            <input
              ref={inputRef}
              type="file"
              accept=".pdf,.doc,.docx"
              className="hidden"
              onChange={(e) => handleFile(e.target.files?.[0])}
            />
          </div>
        )}

        {error && (
          <p className="mt-2 text-[11.5px] font-semibold text-error">{error}</p>
        )}

        {file && (
          <div className="mt-3 flex items-center gap-3 rounded-xl border border-border bg-surface px-3.5 py-2.5">
            <div className="flex h-[34px] w-[34px] shrink-0 items-center justify-center rounded-lg bg-primary/10 text-[11px] font-bold text-primary">
              {fileKindLabel(file)}
            </div>
            <div className="min-w-0 flex-1">
              <p className="truncate text-[13px] font-semibold text-ink">
                {file.name}
              </p>
              <p className="text-[11.5px] text-muted">
                {formatSize(file.size)}
              </p>
            </div>
            <div className="flex shrink-0 items-center gap-1 text-[11.5px] font-bold text-success">
              {status === "reading" ? (
                <>
                  <Loader2 className="h-3.5 w-3.5 animate-spin" />
                  {t("auth.register.reading")}
                </>
              ) : (
                <>
                  <CheckCircle2 className="h-3.5 w-3.5" />
                  {t("auth.register.ready")}
                </>
              )}
            </div>
            <button
              type="button"
              onClick={handleRemove}
              className="shrink-0 text-xs font-semibold text-secondary"
            >
              {t("auth.register.remove")}
            </button>
          </div>
        )}
      </div>

      <div className="flex gap-3">
        <Button
          type="button"
          variant="outline"
          onClick={onSkip}
          className="h-[46px] rounded-xl border-border px-5 text-ink/80"
        >
          {t("auth.register.browseJobs")}
        </Button>
        <Button
          type="button"
          disabled={status !== "ready"}
          onClick={() => onComplete?.(file)}
          className="h-[46px] flex-1 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90 disabled:bg-muted/40"
        >
          {t("auth.register.completeProfile")}
        </Button>
      </div>
    </div>
  );
}
