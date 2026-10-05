import { ChevronLeft, ChevronRight } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function CompanyPagination({ page, pageCount, onPageChange }) {
  return (
    <nav
      aria-label="Company results pages"
      className="flex flex-wrap items-center justify-between gap-3 border-t border-border px-4 py-3.5"
    >
      <div className="text-[13px] text-muted">
        Page {page} of {pageCount}
      </div>
      <div className="flex items-center gap-1.5">
        <Button
          type="button"
          variant="outline"
          size="icon"
          aria-label="Previous page"
          disabled={page <= 1}
          onClick={() => onPageChange(page - 1)}
          className="h-[34px] w-[34px] rounded-lg border-border bg-surface text-ink disabled:opacity-40"
        >
          <ChevronLeft className="h-4 w-4" />
        </Button>
        {Array.from({ length: pageCount }, (_, index) => index + 1).map(
          (pageNumber) => (
            <Button
              key={pageNumber}
              type="button"
              variant={pageNumber === page ? "default" : "outline"}
              aria-current={pageNumber === page ? "page" : undefined}
              onClick={() => onPageChange(pageNumber)}
              className={`h-[34px] min-w-[34px] rounded-lg px-2.5 text-[13px] ${
                pageNumber === page
                  ? "border-primary bg-primary text-primary-foreground"
                  : "border-border bg-surface text-ink"
              }`}
            >
              {pageNumber}
            </Button>
          ),
        )}
        <Button
          type="button"
          variant="outline"
          size="icon"
          aria-label="Next page"
          disabled={page >= pageCount}
          onClick={() => onPageChange(page + 1)}
          className="h-[34px] w-[34px] rounded-lg border-border bg-surface text-ink disabled:opacity-40"
        >
          <ChevronRight className="h-4 w-4" />
        </Button>
      </div>
    </nav>
  );
}
