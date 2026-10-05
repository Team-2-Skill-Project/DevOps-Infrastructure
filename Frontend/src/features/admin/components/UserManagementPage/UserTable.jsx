import { Ban, Eye, PencilLine, ArrowLeftRight, Power, Trash2 } from "lucide-react";
import { cn } from "@/lib/utils";
import { RoleBadge, UserStatusBadge } from "@/features/admin/shared";

const AVATAR_COLORS = ["bg-primary", "bg-secondary", "bg-[#3d6b8a]", "bg-accent text-ink", "bg-[#5a6b4f]", "bg-[#6b4f6b]", "bg-[#4a6fa5]"];

function initials(fullName) {
  return fullName.split(" ").map((part) => part[0]).slice(0, 2).join("").toUpperCase();
}

export function TableHead() {
  return (
    <thead className="border-b border-border bg-[#f7f4ef]">
      <tr>
        {['User', 'Email', 'Role', 'Status', 'Created', 'Last Activity', 'Actions'].map((label) => (
          <th key={label} className={cn("whitespace-nowrap px-4 py-3 text-left text-[11.5px] font-bold uppercase tracking-wide text-muted", label === "Actions" && "text-right")}>{label}</th>
        ))}
      </tr>
    </thead>
  );
}

export function UserTable({ users, onView, onEdit, onChangeRole, onActivate, onDeactivate, onDelete }) {
  return (
    <tbody>
      {users.map((user, index) => (
        <tr key={user.id} className={cn("border-b border-border transition-colors last:border-b-0 hover:bg-background/60", user.status !== "active" && "opacity-70")}>
          <td className="px-4 py-3.5"><div className="flex items-center gap-3"><div className={cn("flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[13px] font-bold text-white", AVATAR_COLORS[index % AVATAR_COLORS.length])}>{initials(user.fullName)}</div><div><div className="text-sm font-semibold text-ink">{user.fullName}</div><div className="mt-0.5 font-mono text-xs text-muted">{user.id}</div></div></div></td>
          <td className="px-4 py-3.5 text-[13.5px] text-ink">{user.email}</td>
          <td className="px-4 py-3.5"><RoleBadge role={user.role} /></td>
          <td className="px-4 py-3.5"><UserStatusBadge status={user.status} /></td>
          <td className="whitespace-nowrap px-4 py-3.5"><div className="text-[13.5px] text-ink">{user.createdLabel}</div><div className="text-xs text-muted">{user.createdAgo}</div></td>
          <td className="whitespace-nowrap px-4 py-3.5"><div className="text-[13.5px] text-ink">{user.activityLabel}</div><div className="text-xs text-muted">{user.activityAgo}</div></td>
          <td className="px-4 py-3.5"><div className="flex items-center justify-end gap-1"><RowIconButton title="View user" onClick={() => onView(user)}><Eye className="h-4 w-4" /></RowIconButton><RowIconButton title="Edit user" onClick={() => onEdit(user)}><PencilLine className="h-4 w-4" /></RowIconButton><RowIconButton title="Change role" onClick={() => onChangeRole(user)}><ArrowLeftRight className="h-4 w-4" /></RowIconButton>{user.status === "active" ? <RowIconButton title="Deactivate" tone="danger" onClick={() => onDeactivate(user)}><Ban className="h-4 w-4" /></RowIconButton> : <RowIconButton title="Activate user" tone="success" onClick={() => onActivate(user)}><Power className="h-4 w-4" /></RowIconButton>}<RowIconButton title="Delete user" tone="danger" onClick={() => onDelete(user)}><Trash2 className="h-4 w-4" /></RowIconButton></div></td>
        </tr>
      ))}
    </tbody>
  );
}

function RowIconButton({ children, title, tone, onClick }) { return <button type="button" title={title} onClick={onClick} className={cn("grid h-8 w-8 place-items-center rounded-lg border border-transparent text-muted transition-colors", tone === "danger" && "hover:border-error/20 hover:bg-error/10 hover:text-error", tone === "success" && "hover:border-success/20 hover:bg-success/10 hover:text-success", !tone && "hover:border-primary/15 hover:bg-primary/8 hover:text-primary")}>{children}</button>; }
