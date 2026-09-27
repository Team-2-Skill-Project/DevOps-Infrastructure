import { Briefcase, Activity, FileText, Map, ShieldCheck } from "lucide-react";

export const NOTIFICATION_TYPE_STYLES = {
  job: { icon: Briefcase, iconClass: "bg-primary/10 text-primary" },
  application: { icon: Activity, iconClass: "bg-success/10 text-success" },
  cv: { icon: FileText, iconClass: "bg-warning/10 text-warning" },
  roadmap: { icon: Map, iconClass: "bg-primary/10 text-primary" },
  platform: { icon: ShieldCheck, iconClass: "bg-error/10 text-error" },
};
