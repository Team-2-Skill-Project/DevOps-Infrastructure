import CompanyStatusBadge from "./CompanyStatusBadge";
import CompanyRowActions from "./CompanyRowActions";
import { getRelativeLabel, formatDisplayDate } from "../../shared/companyDateFormat";

export default function CompanyRow({ company, onVerify, onReject }) {
  return (
    <tr className="border-b border-border transition-colors last:border-b-0 hover:bg-background/60">
      <td className="px-4 py-3.5">
        <div className="flex items-center gap-3">
          <div
            className={`flex h-10 w-10 shrink-0 items-center justify-center rounded-[10px] text-[14px] font-bold ${company.avatarClass}`}
          >
            {company.initials}
          </div>
          <div>
            <div className="text-[14px] font-semibold text-ink">{company.name}</div>
            <div className="mt-0.5 font-mono text-[12px] text-muted">{company.id}</div>
          </div>
        </div>
      </td>
      <td className="px-4 py-3.5">
        <CompanyStatusBadge status={company.status} />
      </td>
      <td className="px-4 py-3.5">
        <div className="text-[13.5px] text-ink">{company.email}</div>
        <div className="mt-0.5 text-[12.5px] text-muted">{company.phone || "—"}</div>
      </td>
      <td className="whitespace-nowrap px-4 py-3.5">
        <div className="text-[13.5px] text-ink">{formatDisplayDate(company.createdAt)}</div>
        <div className="text-[12px] text-muted">{getRelativeLabel(company.createdAt)}</div>
      </td>
      <td className="whitespace-nowrap px-4 py-3.5">
        <div className="text-[13.5px] text-ink">{formatDisplayDate(company.updatedAt)}</div>
        <div className="text-[12px] text-muted">{getRelativeLabel(company.updatedAt)}</div>
      </td>
      <td className="px-4 py-3.5 pr-5">
        <CompanyRowActions company={company} onVerify={onVerify} onReject={onReject} />
      </td>
    </tr>
  );
}
