import * as React from "react";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import {
  Loader2,
  CheckCircle2,
  Plus,
  Briefcase,
  MapPin,
  Clock,
  PenLine,
} from "lucide-react";

import { profileSchema } from "@/features/auth/schema/profile-schema";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { useTranslation } from "react-i18next";

const EXPERIENCE_OPTIONS = [
  "Less than 1 year",
  "1–2 years",
  "3–5 years",
  "6–10 years",
  "10+ years",
];

// Mocked until the real CV-analysis endpoint is wired up.
const MOCK_ANALYSIS = {
  matchedSkills: ["React", "TypeScript", "Next.js", "REST APIs", "Git"],
  growthSkills: ["System Design", "GraphQL", "Testing (Jest)"],
  skillsFound: 14,
  skillsMatched: 9,
  profile: {
    jobTitle: "Frontend Developer",
    location: "Damietta, Egypt",
    experience: "3–5 years",
    bio: "Frontend developer specializing in React, Next.js, and TypeScript, currently freelancing while job searching.",
  },
};

function AutofillTag({ show }) {
  if (!show) return null;
  return (
    <span className="ms-1.5 inline-flex items-center gap-0.5 rounded-md bg-success/10 px-1.5 py-px text-[10px] font-bold text-success">
      ✓ Auto-filled
    </span>
  );
}

/**
 * ProfileCompletionStep — step 4 of 4: "Complete your profile"
 * cvFileName — shown in the "Analyzing ___" status line
 * onBack()   — go back to the CV upload step
 * onFinish(data) — called with the validated profile once the user finishes
 */
