/**
 * Shared Framer Motion variants for admin feature pages.
 * Kept framework-level (no feature coupling) so any admin list can reuse them.
 */

export const containerVariants = {
  hidden: { opacity: 0 },
  visible: {
    opacity: 1,
    transition: { staggerChildren: 0.05 },
  },
};

export const rowVariants = {
  hidden: { opacity: 0, y: 12 },
  visible: {
    opacity: 1,
    y: 0,
    transition: { duration: 0.3, ease: "easeOut" },
  },
  exit: { opacity: 0, scale: 0.98, transition: { duration: 0.2 } },
};