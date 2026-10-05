import React from 'react'
import { motion } from 'framer-motion'
import { ArrowLeft, ArrowRight, Compass, House } from 'lucide-react'
import { Link, useNavigate } from 'react-router-dom'
import { Button } from '@/components/ui/button'
import { useLocalizedPath } from '@/utils/routes'
import { useTranslation } from "react-i18next";

export default function NotFoundPage() {
  const { t, i18n } = useTranslation("common");
  const localizedPath = useLocalizedPath();
  const navigate = useNavigate();
  const isRtl = i18n.dir() === 'rtl';

  return (
    <main dir={i18n.dir()} className="relative flex min-h-[100svh] flex-col overflow-hidden bg-background px-6 py-8 text-ink sm:px-10 lg:px-16">
      <motion.div
        initial={{ opacity: 0, y: -18 }}
        animate={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.5, ease: [0.16, 1, 0.3, 1] }}
        className="relative z-10"
      >
        <Link to={localizedPath('/')} className="flex w-fit items-center gap-2 font-headline-sm text-lg font-bold text-primary">
          <span className="size-2.5 rounded-[3px] bg-secondary" />
          MatchIn
        </Link>
      </motion.div>

      <div className="mx-auto grid w-full max-w-5xl flex-1 content-center items-center gap-10 py-12 md:grid-cols-[0.9fr_1.1fr] md:gap-16">
        <motion.div
          initial={{ opacity: 0, x: -30, scale: 0.96 }}
          animate={{ opacity: 1, x: 0, scale: 1 }}
          transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1], delay: 0.08 }}
          className="relative flex flex-col items-center justify-center md:items-start"
        >
          <motion.div
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.2 }}
            className="mb-5 flex items-center gap-2 rounded-md border border-border bg-surface px-3 py-2 text-xs font-semibold text-primary shadow-sm"
          >
            <Compass className="size-4 text-secondary" />
            {t('notFound.eyebrow')}
          </motion.div>
          <motion.span
            aria-hidden="true"
            initial={{ opacity: 0, scale: 0.8, y: 30 }}
            animate={{ opacity: 1, scale: 1, y: 0 }}
            transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1], delay: 0.15 }}
            className="font-headline-xl text-[clamp(8rem,24vw,15rem)] font-bold leading-[0.78] text-primary tracking-normal"
          >
            404
          </motion.span>
          <motion.div
            initial={{ scaleX: 0 }}
            animate={{ scaleX: 1 }}
            transition={{ duration: 0.5, delay: 0.4 }}
            className="mt-7 h-1 w-20 origin-left rounded-full bg-secondary"
          />
        </motion.div>

        <motion.section
          initial={{ opacity: 0, x: 28 }}
          animate={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.7, ease: [0.16, 1, 0.3, 1], delay: 0.2 }}
          className="flex flex-col items-center text-center md:items-start md:text-start"
        >
          <h1 className="font-headline-xl text-[32px] font-bold leading-tight text-ink sm:text-[40px]">
            {t('notFound.title')}
          </h1>
          <p className="mt-4 max-w-lg font-body-lg leading-relaxed text-muted">
            {t('notFound.description')}
          </p>
          <motion.div
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.5, delay: 0.35 }}
            className="mt-8 flex w-full flex-col gap-3 sm:w-auto sm:flex-row"
          >
            <Button asChild size="lg" className="gap-2 rounded-lg px-6">
              <Link to={localizedPath('/')}>
                <House className="size-4" />
                {t('notFound.home')}
              </Link>
            </Button>
            <Button
              type="button"
              variant="outline"
              size="lg"
              className="gap-2 rounded-lg border-border bg-surface px-6 text-primary hover:bg-surface"
              onClick={() => navigate(-1)}
            >
              {isRtl ? <ArrowRight className="size-4" /> : <ArrowLeft className="size-4" />}
              {t('notFound.back')}
            </Button>
          </motion.div>
        </motion.section>
      </div>
    </main>
  )
}
