import { motion } from "framer-motion";
import { Ban, CalendarClock, XCircle } from "lucide-react";

const bannerIcons = {
  expired: CalendarClock,
  withdrawn: XCircle,
  closed: Ban,
};

const bannerStates = {
  expired: {
    title: "This job posting has expired",
    sub: "The employer is no longer accepting new activity on this listing. Your application history stays visible below.",
  },
  withdrawn: {
    title: "You withdrew this application",
    sub: "Withdrawn on Sep 10, 2026. You can still view everything that happened before you withdrew.",
  },
  closed: {
    title: "This job posting has been closed",
    sub: "The employer closed this role. No further status updates will come through for this application.",
  },
};

export default function ApplicationDetailBanner({ viewState }) {
  if (!["expired", "withdrawn", "closed"].includes(viewState)) return null;

  const BannerIcon = bannerIcons[viewState];

  return (
    <motion.div
      initial={{ opacity: 0, height: 0 }}
      animate={{ opacity: 1, height: "auto" }}
      className="mb-5 rounded-2xl px-5 py-4 flex items-start gap-3 shadow-sm bg-background border border-border text-[#44474E]"
    >
      <BannerIcon className="h-5 w-5 shrink-0 mt-0.5" />
      <div>
        <div className="text-[13.5px] font-semibold">
          {bannerStates[viewState].title}
        </div>
        <div className="text-[12.5px] mt-0.5 opacity-80">
          {bannerStates[viewState].sub}
        </div>
      </div>
    </motion.div>
  );
}
