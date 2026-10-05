import { AlertTriangle, AlertCircle, CheckCircle2 } from "lucide-react";

export const PRIORITY_STYLES = {
  high: {
    label: "HIGH PRIORITY GAP",
    className: "bg-error/10 text-error",
    icon: AlertTriangle,
  },
  medium: {
    label: "MEDIUM PRIORITY GAP",
    className: "bg-warning/10 text-warning",
    icon: AlertCircle,
  },
  low: {
    label: "LOW PRIORITY GAP",
    className: "bg-success/10 text-success",
    icon: CheckCircle2,
  },
};
