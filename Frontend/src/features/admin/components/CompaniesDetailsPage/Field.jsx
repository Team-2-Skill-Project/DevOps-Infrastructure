import { cn } from "@/lib/utils";

/**
 * Field
 * Label/value pair for the read-only info grids. `value` accepts a node so
 * callers can render links with icons; `full` spans both grid columns.
 */
export default function Field({ label, value, full, valueClassName }) {
  return (
    <div className={cn(full && "col-span-2 max-[560px]:col-span-1")}>
      <label className="mb-1 block text-[11.5px] font-bold uppercase tracking-wide text-muted">
        {label}
      </label>
      <div
        className={cn(
          "text-[14.5px] font-medium leading-relaxed text-ink",
          valueClassName,
        )}
      >
        {value}
      </div>
    </div>
  );
}