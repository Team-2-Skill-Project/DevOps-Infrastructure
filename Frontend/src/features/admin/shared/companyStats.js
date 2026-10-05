import { Building2, CheckCircle2, Clock, Ban } from "lucide-react";

export const COMPANY_STATS = [
  {
    key: "total",
    label: "Total Companies",
    value: 148,
    icon: Building2,
    iconClass: "bg-primary/10 text-primary",
  },
  {
    key: "verified",
    label: "Verified",
    value: 112,
    icon: CheckCircle2,
    iconClass: "bg-success/10 text-success",
  },
  {
    key: "pending",
    label: "Pending Review",
    value: 23,
    icon: Clock,
    iconClass: "bg-warning/10 text-warning",
  },
  {
    key: "not-verified",
    label: "Not Verified",
    value: 13,
    icon: Ban,
    iconClass: "bg-muted/10 text-muted",
  },
];
