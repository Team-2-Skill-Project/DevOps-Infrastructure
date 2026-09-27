import { motion } from "framer-motion";
import { BadgeCheck, Globe, MapPin, Zap, Bookmark } from "lucide-react";
import { Card, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Link } from "react-router-dom";
import { useLocalizedPath } from "@/utils/routes";

const LOCATION_ICONS = { globe: Globe, pin: MapPin };

export default function JobCard({ job, index = 0, onApply }) {
  const localizedPath = useLocalizedPath();
  const LocationIcon = LOCATION_ICONS[job.locationIcon] ?? Globe;

  return (
    <motion.div
      initial={{ opacity: 0, y: 24 }}
      whileInView={{ opacity: 1, y: 0 }}
      viewport={{ once: true, amount: 0.2 }}
      transition={{
        duration: 0.5,
        delay: index * 0.06,
        ease: [0.16, 1, 0.3, 1],
      }}
      whileHover={{ y: -4 }}
      className="h-full"
    >
      <Card className="cursor-pointer group flex h-full flex-col justify-between rounded-2xl border-border bg-surface p-5 shadow-[0_2px_8px_rgba(0,0,0,0.02)] transition-all hover:border-primary/40 hover:shadow-md">
        <Link to={localizedPath(`/dashboard/jobs/${job.id}`)}>
          <CardContent className="flex flex-col gap-3 p-0">
            <div className="flex items-start justify-between">
              <div className="flex items-center gap-3">
                <div
                  className={`flex h-11 w-11 items-center justify-center rounded-xl font-headline-lg text-[18px] font-bold ${job.logoClass}`}
                >
                  {job.initial}
                </div>
                <div className="flex flex-col">
                  <span className="flex items-center gap-1 font-headline-sm text-[14px] font-semibold text-ink">
                    {job.company}
                    {job.verified && (
                      <BadgeCheck className="h-4 w-4 fill-success text-surface" />
                    )}
                  </span>
                  <span className="flex items-center gap-1 font-body-sm text-[12px] text-muted">
                    <LocationIcon className="h-3.5 w-3.5" />
                    {job.location}
                  </span>
                </div>
              </div>
              {/* Ai Match */}
              <Badge className="gap-1 rounded-full bg-success/10 px-2 py-0.5 text-[12px] font-semibold text-success hover:bg-success/10">
                <Zap className="h-3.5 w-3.5" />
                {job.matchPercent}% Match
              </Badge>
            </div>

            <div>
              <h3 className="font-headline-sm text-[17px] font-bold text-ink transition-colors group-hover:text-primary">
                {job.title}
              </h3>
              <p className="mt-1 font-headline-sm text-[15px] font-semibold text-ink">
                {job.salaryRange}{" "}
                <span className="font-body-sm text-[12px] font-normal text-muted">
                  / yr
                </span>
              </p>
            </div>

            <div className="flex flex-wrap gap-1.5 pt-1">
              {job.tags.map((tag) => (
                <Badge
                  key={tag}
                  variant="outline"
                  className="rounded-[6px] border-border bg-background px-2 py-0.5 text-[11px] font-normal text-muted"
                >
                  {tag}
                </Badge>
              ))}
            </div>
          </CardContent>
        </Link>

        <div className="mt-4 flex items-center justify-between border-t border-border pt-3">
          <span className="font-body-sm text-[12px] text-muted">
            {job.postedAgo}
          </span>
          <div className="flex items-center gap-2">
            <Button
              variant="outline"
              size="icon"
              className="h-8 w-8 rounded-full border-border text-muted hover:text-primary"
            >
              <Bookmark className="h-4.5 w-4.5" />
            </Button>
            <Button
              size="sm"
              onClick={() => onApply?.(job)}
              className="rounded-lg bg-primary px-4 py-1.5 font-headline-sm text-[12px] font-semibold text-primary-foreground hover:bg-primary/90"
            >
              Quick Apply
            </Button>
          </div>
        </div>
      </Card>
    </motion.div>
  );
}
