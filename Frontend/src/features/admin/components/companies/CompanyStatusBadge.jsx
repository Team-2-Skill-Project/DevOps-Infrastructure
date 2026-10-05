import { COMPANY_STATUS_STYLES } from "@/features/admin/shared/companyStatusStyles";

export default function CompanyStatusBadge({ status, compact = false }) {
  const style = COMPANY_STATUS_STYLES[status];
  const Icon = style.icon;

  return (
    <span
      className={`inline-flex items-center gap-1.5 rounded-full border font-semibold ${
        style.badgeClass
      } ${compact ? "h-[22px] px-2 text-[11px]" : "h-7 px-3 text-[12.5px]"}`}
    >
      <Icon className={compact ? "h-3 w-3" : "h-3.5 w-3.5"} />
      {style.label}
    </span>
  );
}
