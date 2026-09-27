import { Network, Plug, Database } from "lucide-react";

export const PHASE_SKILLS = [
  {
    id: "system-design",
    label: "System Design",
    icon: Network,
    iconClass: "bg-primary/10 text-primary",
    priority: "high",
  },
  {
    id: "api-design",
    label: "API Design",
    icon: Plug,
    iconClass: "bg-primary/10 text-primary",
    priority: "medium",
  },
  {
    id: "caching-strategies",
    label: "Caching Strategies",
    icon: Database,
    iconClass: "bg-primary/10 text-primary",
    priority: "low",
  },
];
