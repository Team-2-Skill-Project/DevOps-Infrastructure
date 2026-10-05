import { useTranslation } from "react-i18next";
import { XCircle, RotateCcw } from "lucide-react";

import { Button } from "@/components/ui/button";
import { Switch } from "@/components/ui/switch";
import { Label } from "@/components/ui/label";
import { cn } from "@/lib/utils";
import Section from "./Section";
import VerificationBadge from "./VerificationBadge";
import {
  VERIFICATION_STATUS,
  VERIFICATION_TONES,
} from "../../shared/companiesDetailsData";

/**
 * VerificationCard
 * Sidebar controls for the verification state: a Switch toggles
 * verified/pending and a secondary button toggles rejected. Replaces the
 * three duplicated full-width button stacks.
 */
export default function VerificationCard({ status, onStatusChange }) {
  const { t } = useTranslation("dashboard");
  const config = VERIFICATION_STATUS[status] ?? VERIFICATION_STATUS.pending;
  const tone = VERIFICATION_TONES[config.tone];
  const isVerified = status === "verified";
  const isRejected = status === "rejected";

  return (
    <Section title={t("pages.admin.companyDetails.verificationStatus")}>
      <VerificationBadge
        status={status}
        label={t(`pages.admin.companyDetails.${config.labelKey}`)}
        className="mb-4"
      />

      {/* Verified toggle */}
      <div className="mb-1.5 flex items-center justify-between gap-3">
        <Label
          htmlFor="company-verified"
          className="text-[13.5px] font-semibold text-ink"
        >
          {t("pages.admin.companyDetails.markAsVerified")}
        </Label>
        <Switch
          id="company-verified"
          checked={isVerified}
          onCheckedChange={(checked) =>
            onStatusChange(checked ? "verified" : "pending")
          }
          aria-label={t("pages.admin.companyDetails.markAsVerified")}
        />
      </div>
      <p className="mb-4 text-xs text-muted">
        {isVerified
          ? t("pages.admin.companyDetails.verifiedHint")
          : t("pages.admin.companyDetails.pendingHint")}
      </p>

      {/* Reject / re-evaluate */}
      <Button
        variant="outline"
        onClick={() => onStatusChange(isRejected ? "pending" : "rejected")}
        className={cn(
          "h-10 w-full justify-center gap-2 rounded-xl border-border bg-surface",
          tone.control,
        )}
      >
        {isRejected ? (
          <>
            <RotateCcw className="h-4 w-4" />
            {t("pages.admin.companyDetails.reEvaluate")}
          </>
        ) : (
          <>
            <XCircle className="h-4 w-4" />
            {t("pages.admin.companyDetails.reject")}
          </>
        )}
      </Button>
    </Section>
  );
}