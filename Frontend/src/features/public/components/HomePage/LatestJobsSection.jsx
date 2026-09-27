import { useMemo, useState } from "react";
import { motion } from "framer-motion";
import { ArrowRight } from "lucide-react";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Tabs, TabsList, TabsTrigger } from "@/components/ui/tabs";
import JobCard from "@/components/shared/JobCard";
import { JOBS, JOB_CATEGORIES } from "@/constants/jobsMock";
import { useTranslation } from "react-i18next";

export default function LatestJobsSection() {
  const [category, setCategory] = useState("all");
  const { t } = useTranslation("common");

  const filteredJobs = useMemo(
    () =>
      category === "all"
        ? JOBS
        : JOBS.filter((job) => job.categories.includes(category)),
    [category],
  );

  return (
    <section id="jobs" className="mx-auto w-full max-w-[1280px] scroll-mt-20 px-6 py-16 md:px-10 lg:px-16 lg:py-24">
      {/* Header */}
      <div className="mb-10 flex flex-col justify-between gap-4 md:flex-row md:items-end">
        <div className="flex max-w-2xl flex-col gap-2">
          <Badge
            variant="outline"
            className="w-fit self-start rounded-full border-border bg-surface px-4 py-1 text-[11px] font-semibold uppercase tracking-wider text-primary"
          >
            {t("home.jobs.badge")}
          </Badge>
          <h2 className="font-headline-xl text-[32px] font-bold tracking-tight text-ink sm:text-[40px]">
            {t("home.jobs.title")}
          </h2>
          <p className="font-body-lg text-muted">
            {t("home.jobs.description")}
          </p>
        </div>

        <div className="flex items-center gap-2">
          <span className="font-body-sm text-[13px] text-muted">
            {t("home.jobs.updated")}
          </span>
          <span className="relative flex h-2 w-2">
            <span className="absolute inline-flex h-full w-full animate-ping rounded-full bg-success opacity-75" />
            <span className="relative inline-flex h-2 w-2 rounded-full bg-success" />
          </span>
        </div>
      </div>

      {/* Category filters */}
      <Tabs
        value={category}
        onValueChange={setCategory}
        className="mb-10 border-b border-border pb-2"
      >
        <TabsList className="h-auto flex-wrap justify-start gap-2 bg-transparent p-0">
          {JOB_CATEGORIES.map((c) => (
            <TabsTrigger
              key={c.value}
              value={c.value}
              className="rounded-full border border-border bg-surface px-4 py-1.5 font-headline-sm text-[13px] font-medium text-muted shadow-none transition-colors data-[state=active]:border-transparent data-[state=active]:bg-primary data-[state=active]:text-primary-foreground data-[state=active]:shadow-none hover:bg-background hover:text-ink"
            >
              {t(`home.jobCategories.${c.value}`)}
            </TabsTrigger>
          ))}
        </TabsList>
      </Tabs>

      {/* Job grid */}
      <div
        key={category}
        className="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
      >
        {filteredJobs.map((job, index) => (
          <JobCard key={job.id} job={job} index={index} />
        ))}
      </div>

      {/* Load more */}
      <motion.div
        initial={{ opacity: 0 }}
        whileInView={{ opacity: 1 }}
        viewport={{ once: true }}
        transition={{ duration: 0.5, delay: 0.2 }}
        className="mt-12 text-center"
      >
        <Button
          variant="outline"
          className="gap-2 rounded-xl border-border bg-surface px-8 py-3 font-headline-sm text-[14px] font-semibold text-primary shadow-sm hover:bg-background hover:shadow"
        >
          {t("home.jobs.exploreAll")}
          <ArrowRight className="h-[18px] w-[18px]" />
        </Button>
      </motion.div>
    </section>
  );
}
