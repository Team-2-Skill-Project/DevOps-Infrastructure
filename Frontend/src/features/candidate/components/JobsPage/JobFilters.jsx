import { AnimatePresence, motion } from "framer-motion";
import { ListFilter, Search } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

export default function JobFilters({ open, onToggle }) {
  return (
    <div className="space-y-3 rounded-2xl border border-border bg-white p-4 shadow-sm">
      <div className="mb-0 flex flex-col gap-3 md:flex-row">
        <div className="relative flex flex-1 items-center">
          <Search className="absolute inset-s-3 z-10 h-5 w-5 text-muted" />
          <Input
            type="search"
            aria-label="Search jobs"
            placeholder="Search by job title, skill, or company..."
            className="h-11 rounded-xl border-border bg-background ps-10 pe-3 text-sm focus-visible:border-primary focus-visible:ring-primary/10"
          />
        </div>
        <Button
          type="button"
          onClick={onToggle}
          aria-expanded={open}
          aria-controls="filters-drawer"
          variant="outline"
          className="h-11 rounded-xl border-border bg-[rgb(234,232,228)] px-4 font-bold text-primary hover:bg-border"
        >
          <ListFilter
            className={`h-4.5 w-4.5 transition-transform duration-200 ${
              open ? "rotate-180" : ""
            }`}
          />
          Filters
          <span className="flex h-5 w-5 items-center justify-center rounded-full bg-primary text-[10px] text-white">
            2
          </span>
        </Button>
        <Button type="button" className="h-11 rounded-xl px-6 font-bold">
          Search
        </Button>
      </div>

      <AnimatePresence initial={false}>
        {open && (
          <motion.div
            id="filters-drawer"
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: "auto", opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.22, ease: "easeOut" }}
            className="overflow-hidden"
          >
            <div className="mt-3 flex flex-col gap-4 border-t border-border pt-3 md:flex-row">
              <FilterSelect
                label="Location"
                options={["All Locations", "Cairo, Egypt", "Remote"]}
              />
              <FilterSelect
                label="Work Type"
                options={["All Types", "Remote", "Hybrid", "On-site"]}
              />
              <FilterSelect
                label="Experience Level"
                options={["Junior", "Mid-Senior", "Lead"]}
              />
              <div className="flex flex-1 items-end gap-2">
                <Button type="button" size="sm" className="w-full font-bold">
                  Apply Filters
                </Button>
              </div>
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
}

function FilterSelect({ label, options }) {
  return (
    <div className="flex-1">
      <label className="mb-1 block text-xs font-bold text-secondary">
        {label}
      </label>
      <Select defaultValue={options[0]}>
        <SelectTrigger className="h-9 w-full rounded-lg border-border bg-background text-xs">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          {options.map((option) => (
            <SelectItem key={option} value={option}>
              {option}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </div>
  );
}
