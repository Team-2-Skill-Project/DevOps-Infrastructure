import CompanyStatusBadge from "./CompanyStatusBadge";

const LEGEND_ITEMS = [
  { status: "verified", description: "Approved & active" },
  { status: "pending", description: "Awaiting admin review" },
  { status: "not-verified", description: "Incomplete or rejected" },
];

export default function CompanyStatusLegend() {
  return (
    <div className="mt-3.5 flex flex-wrap items-center gap-x-4 gap-y-2 text-[12.5px] text-muted">
      {LEGEND_ITEMS.map((item) => (
        <span key={item.status} className="inline-flex items-center gap-1.5">
          <CompanyStatusBadge status={item.status} compact />
          {item.description}
        </span>
      ))}
    </div>
  );
}
