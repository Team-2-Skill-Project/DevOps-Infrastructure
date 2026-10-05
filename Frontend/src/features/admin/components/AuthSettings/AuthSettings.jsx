import { useCallback, useEffect, useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import {
  ChevronDown,
  ImagePlus,
  LayoutGrid,
  Loader2,
  RotateCcw,
  Save,
  Search,
  Upload,
} from "lucide-react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { cn } from "@/lib/utils";
import {
  I18nField,
  PlainField,
} from "@/features/admin/components/CmsHomePage/CmsFields";
import CmsToastStack from "@/features/admin/components/CmsHomePage/ToastStack";

const DRAFT_KEY = "cms:auth:draft";
const LANGS = [
  ["en", "English"],
  ["ar", "العربية"],
];
const MAX_IMAGE_SIZE = 2 * 1024 * 1024;
const EASE = [0.22, 1, 0.36, 1];

const translated = (en, ar = "") => ({ en, ar });

const DEFAULTS = {
  side: {
    badge: translated("Better Opportunities", "فرص أفضل"),
    title: translated(
      "Great companies hire great people",
      "الشركات العظيمة توظف الأشخاص المميزين",
    ),
    description: translated(
      "Build your career with the right opportunities and take the next step toward your future.",
      "ابنِ مسيرتك المهنية مع الفرص المناسبة وخذ خطوتك التالية نحو مستقبلك.",
    ),
  },
  login: {
    image:
      "https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=1200&q=80",
    welcome: translated("Welcome Back", "مرحبًا بعودتك"),
    subtitle: translated(
      "Log in to your account to continue",
      "سجّل الدخول إلى حسابك للمتابعة",
    ),
    remember: translated("Remember me", "تذكرني"),
    forgot: translated("Forgot password?", "هل نسيت كلمة المرور؟"),
    submit: translated("Log In", "تسجيل الدخول"),
    or: translated("or continue with", "أو تابع باستخدام"),
    google: translated("Continue with Google", "المتابعة باستخدام Google"),
    terms: translated(
      "By signing in, you agree to MatchIn's",
      "بتسجيل الدخول، أنت توافق على",
    ),
    termsLink: translated("Terms", "الشروط"),
    privacyLink: translated("Privacy Policy", "سياسة الخصوصية"),
    noAccount: translated("Don't have an account?", "ليس لديك حساب؟"),
    create: translated("Create an account →", "إنشاء حساب ←"),
  },
  register: {
    image:
      "https://images.unsplash.com/photo-1633114128814-11fac33f707b?fm=jpg&q=80&w=1400&auto=format&fit=crop",
    step: translated("STEP {{current}} OF 4", "الخطوة {{current}} من 4"),
    createTitle: translated("Create your account", "أنشئ حسابك"),
    createDescription: translated(
      "Start with the basics — you can round out your profile next.",
      "ابدأ بالأساسيات — يمكنك إكمال ملفك الشخصي لاحقًا.",
    ),
    agree: translated("I agree to MatchIn", "أوافق على"),
    terms: translated("Terms", "الشروط"),
    privacy: translated("Privacy Policy", "سياسة الخصوصية"),
    submitting: translated("Creating account…", "جارٍ إنشاء الحساب…"),
    submit: translated("Create account", "إنشاء الحساب"),
    haveAccount: translated(
      "Already have an account?",
      "لديك حساب بالفعل؟",
    ),
    signIn: translated("Sign in", "تسجيل الدخول"),
    verifyTitle: translated("Verify your email", "تأكيد بريدك الإلكتروني"),
    verifyDescription: translated(
      "We sent a 6-digit code to {{email}}. Enter it below to continue.",
      "أرسلنا رمزًا من 6 أرقام إلى {{email}}. أدخله أدناه للمتابعة.",
    ),
    yourEmail: translated("your email", "بريدك الإلكتروني"),
    resendIn: translated("Resend code in", "إعادة إرسال الرمز خلال"),
    resend: translated("Resend code", "إعادة إرسال الرمز"),
    back: translated("Back", "رجوع"),
    verify: translated("Verify & continue", "تأكيد ومتابعة"),
    uploadTitle: translated("Upload your CV", "ارفع سيرتك الذاتية"),
    uploadDescription: translated(
      "We'll read it once, privately, to pull out your skills and pre-fill your profile.",
      "سنقرأها مرة واحدة وبخصوصية لاستخراج مهاراتك وتعبئة ملفك الشخصي.",
    ),
    uploadLabel: translated("UPLOAD CV", "رفع السيرة الذاتية"),
    uploadHelp: translated(
      "PDF or DOCX, up to 10MB. This lets us auto-fill your profile and match you to better roles.",
      "PDF أو DOCX، بحد أقصى 10MB. يساعدنا ذلك على تعبئة ملفك ومطابقتك مع فرص أفضل.",
    ),
    drop: translated("Drop your CV here, or", "أفلت سيرتك هنا، أو"),
    browse: translated("browse", "تصفح"),
    fileLimit: translated("PDF or DOCX, up to 10MB", "PDF أو DOCX، بحد أقصى 10MB"),
    reading: translated("Reading…", "جارٍ القراءة…"),
    ready: translated("Ready", "جاهز"),
    remove: translated("Remove", "إزالة"),
    browseJobs: translated("Browse Jobs", "تصفح الوظائف"),
    completeProfile: translated("Complete Profile", "إكمال الملف الشخصي"),
    profileReading: translated("Reading your CV", "جارٍ قراءة سيرتك"),
    profileTitle: translated("Complete your profile", "أكمل ملفك الشخصي"),
    profileReadingDescription: translated(
      "Our model is scanning for roles, tools, and skills — this takes a few seconds.",
      "نبحث عن الأدوار والأدوات والمهارات — سيستغرق ذلك بضع ثوانٍ.",
    ),
    profileDescription: translated(
      "Here's what we found — review your skills and profile details below before finishing up.",
      "إليك ما وجدناه — راجع مهاراتك وتفاصيل ملفك قبل الإنهاء.",
    ),
    analyzing: translated("Analyzing {{file}}", "تحليل {{file}}"),
    yourProfile: translated("your profile", "ملفك الشخصي"),
    cvAnalyzed: translated("CV analyzed", "تم تحليل السيرة الذاتية"),
    extracting: translated("Extracting your skills and experience…", "جارٍ استخراج مهاراتك وخبراتك…"),
    extracted: translated("Your profile is ready to review.", "ملفك الشخصي جاهز للمراجعة."),
    skillsSummary: translated(
      "{{found}} skills found · {{matched}} matched",
      "تم العثور على {{found}} مهارة · تطابقت {{matched}}",
    ),
    matchedSkills: translated("MATCHED SKILLS", "المهارات المتطابقة"),
    growth: translated("GROWTH OPPORTUNITIES", "فرص التطور"),
    profile: translated("PROFILE DETAILS", "تفاصيل الملف الشخصي"),
    jobTitle: translated("Target job title", "المسمى الوظيفي المستهدف"),
    jobTitlePlaceholder: translated(
      "e.g. Frontend Developer",
      "مثال: مطور واجهات أمامية",
    ),
    location: translated("Location", "الموقع"),
    locationPlaceholder: translated("City, Country", "المدينة، الدولة"),
    experience: translated("Experience", "الخبرة"),
    select: translated("Select experience", "اختر مستوى الخبرة"),
    bio: translated("Professional summary", "نبذة مهنية"),
    optional: translated("optional", "اختياري"),
    bioPlaceholder: translated(
      "Tell employers about your experience and goals…",
      "أخبر أصحاب العمل عن خبراتك وأهدافك…",
    ),
    dashboard: translated("Finish profile", "إنهاء الملف الشخصي"),
  },
};

const FIELD_SECTIONS = {
  shared: [
    { key: "badge", label: "Side panel badge" },
    { key: "title", label: "Side panel title" },
    { key: "description", label: "Side panel description", multiline: true },
  ],
  login: [
    { key: "welcome", label: "Welcome heading" },
    { key: "subtitle", label: "Welcome description" },
    { key: "remember", label: "Remember-me label" },
    { key: "forgot", label: "Forgot-password link" },
    { key: "submit", label: "Sign-in button" },
    { key: "or", label: "Social sign-in separator" },
    { key: "google", label: "Google button" },
    { key: "terms", label: "Terms notice" },
    { key: "termsLink", label: "Terms link" },
    { key: "privacyLink", label: "Privacy link" },
    { key: "noAccount", label: "No-account prompt" },
    { key: "create", label: "Create-account link" },
  ],
  register: [
    { key: "step", label: "Step indicator" },
    { key: "createTitle", label: "Create-account heading" },
    { key: "createDescription", label: "Create-account description", multiline: true },
    { key: "agree", label: "Terms agreement text" },
    { key: "terms", label: "Terms link" },
    { key: "privacy", label: "Privacy link" },
    { key: "submit", label: "Create-account button" },
    { key: "submitting", label: "Submitting button" },
    { key: "haveAccount", label: "Existing-account prompt" },
    { key: "signIn", label: "Sign-in link" },
    { key: "verifyTitle", label: "Email-verification heading" },
    { key: "verifyDescription", label: "Email-verification description", multiline: true },
    { key: "yourEmail", label: "Email fallback" },
    { key: "resendIn", label: "Resend countdown label" },
    { key: "resend", label: "Resend-code link" },
    { key: "back", label: "Back button" },
    { key: "verify", label: "Verify button" },
    { key: "uploadTitle", label: "CV-upload heading" },
    { key: "uploadDescription", label: "CV-upload description", multiline: true },
    { key: "uploadLabel", label: "CV-upload label" },
    { key: "uploadHelp", label: "CV-upload help", multiline: true },
    { key: "drop", label: "CV drop prompt" },
    { key: "browse", label: "Browse link" },
    { key: "fileLimit", label: "CV file limit" },
    { key: "reading", label: "CV-reading status" },
    { key: "ready", label: "CV-ready status" },
    { key: "remove", label: "Remove-CV button" },
    { key: "browseJobs", label: "Browse-jobs button" },
    { key: "completeProfile", label: "Complete-profile button" },
    { key: "profileReading", label: "Profile-analysis heading" },
    { key: "profileTitle", label: "Profile heading" },
    { key: "profileReadingDescription", label: "Profile-analysis description", multiline: true },
    { key: "profileDescription", label: "Profile description", multiline: true },
    { key: "analyzing", label: "Analysis status ({{file}})", multiline: true },
    { key: "yourProfile", label: "Profile-name fallback" },
    { key: "cvAnalyzed", label: "Analysis-complete status" },
    { key: "extracting", label: "Extraction status" },
    { key: "extracted", label: "Extraction-complete status" },
    { key: "skillsSummary", label: "Skills summary ({{found}}, {{matched}})" },
    { key: "matchedSkills", label: "Matched-skills heading" },
    { key: "growth", label: "Growth-skills heading" },
    { key: "profile", label: "Profile-details heading" },
    { key: "jobTitle", label: "Target-job-title label" },
    { key: "jobTitlePlaceholder", label: "Target-job-title placeholder" },
    { key: "location", label: "Location label" },
    { key: "locationPlaceholder", label: "Location placeholder" },
    { key: "experience", label: "Experience label" },
    { key: "select", label: "Experience selector placeholder" },
    { key: "bio", label: "Professional-summary label" },
    { key: "optional", label: "Optional label" },
    { key: "bioPlaceholder", label: "Professional-summary placeholder", multiline: true },
    { key: "dashboard", label: "Finish-profile button" },
  ],
};

function clone(value) {
  return JSON.parse(JSON.stringify(value));
}

function isTranslated(value) {
  return value && typeof value === "object" && ("en" in value || "ar" in value);
}

function mergeDefaults(saved) {
  const defaults = clone(DEFAULTS);
  return {
    ...defaults,
    ...saved,
    side: { ...defaults.side, ...saved.side },
    login: { ...defaults.login, ...saved.login },
    register: { ...defaults.register, ...saved.register },
  };
}

function SettingCard({ title, description, open, onToggle, children }) {
  return (
    <motion.section
      layout
      className="overflow-hidden rounded-2xl border border-border bg-surface shadow-sm"
    >
      <button
        type="button"
        onClick={onToggle}
        className="flex w-full items-center gap-3 px-5 py-4 text-left"
      >
        <span className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
          <LayoutGrid className="h-4 w-4" />
        </span>
        <span className="min-w-0 flex-1">
          <b className="block text-[15px] text-ink">{title}</b>
          <span className="block truncate text-xs text-muted">{description}</span>
        </span>
        <ChevronDown
          className={cn("h-5 w-5 text-muted transition", open && "rotate-180")}
        />
      </button>
      <AnimatePresence initial={false}>
        {open && (
          <motion.div
            initial={{ height: 0, opacity: 0 }}
            animate={{ height: "auto", opacity: 1 }}
            exit={{ height: 0, opacity: 0 }}
            transition={{ duration: 0.25, ease: EASE }}
            className="overflow-hidden border-t border-border"
          >
            <div className="grid gap-5 px-5 pb-5 pt-4 sm:grid-cols-2">
              {children}
            </div>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.section>
  );
}

function ImageField({ label, value, onChange, onError }) {
  const handleFile = (event) => {
    const file = event.target.files?.[0];
    event.target.value = "";
    if (!file) return;
    if (!file.type.startsWith("image/")) {
      onError("Please select an image file.");
      return;
    }
    if (file.size > MAX_IMAGE_SIZE) {
      onError("Image must be 2 MB or smaller.");
      return;
    }

    const reader = new FileReader();
    reader.onload = () => onChange(reader.result);
    reader.onerror = () => onError("Could not read the selected image.");
    reader.readAsDataURL(file);
  };

  return (
    <div className="space-y-2 sm:col-span-2">
      <PlainField
        label={`${label} URL`}
        value={value}
        onChange={onChange}
        type="url"
      />
      <div className="flex flex-wrap items-center gap-3">
        {value ? (
          <img
            src={value}
            alt={`${label} preview`}
            className="h-16 w-24 rounded-lg border border-border object-cover"
          />
        ) : (
          <div className="flex h-16 w-24 items-center justify-center rounded-lg border border-dashed border-border bg-background text-muted">
            <ImagePlus className="h-5 w-5" />
          </div>
        )}
        <label className="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-border bg-surface px-3 py-2 text-xs font-semibold text-ink hover:bg-background">
          <Upload className="h-4 w-4" />
          Upload image (max 2 MB)
          <input
            type="file"
            accept="image/*"
            className="sr-only"
            onChange={handleFile}
          />
        </label>
      </div>
    </div>
  );
}

export default function AuthSettings() {
  const [bootState] = useState(() => {
    try {
      const raw = localStorage.getItem(DRAFT_KEY);
      return raw
        ? { state: mergeDefaults(JSON.parse(raw)), restored: true, loadError: false }
        : { state: clone(DEFAULTS), restored: false, loadError: false };
    } catch {
      return { state: clone(DEFAULTS), restored: false, loadError: true };
    }
  });
  const [state, setState] = useState(bootState.state);
  const [dirty, setDirty] = useState(bootState.restored);
  const [saving, setSaving] = useState(false);
  const [query, setQuery] = useState("");
  const [viewLang, setViewLang] = useState("both");
  const [activeTab, setActiveTab] = useState("login");
  const [openSections, setOpenSections] = useState(
    () => new Set(["shared", "login", "register"]),
  );
  const [toasts, setToasts] = useState([]);

  const toast = useCallback((msg, kind = "ok") => {
    const id = Date.now() + Math.random();
    setToasts((items) => [...items, { id, msg, kind }]);
    setTimeout(
      () => setToasts((items) => items.filter((item) => item.id !== id)),
      3200,
    );
  }, []);

  useEffect(() => {
    const onBeforeUnload = (event) => {
      if (!dirty) return;
      event.preventDefault();
      event.returnValue = "";
    };
    window.addEventListener("beforeunload", onBeforeUnload);
    return () => window.removeEventListener("beforeunload", onBeforeUnload);
  }, [dirty]);

  const patch = (section, key, value) => {
    setState((current) => ({
      ...current,
      [section]: { ...current[section], [key]: value },
    }));
    setDirty(true);
  };

  const toggleSection = (id) => {
    setOpenSections((previous) => {
      const next = new Set(previous);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  };

  const matchesQuery = (section, field) => {
    if (!query.trim()) return true;
    const searchable = `${section} ${field.label} ${JSON.stringify(
      state[section]?.[field.key] ?? "",
    )}`;
    return searchable.toLowerCase().includes(query.trim().toLowerCase());
  };

  const visibleFields = {
    shared: FIELD_SECTIONS.shared.filter((field) =>
      matchesQuery("side", field),
    ),
    login: FIELD_SECTIONS.login.filter((field) =>
      matchesQuery("login", field),
    ),
    register: FIELD_SECTIONS.register.filter((field) =>
      matchesQuery("register", field),
    ),
  };

  const renderTextFields = (section, fields) =>
    fields.map((field) => (
      <I18nField
        key={field.key}
        label={field.label}
        value={state[section][field.key]}
        onChange={(value) => patch(section, field.key, value)}
        multiline={field.multiline}
        viewLang={viewLang}
        langs={LANGS}
        isI18n={isTranslated}
      />
    ));

  const save = async (asDraft = false) => {
    setSaving(true);
    try {
      localStorage.setItem(DRAFT_KEY, JSON.stringify(state));
      setDirty(false);
      toast(asDraft ? "Draft saved." : "Auth settings saved.");
    } catch (error) {
      toast(`Save failed: ${error.message}`, "err");
    } finally {
      setSaving(false);
    }
  };

  const reset = () => {
    if (!window.confirm("Reset auth settings to their defaults?")) return;
    setState(clone(DEFAULTS));
    setDirty(true);
    toast("Defaults restored — save to apply them.", "warn");
  };

  const renderImage = (section) => (
    <ImageField
      label={`${section === "login" ? "Login" : "Register"} side-panel image`}
      value={state[section].image}
      onChange={(value) => patch(section, "image", value)}
      onError={(message) => toast(message, "err")}
    />
  );

  const renderTabSections = (section) => {
    const fields = visibleFields[section];
    const showShared = visibleFields.shared.length > 0;
    const showPage = fields.length > 0 || !query.trim();
    if (!showShared && !showPage) {
      return (
        <p className="rounded-xl border border-border bg-surface p-5 text-sm text-muted">
          No matching fields found.
        </p>
      );
    }

    return (
      <div className="space-y-3">
        {showShared && (
          <SettingCard
            title="Shared side panel"
            description="Translated copy shown alongside both authentication forms."
            open={openSections.has("shared") || !!query}
            onToggle={() => toggleSection("shared")}
          >
            {renderTextFields("side", visibleFields.shared)}
          </SettingCard>
        )}
        {showPage && (
          <SettingCard
            title={section === "login" ? "Login page" : "Register page"}
            description={
              section === "login"
                ? "Login form, social sign-in, and footer copy."
                : "Account creation, email verification, CV upload, and profile copy."
            }
            open={openSections.has(section) || !!query}
            onToggle={() => toggleSection(section)}
          >
            {renderImage(section)}
            {renderTextFields(section, fields)}
          </SettingCard>
        )}
      </div>
    );
  };

  return (
    <div className="min-h-screen bg-background text-ink">
      <main className="mx-auto max-w-4xl px-5 pb-16 pt-8">
        <motion.div
          initial={{ opacity: 0, y: 12 }}
          animate={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.4, ease: EASE }}
        >
          <h1 className="text-2xl font-bold text-primary">
            Login & Register CMS
          </h1>
          <p className="mt-1 text-sm text-muted">
            Manage authentication page content and side-panel images in English
            and Arabic.
          </p>
          {bootState.loadError && (
            <p
              role="alert"
              className="mt-3 rounded-lg border border-error/30 bg-error/10 px-3 py-2 text-sm text-error"
            >
              Could not load saved auth settings. Default values are shown.
            </p>
          )}
          {bootState.restored && (
            <p className="mt-3 text-xs text-warning">
              Restored your previously saved auth settings.
            </p>
          )}

          <div className="mt-4 flex flex-wrap items-center gap-2">
            <div className="relative max-w-sm flex-1">
              <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
              <Input
                value={query}
                onChange={(event) => setQuery(event.target.value)}
                placeholder="Search sections and content..."
                className="pl-9"
              />
            </div>
            <select
              value={viewLang}
              onChange={(event) => setViewLang(event.target.value)}
              className="rounded-lg border border-border bg-surface px-3 py-2 text-sm"
              aria-label="Languages to show"
            >
              <option value="both">EN + عربي</option>
              {LANGS.map(([code, name]) => (
                <option key={code} value={code}>
                  {name} only
                </option>
              ))}
            </select>
            <Button
              type="button"
              variant="outline"
              size="sm"
              className="ml-auto border-border"
              onClick={() =>
                setOpenSections(new Set(["shared", "login", "register"]))
              }
            >
              Expand all
            </Button>
            <Button
              type="button"
              variant="outline"
              size="sm"
              className="border-border"
              onClick={() => setOpenSections(new Set())}
            >
              Collapse all
            </Button>
          </div>
        </motion.div>

        <Tabs
          value={activeTab}
          onValueChange={setActiveTab}
          className="mt-8 gap-5"
        >
          <div className="sticky top-0 z-10 h-auto rounded-2xl border border-border bg-surface/90 p-2 shadow-sm backdrop-blur-sm">
            <TabsList className="flex h-16! w-full justify-start gap-2 overflow-x-auto bg-transparent px-1">
              <TabsTrigger
                value="login"
                className="h-auto min-w-40 flex-col items-start gap-1 rounded-xl px-3 py-2 text-start text-muted after:hidden data-[state=active]:scale-[1.02] data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-sm"
              >
                <span className="text-sm font-semibold">Login page</span>
                <span className="text-[10px] opacity-75">Sign-in content</span>
              </TabsTrigger>
              <TabsTrigger
                value="register"
                className="h-auto min-w-40 flex-col items-start gap-1 rounded-xl px-3 py-2 text-start text-muted after:hidden data-[state=active]:scale-[1.02] data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-sm"
              >
                <span className="text-sm font-semibold">Register page</span>
                <span className="text-[10px] opacity-75">Account and onboarding</span>
              </TabsTrigger>
            </TabsList>
          </div>

          <TabsContent value="login" className="mt-0">
            {renderTabSections("login")}
          </TabsContent>
          <TabsContent value="register" className="mt-0">
            {renderTabSections("register")}
          </TabsContent>
        </Tabs>

        <div className="mt-8 flex flex-wrap items-center gap-2 border-t border-border pt-5">
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
            onClick={reset}
            className="ml-auto gap-1 border-border text-error hover:bg-background"
          >
            <RotateCcw className="h-4 w-4" />
            Reset defaults
          </Button>
          <Button
            type="button"
            variant="outline"
            size="sm"
            disabled={saving}
            onClick={() => save(true)}
            className="gap-1 border-border"
          >
            <Save className="h-4 w-4" />
            Save draft
          </Button>
          <Button
            type="button"
            size="sm"
            disabled={saving}
            onClick={() => save()}
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
      </main>

      <CmsToastStack
        toasts={toasts}
        onDismiss={(id) =>
          setToasts((items) => items.filter((item) => item.id !== id))
        }
        ease={EASE}
      />
    </div>
  );
}
