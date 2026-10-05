import { useState } from "react";
import { useTranslation } from "react-i18next";
import { Link } from "react-router-dom";
import { ArrowLeft, CheckCircle2 } from "lucide-react";

import { Button } from "@/components/ui/button";
import {
  COMPANY,
  COMPANY_ACTIVITY,
  COMPANY_JOBS,
  CompanyActivity,
  CompanyBreadcrumbs,
  CompanyDates,
  CompanyHeader,
  CompanyInformation,
  CompanyJobs,
  CompanyQuickLinks,
  ContactInformation,
  DEFAULT_VERIFICATION_STATUS,
  OverviewStats,
  VERIFICATION_STATUS,
  VerificationBanner,
  VerificationCard,
} from "@/features/admin/components/CompaniesDetailsPage";

const COMPANIES_PATH = "/admin/company-management";

/**
 * CompaniesDetailsPage
 * Thin composition shell for a single company. Owns only the verification
 * state; every visual block lives in the co-located component folder.
 */
export default function CompanyDetails() {
  const { t } = useTranslation("dashboard");
  const [verificationStatus, setVerificationStatus] = useState(
    DEFAULT_VERIFICATION_STATUS,
  );

  const statusConfig =
    VERIFICATION_STATUS[verificationStatus] ?? VERIFICATION_STATUS.pending;
  const base = "pages.admin.companyDetails";

  return (
    <div className="min-h-screen bg-background px-4 py-7 pb-16 sm:px-7">
      <div className="mx-auto max-w-275 space-y-0">
        <CompanyBreadcrumbs companyId={COMPANY.id} backTo={COMPANIES_PATH} />

        <CompanyHeader
          company={{ ...COMPANY, name: COMPANY.name }}
          backTo={COMPANIES_PATH}
        />

        <div className="mb-5.5">
          <VerificationBanner
            status={verificationStatus}
            title={t(`${base}.${statusConfig.bannerKey}`)}
            description={t(`${base}.bannerDescriptions.${verificationStatus}`)}
          />
        </div>

        <OverviewStats stats={COMPANY.stats} />

        <div className="grid grid-cols-[1fr_340px] items-start gap-5 max-[900px]:grid-cols-1">
          <div className="space-y-5">
            <CompanyInformation
              profile={{ ...COMPANY.profile, companyName: COMPANY.name }}
            />
            <ContactInformation contact={COMPANY.contact} />
            <CompanyJobs jobs={COMPANY_JOBS} />
            <CompanyActivity activity={COMPANY_ACTIVITY} />
          </div>

          <aside className="sticky top-6 space-y-5">
            <VerificationCard
              status={verificationStatus}
              onStatusChange={setVerificationStatus}
            />
            <CompanyDates dates={COMPANY.dates} />
            <CompanyQuickLinks />
          </aside>
        </div>

        {/* Footer */}
        <div className="mt-2 flex flex-wrap items-center justify-between gap-3 pt-2">
          <Button
            variant="ghost"
            asChild
            className="h-10 gap-2 rounded-xl text-muted hover:text-ink"
          >
            <Link to={COMPANIES_PATH}>
              <ArrowLeft className="h-4 w-4" />
              {t(`${base}.backToCompanies`)}
            </Link>
          </Button>
          <div className="flex flex-wrap items-center gap-2.5">
            <Button
              variant="outline"
              className="h-10 rounded-xl border-border bg-surface text-ink"
            >
              {t(`${base}.editCompany`)}
            </Button>
            <Button className="h-10 gap-2 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90">
              <CheckCircle2 className="h-4 w-4" />
              {t(`${base}.saveChanges`)}
            </Button>
          </div>
        </div>
      </div>
    </div>
  );
}