import { useState } from "react";
import { motion } from "framer-motion";
import { ChevronDown } from "lucide-react";
import { Button } from "@/components/ui/button";
import HeroBadge from "./HeroBadge";
import HeroHeadline from "./HeroHeadline";
import HeroSearchBar from "./HeroSearchBar";
import HeroPopularSearches from "./HeroPopularSearches";
import HeroMetrics from "./HeroMetrics";
import { useMouseSpotlight } from "../../../hooks/useMouseSpotlight";
import { useTranslation } from "react-i18next";

export default function HeroSection() {
  const [query, setQuery] = useState("");
  const [location, setLocation] = useState("");
  const { t } = useTranslation("common");
  const { ref: spotlightRef, handleMouseMove } = useMouseSpotlight();

  const handleSearch = () => {
    // TODO: wire this up to the real jobs search route/query params
    console.log("Searching:", { query, location });
  };

  const handlePopularSelect = (tag) => {
    if (tag === "Remote") {
      setLocation("Remote");
    } else {
      setQuery(tag);
    }
  };
  return (
    <section
      ref={spotlightRef}
      onMouseMove={handleMouseMove}
      className="relative w-full overflow-hidden border-b border-border/20 bg-primary pb-16 pt-12 text-primary-foreground lg:pb-24 lg:pt-16"
      id="home"
    >
      {/* Mouse-tracking spotlight — uses the theme's --color-accent CSS var */}
      <div
        className="pointer-events-none absolute inset-0 opacity-40 transition-opacity duration-300"
        style={{
          background:
            "radial-gradient(600px circle at var(--mouse-x, 50%) var(--mouse-y, 50%), color-mix(in srgb, var(--color-accent) 15%, transparent), transparent 70%)",
        }}
      />

      {/* Ambient glowing orbs */}
      <motion.div
        className="pointer-events-none absolute -inset-s-20 -top-32 h-[520px] w-[520px] rounded-full bg-accent/20 blur-[120px]"
        animate={{ opacity: [0.6, 1, 0.6], scale: [1, 1.08, 1] }}
        transition={{ duration: 8, repeat: Infinity, ease: "easeInOut" }}
      />
      <motion.div
        className="pointer-events-none absolute -inset-e-28 top-1/4 h-[500px] w-[500px] rounded-full bg-secondary/20 blur-[130px]"
        animate={{ x: [0, 24, 0], y: [0, -16, 0] }}
        transition={{ duration: 10, repeat: Infinity, ease: "easeInOut" }}
      />
      <div className="pointer-events-none absolute -bottom-24 inset-s-1/3 h-[300px] w-[640px] rounded-full bg-surface/10 blur-[100px]" />

      {/* Subtle grid overlay */}
      <div
        className="pointer-events-none absolute inset-0 bg-[size:3.5rem_3.5rem] opacity-[0.035]"
        style={{
          backgroundImage:
            "linear-gradient(to right, var(--color-primary-foreground) 1px, transparent 1px), linear-gradient(to bottom, var(--color-primary-foreground) 1px, transparent 1px)",
        }}
      />

      <div className="relative z-10 mx-auto flex max-w-[1280px] flex-col items-center px-6 text-center md:px-10 lg:px-16">
        <HeroBadge />
        <HeroHeadline />

        <HeroSearchBar
          query={query}
          onQueryChange={setQuery}
          location={location}
          onLocationChange={setLocation}
          onSubmit={handleSearch}
        />

        <HeroPopularSearches onSelect={handlePopularSelect} />
        <HeroMetrics />

        <Button
          asChild
          variant="ghost"
          className="h-auto flex-col gap-1 p-0 text-primary-foreground/60 hover:bg-transparent hover:text-primary-foreground"
        >
          <motion.a
            href="#jobs"
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            transition={{ duration: 0.6, delay: 0.7 }}
          >
            <span className="text-[11px] font-semibold uppercase tracking-widest">
              {t("home.hero.scrollToExplore")}
            </span>
            <motion.span
              animate={{ y: [0, 6, 0] }}
              transition={{
                duration: 1.4,
                repeat: Infinity,
                ease: "easeInOut",
              }}
            >
              <ChevronDown className="h-5 w-5" />
            </motion.span>
          </motion.a>
        </Button>
      </div>
    </section>
  );
}
