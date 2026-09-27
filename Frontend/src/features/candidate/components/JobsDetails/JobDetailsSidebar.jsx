export default function JobDetailsSidebar() {
  return (
    <>
      <div className="bg-surface border border-border rounded-2xl p-6 shadow-xs">
        <h4 className="font-dm-sans text-lg font-bold text-primary mb-4">
          Hiring Process Insights
        </h4>
        <div className="space-y-3 text-xs">
          <InsightRow
            label="Engineering Org Size"
            value="45 frontend & full-stack"
          />
          <InsightRow
            label="Interview Velocity"
            value="Initial screen in ~3 days"
          />
          <InsightRow label="Interview Rounds" value="3 Focused Rounds" />
        </div>
      </div>

      <div className="bg-surface border border-border rounded-2xl p-6 shadow-xs">
        <h4 className="font-dm-sans text-lg font-bold text-primary mb-3">
          Culture & Flexibility
        </h4>
        <ul className="text-xs text-muted space-y-2.5">
          <CultureItem>
            <strong>Hybrid Cadence:</strong> 2 days collaborative in Cairo Tech
            Center (Maadi), 3 days high-focus remote.
          </CultureItem>
          <CultureItem>
            <strong>Async-First Philosophy:</strong> Written RFCs and clear
            documentation take precedence over impromptu meetings.
          </CultureItem>
          <CultureItem>
            <strong>Annual Team Summits:</strong> All-expenses-paid regional
            offsites focused on hackathons and cross-team cohesion.
          </CultureItem>
        </ul>
      </div>
    </>
  );
}

function InsightRow({ label, value }) {
  return (
    <div className="flex items-center justify-between border-b border-border pb-2 last:border-b-0 last:pb-0">
      <span className="text-muted">{label}</span>
      <span className="font-bold text-primary">{value}</span>
    </div>
  );
}

function CultureItem({ children }) {
  return (
    <li className="flex items-start gap-2">
      <span className="text-primary font-bold">•</span>
      <span>{children}</span>
    </li>
  );
}
