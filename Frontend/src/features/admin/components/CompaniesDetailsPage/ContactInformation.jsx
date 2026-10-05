import { useTranslation } from "react-i18next";
import { Mail, Phone, Link2 } from "lucide-react";

import Section from "./Section";
import Field from "./Field";

/**
 * ContactInformation
 * Read-only contact grid. Mail/phone fields render as `mailto:`/`tel:` links.
 */
export default function ContactInformation({ contact }) {
  const { t } = useTranslation("dashboard");
  const key = "pages.admin.companyDetails.fields";

  const linkClass =
    "inline-flex items-center gap-1.5 font-semibold text-primary hover:underline";
  const iconClass = "h-3.5 w-3.5 text-muted";

  return (
    <Section title={t("pages.admin.companyDetails.contactInformation")}>
      <div className="grid grid-cols-2 gap-x-6 gap-y-4 max-[560px]:grid-cols-1">
        <Field
          label={t(`${key}.primaryEmail`)}
          value={
            <a href={`mailto:${contact.email}`} className={linkClass}>
              <Mail className={iconClass} />
              {contact.email}
            </a>
          }
        />
        <Field
          label={t(`${key}.phone`)}
          value={
            <a href={`tel:${contact.phone}`} className={linkClass}>
              <Phone className={iconClass} />
              {contact.phone}
            </a>
          }
        />
        <Field
          label={t(`${key}.hrEmail`)}
          value={
            <a href={`mailto:${contact.hrEmail}`} className={linkClass}>
              <Mail className={iconClass} />
              {contact.hrEmail}
            </a>
          }
        />
        <Field
          label={t(`${key}.linkedin`)}
          value={
            <a
              href={`https://${contact.linkedin}`}
              target="_blank"
              rel="noreferrer"
              className={linkClass}
            >
              <Link2 className={iconClass} />
              {contact.linkedin}
            </a>
          }
        />
      </div>
    </Section>
  );
}