export function ProfileCompletionStep({ cvFileName, onBack, onFinish }) {
  const { t } = useTranslation("common");
  const [analyzing, setAnalyzing] = React.useState(true);
  const [autofilled, setAutofilled] = React.useState(new Set());

  const form = useForm({
    resolver: zodResolver(profileSchema),
    defaultValues: { jobTitle: "", location: "", experience: "", bio: "" },
  });

  React.useEffect(() => {
    // TODO: replace with the real CV-analysis call; this mock just
    // simulates the delay and result shape.
    const id = setTimeout(() => {
      form.reset(MOCK_ANALYSIS.profile);
      setAutofilled(new Set(["jobTitle", "location", "experience", "bio"]));
      setAnalyzing(false);
    }, 1800);
    return () => clearTimeout(id);
  }, [form]);

  const clearAutofill = (name) => {
    setAutofilled((prev) => {
      if (!prev.has(name)) return prev;
      const next = new Set(prev);
      next.delete(name);
      return next;
    });
  };

  return (
    <div>
      <div className="mb-1 text-[11px] font-semibold text-secondary">
        {t("auth.register.step", { current: 4 })}
      </div>
      <h1 className="mb-1 font-[DM_Sans] text-[23px] font-bold leading-7 tracking-tight text-ink">
        {analyzing
          ? t("auth.register.profileReading")
          : t("auth.register.profileTitle")}
      </h1>
      <p className="mb-5 text-[13.5px] text-muted">
        {analyzing
          ? t("auth.register.profileReadingDescription")
          : t("auth.register.profileDescription")}
      </p>

      <div className={cnStatus(analyzing)}>
        {analyzing ? (
          <Loader2 className="h-5 w-5 shrink-0 animate-spin text-primary" />
        ) : (
          <div className="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-success">
            <CheckCircle2 className="h-4 w-4 text-success-foreground" />
          </div>
        )}
        <div>
          <p className="text-[13px] font-semibold text-ink">
            {analyzing
              ? t("auth.register.analyzing", {
                  file: cvFileName || t("auth.register.yourProfile"),
                })
              : t("auth.register.cvAnalyzed")}
          </p>
          <p className="text-xs text-muted">
            {analyzing
              ? t("auth.register.extracting")
              : t("auth.register.extracted")}
          </p>
        </div>
      </div>

      {!analyzing && (
        <>
          <div className="mb-4 flex items-center gap-3 rounded-xl bg-success/10 px-3.5 py-2.5 text-[12.5px] font-semibold text-success">
            <span className="h-1.5 w-1.5 shrink-0 rounded-full bg-success" />
            {t("auth.register.skillsSummary", {
              found: MOCK_ANALYSIS.skillsFound,
              matched: MOCK_ANALYSIS.skillsMatched,
            })}
          </div>

          <p className="mb-2 text-[10.5px] font-semibold tracking-wide text-muted">
            {t("auth.register.matchedSkills")}
          </p>
          <div className="mb-3.5 flex flex-wrap gap-1.5">
            {MOCK_ANALYSIS.matchedSkills.map((skill) => (
              <Badge
                key={skill}
                variant="outline"
                className="gap-1 rounded-lg border-success bg-surface text-ink"
              >
                <CheckCircle2 className="h-3 w-3 text-success" />
                {skill}
              </Badge>
            ))}
          </div>

          <p className="mb-2 text-[10.5px] font-semibold tracking-wide text-muted">
            {t("auth.register.growth")}
          </p>
          <div className="mb-4 flex flex-wrap gap-1.5">
            {MOCK_ANALYSIS.growthSkills.map((skill) => (
              <Badge
                key={skill}
                variant="outline"
                className="gap-1 rounded-lg border-border bg-surface text-muted"
              >
                <Plus className="h-3 w-3 text-warning" />
                {skill}
              </Badge>
            ))}
          </div>

          <p className="mb-2 text-[10.5px] font-semibold tracking-wide text-muted">
            {t("auth.register.profile")}
          </p>

          <Form {...form}>
            <form
              onSubmit={form.handleSubmit(onFinish)}
              className="flex flex-col gap-3"
            >
              <FormField
                control={form.control}
                name="jobTitle"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-[11.5px] font-semibold text-ink/80">
                      {t("auth.register.jobTitle")}
                      <AutofillTag show={autofilled.has("jobTitle")} />
                    </FormLabel>
                    <FormControl>
                      <div className="relative">
                        <Briefcase className="pointer-events-none absolute inset-s-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                        <Input
                          placeholder={t("auth.register.jobTitlePlaceholder")}
                          className="h-11 rounded-xl border-border ps-9"
                          {...field}
                          onChange={(e) => {
                            clearAutofill("jobTitle");
                            field.onChange(e);
                          }}
                        />
                      </div>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <div className="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <FormField
                  control={form.control}
                  name="location"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel className="text-[11.5px] font-semibold text-ink/80">
                        {t("auth.register.location")}
                        <AutofillTag show={autofilled.has("location")} />
                      </FormLabel>
                      <FormControl>
                        <div className="relative">
                          <MapPin className="pointer-events-none absolute inset-s-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
                          <Input
                            placeholder={t("auth.register.locationPlaceholder")}
                            className="h-11 rounded-xl border-border ps-9"
                            {...field}
                            onChange={(e) => {
                              clearAutofill("location");
                              field.onChange(e);
                            }}
                          />
                        </div>
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />

                <FormField
                  control={form.control}
                  name="experience"
                  render={({ field }) => (
                    <FormItem>
                      <FormLabel className="text-[11.5px] font-semibold text-ink/80">
                        {t("auth.register.experience")}
                        <AutofillTag show={autofilled.has("experience")} />
                      </FormLabel>
                      <FormControl>
                        <div className="relative">
                          <Clock className="pointer-events-none absolute inset-s-3 top-1/2 z-10 h-4 w-4 -translate-y-1/2 text-muted" />
                          <Select
                            value={field.value}
                            onValueChange={(value) => {
                              clearAutofill("experience");
                              field.onChange(value);
                            }}
                          >
                            <SelectTrigger className="h-11 rounded-xl border-border ps-9">
                              <SelectValue
                                placeholder={t("auth.register.select")}
                              />
                            </SelectTrigger>
                            <SelectContent>
                              {EXPERIENCE_OPTIONS.map((opt) => (
                                <SelectItem key={opt} value={opt}>
                                  {opt}
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </div>
                      </FormControl>
                      <FormMessage />
                    </FormItem>
                  )}
                />
              </div>

              <FormField
                control={form.control}
                name="bio"
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-[11.5px] font-semibold text-ink/80">
                      {t("auth.register.bio")}{" "}
                      <span className="font-normal text-muted">
                        ({t("auth.register.optional")})
                      </span>
                      <AutofillTag show={autofilled.has("bio")} />
                    </FormLabel>
                    <FormControl>
                      <div className="relative">
                        <PenLine className="pointer-events-none absolute inset-s-3 top-3.5 h-4 w-4 text-muted" />
                        <Textarea
                          placeholder={t("auth.register.bioPlaceholder")}
                          className="min-h-[54px] rounded-xl border-border ps-9"
                          {...field}
                          onChange={(e) => {
                            clearAutofill("bio");
                            field.onChange(e);
                          }}
                        />
                      </div>
                    </FormControl>
                    <FormMessage />
                  </FormItem>
                )}
              />

              <div className="mt-1 flex gap-3">
                <Button
                  type="button"
                  variant="outline"
                  onClick={onBack}
                  className="h-[46px] rounded-xl border-border px-5 text-ink/80"
                >
                  {t("auth.register.back")}
                </Button>
                <Button
                  type="submit"
                  className="h-[46px] flex-1 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90"
                >
                  {t("auth.register.dashboard")}
                </Button>
              </div>
            </form>
          </Form>
        </>
      )}
    </div>
  );
}

function cnStatus(analyzing) {
  return [
    "mb-4 flex items-center gap-3.5 rounded-xl border p-3.5",
    analyzing ? "border-border bg-surface" : "border-success/35 bg-success/10",
  ].join(" ");
}
