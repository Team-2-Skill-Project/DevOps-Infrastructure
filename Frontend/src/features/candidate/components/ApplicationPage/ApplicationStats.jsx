import { Award, Briefcase, MessageSquare, Send, XCircle } from "lucide-react";

const statItems = [
  {
    key: "total",
    label: "Total",
    icon: Briefcase,
    color: "text-primary",
    iconBg: "bg-primary/10",
  },
  {
    key: "underReview",
    label: "Under Review",
    icon: Send,
    color: "text-primary",
    iconBg: "bg-primary/10",
  },
  {
    key: "interview",
    label: "Interview",
    icon: MessageSquare,
    color: "text-warning",
    iconBg: "bg-warning/10",
  },
  {
    key: "offer",
    label: "Offer",
    icon: Award,
    color: "text-success",
    iconBg: "bg-success/10",
  },
  {
    key: "rejected",
    label: "Rejected",
    icon: XCircle,
    color: "text-[#B3271E]",
    iconBg: "bg-[#B3271E]/10",
  },
];

export default function ApplicationStats({ stats }) {
  return (
    <div className="grid grid-cols-2 gap-3 sm:grid-cols-5">
      {statItems.map(({ key, label, icon: Icon, color, iconBg }) => (
        <div
          key={key}
          className="bg-white border border-border rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow"
        >
          <div
            className={`w-8 h-8 rounded-xl ${iconBg} flex items-center justify-center mb-3 ${color}`}
          >
            <Icon className="w-4 h-4" />
          </div>
          <div
            className={`font-extrabold text-[22px] tabular-nums leading-none ${key !== "total" ? color : ""}`}
          >
            {stats[key]}
          </div>
          <div className="text-[11px] font-semibold text-muted mt-1.5">
            {label}
          </div>
        </div>
      ))}
    </div>
  );
}
