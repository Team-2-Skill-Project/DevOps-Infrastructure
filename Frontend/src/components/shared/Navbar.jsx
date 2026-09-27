import { motion } from "framer-motion"
import { Link } from "react-router-dom"
import { Button } from "@/components/ui/button"
import { useLocalizedPath } from "@/utils/routes";

export function Navbar() {
  const localizedPath = useLocalizedPath();

  return (
    <motion.header
      initial={{ y: -20, opacity: 0 }}
      animate={{ y: 0, opacity: 1 }}
      transition={{ duration: 0.35, ease: "easeOut" }}
      className="flex items-center justify-between border-b px-6 py-4"
    >
      <Link to={localizedPath("/")} className="text-lg font-semibold">
        MatchIn
      </Link>
      <nav className="flex items-center gap-4">
        <Link to={localizedPath("/dashboard/jobs")} className="text-sm text-muted-foreground hover:text-foreground">
          Jobs
        </Link>
        <Button size="sm">Sign in</Button>
      </nav>
    </motion.header>
  )
}
