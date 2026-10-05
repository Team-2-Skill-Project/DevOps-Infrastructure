import { useState } from "react";
import { useTranslation } from "react-i18next";
import { ChevronRight, RotateCcw, ExternalLink, Briefcase, Building2 } from "lucide-react";
import { cn } from "@/lib/utils";
import {
  PageHeader,
  Badge,
  ReadOnlyBanner,
} from "@/features/admin/shared";
import {
  DetailCard,
  DiffViewer,
  RelatedEntityCard,
  AuditLogDetailsSkeleton,
  AUDIT_LOG_DETAIL,
  RELATED_ENTITIES,
} from "@/features/admin/components/auditLogDetails";

/** Small uppercase label used inside detail cards. */
function FieldLabel({ children }) {
  return (
    <label className="text-[11px] font-bold uppercase tracking-wider text-muted block mb-1">
      {children}
    </label>
  );
}

/**
 * AuditLogDetails (page)
 * Container for a single audit-log record. Owns the loading state and
 * composes breadcrumb, header, read-only banner, status strip and the
 * animated section cards (actor, entity, diff, related entities).
 */
export default function AuditLogDetails() {
  const { t } = useTranslation("dashboard");
  const [isLoading, setIsLoading] = useState(false);
  const log = AUDIT_LOG_DETAIL;

  const handleReturn = () => {
    setIsLoading(true);
    setTimeout(() => setIsLoading(false), 1200);
  };

  if (isLoading) return <AuditLogDetailsSkeleton />;

  return (
    <div className="max-w-225 mx-auto p-4 sm:p-7 pb-16 bg-background text-[#222831] min-h-screen font-sans antialiased">
      <PageHeader
        breadcrumbs={
          <nav className="flex items-center gap-1.5 text-xs sm:text-sm text-muted mb-3 flex-wrap">
            <a href="#" className="hover:text-primary transition-colors">
              {t("pages.admin.auditLogs")}
            </a>
            <ChevronRight className="w-3.5 h-3.5 opacity-60" />
            <span className="font-medium text-[#222831]">{log.id}</span>
          </nav>
        }
        title={t("pages.admin.auditLogDetails")}
        subtitle={
          <span className="text-xs sm:text-sm font-mono text-muted">
            #{log.id}
          </span>
        }
        actions={
          <button
            type="button"
            onClick={handleReturn}
            className="inline-flex items-center gap-2 h-10 px-4 rounded-xl text-xs sm:text-sm font-semibold border border-transparent hover:bg-black/5 text-muted hover:text-[#222831] transition-colors"
          >
            <RotateCcw className="w-4 h-4" /> Return to Audit Logs
          </button>
        }
      />

      {/* Read-Only Banner */}
      <ReadOnlyBanner>
        This is an immutable record. Audit logs cannot be edited or deleted.
        They exist for full administrative traceability.
      </ReadOnlyBanner>

      {/* Status Strip */}
      <div className="flex flex-wrap items-center gap-3.5 p-3.5 px-4 bg-white border border-border rounded-2xl mb-5 shadow-sm">
        <Badge
          type={log.actionType}
          text={log.action}
          className="h-8 px-3.5 text-xs sm:text-sm font-bold"
        />
        <div className="flex items-center gap-2 text-xs sm:text-sm text-muted flex-wrap">
          <span>
            by{" "}
            <strong className="text-[#222831] font-semibold">
              {log.actor.name}
            </strong>
          </span>
          <span className="w-1 h-1 rounded-full bg-border" />
          <span>{log.timestamp}</span>
          <span className="w-1 h-1 rounded-full bg-border" />
          <span>{log.relative}</span>
        </div>
        <Badge type="readonly" text="Read-only" className="ml-auto" />
      </div>

      {/* Card 1: Actor */}
      <DetailCard title={t("pages.admin.auditDetails.actor")} hint="Who performed this action">
        <div className="flex items-center gap-3">
          <div
            className={cn(
              "w-11 h-11 rounded-full text-white flex items-center justify-center font-bold text-sm shrink-0",
              log.actor.avatarClass,
            )}
          >
            {log.actor.initials}
          </div>
          <div>
            <div className="font-bold text-base text-[#222831]">
              {log.actor.name}
            </div>
            <div className="text-xs sm:text-sm text-muted">
              {log.actor.role} · {log.actor.code}
            </div>
          </div>
        </div>
      </DetailCard>

      {/* Card 2: Entity */}
      <DetailCard
        title={t("pages.admin.auditDetails.entity")}
        hint="What was affected"
        delay={0.05}
        bodyClassName="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs sm:text-sm"
      >
        <div>
          <FieldLabel>Entity Type</FieldLabel>
          <div className="font-semibold text-[#222831]">{log.entity.type}</div>
        </div>
        <div>
          <FieldLabel>Entity ID</FieldLabel>
          <div className="font-mono text-[#222831]">{log.entity.id}</div>
        </div>
        <div className="sm:col-span-2">
          <FieldLabel>Entity Title</FieldLabel>
          <a
            href={log.entity.href}
            className="inline-flex items-center gap-1.5 font-bold text-primary hover:underline"
          >
            {log.entity.title} <ExternalLink className="w-3.5 h-3.5" />
          </a>
        </div>
      </DetailCard>

      {/* Card 3: What Changed */}
      <DetailCard
        title={t("pages.admin.auditDetails.changes")}
        hint="Previous value → New value"
        delay={0.1}
      >
        <DiffViewer
          summary={
            <>
              <strong>{log.change.field}</strong> changed from{" "}
              <strong>{log.change.from}</strong> to{" "}
              <strong>{log.change.to}</strong>
            </>
          }
          previous={log.previousData}
          next={log.nextData}
          changes={log.fieldChanges}
        />
      </DetailCard>

      {/* Card 4: Related Entities */}
      <DetailCard
        title={t("pages.admin.auditDetails.relatedEntities")}
        hint="Jump to linked records"
        delay={0.15}
        bodyClassName="grid grid-cols-1 sm:grid-cols-2 gap-3.5"
      >
        {RELATED_ENTITIES.map((entity) => (
          <RelatedEntityCard key={entity.id} {...entity} />
        ))}
      </DetailCard>

      {/* Footer Actions */}
      <div className="flex items-center justify-between gap-3 mt-4 flex-wrap">
        <button
          type="button"
          onClick={handleReturn}
          className="inline-flex items-center gap-2 h-10 px-4 rounded-xl text-xs sm:text-sm font-semibold hover:bg-black/5 text-muted hover:text-[#222831] transition-colors"
        >
          <RotateCcw className="w-4 h-4" /> Return to Audit Logs
        </button>
        <div className="flex gap-2.5 flex-wrap">
          <button
            type="button"
            className="inline-flex items-center gap-2 h-10 px-4 rounded-xl text-xs sm:text-sm font-semibold border border-border bg-white hover:bg-[#f5f2ec] transition-colors"
          >
            <Building2 className="w-4 h-4" /> View Related Company
          </button>
          <button
            type="button"
            className="inline-flex items-center gap-2 h-10 px-4 rounded-xl text-xs sm:text-sm font-semibold bg-primary text-white hover:bg-[#182a4a] transition-colors"
          >
            <Briefcase className="w-4 h-4" /> View Related Job
          </button>
        </div>
      </div>
    </div>
  );
}
