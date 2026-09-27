import { AnimatePresence, motion } from "framer-motion";
import { CircleAlert, FileText, Send, Upload, X, Zap } from "lucide-react";
import Modal from "@/components/shared/Modal";
import { Button } from "@/components/ui/button";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { Textarea } from "@/components/ui/textarea";
import { Link } from "react-router-dom";
import { useLocalizedPath } from "@/utils/routes";

const CVS = [
  {
    value: "resume-2026.pdf",
    label: "resume_2026.pdf",
    meta: "Updated Aug 12",
  },
  {
    value: "resume-old.pdf",
    label: "resume_frontend_old.pdf",
    meta: "Updated Mar 03",
  },
];

export default function JobApplicationModal({ job, form, onClose, onSubmit }) {
  const { control, formState } = form;
  const { errors } = formState;

  return (
    <Modal onClose={onClose} maxWidth="max-w-[560px]">
      <div className="max-h-[90vh] overflow-y-auto scrollbar-none">
        <div className="sticky top-0 z-10 flex items-start justify-between rounded-t-2xl border-b border-border bg-white p-6">
          <div>
            <div className="mb-1 text-[11px] font-bold text-secondary">
              Job Application
            </div>
            <h2 className="text-lg font-bold text-primary">
              {job.title}{" "}
              <span className="text-sm font-normal text-muted">
                at {job.company}
              </span>
            </h2>
          </div>
          <Button
            type="button"
            onClick={onClose}
            variant="ghost"
            size="icon-sm"
            className="rounded-lg text-muted hover:bg-background"
            title="Close"
          >
            <X className="h-5 w-5" />
          </Button>
        </div>

        <div className="space-y-5 p-6">
          <div className="flex flex-wrap gap-2 text-[11px] font-bold">
            <span className="flex h-fit items-center gap-1.5 rounded-lg border border-border bg-background px-3 py-1 text-primary">
              <Zap className="h-4 w-4" /> Method: Internal
            </span>
            <span className="flex h-fit items-center gap-1.5 rounded-lg border border-border bg-background px-3 py-1 text-primary">
              <Send className="h-4 w-4" /> Source: MatchIn Feed
            </span>
          </div>

          <AnimatePresence>
            {Object.keys(errors).length > 0 && (
              <motion.div
                initial={{ opacity: 0, y: -6 }}
                animate={{ opacity: 1, y: 0 }}
                exit={{ opacity: 0, y: -6 }}
                className="flex items-center gap-2 rounded-xl border border-error/30 bg-[#FBEAE8] px-4 py-3 text-xs font-bold text-error"
              >
                <CircleAlert className="h-4.5 w-4.5" />
                Please correct the highlighted errors before submitting.
              </motion.div>
            )}
          </AnimatePresence>

          <Form {...form}>
            <form
              id="job-application-form"
              className="space-y-5"
              onSubmit={form.handleSubmit(onSubmit)}
            >
              <ApplicantDetails />
              <ResumeField control={control} />
              <div>
                <Label className="mb-2 text-xs font-bold text-primary">
                  Additional Note{" "}
                  <span className="font-normal text-muted">(Optional)</span>
                </Label>
                <Textarea
                  rows={3}
                  placeholder="Add a short note for the hiring team — why are you a great fit?"
                  className="resize-none rounded-xl border-border bg-background/50 p-3 text-xs focus-visible:border-primary focus-visible:ring-0"
                />
              </div>
              <SelectField
                control={control}
                label="Are you legally authorized to work in this location?"
                options={["Yes", "No", "Require Sponsorship"]}
              />
              <FormField
                control={control}
                name="startDate"
                rules={{ required: "Please select your earliest start date." }}
                render={({ field }) => (
                  <FormItem>
                    <FormLabel className="text-xs font-bold text-primary">
                      Earliest Start Date <span className="text-error">*</span>
                    </FormLabel>
                    <FormControl>
                      <Input
                        type="date"
                        className="h-10 rounded-xl bg-surface-white px-3 text-xs focus-visible:border-primary focus-visible:ring-0"
                        {...field}
                      />
                    </FormControl>
                    <FormMessage className="text-xs font-bold text-error" />
                  </FormItem>
                )}
              />
            </form>
          </Form>
        </div>

        <div className="sticky bottom-0 flex items-center gap-3 rounded-b-2xl border-t border-border bg-white p-6">
          <Button
            type="button"
            onClick={onClose}
            variant="outline"
            className="h-10 rounded-xl px-5 text-xs font-bold text-primary hover:bg-background"
          >
            Cancel
          </Button>
          <Button
            type="submit"
            form="job-application-form"
            className="h-10 flex-1 rounded-xl text-xs font-bold"
          >
            Submit Application
          </Button>
        </div>
      </div>
    </Modal>
  );
}

