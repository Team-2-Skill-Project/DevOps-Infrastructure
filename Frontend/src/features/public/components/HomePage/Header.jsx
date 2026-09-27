import { Link, useLocation, useNavigate, useParams } from "react-router-dom";
import { Menu, User } from "lucide-react";
import { Button } from "@/components/ui/button";
import { Sheet, SheetContent, SheetTrigger, SheetClose } from "@/components/ui/sheet";

import { NAV_LINKS } from "@/constants/navLinks";
import LogoNavy from "@/assets/logo/Full_logo_navy.svg";
import { useTranslation } from "react-i18next";
import { useLocalizedPath } from "@/utils/routes";
import LanguageToggle from "@/components/shared/LanguageToggle";

export default function Header()
{
    const { lang } = useParams();
    const navigate = useNavigate();
    const location = useLocation();
    const localizedPath = useLocalizedPath();

    const handleLanguageChange = (newLang) => {
      const segments = location.pathname.split("/");

      segments[1] = newLang;

      navigate(segments.join("/"));
    };

  const { t } = useTranslation("common");

  return (
    <header className="sticky top-0 z-50 w-full border-b border-border bg-surface/95 backdrop-blur-md">
      <div className="mx-auto flex h-20 max-w-[1280px] items-center justify-between gap-4 px-6 md:px-10 lg:px-16">
        {/* Logo + desktop nav */}
        <div className="flex items-center gap-8">
          <a href="#home" className="group flex items-center gap-2 shrink-0">
            <img
              src={LogoNavy}
              alt="MatchIn Logo"
              className="h-8 w-auto object-contain transition-transform group-hover:scale-105"
            />
          </a>

          <nav className="hidden items-center gap-6 lg:flex">
            {NAV_LINKS.map((link) => (
              <a
                key={link.id}
                href={`${location.pathname}#${link.id}`}
                className="font-headline-sm text-[14px] font-medium text-muted transition-colors hover:text-primary"
              >
                {t(link.labelKey)}
              </a>
            ))}
          </nav>
        </div>

        {/* Right side actions */}
        <div className="flex items-center gap-3">
          <LanguageToggle language={lang} onChange={handleLanguageChange} />

          {/* TODO: point to the real sign-in / post-a-job routes */}
          <Button
            asChild
            variant="ghost"
            className="hidden font-headline-sm text-[14px] font-medium text-muted hover:bg-transparent hover:text-primary sm:inline-flex"
          >
            <Link to={localizedPath("/auth/login")}>{t("actions.apply")}</Link>
          </Button>

          {/* <Button
            asChild
            className="hidden rounded-xl bg-primary px-4 py-[10px] font-headline-sm text-[14px] font-semibold text-primary-foreground shadow-sm hover:bg-primary/90 hover:shadow sm:inline-flex"
          >
            <Link to={localizedPath("/post-a-job")}>Post a Job</Link>
          </Button> */}

          {/* TODO: point to the real profile/dashboard route */}
          <Button
            asChild
            variant="outline"
            size="icon"
            className="h-9 w-9 shrink-0 rounded-full border-border bg-background text-primary shadow-sm hover:bg-surface"
          >
            <Link to={localizedPath("/dashboard")}>
              <User className="h-[18px] w-[18px]" />
            </Link>
          </Button>

          {/* Mobile menu */}
          <Sheet>
            <SheetTrigger asChild>
              <Button
                variant="outline"
                size="icon"
                className="h-9 w-9 shrink-0 rounded-full border-border bg-background text-primary lg:hidden"
              >
                <Menu className="h-[18px] w-[18px]" />
              </Button>
            </SheetTrigger>
            <SheetContent side="right" className="w-72 bg-surface">
              <nav className="mt-8 flex flex-col gap-1">
                {NAV_LINKS.map((link) => (
                  <SheetClose asChild key={link.id}>
                    <a
                      href={`${location.pathname}#${link.id}`}
                      className="rounded-lg px-3 py-2.5 font-headline-sm text-[15px] font-medium text-ink transition-colors hover:bg-background hover:text-primary"
                    >
                      {t(link.labelKey)}
                    </a>
                  </SheetClose>
                ))}
              </nav>

              <div className="mt-6 flex flex-col gap-2 border-t border-border pt-6">
                <SheetClose asChild>
                  <Button
                    asChild
                    variant="outline"
                    className="justify-center rounded-xl border-border text-primary"
                  >
                    <Link to={localizedPath("/auth/login")}>{t("actions.signIn")}</Link>
                  </Button>
                </SheetClose>
                <SheetClose asChild>
                  <Button
                    asChild
                    className="justify-center rounded-xl bg-primary text-primary-foreground hover:bg-primary/90"
                  >
                    <Link to={localizedPath("/post-a-job")}>{t("actions.postJob")}</Link>
                  </Button>
                </SheetClose>
                <div className="mt-2 flex justify-center">
                  <LanguageToggle
                    language={lang}
                    onChange={handleLanguageChange}
                  />
                </div>
              </div>
            </SheetContent>
          </Sheet>
        </div>
      </div>
    </header>
  );
}
