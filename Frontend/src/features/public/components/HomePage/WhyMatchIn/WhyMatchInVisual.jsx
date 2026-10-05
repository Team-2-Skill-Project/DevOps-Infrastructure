import { useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { Play, ShieldCheck, X } from "lucide-react";
import Modal from "@/components/shared/Modal";

// Replace with your real tutorial video ID
const YOUTUBE_VIDEO_ID = "wsLIkM7f_YY";

export default function WhyMatchInVisual() {
  const [isVideoOpen, setIsVideoOpen] = useState(false);

  return (
    <div className="relative pb-16 lg:col-span-6">
      <motion.div
        initial={{ opacity: 0, scale: 0.96 }}
        whileInView={{ opacity: 1, scale: 1 }}
        viewport={{ once: true, amount: 0.3 }}
        transition={{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }}
        onClick={() => setIsVideoOpen(true)}
        className="group relative aspect-video w-full cursor-pointer overflow-hidden rounded-[20px] border border-border bg-surface p-1.5 shadow-[0_12px_32px_rgba(27,28,26,0.08)]"
      >
        <div className="relative h-full w-full overflow-hidden rounded-2xl">
          <img
            alt="MatchIn Interactive Platform Preview"
            src="https://cdn.arabsstock.com/uploads/images/316068/discussion-and-dialogue-between-colleagues-thumbnail-316068.webp"
            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
          />
          <div className="absolute inset-0 flex items-center justify-center bg-primary/40">
            <motion.div
              whileHover={{ scale: 1.05 }}
              className="flex items-center gap-2 rounded-full border border-border bg-surface/90 px-6 py-2.5 shadow-[0_8px_24px_rgba(0,0,0,0.15)] backdrop-blur-md transition-colors hover:bg-surface"
            >
              <div className="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-primary-foreground shadow-sm">
                <Play className="h-[18px] w-[18px] fill-current" />
              </div>
              <span className="font-headline-sm text-[13px] font-bold text-ink">
                Watch How We Work (2 min video)
              </span>
            </motion.div>
          </div>
        </div>
      </motion.div>

      {/* Floating security badge */}
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        whileInView={{ opacity: 1, y: 0 }}
        viewport={{ once: true }}
        transition={{ duration: 0.6, delay: 0.3, ease: [0.16, 1, 0.3, 1] }}
        whileHover={{ y: -4 }}
        className="absolute -bottom-6 -inset-s-4 flex max-w-sm items-center gap-3 rounded-2xl border border-border bg-surface/95 p-4 shadow-[0_10px_24px_rgba(0,0,0,0.08)] backdrop-blur-md sm:inset-s-6"
      >
        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-[10px] bg-success/15 text-success">
          <ShieldCheck className="h-[19px] w-[19px]" />
        </div>
        <div className="flex flex-col">
          <span className="font-headline-sm text-[13px] font-bold text-ink">
            Enterprise-Grade Security
          </span>
          <span className="font-body-sm text-[12px] text-muted">
            SOC2 Type II Certified &amp; End-to-End Encryption
          </span>
        </div>
      </motion.div>

      {/* Video modal */}
      <AnimatePresence>
        {isVideoOpen && (
          <Modal
            onClose={() => setIsVideoOpen(false)}
            maxWidth="max-w-3xl"
            className="overflow-hidden bg-black"
          >
            <div className="relative">
              <button
                type="button"
                onClick={() => setIsVideoOpen(false)}
                className="absolute right-3 top-3 z-10 flex h-8 w-8 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/80"
                aria-label="Close video"
              >
                <X className="h-4 w-4" />
              </button>
              <div className="aspect-video w-full">
                <iframe
                  className="h-full w-full"
                  src={`https://www.youtube.com/embed/${YOUTUBE_VIDEO_ID}?autoplay=1`}
                  title="How We Work"
                  frameBorder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                  allowFullScreen
                />
              </div>
            </div>
          </Modal>
        )}
      </AnimatePresence>
    </div>
  );
}