function ApplicantDetails() {
  const localizedPath = useLocalizedPath();

  return (
    <div>
      <div className="mb-2 text-[11px] font-bold text-muted">
        Applicant Details
      </div>
      <div className="grid grid-cols-2 gap-3">
        <ProfileInput label="Full Name" placeholder="Ahmed Mahmoud" />
        <ProfileInput label="Email Address" placeholder="ahmed@example.com" />
      </div>
      <div className="mt-1.5 text-[11px] text-muted">
        Fetched from your profile —{" "}
        <Link to={localizedPath("/dashboard/profile")} className="font-bold text-primary hover:underline">
          Edit Profile
        </Link>
      </div>
    </div>
  );
}

function ProfileInput({ label, placeholder }) {
  return (
    <div className="h-[46.8px] rounded-xl border border-border bg-background px-3 py-2">
      <div className="-mb-2 text-[10px] text-muted">{label}</div>
      <Input
        className="h-auto border-0 bg-transparent p-0 text-[12px]! font-bold text-primary shadow-none focus-visible:ring-0"
        placeholder={placeholder}
      />
    </div>
  );
}

function ResumeField({ control }) {
  return (
    <FormField
      control={control}
      name="cv"
      rules={{ required: "Please select a resume to continue." }}
      render={({ field }) => (
        <FormItem>
          <FormLabel className="text-xs font-bold text-primary">
            Select Resume (CV) <span className="text-error">*</span>
          </FormLabel>
          <FormControl>
            <div className="space-y-2">
              {CVS.map((resume) => (
                <label
                  key={resume.value}
                  className={`flex cursor-pointer items-center gap-3 rounded-xl border p-3 transition-all ${field.value === resume.value ? "border-primary bg-primary/5" : "border-border"}`}
                >
                  <input
                    type="radio"
                    name={field.name}
                    value={resume.value}
                    checked={field.value === resume.value}
                    onChange={field.onChange}
                    className="accent-primary"
                  />
                  <FileText className="text-muted" />
                  <span className="flex-1 text-xs font-bold text-primary">
                    {resume.label}
                  </span>
                  <span className="text-[11px] text-muted">{resume.meta}</span>
                </label>
              ))}
              <Button
                type="button"
                variant="link"
                className="h-auto px-0 pt-1 text-xs font-bold text-primary"
              >
                <Upload className="h-4.5 w-4.5" /> Upload another CV
              </Button>
            </div>
          </FormControl>
          <FormMessage className="text-xs font-bold text-error" />
        </FormItem>
      )}
    />
  );
}

function SelectField({ control, label, options }) {
  return (
    <FormField
      control={control}
      name="authorized"
      rules={{ required: "This field is required by the employer." }}
      render={({ field }) => (
        <FormItem>
          <FormLabel className="text-xs font-bold text-primary">
            {label} <span className="text-error">*</span>
          </FormLabel>
          <Select value={field.value} onValueChange={field.onChange}>
            <FormControl>
              <SelectTrigger className="h-10 w-full rounded-xl bg-background text-xs">
                <SelectValue placeholder="Select an answer" />
              </SelectTrigger>
            </FormControl>
            <SelectContent>
              {options.map((option) => (
                <SelectItem key={option} value={option}>
                  {option}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          <FormMessage className="text-xs font-bold text-error" />
        </FormItem>
      )}
    />
  );
}
