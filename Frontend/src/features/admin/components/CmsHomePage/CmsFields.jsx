import { cn } from "@/lib/utils";
import { Input } from "@/components/ui/input";

export function I18nField({ label, value, onChange, multiline, viewLang, langs, isI18n }) {
  const obj = isI18n(value) ? value : { en: value ?? "", ar: "" };
  const visibleLangs = viewLang === "both" ? langs : langs.filter(([code]) => code === viewLang);

  return (
    <div className={cn(multiline || visibleLangs.length > 1 ? "col-span-2" : "")}>
      <label className="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-muted">{label}</label>
      <div className={cn("grid gap-2", visibleLangs.length > 1 && "grid-cols-2")}>
        {visibleLangs.map(([code]) => {
          const isAr = code === "ar";
          const shared = {
            value: obj[code] ?? "",
            onChange: (event) => onChange({ ...obj, [code]: event.target.value }),
            className: cn("w-full rounded-lg border border-border bg-surface px-3 py-2 text-sm text-ink", "focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary", isAr ? "pl-9 text-right" : "pr-9"),
            dir: isAr ? "rtl" : "ltr",
            lang: code,
          };
          return <div key={code} className="relative">{multiline ? <textarea rows={3} {...shared} /> : <input type="text" {...shared} />}<span className={cn("pointer-events-none absolute top-2 text-[9px] font-bold uppercase text-muted/50", isAr ? "left-2" : "right-2")}>{code}</span></div>;
        })}
      </div>
    </div>
  );
}

export function PlainField({ label, value, onChange, type = "text" }) {
  return <div><label className="mb-1 block text-[11px] font-semibold uppercase tracking-wide text-muted">{label}</label><Input type={type} value={value ?? ""} onChange={(event) => onChange(type === "number" ? (event.target.value === "" ? 0 : +event.target.value) : event.target.value)} className="rounded-lg border-border bg-surface text-sm" /></div>;
}

export function ToggleField({ label, checked, onChange }) {
  return <label className="col-span-2 flex cursor-pointer items-center gap-2 text-sm text-ink"><input type="checkbox" className="rounded border-border text-primary focus:ring-primary" checked={!!checked} onChange={(event) => onChange(event.target.checked)} />{label}</label>;
}