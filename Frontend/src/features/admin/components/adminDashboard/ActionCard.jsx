import { motion } from "framer-motion";
import { ArrowRight } from "lucide-react";
import { Card } from "@/components/ui/card";
import { cn } from "@/lib/utils";
import { itemVariants } from "../../shared/motionVariants";


export default function ActionCard({
  icon: Icon,
  iconClass,
  title,
  description,
  href = "#",
}) {
  return (
    <motion.a
      variants={itemVariants}
      whileHover={{ y: -2 }}
      href={href}
      className="block text-inherit group"
    >
      <Card className="gap-3.5 bg-white border border-border rounded-2xl p-[20px_18px] py-[20px] flex flex-col text-inherit shadow-[0_2px_8px_rgba(0,0,0,0.03)] hover:border-primary/30 hover:shadow-[0_4px_12px_rgba(0,0,0,0.06)] transition-all">
        <div className={cn("w-11 h-11 rounded-xl grid place-items-center", iconClass)}>
          <Icon className="w-5.5 h-5.5" />
        </div>
        <div>
          <div className="text-[14.5px] font-semibold text-[#222831] tracking-tight">
            {title}
          </div>
          <div className="text-[12.5px] text-muted leading-snug mt-0.5">
            {description}
          </div>
        </div>
        <span className="mt-auto text-[12.5px] font-semibold text-primary inline-flex items-center gap-1 group-hover:translate-x-0.5 transition-transform">
          Open
          <ArrowRight className="w-3.5 h-3.5" />
        </span>
      </Card>
    </motion.a>
  );
}