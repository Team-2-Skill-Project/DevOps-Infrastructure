import { CheckCircle2, Flag } from "lucide-react";

export const MILESTONE_STATUS_STYLES = {
  completed: {
    icon: CheckCircle2,
    circleClass: "bg-success/15 text-success",
    textClass: "text-ink",
  },
  "in-progress": {
    icon: Flag,
    circleClass: "bg-warning/15 text-warning",
    textClass: "text-ink",
  },
  "not-started": {
    icon: Flag,
    circleClass: "border-2 border-border bg-background text-muted",
    textClass: "text-muted",
  },
};
