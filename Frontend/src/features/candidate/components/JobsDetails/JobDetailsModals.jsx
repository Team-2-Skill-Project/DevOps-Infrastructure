import Modal from "@/components/shared/Modal";
import { Button } from "@/components/ui/button";
import { ArrowUpRight, X } from "lucide-react";

export default function JobDetailsModals({
  activeModal,
  onClose,
  mentorMessages,
  onAskMentorQuestion,
}) {
  return (
    <>
      {activeModal === "source" && <SourceModal onClose={onClose} />}
      {activeModal === "mentor" && (
        <MentorModal
          messages={mentorMessages}
          onClose={onClose}
          onAskQuestion={onAskMentorQuestion}
        />
      )}
    </>
  );
}

function SourceModal({ onClose }) {
  return (
    <Modal onClose={onClose} maxWidth="max-w-[440px]" className="p-7">
      <div className="flex items-start justify-between mb-4">
        <div>
          <div className="text-[11px] font-bold text-secondary mb-1">
            Original Job Source
          </div>
          <h2 className="font-dm-sans font-bold text-xl text-primary">
            TechNova Career Portal
          </h2>
        </div>
        <CloseButton onClick={onClose} />
      </div>
      <div className="border border-border rounded-xl p-4 text-xs space-y-2.5 bg-background/50 mb-5">
        <DetailRow label="Source URL:" value="careers.technova.io/tn-882" />
        <DetailRow label="Scraped:" value="2 hours ago" />
        <DetailRow
          label="Verification:"
          value="Verified Direct Employer"
          valueClassName="text-success"
        />
      </div>
      <div className="flex gap-3">
        <Button
          variant="ghost"
          className="flex-1 h-10 rounded-xl border border-border text-xs font-bold text-muted hover:bg-background"
          onClick={onClose}
        >
          Stay Here
        </Button>
        <Button
          variant="ghost"
          className="flex-1 h-10 rounded-xl bg-primary text-primary-foreground text-xs font-bold hover:bg-primary/90"
          onClick={onClose}
        >
          Open Original Post <ArrowUpRight className="h-4 w-4" />
        </Button>
      </div>
    </Modal>
  );
}

function MentorModal({ messages, onClose, onAskQuestion }) {
  const questions = [
    "What will the technical interview focus on?",
    "How do I justify the Testing gap?",
    "Draft a strong cover note",
  ];

  return (
    <Modal
      onClose={onClose}
      maxWidth="max-w-[520px]"
      className="flex max-h-[85vh] flex-col"
    >
      <div className="flex items-start justify-between p-5 border-b border-border">
        <div>
          <div className="text-[11px] font-bold text-secondary mb-1">
            AI Mentor
          </div>
          <h2 className="font-dm-sans font-bold text-xl text-primary">
            Prepare for TechNova Labs
          </h2>
        </div>
        <CloseButton onClick={onClose} />
      </div>
      <div className="p-5 space-y-3 overflow-y-auto flex-1 text-xs">
        {messages.map((message, index) => (
          <div
            key={`${message.sender}-${index}`}
            className={`rounded-xl p-3.5 leading-relaxed ${message.sender === "user" ? "bg-primary text-primary-foreground ms-8" : "bg-background border border-border text-muted me-8"}`}
          >
            <span
              className={`font-bold block mb-1 ${message.sender === "user" ? "opacity-70" : "text-primary"}`}
            >
              {message.sender === "user" ? "You:" : "Mentor:"}
            </span>
            {message.text}
          </div>
        ))}
      </div>
      <div className="p-4 border-t border-border flex flex-wrap gap-2">
        {questions.map((question) => (
          <Button
            key={question}
            variant="ghost"
            onClick={() => onAskQuestion(question)}
            className="px-3 py-1.5 rounded-lg border border-border bg-background text-primary text-[11px] font-semibold hover:bg-muted/10 transition-colors"
          >
            {question}
          </Button>
        ))}
      </div>
    </Modal>
  );
}

function CloseButton({ onClick }) {
  return (
    <Button
      variant="ghost"
      onClick={onClick}
      className="w-8 h-8 rounded-lg flex items-center justify-center hover:bg-background text-muted"
      aria-label="Close"
    >
      <X className="h-4 w-4" />
    </Button>
  );
}

function DetailRow({ label, value, valueClassName = "text-primary" }) {
  return (
    <div className="flex justify-between">
      <span className="text-muted">{label}</span>
      <span className={`font-bold ${valueClassName}`}>{value}</span>
    </div>
  );
}
