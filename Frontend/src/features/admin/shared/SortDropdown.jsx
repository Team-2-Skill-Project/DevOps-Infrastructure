import { ChevronDown } from "lucide-react";

/**
 * SortDropdown
 * Native select styled as a rounded pill with a custom chevron.
 * `options` is a list of `{ value, label }`.
 */
export default function SortDropdown({
  value,
  onChange,
  options = [],
  className,
}) {
  return (
    <div className={`relative ${className ?? ""}`}>
      <select
        value={value}
        onChange={(e) => onChange?.(e.target.value)}
        className="h-11 pl-4 pr-10 border border-border rounded-full bg-white text-[13px] font-medium outline-none focus:border-primary shadow-sm appearance-none cursor-pointer text-[#1B1C1A] w-full sm:w-auto"
      >
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
      <ChevronDown className="w-4 h-4 text-muted absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none" />
    </div>
  );
}
