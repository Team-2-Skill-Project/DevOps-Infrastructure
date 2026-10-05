import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { Check, SlidersHorizontal } from "lucide-react";
import Status from "@/components/shared/Status";
import { useLocalizedPath } from "@/utils/routes";

const SUMMARY_FIELDS = [
  { key: "jobTitle", label: "Target Title" },
  { key: "jobType", label: "Job Type" },
  { key: "workStyle", label: "Work Style" },
  { key: "location", label: "Location" },
];

export default function OnboardingCompletedSummary({ formData, onEdit }) {
  const { t } = useTranslation("dashboard");
  const navigate = useNavigate();
  const localizedPath = useLocalizedPath();

  return (
    <Status
      icon={<Check className="h-6 w-6" />}
      iconClassName="bg-success text-success-foreground"
      title={t("pages.onboarding.complete")}
      subtitle="Your AI career profile has been successfully configured. We are now calibrating jobs for you."
      secondaryButton={{ label: "Edit Choices", onClick: onEdit }}
      primaryButton={{ label: "Go to Dashboard", onClick: () => navigate(localizedPath("/dashboard")) }}
    >
      <div className="mb-5 rounded-2xl border border-border bg-background p-4 text-left">
        <div className="mb-3 flex items-center gap-2 text-[12px] font-bold text-ink">
          <SlidersHorizontal className="h-4 w-4 text-primary" />
          {t("pages.onboarding.preferences")}
        </div>
        <div className="grid grid-cols-2 gap-4">
          {SUMMARY_FIELDS.map((field) => (
            <div key={field.key}>
              <div className="text-[11px] text-muted">{field.label}</div>
              <div className="text-[13px] font-semibold text-ink">
                {formData[field.key] || "—"}
              </div>
            </div>
          ))}
        </div>
      </div>
    </Status>
  );
}
