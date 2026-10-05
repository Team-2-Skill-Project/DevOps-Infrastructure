import { useMemo, useState } from "react";
import CompaniesPageHeader from "../components/companies/CompaniesPageHeader";
import CompaniesResultsBar from "../components/companies/CompaniesResultsBar";
import CompaniesTable from "../components/companies/CompaniesTable";
import CompaniesToolbar from "../components/companies/CompaniesToolbar";
import CompanyPagination from "../components/companies/CompanyPagination";
import CompanyStatsStrip from "../components/companies/CompanyStatsStrip";
import CompanyStatusLegend from "../components/companies/CompanyStatusLegend";
import { INITIAL_COMPANIES } from "@/features/admin/shared/companiesData";

const PAGE_SIZE = 8;
const CURRENT_TIME = Date.now();

function downloadCompaniesCsv(companies) {
  const columns = [
    ["Company ID", "id"],
    ["Company", "name"],
    ["Status", "status"],
    ["Email", "email"],
    ["Phone", "phone"],
    ["Created", "createdAt"],
    ["Last updated", "updatedAt"],
  ];
  const escapeCsvValue = (value) => `"${String(value ?? "").replaceAll('"', '""')}"`;
  const csv = [
    columns.map(([label]) => escapeCsvValue(label)).join(","),
    ...companies.map((company) =>
      columns.map(([, key]) => escapeCsvValue(company[key])).join(","),
    ),
  ].join("\r\n");
  const blob = new Blob(["\uFEFF", csv], { type: "text/csv;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.download = "companies.csv";
  link.click();
  window.setTimeout(() => URL.revokeObjectURL(url), 0);
}

export default function CompaniesPage() {
  const [companies, setCompanies] = useState(INITIAL_COMPANIES);
  const [search, setSearch] = useState("");
  const [statusFilter, setStatusFilter] = useState("all");
  const [dateFilter, setDateFilter] = useState("any");
  const [sort, setSort] = useState("newest");
  const [page, setPage] = useState(1);

  const filteredCompanies = useMemo(() => {
    const normalizedSearch = search.trim().toLocaleLowerCase();
    const dateThreshold =
      dateFilter === "any"
        ? null
        : CURRENT_TIME - Number(dateFilter) * 24 * 60 * 60 * 1000;

    return companies
      .filter((company) => {
        const matchesSearch =
          !normalizedSearch ||
          [company.name, company.email, company.id].some((value) =>
            value.toLocaleLowerCase().includes(normalizedSearch),
          );
        const matchesStatus =
          statusFilter === "all" || company.status === statusFilter;
        const matchesDate =
          dateThreshold === null ||
          new Date(`${company.createdAt}T12:00:00`).getTime() >= dateThreshold;
        return matchesSearch && matchesStatus && matchesDate;
      })
      .sort((first, second) => {
        switch (sort) {
          case "oldest":
            return first.createdAt.localeCompare(second.createdAt);
          case "name-asc":
            return first.name.localeCompare(second.name);
          case "name-desc":
            return second.name.localeCompare(first.name);
          case "updated":
            return second.updatedAt.localeCompare(first.updatedAt);
          case "newest":
          default:
            return second.createdAt.localeCompare(first.createdAt);
        }
      });
  }, [companies, dateFilter, search, sort, statusFilter]);

  const pageCount = Math.max(1, Math.ceil(filteredCompanies.length / PAGE_SIZE));
  const currentPage = Math.min(page, pageCount);
  const pageStart = (currentPage - 1) * PAGE_SIZE;
  const visibleCompanies = filteredCompanies.slice(pageStart, pageStart + PAGE_SIZE);

  const updateCompanyStatus = (id, status) => {
    setCompanies((currentCompanies) =>
      currentCompanies.map((company) =>
        company.id === id
          ? { ...company, status, updatedAt: new Date().toISOString().slice(0, 10) }
          : company,
      ),
    );
  };

  const clearFilters = () => {
    setSearch("");
    setStatusFilter("all");
    setDateFilter("any");
    setPage(1);
  };

  const refreshCompanies = () => {
    setCompanies(INITIAL_COMPANIES);
    clearFilters();
    setSort("newest");
  };

  return (
    <main className="min-h-screen bg-background px-4 py-6 text-ink antialiased sm:px-7 sm:py-8">
      <div className="mx-auto max-w-7xl">
        <CompaniesPageHeader
          onExport={() => downloadCompaniesCsv(filteredCompanies)}
          onRefresh={refreshCompanies}
        />

        <CompanyStatsStrip companies={companies} />

        <CompaniesToolbar
          search={search}
          onSearchChange={(value) => {
            setSearch(value);
            setPage(1);
          }}
          statusFilter={statusFilter}
          onStatusFilterChange={(value) => {
            setStatusFilter(value);
            setPage(1);
          }}
          dateFilter={dateFilter}
          onDateFilterChange={(value) => {
            setDateFilter(value);
            setPage(1);
          }}
          onClearFilters={clearFilters}
        />

        <CompaniesResultsBar
          total={filteredCompanies.length}
          start={filteredCompanies.length === 0 ? 0 : pageStart + 1}
          end={Math.min(pageStart + PAGE_SIZE, filteredCompanies.length)}
          sort={sort}
          onSortChange={setSort}
        />

        <section className="overflow-hidden rounded-2xl border border-border bg-surface shadow-sm">
          <div className="overflow-x-auto">
            <CompaniesTable
              companies={visibleCompanies}
              onVerify={(id) => updateCompanyStatus(id, "verified")}
              onReject={(id) => updateCompanyStatus(id, "not-verified")}
            />
          </div>
          <CompanyPagination
            page={currentPage}
            pageCount={pageCount}
            onPageChange={setPage}
          />
        </section>

        <CompanyStatusLegend />
      </div>
    </main>
  );
}
