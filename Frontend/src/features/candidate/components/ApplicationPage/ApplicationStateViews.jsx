import { AlertTriangle, Briefcase, RefreshCw, Send } from "lucide-react";
import { motion } from "framer-motion";
import { Button } from "@/components/ui/button";
import ApplicationSkeleton from "@/components/layouts/skeleton/ApplicationSkeleton";

export default function ApplicationStateViews({ viewState, onLoaded }) {
  if (viewState === "loading") return <ApplicationSkeleton />;

  if (viewState === "empty") {
    return (
      <motion.div
        initial={{ opacity: 0, scale: 0.98 }}
        animate={{ opacity: 1, scale: 1 }}
        exit={{ opacity: 0, scale: 0.98 }}
        className="bg-white border border-border rounded-3xl py-20 px-6 text-center shadow-sm"
      >
        <div className="w-16 h-16 rounded-2xl bg-primary/5 flex items-center justify-center mx-auto mb-5 text-primary">
          <Send className="w-7 h-7" />
        </div>
        <h2 className="font-dm-sans font-bold text-[20px] mb-2 text-[#1B1C1A]">
          No applications yet
        </h2>
        <p className="text-[13.5px] text-muted max-w-sm mx-auto mb-7 leading-relaxed">
          Once you apply to a role, it'll show up here with its status and every
          update along the way.
        </p>
        <Button
          onClick={onLoaded}
          className="bg-primary text-white text-[13px] font-semibold px-6 h-11 rounded-full inline-flex items-center gap-2 shadow-sm hover:bg-[#0F2036] transition-all"
        >
          <Briefcase className="w-4 h-4" /> Browse Jobs
        </Button>
      </motion.div>
    );
  }

  return (
    <motion.div
      initial={{ opacity: 0, scale: 0.98 }}
      animate={{ opacity: 1, scale: 1 }}
      exit={{ opacity: 0, scale: 0.98 }}
      className="bg-white border border-border rounded-3xl py-20 px-6 text-center shadow-sm"
    >
      <div className="w-16 h-16 rounded-2xl bg-[#B3271E]/10 flex items-center justify-center mx-auto mb-5 text-[#B3271E]">
        <AlertTriangle className="w-7 h-7" />
      </div>
      <h2 className="font-dm-sans font-bold text-[20px] mb-2 text-[#1B1C1A]">
        Couldn't load your applications
      </h2>
      <p className="text-[13.5px] text-muted max-w-sm mx-auto mb-7 leading-relaxed">
        Something went wrong on our end. Please try again.
      </p>
      <Button
        onClick={onLoaded}
        className="bg-primary text-white text-[13px] font-semibold px-6 h-11 rounded-full inline-flex items-center gap-2 shadow-sm hover:bg-[#0F2036] transition-all"
      >
        <RefreshCw className="w-4 h-4" /> Retry
      </Button>
    </motion.div>
  );
}
