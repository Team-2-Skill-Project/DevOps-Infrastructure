import { useEffect, useState } from "react";
import { AnimatePresence, motion } from "framer-motion";
import { FilterX } from "lucide-react";
import { Button } from "@/components/ui/button";
import ApplicationCard from "./ApplicationCard";

export default function ApplicationList({
  applications,
  onOpenDetails,
  onWithdraw,
  onClearFilters,
}) {
  const [openMenuId, setOpenMenuId] = useState(null);

  useEffect(() => {
    const handleOutsideClick = () => setOpenMenuId(null);
    window.addEventListener("click", handleOutsideClick);
    return () => window.removeEventListener("click", handleOutsideClick);
  }, []);

  const handleMenuToggle = (event, id) => {
    event.stopPropagation();
    setOpenMenuId((currentId) => (currentId === id ? null : id));
  };

  const handleOpenDetails = (app) => {
    setOpenMenuId(null);
    onOpenDetails(app);
  };

  const handleWithdraw = (id) => {
    setOpenMenuId(null);
    onWithdraw(id);
  };

  return (
    <motion.div layout className="space-y-3">
      <AnimatePresence>
        {applications.map((app) => (
          <ApplicationCard
            key={app.id}
            app={app}
            openMenuId={openMenuId}
            onMenuToggle={handleMenuToggle}
            onOpenDetails={handleOpenDetails}
            onWithdraw={handleWithdraw}
          />
        ))}
      </AnimatePresence>
      {applications.length === 0 && (
        <motion.div
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          className="py-16 text-center bg-white border border-border rounded-2xl"
        >
          <FilterX className="w-8 h-8 text-muted mx-auto mb-2" />
          <p className="text-[13.5px] text-muted">
            No applications match your search or filter settings.
          </p>
          <Button
            type="button"
            variant="link"
            onClick={onClearFilters}
            className="mt-3 text-[12.5px] font-semibold text-primary hover:underline"
          >
            Clear filters
          </Button>
        </motion.div>
      )}
    </motion.div>
  );
}
