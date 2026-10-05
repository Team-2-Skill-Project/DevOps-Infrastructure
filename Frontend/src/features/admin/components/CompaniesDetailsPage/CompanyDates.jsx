import { useTranslation } from "react-i18next";
import Section from "./Section";

/**
 * CompanyDates
 * Created / updated / submitted timestamps.
 */
export default function CompanyDates({ dates }) {
  const { t } = useTranslation("dashboard");

  return (
    <Section
      title={t("pages.admin.companyDetails.dates")}
      bodyClassName="px-5 py-1.5"
    >
      <dl className="divide-y divide-border">
        {dates.map((item) => (
          <div
            key={item.label}
            className="flex items-start justify-between gap-3 py-2.5 text-[13.5px]"
          >
            <dt className="text-muted">
              {t(`pages.admin.companyDetails.dateLabels.${item.label}`)}
            </dt>
            <dd className="text-right font-semibold text-ink">
              {item.date}
              <span className="mt-0.5 block text-xs font-normal text-muted">
                {item.sub}
              </span>
            </dd>
          </div>
        ))}
      </dl>
    </Section>
  );
}