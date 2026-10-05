import StatPill from "./StatPill";
import { COMPANY_STATS } from "@/features/admin/shared/companyStats";
import { INITIAL_COMPANIES } from "../../shared/companiesData";

function countStatuses(companies) {
  return companies.reduce(
    (counts, company) => {
      counts[company.status] += 1;
      return counts;
    },
    { verified: 0, pending: 0, "not-verified": 0 },
  );
}

export default function CompanyStatsStrip({ companies = INITIAL_COMPANIES }) {
  const currentCounts = countStatuses(companies);
  const initialCounts = countStatuses(INITIAL_COMPANIES);

  return (
    <div className="mb-5 grid grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-4">
      {COMPANY_STATS.map((stat, index) => {
        const value =
          stat.key === "total"
            ? stat.value
            : stat.value + currentCounts[stat.key] - initialCounts[stat.key];
        return (
          <StatPill
            key={stat.key}
            stat={{ ...stat, value }}
            index={index}
          />
        );
      })}
    </div>
  );
}
