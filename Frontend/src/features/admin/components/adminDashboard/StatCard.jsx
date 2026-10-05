import { motion } from "framer-motion";
import { ArrowUpRight } from "lucide-react";
import { Card } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import { cn } from "@/lib/utils";
import { itemVariants } from "../../shared/motionVariants";


export default function StatCard({ icon: Icon, iconClass, value, label, sublabel, trend }) {
  const isUp = trend?.type === "up";

  return (
    <motion.div variants={itemVariants}>
      <Card className="gap-3 bg-white border border-border rounded-2xl p-[20px_18px] py-[20px] shadow-[0_2px_8px_rgba(0,0,0,0.03)] hover:border-primary/25 hover:shadow-[0_4px_12px_rgba(0,0,0,0.06)] transition-all flex flex-col justify-between">
        <div className="flex items-center justify-between">
          <div className={cn("w-10 h-10 rounded-xl grid place-items-center", iconClass)}>
            <Icon className="w-5 h-5" />
          </div>
          {trend && (
            <Badge
              className={cn(
                "text-[11.5px] font-semibold inline-flex items-center gap-1 px-2 py-0.5 rounded-full border-transparent",
                isUp ? "bg-success/12 text-success" : "bg-muted/10 text-muted"
              )}
            >
              {isUp && <ArrowUpRight className="w-3 h-3 stroke-[2.5]" />}
              {trend.label}
            </Badge>
          )}
        </div>
        <div>
          <div className="text-[28px] font-bold tracking-tight text-[#222831] leading-tight">
            {value}
          </div>
          <div className="text-[13px] font-medium text-muted">{label}</div>
          {sublabel && (
            <div className="text-[12px] text-muted mt-0.5">{sublabel}</div>
          )}
        </div>
      </Card>
    </motion.div>
  );
}