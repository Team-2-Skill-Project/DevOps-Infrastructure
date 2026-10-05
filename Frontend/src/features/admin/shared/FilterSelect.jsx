import { cn } from "@/lib/utils";

const SIZES = {
  md: "h-10.5 px-3.5 pr-8 text-xs sm:text-sm font-medium rounded-xl min-w-35",
  sm: "h-8 px-2 pr-6 text-xs rounded-lg",
};

/**
 * FilterSelect
 * Standardized native <select> used in table toolbars/results bars.
 * `options` is a list of `{ value, label }` (or plain strings).
 * Works controlled (value + onChange) or uncontrolled.
 */
export default function FilterSelect({
  options = [],
  value,
  onChange,
  size = "md",
  ariaLabel,
  className,
}) {
  return (
    <select
      value={value}
      onChange={(e) => onChange?.(e.target.value)}
      aria-label={ariaLabel}
      className={cn(
        "border border-border bg-white outline-none focus:border-primary cursor-pointer",
        SIZES[size] ?? SIZES.md,
        className,
      )}
    >
      {options.map((option) => {
        const { value: optionValue, label } =
          typeof option === "string" ? { value: option, label: option } : option;
        return (
          <option key={optionValue} value={optionValue}>
            {label}
          </option>
        );
      })}
    </select>
  );
}
