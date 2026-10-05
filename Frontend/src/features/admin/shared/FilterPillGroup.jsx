import FilterPill from "./FilterPill";

/**
 * FilterPillGroup
 * Renders a horizontal, scrollable row of FilterPills from a `{id,label}[]`.
 */
export default function FilterPillGroup({
  options = [],
  value,
  onChange,
  className,
}) {
  return (
    <div
      className={`flex items-center gap-2 overflow-x-auto pb-1 -mx-1 px-1 scrollbar-thin ${
        className ?? ""
      }`}
    >
      {options.map((pill) => (
        <FilterPill
          key={pill.id}
          label={pill.label}
          active={value === pill.id}
          onClick={() => onChange?.(pill.id)}
        />
      ))}
    </div>
  );
}