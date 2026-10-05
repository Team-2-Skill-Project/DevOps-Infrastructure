import { ChevronLeft, ChevronRight } from "lucide-react";
import { cn } from "@/lib/utils";
import { useTranslation } from "react-i18next";

export default function Pagination({ page, totalPages, onPageChange }) {
  const { t } = useTranslation("common");
  return <div className="flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3.5"><div className="text-[13px] text-muted">{t("ui.pagination.page", { page, total: totalPages })}</div><div className="flex items-center gap-1.5"><PageButton disabled={page === 1} onClick={() => onPageChange(Math.max(1, page - 1))} aria-label={t("ui.pagination.previous")}><ChevronLeft className="h-3.5 w-3.5" /></PageButton>{Array.from({ length: totalPages }).map((_, index) => <PageButton key={index} active={page === index + 1} onClick={() => onPageChange(index + 1)}>{index + 1}</PageButton>)}<PageButton disabled={page === totalPages} onClick={() => onPageChange(Math.min(totalPages, page + 1))} aria-label={t("ui.pagination.next")}><ChevronRight className="h-3.5 w-3.5" /></PageButton></div></div>;
}

function PageButton({ children, active, disabled, onClick, ...rest }) { return <button type="button" disabled={disabled} onClick={onClick} className={cn("h-[34px] min-w-[34px] rounded-lg border px-2.5 text-[13px] font-medium transition-colors disabled:cursor-not-allowed disabled:opacity-40", active ? "border-primary bg-primary text-primary-foreground" : "border-border bg-surface text-ink hover:bg-background")} {...rest}>{children}</button>; }