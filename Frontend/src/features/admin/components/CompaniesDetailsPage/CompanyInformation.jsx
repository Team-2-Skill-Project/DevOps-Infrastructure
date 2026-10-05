import { useTranslation } from "react-i18next";
import { ExternalLink } from "lucide-react";

import Section from "./Section";
import Field from "./Field";

/**
 * CompanyInformation
 * Read-only core profile grid.
 */
export default function CompanyInformation({ profile }) {
  const { t } = useTranslation("dashboard");
  const key = "pages.admin.companyDetails.fields";

  return (
    <Section
      title={t("pages.admin.companyDetails.companyInformation")}
      hint={t("pages.admin.companyDetails.coreProfileDetails")}
    >
      <div className="grid grid-cols-2 gap-x-6 gap-y-4 max-[560px]:grid-cols-1">
        <Field label={t(`${key}.companyName`)} value={profile.companyName} />
        <Field label={t(`${key}.legalName`)} value={profile.legalName} />
        <Field label={t(`${key}.industry`)} value={profile.industry} />
        <Field label={t(`${key}.companySize`)} value={profile.size} />
        <Field
          label={t(`${key}.website`)}
          value={
            <a
              href={profile.website}
              target="_blank"
              rel="noreferrer"
              className="inline-flex items-center gap-1 font-semibold text-primary hover:underline"
            >
              {profile.website}
              <ExternalLink className="h-3 w-3" />
            </a>
          }
        />
        <Field label={t(`${key}.headquarters`)} value={profile.headquarters} />
        <Field
          full
          label={t(`${key}.about`)}
          value={profile.about}
          valueClassName="font-normal leading-relaxed text-muted"
        />
      </div>
    </Section>
  );
}