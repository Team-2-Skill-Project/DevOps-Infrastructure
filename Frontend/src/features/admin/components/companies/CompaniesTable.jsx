import CompanyRow from "./CompanyRow";

const COLUMNS = ["Company", "Verification", "Contact", "Created", "Last Updated"];

export default function CompaniesTable({ companies, onVerify, onReject }) {
  return (
    <table className="w-full min-w-[920px] border-collapse">
      <thead className="border-b border-border bg-background">
        <tr>
          {COLUMNS.map((col) => (
            <th
              key={col}
              className="px-4 py-3 text-left text-[11.5px] font-bold uppercase tracking-wider text-muted"
            >
              {col}
            </th>
          ))}
          <th className="px-4 py-3 pr-5 text-right text-[11.5px] font-bold uppercase tracking-wider text-muted">
            Actions
          </th>
        </tr>
      </thead>
      <tbody>
        {companies.map((company) => (
          <CompanyRow
            key={company.id}
            company={company}
            onVerify={onVerify}
            onReject={onReject}
          />
        ))}
        {companies.length === 0 && (
          <tr>
            <td colSpan={6} className="px-4 py-12 text-center text-[13.5px] text-muted">
              No companies match these filters.
            </td>
          </tr>
        )}
      </tbody>
    </table>
  );
}
