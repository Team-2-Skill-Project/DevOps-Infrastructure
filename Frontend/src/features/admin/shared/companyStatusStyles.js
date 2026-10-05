import { CheckCircle2, Clock, Ban } from "lucide-react";

export const COMPANY_STATUS_STYLES = {
  verified: {
    label: "Verified",
    icon: CheckCircle2,
    badgeClass: "bg-success/10 text-success border-success/25",
  },
  pending: {
    label: "Pending",
    icon: Clock,
    badgeClass: "bg-warning/10 text-warning border-warning/30",
  },
  "not-verified": {
    label: "Not Verified",
    icon: Ban,
    badgeClass: "bg-muted/10 text-muted border-muted/20",
  },
};
