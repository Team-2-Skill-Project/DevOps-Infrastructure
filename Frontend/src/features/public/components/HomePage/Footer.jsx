import { motion } from "framer-motion";
import { Link } from "react-router-dom";
import FooterLinkColumn from "./FooterLinkColumn";
import NewsletterForm from "./NewsletterForm";
import { FOOTER_COLUMNS, FOOTER_LEGAL_LINKS } from "@/constants/footerLinks";
import LogoNavy from "@/assets/logo/Full_logo_navy.svg";
import { useTranslation } from "react-i18next";
import { useLocalizedPath } from "@/utils/routes";

export default function Footer() {
  const year = new Date().getFullYear();
  const { t } = useTranslation("common");
  const localizedPath = useLocalizedPath();

  return (
    <footer className="w-full border-t border-border bg-surface">
      <div className="mx-auto max-w-[1280px] px-6 pb-16 pt-20 md:px-10 lg:px-16">
        <div className="mb-16 grid grid-cols-1 gap-12 md:grid-cols-2 lg:grid-cols-5">
          {/* Brand + newsletter */}
          <motion.div
            initial={{ opacity: 0, y: 16 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true, amount: 0.3 }}
            transition={{ duration: 0.5 }}
            className="flex flex-col gap-4 lg:col-span-2"
          >
            <Link to={localizedPath("/")} className="flex items-center gap-2">
              <img src={LogoNavy} alt="MatchIn Logo" className="h-7 w-auto object-contain" />
            </Link>
            <p className="max-w-sm font-body-md text-[14px] leading-relaxed text-muted">
              {t("home.footer.description")}
            </p>
            <NewsletterForm />
          </motion.div>

          {/* Link columns */}
          {FOOTER_COLUMNS.map((column, i) => (
            <motion.div
              key={column.titleKey}
              initial={{ opacity: 0, y: 16 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true, amount: 0.3 }}
              transition={{ duration: 0.5, delay: 0.1 + i * 0.08 }}
            >
              <FooterLinkColumn column={column} />
            </motion.div>
          ))}
        </div>

        {/* Bottom bar */}
        <div className="flex flex-col items-center justify-between gap-4 border-t border-border pt-8 font-body-sm text-[13px] text-muted sm:flex-row">
          <p>{t("home.footer.copyright", { year })}</p>
          <div className="flex items-center gap-6">
            {FOOTER_LEGAL_LINKS.map((link) => (
              <Link key={link.labelKey} to={localizedPath(link.to)} className="transition-colors hover:text-ink">
                {t(link.labelKey)}
              </Link>
            ))}
          </div>
        </div>
      </div>
    </footer>
  );
}
