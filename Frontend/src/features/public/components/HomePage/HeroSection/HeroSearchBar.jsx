import { useState, useRef } from "react";
import { motion } from "framer-motion";
import {
  Search,
  MapPin,
  X,
  Code2,
  LineChart,
  Layers,
  Database,
} from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import {
  Popover,
  PopoverAnchor,
  PopoverContent,
} from "@/components/ui/popover";
import {
  Command,
  CommandGroup,
  CommandItem,
  CommandList,
} from "@/components/ui/command";
import { useTranslation } from "react-i18next";

const SUGGESTED_ROLES = [
  {
    label: "Frontend Dev",
    count: "940+ open",
    icon: Code2,
    color: "text-accent",
  },
  {
    label: "Data Scientist",
    count: "620+ open",
    icon: LineChart,
    color: "text-success",
  },
  {
    label: "Product Manager",
    count: "410+ open",
    icon: Layers,
    color: "text-secondary",
  },
  {
    label: "Backend Engineer",
    count: "830+ open",
    icon: Database,
    color: "text-warning",
  },
];

export default function HeroSearchBar({
  query,
  onQueryChange,
  location,
  onLocationChange,
  onSubmit,
}) {
  const { t } = useTranslation("common");
  const [showDropdown, setShowDropdown] = useState(false);
  const inputRef = useRef(null);

  const filteredRoles = SUGGESTED_ROLES.filter((r) =>
    r.label.toLowerCase().includes(query.trim().toLowerCase()),
  );

  return (
    <motion.div
      initial={{ opacity: 0, y: 20 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.7, delay: 0.25, ease: [0.16, 1, 0.3, 1] }}
      className="mb-4 w-full max-w-3xl"
    >
      <Card className="rounded-2xl border-primary-foreground/20 bg-primary-foreground/[0.08] p-0 shadow-2xl backdrop-blur-xl transition-all duration-300 hover:border-primary-foreground/40 md:rounded-full">
        <CardContent className="flex flex-col items-stretch gap-1 p-2 sm:p-2.5 md:flex-row md:gap-0">
          {/* Job title / keyword */}
          <Popover open={showDropdown} onOpenChange={setShowDropdown}>
            <PopoverAnchor asChild>
              <div className="group relative flex flex-1 items-center px-4 py-2.5">
                <Search className="me-2.5 h-5 w-5 shrink-0 text-primary-foreground/60 transition-colors group-focus-within:text-accent" />
                <Input
                  ref={inputRef}
                  autoComplete="off"
                  placeholder={t("home.hero.jobSearchPlaceholder")}
                  value={query}
                  onChange={(e) => {
                    onQueryChange(e.target.value);
                    setShowDropdown(e.target.value.trim().length > 0);
                  }}
                  onFocus={() =>
                    query.trim().length > 0 && setShowDropdown(true)
                  }
                  className="h-auto border-0 bg-transparent p-0 text-[15px] font-medium text-primary-foreground shadow-none placeholder:text-primary-foreground/60 focus-visible:ring-0 focus-visible:ring-offset-0"
                />
                {query.length > 0 && (
                  <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    onClick={() => {
                      onQueryChange("");
                      setShowDropdown(false);
                      inputRef.current?.focus();
                    }}
                    className="h-auto w-auto shrink-0 p-1 text-primary-foreground/50 hover:bg-transparent hover:text-primary-foreground"
                    title={t("home.hero.clearSearch")}
                  >
                    <X className="h-[18px] w-[18px]" />
                  </Button>
                )}
              </div>
            </PopoverAnchor>

            <PopoverContent
              align="start"
              onOpenAutoFocus={(e) => e.preventDefault()}
              className="w-[--radix-popover-trigger-width] border-primary-foreground/20 bg-primary/95 p-2 text-left backdrop-blur-xl md:w-80"
            >
              <Command shouldFilter={false} className="bg-transparent">
                <CommandList>
                  <CommandGroup
                    heading={t("home.hero.suggestedRoles")}
                    className="[&_[cmdk-group-heading]]:px-1 [&_[cmdk-group-heading]]:text-[11px] [&_[cmdk-group-heading]]:font-semibold [&_[cmdk-group-heading]]:uppercase [&_[cmdk-group-heading]]:tracking-wider [&_[cmdk-group-heading]]:text-primary-foreground/50"
                  >
                    {filteredRoles.map(
                      ({ label, count, icon: Icon, color }) => (
                        <CommandItem
                          key={label}
                          value={label}
                          onSelect={() => {
                            onQueryChange(label);
                            setShowDropdown(false);
                          }}
                          className="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 text-[14px] text-primary-foreground data-[selected=true]:bg-primary-foreground/10"
                        >
                          <span className="flex items-center gap-2">
                            <Icon className={`h-4 w-4 ${color}`} />
                            {label}
                          </span>
                          <span className="text-[11px] text-primary-foreground/50">
                            {count}
                          </span>
                        </CommandItem>
                      ),
                    )}
                  </CommandGroup>
                </CommandList>
              </Command>
            </PopoverContent>
          </Popover>

          <Separator
            orientation="vertical"
            className="my-2 hidden w-px self-stretch bg-primary-foreground/20 md:block"
          />

          {/* Location */}
          <div className="group flex flex-1 items-center border-t border-primary-foreground/15 px-4 py-2.5 md:border-t-0">
            <MapPin className="me-2 h-[21px] w-[21px] shrink-0 text-primary-foreground/60 transition-colors group-focus-within:text-accent" />
            <Input
              value={location}
              onChange={(e) => onLocationChange(e.target.value)}
              placeholder={t("home.hero.locationPlaceholder")}
              className="h-auto border-0 bg-transparent p-0 text-[15px] font-medium text-primary-foreground shadow-none placeholder:text-primary-foreground/60 focus-visible:ring-0 focus-visible:ring-offset-0"
            />
          </div>

          {/* Submit */}
          <Button
            asChild
            variant="secondary"
            className="shrink-0 gap-2 rounded-xl border border-primary-foreground/20 px-6 py-3.5 font-headline-sm font-semibold shadow-md hover:shadow-xl md:rounded-full"
          >
            <motion.button
              type="button"
              onClick={onSubmit}
              whileTap={{ scale: 0.95 }}
              whileHover={{ scale: 1.02 }}
            >
              <Search className="h-5 w-5" />
              <span className="tracking-wide">{t("home.hero.searchJobs")}</span>
            </motion.button>
          </Button>
        </CardContent>
      </Card>
    </motion.div>
  );
}
