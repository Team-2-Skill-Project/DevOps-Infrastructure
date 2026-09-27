import { useState } from "react";
import { motion, AnimatePresence } from "framer-motion";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { useTranslation } from "react-i18next";

export default function NewsletterForm({ onSubscribe }) {
  const [email, setEmail] = useState("");
  const [submitted, setSubmitted] = useState(false);
  const { t } = useTranslation("common");

  const handleSubmit = (e) => {
    e.preventDefault();
    if (!email.trim()) return;
    onSubscribe?.(email.trim());
    setSubmitted(true);
    setEmail("");
  };

  return (
    <div className="mt-2 flex flex-col gap-2">
      <span className="font-headline-sm text-[13px] font-semibold text-ink">
        {t("home.footer.subscribe")}
      </span>
      <form onSubmit={handleSubmit} className="flex max-w-md items-center gap-2">
        <Input
          type="email"
          required
          value={email}
          onChange={(e) => {
            setEmail(e.target.value);
            setSubmitted(false);
          }}
          placeholder={t("home.footer.emailPlaceholder")}
          className="flex-1 rounded-[10px] border-border bg-surface px-4 py-[10px] text-[13px] text-ink shadow-sm placeholder:text-muted focus-visible:border-primary focus-visible:ring-0"
        />
        <Button
          type="submit"
          className="rounded-[10px] bg-primary px-4 py-[10px] text-[13px] font-semibold text-primary-foreground shadow-sm hover:bg-primary/90"
        >
          {t("home.footer.join")}
        </Button>
      </form>
      <AnimatePresence>
        {submitted && (
          <motion.p
            initial={{ opacity: 0, y: -4 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0 }}
            className="text-[12px] font-medium text-success"
          >
            {t("home.footer.confirmation")}
          </motion.p>
        )}
      </AnimatePresence>
    </div>
  );
}
