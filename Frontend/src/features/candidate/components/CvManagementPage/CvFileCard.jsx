import { useRef } from "react";
import { motion } from "framer-motion";
import { FileText, Upload } from "lucide-react";
import { Button } from "@/components/ui/button";

const ease = [0.16, 1, 0.3, 1];

/**
 * Current CV file info + upload trigger.
 *
 * @param {{
 *   fileName: string,
 *   uploadedAt: string,
 *   size: string,
 *   onFileSelect: (file: File) => void,
 * }} props
 */
export default function CvFileCard({ fileName, uploadedAt, size, onFileSelect }) {
  const inputRef = useRef(null);

  const handleChange = (e) => {
    const file = e.target.files?.[0];
    e.target.value = "";
    if (file) onFileSelect(file);
  };

  return (
    <motion.div
      layout
      initial={{ opacity: 0, y: 12 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4, delay: 0.08, ease }}
      className="flex flex-col items-center justify-between gap-4 rounded-xl border border-border bg-background p-4 md:flex-row"
    >
      <div className="flex items-center gap-3">
        <div className="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 text-secondary">
          <FileText className="h-6 w-6" />
        </div>
        <div>
          <motion.h4
            key={fileName}
            initial={{ opacity: 0, x: -6 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.25, ease }}
            className="text-sm font-bold text-ink"
          >
            {fileName}
          </motion.h4>
          <p className="text-xs text-muted">
            Uploaded: {uploadedAt} • Size: {size}
          </p>
        </div>
      </div>

      <div className="flex gap-2">
        <input
          ref={inputRef}
          type="file"
          accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
          className="hidden"
          onChange={handleChange}
        />
        <Button
          type="button"
          onClick={() => inputRef.current?.click()}
          className="flex items-center gap-1 rounded-xl bg-primary px-4 py-2 text-xs font-bold text-primary-foreground hover:bg-primary/90"
        >
          <Upload className="h-4 w-4" />
          Upload New CV
        </Button>
      </div>
    </motion.div>
  );
}
