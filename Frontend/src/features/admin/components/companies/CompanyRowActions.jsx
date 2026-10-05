import { Link } from "react-router-dom";
import { CheckCircle2, Eye } from "lucide-react";
import { Button } from "@/components/ui/button";

export default function CompanyRowActions({ company, onVerify, onReject }) {
  return (
    <div className="flex items-center justify-end gap-1.5">
      {company.status === "verified" ? (
        <Button
          type="button"
          variant="outline"
          size="sm"
          className="h-8 rounded-lg border-border bg-surface text-[12.5px] font-semibold text-ink hover:bg-background"
        >
          Manage
        </Button>
      ) : (
        <Button
          type="button"
          size="sm"
          onClick={() => onVerify(company.id)}
          className="h-8 gap-1.5 rounded-lg bg-success text-[12.5px] font-semibold text-success-foreground hover:bg-success/90"
        >
          <CheckCircle2 className="h-[14px] w-[14px]" />
          Verify
        </Button>
      )}

      {company.status === "pending" && (
        <Button
          type="button"
          variant="outline"
          size="sm"
          onClick={() => onReject(company.id)}
          className="h-8 rounded-lg border-error/35 bg-transparent text-[12.5px] font-semibold text-error hover:border-error hover:bg-error/10"
        >
          Reject
        </Button>
      )}

      {/* TODO: point to the real company detail route */}
      <Button
        asChild
        type="button"
        variant="ghost"
        size="icon"
        className="h-8 w-8 rounded-lg text-muted hover:bg-primary/10 hover:text-primary"
      >
        <Link to={`/admin/companies/${company.id}`} title="View company">
          <Eye className="h-4 w-4" />
        </Link>
      </Button>
    </div>
  );
}
