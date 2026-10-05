import { cn } from "@/lib/utils";

export default function JobCard({ children, className }) {
  return (
    <div className={cn("mb-5 rounded-3xl border border-border bg-surface p-6 shadow-sm", className)}>
      {children}
    </div>
  );
}