import { useCallback, useEffect, useState } from "react";
import { useTranslation } from "react-i18next";
import { motion } from "framer-motion";
import {
  Loader2,
  Upload,
  RotateCcw,
  Save,
  Search,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { cn } from "@/lib/utils";
import CmsToastStack from "@/features/admin/components/CmsHomePage/ToastStack";
import CmsSectionCard from "@/features/admin/components/CmsHomePage/SectionCard";

/* ------------------------------------------------------------------ */
/* Constants & defaults (mirrors admin-cms.html)                      */
/* ------------------------------------------------------------------ */

const DRAFT_KEY = "cms:home:draft";
const LANGS = [
  ["en", "English"],
  ["ar", "العربية"],
];

const DEFAULTS = {
  version: 1,
  meta: { title: { en: "SkillMatch - AI-Powered Career Platform", ar: "" } },
  media: { philosophyImage: "", whyImage: "" },
  sections: {
    navbar: { visible: true, order: 0 },
    hero: { visible: true, order: 10 },
    stats: { visible: true, order: 11 },
    jobs: { visible: true, order: 20 },
    philosophy: { visible: true, order: 30 },
    features: { visible: true, order: 40 },
    mentor: { visible: true, order: 41 },
    why: { visible: true, order: 50 },
    howItWorks: { visible: false, order: 60 },
    testimonials: { visible: false, order: 70 },
    cta: { visible: true, order: 80 },
    footer: { visible: true, order: 90 },
  },
  navbar: {
    brand: { en: "SkillMatch", ar: "" },
    logoUrl: "",
    signIn: { label: { en: "Sign In", ar: "" }, href: "/sign-in" },
    cta: { label: { en: "Post a Job", ar: "" }, href: "/post-a-job" },
    links: [
      { label: { en: "Explore Jobs", ar: "" }, href: "/explore-jobs", visible: true },
      { label: { en: "Employers", ar: "" }, href: "/employers", visible: true },
      { label: { en: "AI Mentor", ar: "" }, href: "/ai-mentor", visible: true },
      { label: { en: "Pricing", ar: "" }, href: "/pricing", visible: true },
      { label: { en: "About", ar: "" }, href: "/about", visible: true },
    ],
  },
  hero: {
    badge: { en: "AI-POWERED CAREER INTELLIGENCE", ar: "" },
    headlinePre: { en: "Your skills deserve the right", ar: "" },
    headlineHighlight: { en: "opportunity", ar: "" },
    subtitle: {
      en: "Upload your CV once. Our AI maps every skill, matches you to curated jobs, and builds a personalized roadmap to close every gap — with a mentor that never sleeps.",
      ar: "",
    },
    backgroundImage: "",
    searchPlaceholder: { en: "Job title, skills, or company...", ar: "" },
    locationDefault: { en: "Remote", ar: "" },
    searchButton: { en: "Search Jobs", ar: "" },
    popularLabel: { en: "Popular:", ar: "" },
    scrollCue: { en: "Scroll to Explore", ar: "" },
    popular: [
      { label: { en: "Frontend Dev", ar: "" }, visible: true },
      { label: { en: "Data Science", ar: "" }, visible: true },
      { label: { en: "Product Manager", ar: "" }, visible: true },
      { label: { en: "Remote", ar: "" }, visible: true },
    ],
  },
  stats: {
    items: [
      { value: 180, suffix: "K+", label: { en: "Active Job Seekers", ar: "" }, decimals: 0, visible: true },
      { value: 4200, suffix: "+", label: { en: "Partner Companies", ar: "" }, decimals: 0, visible: true },
      { value: 93, suffix: "%", label: { en: "Match Accuracy", ar: "" }, decimals: 0, visible: true },
      { value: 2.4, suffix: "×", label: { en: "Faster Hiring", ar: "" }, decimals: 1, visible: true },
    ],
  },
  jobs: {
    badge: { en: "Recent Opportunities", ar: "" },
    title: { en: "Latest Jobs & Open Roles", ar: "" },
    subtitle: {
      en: "Explore recently published roles with verified salaries and direct AI skill match scoring.",
      ar: "",
    },
    updatedText: { en: "Updated 12 mins ago", ar: "" },
    more: { label: { en: "Explore All 12,000+ Open Positions", ar: "" }, href: "/explore-jobs" },
    filters: [
      { label: { en: "All Roles", ar: "" }, visible: true },
      { label: { en: "Frontend", ar: "" }, visible: true },
      { label: { en: "Remote", ar: "" }, visible: true },
    ],
    items: [
      {
        company: { en: "Vercel Partner", ar: "" },
        location: { en: "Remote", ar: "" },
        match: "98% Match",
        title: { en: "Senior Frontend Engineer (React / Next.js)", ar: "" },
        salary: "$145,000 - $175,000",
        unit: "/ yr",
        skills: "React, TypeScript, Tailwind CSS, Next.js 14",
        posted: { en: "Posted 2h ago", ar: "" },
        applyLabel: { en: "Quick Apply", ar: "" },
        link: "#",
        visible: true,
      },
    ],
  },
  philosophy: {
    livePill: { en: "Live Evaluation Engine", ar: "" },
    badge: { en: "The SkillMatch Philosophy", ar: "" },
    title: { en: "Hiring shouldn't be a game of buzzwords.", ar: "" },
    body: {
      en: "SkillMatch eliminates inflated resumes and keyword-stuffed applicant pipelines.",
      ar: "",
    },
    button: { label: { en: "Discover the Matching Engine", ar: "" }, href: "#" },
    link: { label: { en: "Read our methodology", ar: "" }, href: "#" },
    candidate: {
      visible: true,
      name: { en: "Alex Lin", ar: "" },
      role: { en: "Staff Systems Architect", ar: "" },
      fit: "98.8% Fit",
      tag1: { en: "Distributed Systems", ar: "" },
      tag2: { en: "Go & Rust", ar: "" },
      badge: { en: "Top 1%", ar: "" },
    },
    tiles: [
      {
        icon: "analytics",
        title: { en: "Verifiable Skill Telemetry", ar: "" },
        description: { en: "Deep-dive objective competency scores.", ar: "" },
        visible: true,
      },
    ],
    points: [
      { text: { en: "Neural match accuracy across verified skills", ar: "" }, visible: true },
    ],
  },
  features: {
    badge: { en: "Specialized Sectors", ar: "" },
    title: { en: "One Platform for Every Career Path", ar: "" },
    subtitle: {
      en: "Browse curated categories and use AI-powered guidance.",
      ar: "",
    },
    items: [
      {
        icon: "terminal",
        title: { en: "Development", ar: "" },
        description: { en: "3,420+ Open Jobs", ar: "" },
        tag: { en: "Frontend, Back, AI", ar: "" },
        link: "/explore-jobs",
        visible: true,
      },
    ],
  },
  mentor: {
    badge: { en: "Next-Gen Intelligence", ar: "" },
    title: { en: "AI Career Mentor", ar: "" },
    body: {
      en: "Get personalized career guidance, identify skill gaps, and prepare for your next opportunity.",
      ar: "",
    },
    button: { label: { en: "Talk to AI Mentor", ar: "" }, href: "/ai-mentor" },
    note: { en: "Available 24/7 • Instant Feedback • Free Plan Included", ar: "" },
    chips: [
      { text: { en: "Mock Interviews", ar: "" }, visible: true },
      { text: { en: "Resume Gap Analysis", ar: "" }, visible: true },
    ],
  },
  why: {
    badge: { en: "Why SkillMatch", ar: "" },
    title: { en: "Better Matches. Faster Hiring.", ar: "" },
    body: {
      en: "We simplify hiring for job seekers and employers with verified listings and AI matching.",
      ar: "",
    },
    button: { label: { en: "Why SkillMatch", ar: "" }, href: "#" },
    videoLabel: { en: "Watch How We Work (2 min video)", ar: "" },
    videoHref: "",
    security: {
      title: { en: "Enterprise-Grade Security", ar: "" },
      subtitle: { en: "SOC2 Type II Certified & End-to-End Encryption", ar: "" },
    },
    benefits: [
      {
        icon: "verified",
        title: { en: "Verified Jobs", ar: "" },
        description: { en: "Every role is reviewed for quality and trust.", ar: "" },
        visible: true,
      },
    ],
  },
  howItWorks: {
    badge: { en: "How it works", ar: "" },
    title: { en: "From CV to offer in three steps", ar: "" },
    subtitle: { en: "", ar: "" },
    steps: [
      {
        number: 1,
        icon: "upload_file",
        title: { en: "Upload your CV", ar: "" },
        description: { en: "Our AI reads it once and maps every skill.", ar: "" },
        visible: true,
      },
      {
        number: 2,
        icon: "bolt",
        title: { en: "Get matched", ar: "" },
        description: { en: "Curated roles ranked by verified skill fit.", ar: "" },
        visible: true,
      },
      {
        number: 3,
        icon: "route",
        title: { en: "Close the gaps", ar: "" },
        description: { en: "A personalized roadmap and an AI mentor.", ar: "" },
        visible: true,
      },
    ],
  },
  testimonials: {
    badge: { en: "Testimonials", ar: "" },
    title: { en: "Loved by candidates and teams", ar: "" },
    subtitle: { en: "", ar: "" },
    items: [],
  },
  blocks: { items: [] },
  cta: {
    badge: { en: "Start Your Journey", ar: "" },
    title: { en: "Ready to Find Your Next Opportunity?", ar: "" },
    body: {
      en: "Upload your CV to unlock instant AI skill scoring and curated match telemetry.",
      ar: "",
    },
    primary: { label: { en: "Upload CV", ar: "" }, href: "/upload-cv" },
    secondary: { label: { en: "Browse Jobs", ar: "" }, href: "/explore-jobs" },
    checks: [
      { text: { en: "Instant AI skill scoring", ar: "" }, visible: true },
      { text: { en: "No credit card required", ar: "" }, visible: true },
    ],
  },
  footer: {
    description: {
      en: "High-signal talent matching powered by deep graph neural networks.",
      ar: "",
    },
    newsletterTitle: { en: "Subscribe to Career Intelligence", ar: "" },
    newsletterPlaceholder: { en: "Enter your work email", ar: "" },
    newsletterButton: { en: "Join", ar: "" },
    copyright: { en: "© 2026 SkillMatch Inc. All rights reserved.", ar: "" },
    columns: [
      {
        title: { en: "Platform", ar: "" },
        links: [
          { label: { en: "Job Search Engine", ar: "" }, href: "#", visible: true },
          { label: { en: "AI Career Copilot", ar: "" }, href: "#", visible: true },
        ],
      },
    ],
    legal: [
      { label: { en: "Security", ar: "" }, href: "#", visible: true },
      { label: { en: "Terms of Service", ar: "" }, href: "#", visible: true },
    ],
  },
};

const SECTION_META = [
  {
    group: "Page settings",
    keys: ["sections"],
  },
  {
    group: "Top of the page",
    keys: ["navbar", "hero", "stats"],
  },
  {
    group: "Main content",
    keys: [
      "jobs",
      "philosophy",
      "features",
      "mentor",
      "why",
      "howItWorks",
      "testimonials",
      "blocks",
    ],
  },
  {
    group: "Bottom of the page",
    keys: ["cta", "footer"],
  },
];

const SECTION_INFO = {
  sections: {
    label: "Sections & Global",
    description: "Browser tab title, shared images, and section visibility/order.",
    sectionKey: null,
  },
  navbar: {
    label: "Navbar",
    description: "Logo, brand name, menu links and top-right buttons.",
    sectionKey: "navbar",
  },
  hero: {
    label: "Hero",
    description: "Top banner: badge, headline, search box and popular chips.",
    sectionKey: "hero",
  },
  stats: {
    label: "Stats",
    description: "Animated counters under the hero search box.",
    sectionKey: "stats",
  },
  jobs: {
    label: "Latest jobs",
    description: "Filter pills and job cards.",
    sectionKey: "jobs",
  },
  philosophy: {
    label: "Philosophy",
    description: "Two-column story block with tiles and candidate card.",
    sectionKey: "philosophy",
  },
  features: {
    label: "Features grid",
    description: "Category cards for career paths.",
    sectionKey: "features",
  },
  mentor: {
    label: "AI Mentor card",
    description: "Dark AI Career Mentor banner.",
    sectionKey: "mentor",
  },
  why: {
    label: "Why SkillMatch",
    description: "Benefits and video card.",
    sectionKey: "why",
  },
  howItWorks: {
    label: "How it works",
    description: "Optional step-by-step section (hidden by default).",
    sectionKey: "howItWorks",
  },
  testimonials: {
    label: "Testimonials",
    description: "Optional customer quotes (hidden by default).",
    sectionKey: "testimonials",
  },
  blocks: {
    label: "Custom blocks",
    description: "Add free-form sections: text, image and a button.",
    sectionKey: null,
  },
  cta: {
    label: "CTA banner",
    description: "Final call-to-action above the footer.",
    sectionKey: "cta",
  },
  footer: {
    label: "Footer",
    description: "Description, newsletter, link columns and legal links.",
    sectionKey: "footer",
  },
};

const ease = [0.16, 1, 0.3, 1];

function clone(o) {
  return JSON.parse(JSON.stringify(o));
}

function tx(v) {
  if (v && typeof v === "object" && ("en" in v || "ar" in v)) {
    return v.en || v.ar || "";
  }
  return v ?? "";
}

function isI18n(v) {
  return v && typeof v === "object" && ("en" in v || "ar" in v);
}


export default function CmsHomePage() {
  const { t } = useTranslation("dashboard");
  const [state, setState] = useState(() => clone(DEFAULTS));
  const [dirty, setDirty] = useState(false);
  const [saving, setSaving] = useState(false);
  const [query, setQuery] = useState("");
  const [viewLang, setViewLang] = useState("both");
  const [activeTab, setActiveTab] = useState("sections");
  const [openSecs, setOpenSecs] = useState(() => new Set(["sections"]));
  const [toasts, setToasts] = useState([]);

  const toast = useCallback((msg, kind = "ok") => {
    const id = Date.now() + Math.random();
    setToasts((t) => [...t, { id, msg, kind }]);
    setTimeout(() => setToasts((t) => t.filter((x) => x.id !== id)), 3200);
  }, []);

  // Boot: restore draft if present
  useEffect(() => {
    try {
      const raw = localStorage.getItem(DRAFT_KEY);
      if (raw) {
        const draft = JSON.parse(raw);
        setState({ ...clone(DEFAULTS), ...draft, sections: { ...DEFAULTS.sections, ...draft.sections } });
        setDirty(true);
        toast("Restored your unsaved draft", "warn");
      }
    } catch {
      /* ignore */
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  useEffect(() => {
    const onBeforeUnload = (e) => {
      if (dirty) {
        e.preventDefault();
        e.returnValue = "";
      }
    };
    window.addEventListener("beforeunload", onBeforeUnload);
    return () => window.removeEventListener("beforeunload", onBeforeUnload);
  }, [dirty]);

  useEffect(() => {
    const onKey = (e) => {
      if ((e.ctrlKey || e.metaKey) && e.key === "s") {
        e.preventDefault();
        doSave();
      }
    };
    window.addEventListener("keydown", onKey);
    return () => window.removeEventListener("keydown", onKey);
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [state, dirty]);

  const markDirty = () => setDirty(true);

  const patchRoot = (patch) => {
    setState((s) => ({ ...s, ...patch }));
    markDirty();
  };

  const patchSection = (key, patch) => {
    setState((s) => ({
      ...s,
      [key]: { ...s[key], ...patch },
    }));
    markDirty();
  };

  const toggleSec = (id) => {
    setOpenSecs((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const expandAll = (open) => {
    if (open) setOpenSecs(new Set(Object.keys(SECTION_INFO)));
    else setOpenSecs(new Set());
  };

  const doSave = async () => {
    setSaving(true);
    // Mock API publish
    await new Promise((r) => setTimeout(r, 700));
    try {
      localStorage.setItem(DRAFT_KEY, JSON.stringify({ ...state, updatedAt: new Date().toISOString() }));
      // In real app: POST /api/cms/home
      setDirty(false);
      toast("Changes saved and published");
    } catch (err) {
      toast("Save failed: " + err.message, "err");
    } finally {
      setSaving(false);
    }
  };

  const doDraft = () => {
    localStorage.setItem(DRAFT_KEY, JSON.stringify(state));
    toast("Draft saved (preview with cms_preview=draft)", "warn");
  };

  const doReset = () => {
    if (!window.confirm("Reset everything to the default content?")) return;
    setState(clone(DEFAULTS));
    markDirty();
    toast("Defaults restored — press Save to publish", "warn");
  };

  const matchesQuery = (key) => {
    if (!query) return true;
    const q = query.toLowerCase();
    const info = SECTION_INFO[key];
    const blob =
      (info?.label || "") +
      (info?.description || "") +
      JSON.stringify(key === "sections" ? state : state[key] || {});
    return blob.toLowerCase().includes(q);
  };

  const sectionTabs = SECTION_META.flatMap(({ group, keys }) =>
    keys.filter(matchesQuery).map((id) => ({ id, group })),
  );

  const selectedTab = sectionTabs.some(({ id }) => id === activeTab)
    ? activeTab
    : sectionTabs[0]?.id || activeTab;

  const ActionRow = (
    <div className="flex flex-wrap items-center gap-2">
      <span
        className={cn(
          "text-xs font-medium text-warning",
          !dirty && "invisible",
        )}
      >
        ● Unsaved changes
      </span>
      <Button
        type="button"
        variant="outline"
        size="sm"
        onClick={doReset}
        className="ml-auto gap-1 border-border text-error hover:bg-background"
      >
        <RotateCcw className="h-4 w-4" />
        Reset defaults
      </Button>
      <Button
        type="button"
        variant="outline"
        size="sm"
        onClick={doDraft}
        className="gap-1 border-border"
      >
        <Save className="h-4 w-4" />
        Save draft
      </Button>
      <Button
        type="button"
        size="sm"
        onClick={doSave}
        disabled={saving}
        className="gap-1 bg-primary text-primary-foreground hover:bg-primary/90"
      >
        {saving ? (
          <Loader2 className="h-4 w-4 animate-spin" />
        ) : (
          <Upload className="h-4 w-4" />
        )}
        Save changes
      </Button>
    </div>
  );

  return (
    <div className="min-h-screen bg-background text-ink">
      <main className="mx-auto max-w-4xl px-5 pb-16 pt-8">
        <motion.div
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4, ease }}
        >
          <div className="mb-2 flex items-start gap-4">
            <h1 className="text-2xl font-bold text-primary">
              {t("pages.admin.cms")}
            </h1>
          </div>

          <div className="mt-4 flex flex-wrap items-center gap-2">
            <div className="relative max-w-sm flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
              <Input
                value={query}
                onChange={(e) => setQuery(e.target.value)}
                placeholder="Search sections and content…"
                className="pl-9"
              />
            </div>
            <select
              value={viewLang}
              onChange={(e) => setViewLang(e.target.value)}
              className="rounded-lg border border-border bg-surface px-3 py-2 text-sm"
              title="Which languages to show"
            >
              <option value="both">EN + عربي</option>
              {LANGS.map(([c, n]) => (
                <option key={c} value={c}>
                  {n} only
                </option>
              ))}
            </select>
            <Button
              type="button"
              variant="outline"
              size="sm"
              className="ml-auto border-border"
              onClick={() => expandAll(true)}
            >
              Expand all
            </Button>
            <Button
              type="button"
              variant="outline"
              size="sm"
              className="border-border"
              onClick={() => expandAll(false)}
            >
              Collapse all
            </Button>
          </div>
        </motion.div>

        <Tabs
          value={selectedTab}
          onValueChange={setActiveTab}
          className="mt-8 gap-5"
        >
          <div className="sticky top-0 h-auto z-10 rounded-2xl border border-border bg-surface/90 p-2 shadow-sm backdrop-blur-sm">
            <TabsList className="flex w-full gap-2 h-16! overflow-x-auto justify-start bg-transparent px-1 scrollbar-thin ">
              {" "}
              {sectionTabs.map(({ id, group }) => {
                const info = SECTION_INFO[id];
                const sectionKey = info?.sectionKey;
                const visible = sectionKey
                  ? state.sections?.[sectionKey]?.visible !== false
                  : true;
                const isActive = selectedTab === id;

                return (
                  <TabsTrigger
                    key={id}
                    value={id}
                    title={info?.label || id}
                    className={cn(
                      "group relative min-w-40 h-auto flex-col items-start gap-1 rounded-xl px-2 py-1 text-start text-muted",
                      "transition-all duration-200 ease-out",
                      "hover:bg-muted/50 hover:text-foreground",
                      "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/50 focus-visible:ring-offset-1",
                      "after:hidden",
                      "data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-sm data-[state=active]:scale-[1.02]",
                    )}
                  >
                    <span className="flex w-full items-center justify-between gap-2">
                      <span className="truncate text-sm font-semibold">
                        {info?.label || id}
                      </span>
                      <span
                        className={cn(
                          "h-1.5 w-1.5 shrink-0 rounded-full transition-colors",
                          visible ? "bg-success" : "bg-muted/50",
                          !visible && isActive && "bg-primary-foreground/50",
                        )}
                      />
                    </span>
                    <span
                      className={cn(
                        "max-w-[150px] truncate text-[10px] font-medium opacity-70",
                        isActive && "opacity-90",
                      )}
                    >
                      {group}
                    </span>
                  </TabsTrigger>
                );
              })}
            </TabsList>
          </div>

          {sectionTabs.map(({ id }) => (
            <TabsContent
              key={id}
              value={id}
              className="mt-0 data-[state=active]:animate-in data-[state=active]:fade-in-0 data-[state=active]:slide-in-from-bottom-1 data-[state=active]:duration-200"
            >
              <CmsSectionCard
                id={id}
                open={openSecs.has(id) || !!query}
                onToggle={() => toggleSec(id)}
                data={state[id]}
                rootState={state}
                viewLang={viewLang}
                langs={LANGS}
                isI18n={isI18n}
                tx={tx}
                clone={clone}
                sectionInfo={SECTION_INFO}
                ease={ease}
                onPatchSection={(patch) => patchSection(id, patch)}
                onPatchRoot={patchRoot}
              />
            </TabsContent>
          ))}
        </Tabs>

        <div className="mt-8 border-t border-border pt-5">{ActionRow}</div>
      </main>

      <CmsToastStack
        toasts={toasts}
        onDismiss={(id) => setToasts((t) => t.filter((x) => x.id !== id))}
        ease={ease}
      />
    </div>
  );
}
