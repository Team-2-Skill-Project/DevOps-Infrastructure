import LogoText from "@/assets/logo/logo_text.svg";

export default function DashboardFooter() {
  return (
    <footer className="w-full border-t border-border bg-surface py-8">
      <div className="mx-auto flex max-w-[1360px] flex-col items-center justify-between gap-4 px-6 text-muted sm:flex-row">
        <div className="flex items-center gap-3">
          <span className="font-serif text-[18px] font-bold text-primary">
            <img src={LogoText} alt="MatchIn Logo" />
          </span>
          <span className="text-[12px] text-muted/70">
            © 2026 MatchIn Intelligence Inc. All rights reserved.
          </span>
        </div>
        <div className="flex items-center gap-6 text-[13px]">
          <a
            href="#"
            className="font-medium transition-colors hover:text-primary"
          >
            Platform Integrity
          </a>
          <a
            href="#"
            className="font-medium transition-colors hover:text-primary"
          >
            Privacy Architecture
          </a>
          <a
            href="#"
            className="font-medium transition-colors hover:text-primary"
          >
            Terms of Service
          </a>
          <a
            href="#"
            className="font-medium transition-colors hover:text-primary"
          >
            Talent API
          </a>
        </div>
      </div>
    </footer>
  );
}
