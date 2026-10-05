import * as React from "react";
import { useNavigate } from "react-router-dom";
import { useTranslation } from "react-i18next";
import { AnimatePresence } from "framer-motion";
import {
  Search,
  Download,
  Plus,
  Users as UsersIcon,
  CheckCircle2,
  Ban,
  Power,
  ShieldCheck,
  Trash2,
  X,
  AlertTriangle,
  SearchX,
} from "lucide-react";

import Status from "@/components/shared/Status";
import Modal from "@/components/shared/Modal";
import { UsersTableSkeleton } from "@/components/layouts/skeleton/UsersTableSkeleton";
import { StatsStripSkeleton } from "@/components/layouts/skeleton/StatsStripSkeleton";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";
import { RoleBadge, UserStatusBadge, ROLE_LABELS, STATUS_LABELS } from "@/features/admin/shared";
import { TableHead, UserTable } from "@/features/admin/components/UserManagementPage/UserTable";
import Pagination from "@/features/admin/components/UserManagementPage/Pagination";
import { UserFormBody, ChangeRoleBody } from "@/features/admin/components/UserManagementPage/UserDialogs";

const StatusPill = UserStatusBadge;

// TODO: replace with a real fetch, e.g. GET /admin/users?search=&role=&status=&page=
const MOCK_USERS = [
  { id: "USR-4821", fullName: "Maya Khaled", email: "maya.k@matchin.io", role: "candidate", status: "active", createdLabel: "Sep 18, 2026", createdAgo: "4 days ago", createdDaysAgo: 4, activityLabel: "Sep 22, 2026", activityAgo: "1 hr ago", activityDaysAgo: 0 },
  { id: "USR-3902", fullName: "Ahmed Hassan", email: "ahmed@brightpath.io", role: "recruiter", status: "active", createdLabel: "Aug 5, 2026", createdAgo: "48 days ago", createdDaysAgo: 48, activityLabel: "Sep 22, 2026", activityAgo: "3 hrs ago", activityDaysAgo: 0 },
  { id: "USR-2214", fullName: "Youssef Nabil", email: "youssef.nabil@gmail.com", role: "admin", status: "active", createdLabel: "Jan 12, 2026", createdAgo: "255 days ago", createdDaysAgo: 255, activityLabel: "Sep 22, 2026", activityAgo: "10 min ago", activityDaysAgo: 0 },
  { id: "USR-1788", fullName: "Karim Mostafa", email: "karim.m@yahoo.com", role: "candidate", status: "suspended", createdLabel: "Feb 8, 2026", createdAgo: "226 days ago", createdDaysAgo: 226, activityLabel: "Aug 30, 2026", activityAgo: "23 days ago", activityDaysAgo: 23 },
  { id: "USR-4990", fullName: "Dina Saleh", email: "dina.saleh@icloud.com", role: "candidate", status: "active", createdLabel: "Sep 20, 2026", createdAgo: "2 days ago", createdDaysAgo: 2, activityLabel: "Sep 22, 2026", activityAgo: "20 min ago", activityDaysAgo: 0 },
  { id: "USR-3355", fullName: "Laila Fathy", email: "laila.fathy@outlook.com", role: "recruiter", status: "inactive", createdLabel: "May 3, 2026", createdAgo: "141 days ago", createdDaysAgo: 141, activityLabel: "Jul 14, 2026", activityAgo: "70 days ago", activityDaysAgo: 70 },
  { id: "USR-5011", fullName: "Omar Zaki", email: "omar.zaki@matchin.io", role: "candidate", status: "active", createdLabel: "Sep 1, 2026", createdAgo: "21 days ago", createdDaysAgo: 21, activityLabel: "Sep 21, 2026", activityAgo: "1 day ago", activityDaysAgo: 1 },
  { id: "USR-0940", fullName: "Sara Adel", email: "sara.adel@nexuslabs.io", role: "recruiter", status: "active", createdLabel: "Mar 30, 2026", createdAgo: "175 days ago", createdDaysAgo: 175, activityLabel: "Sep 20, 2026", activityAgo: "2 days ago", activityDaysAgo: 2 },
];

function fetchUsers() {
  return new Promise((resolve) => setTimeout(() => resolve(MOCK_USERS), 800));
}

const PAGE_SIZE = 8;

export function UserManagementPage() {
  const navigate = useNavigate();
  const { t } = useTranslation("dashboard");

  const [pageState, setPageState] = React.useState("loading"); // loading | loaded | error
  const [users, setUsers] = React.useState([]);

  const [search, setSearch] = React.useState("");
  const [roleFilter, setRoleFilter] = React.useState("");
  const [statusFilter, setStatusFilter] = React.useState("");
  const [sortBy, setSortBy] = React.useState("newest");
  const [page, setPage] = React.useState(1);

  const [modal, setModal] = React.useState(null); // { type, user }
  const [modalBusy, setModalBusy] = React.useState(false);

  const load = React.useCallback(() => {
    setPageState("loading");
    fetchUsers()
      .then((data) => {
        setUsers(data);
        setPageState("loaded");
      })
      .catch(() => setPageState("error"));
  }, []);

  React.useEffect(() => {
    load();
  }, [load]);

  const filtered = React.useMemo(() => {
    let list = users.filter((u) => {
      const q = search.trim().toLowerCase();
      const matchesSearch =
        !q || u.fullName.toLowerCase().includes(q) || u.email.toLowerCase().includes(q) || u.id.toLowerCase().includes(q);
      const matchesRole = !roleFilter || u.role === roleFilter;
      const matchesStatus = !statusFilter || u.status === statusFilter;
      return matchesSearch && matchesRole && matchesStatus;
    });

    list = [...list].sort((a, b) => {
      if (sortBy === "newest") return a.createdDaysAgo - b.createdDaysAgo;
      if (sortBy === "oldest") return b.createdDaysAgo - a.createdDaysAgo;
      if (sortBy === "name") return a.fullName.localeCompare(b.fullName);
      if (sortBy === "activity") return a.activityDaysAgo - b.activityDaysAgo;
      return 0;
    });

    return list;
  }, [users, search, roleFilter, statusFilter, sortBy]);

  const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
  const pageItems = filtered.slice((page - 1) * PAGE_SIZE, page * PAGE_SIZE);

  React.useEffect(() => setPage(1), [search, roleFilter, statusFilter]);

  const stats = React.useMemo(() => {
    const total = users.length;
    const active = users.filter((u) => u.status === "active").length;
    const inactiveOrSuspended = users.filter((u) => u.status !== "active").length;
    const admins = users.filter((u) => u.role === "admin").length;
    return { total, active, inactiveOrSuspended, admins };
  }, [users]);

  const hasActiveFilters = Boolean(search || roleFilter || statusFilter);
  const clearFilters = () => {
    setSearch("");
    setRoleFilter("");
    setStatusFilter("");
  };

  const openModal = (type, user) => setModal({ type, user });
  const closeModal = () => {
    if (modalBusy) return;
    setModal(null);
  };

  // ---------- Mutations (all mocked — TODO: wire to real endpoints) ----------
  const runMutation = async (apply) => {
    setModalBusy(true);
    await new Promise((r) => setTimeout(r, 700));
    setUsers((prev) => apply(prev));
    setModalBusy(false);
    setModal(null);
  };

  const handleAddUser = (form) =>
    runMutation((prev) => [
      {
        id: `USR-${Math.floor(1000 + Math.random() * 9000)}`,
        fullName: form.fullName,
        email: form.email,
        role: form.role,
        status: "active",
        createdLabel: "Just now",
        createdAgo: "Just now",
        createdDaysAgo: 0,
        activityLabel: "—",
        activityAgo: "Never",
        activityDaysAgo: 9999,
      },
      ...prev,
    ]);

  const handleEditUser = (form) =>
    runMutation((prev) => prev.map((u) => (u.id === modal.user.id ? { ...u, ...form } : u)));

  const handleChangeRole = (newRole) =>
    runMutation((prev) => prev.map((u) => (u.id === modal.user.id ? { ...u, role: newRole } : u)));

  const handleSetStatus = (newStatus) =>
    runMutation((prev) => prev.map((u) => (u.id === modal.user.id ? { ...u, status: newStatus } : u)));

  const handleDelete = () =>
    runMutation((prev) => prev.filter((u) => u.id !== modal.user.id));

  return (
    <div className="min-h-screen bg-background px-7 py-8">
      <div className="mx-auto max-w-[1280px]">
        {/* Header */}
        <div className="mb-6 flex flex-wrap items-start justify-between gap-4">
          <div>
            <h1 className="text-[22px] font-bold tracking-tight text-ink">{t("pages.admin.userManagement")}</h1>
            <p className="mt-1 text-sm text-muted">Search, view, and manage all platform users</p>
          </div>
          <div className="flex flex-wrap gap-2.5">
            <Button variant="outline" className="h-10 gap-2 rounded-xl border-border text-ink">
              <Download className="h-4 w-4" />
              Export
            </Button>
            <Button
              onClick={() => openModal("add")}
              className="h-10 gap-2 rounded-xl bg-primary text-primary-foreground hover:bg-primary/90"
            >
              <Plus className="h-4 w-4" />
              Add User
            </Button>
          </div>
        </div>

        {/* Stats */}
        {pageState === "loading" ? (
          <StatsStripSkeleton />
        ) : (
          <div className="mb-5.5 grid grid-cols-4 gap-3.5 max-[800px]:grid-cols-2 max-[440px]:grid-cols-1">
            <div className="flex items-center gap-3.5 rounded-2xl border border-border bg-surface p-4 shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                <UsersIcon className="h-5 w-5" />
              </div>
              <div>
                <div className="text-[22px] font-bold leading-tight tracking-tight text-ink">{stats.total}</div>
                <div className="mt-0.5 text-[12.5px] font-medium text-muted">Total Users</div>
              </div>
            </div>
            <div className="flex items-center gap-3.5 rounded-2xl border border-border bg-surface p-4 shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-success/12 text-success">
                <CheckCircle2 className="h-5 w-5" />
              </div>
              <div>
                <div className="text-[22px] font-bold leading-tight tracking-tight text-ink">{stats.active}</div>
                <div className="mt-0.5 text-[12.5px] font-medium text-muted">Active</div>
              </div>
            </div>
            <div className="flex items-center gap-3.5 rounded-2xl border border-border bg-surface p-4 shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-muted/12 text-muted">
                <Ban className="h-5 w-5" />
              </div>
              <div>
                <div className="text-[22px] font-bold leading-tight tracking-tight text-ink">{stats.inactiveOrSuspended}</div>
                <div className="mt-0.5 text-[12.5px] font-medium text-muted">Inactive / Suspended</div>
              </div>
            </div>
            <div className="flex items-center gap-3.5 rounded-2xl border border-border bg-surface p-4 shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
              <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-secondary/12 text-secondary">
                <ShieldCheck className="h-5 w-5" />
              </div>
              <div>
                <div className="text-[22px] font-bold leading-tight tracking-tight text-ink">{stats.admins}</div>
                <div className="mt-0.5 text-[12.5px] font-medium text-muted">Admins</div>
              </div>
            </div>
          </div>
        )}

        {/* Toolbar */}
        <div className="mb-4.5 flex flex-col gap-3">
          <div className="flex flex-wrap items-center gap-3">
            <div className="relative min-w-[220px] flex-1">
              <Search className="pointer-events-none absolute left-3.5 top-1/2 h-[18px] w-[18px] -translate-y-1/2 text-muted" />
              <Input
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search by name, email, or user ID…"
                className="h-[42px] rounded-xl border-border pl-10"
              />
            </div>

            <Select value={roleFilter || "all"} onValueChange={(v) => setRoleFilter(v === "all" ? "" : v)}>
              <SelectTrigger className="h-[42px] min-w-[140px] rounded-xl border-border"><SelectValue placeholder="All Roles" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Roles</SelectItem>
                <SelectItem value="candidate">Candidate</SelectItem>
                <SelectItem value="recruiter">Recruiter</SelectItem>
                <SelectItem value="admin">Admin</SelectItem>
              </SelectContent>
            </Select>

            <Select value={statusFilter || "all"} onValueChange={(v) => setStatusFilter(v === "all" ? "" : v)}>
              <SelectTrigger className="h-[42px] min-w-[140px] rounded-xl border-border"><SelectValue placeholder="All Statuses" /></SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Statuses</SelectItem>
                <SelectItem value="active">Active</SelectItem>
                <SelectItem value="inactive">Inactive</SelectItem>
                <SelectItem value="suspended">Suspended</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {hasActiveFilters && (
            <div className="flex flex-wrap items-center gap-2">
              {search && (
                <span className="inline-flex h-7 items-center gap-1.5 rounded-full bg-primary/8 px-2.5 text-[12.5px] font-medium text-primary">
                  Search: {search}
                  <button type="button" onClick={() => setSearch("")} className="opacity-65 hover:opacity-100"><X className="h-3 w-3" /></button>
                </span>
              )}
              {roleFilter && (
                <span className="inline-flex h-7 items-center gap-1.5 rounded-full bg-primary/8 px-2.5 text-[12.5px] font-medium text-primary">
                  Role: {ROLE_LABELS[roleFilter]}
                  <button type="button" onClick={() => setRoleFilter("")} className="opacity-65 hover:opacity-100"><X className="h-3 w-3" /></button>
                </span>
              )}
              {statusFilter && (
                <span className="inline-flex h-7 items-center gap-1.5 rounded-full bg-primary/8 px-2.5 text-[12.5px] font-medium text-primary">
                  Status: {STATUS_LABELS[statusFilter]}
                  <button type="button" onClick={() => setStatusFilter("")} className="opacity-65 hover:opacity-100"><X className="h-3 w-3" /></button>
                </span>
              )}
              <button type="button" onClick={clearFilters} className="text-[12.5px] font-semibold text-muted hover:text-ink hover:underline">
                Clear all
              </button>
            </div>
          )}
        </div>

        {/* Results bar */}
        {pageState === "loaded" && (
          <div className="mb-3 flex flex-wrap items-center justify-between gap-3">
            <div className="text-[13.5px] text-muted">
              Showing <strong className="font-semibold text-ink">{filtered.length === 0 ? 0 : (page - 1) * PAGE_SIZE + 1}–{Math.min(page * PAGE_SIZE, filtered.length)}</strong> of{" "}
              <strong className="font-semibold text-ink">{filtered.length}</strong> users
            </div>
            <div className="flex items-center gap-2 text-[13px] text-muted">
              <span>Sort by</span>
              <Select value={sortBy} onValueChange={setSortBy}>
                <SelectTrigger className="h-[34px] min-w-[160px] rounded-lg border-border text-[13px]"><SelectValue /></SelectTrigger>
                <SelectContent>
                  <SelectItem value="newest">Newest first</SelectItem>
                  <SelectItem value="oldest">Oldest first</SelectItem>
                  <SelectItem value="name">Name A–Z</SelectItem>
                  <SelectItem value="activity">Last activity</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        )}

        {/* Table / states */}
        <div className="overflow-hidden rounded-2xl border border-border bg-surface shadow-[0_2px_8px_rgba(0,0,0,0.03)]">
          {pageState === "error" ? (
            <Status
              icon={<AlertTriangle className="h-6 w-6" />}
              iconClassName="bg-error/10 text-error"
              title={t("pages.admin.userManagementStates.loadFailed")}
              subtitle="Something went wrong while fetching the users list."
              primaryButton={{ label: "Retry", onClick: load }}
            />
          ) : pageState === "loading" ? (
            <div className="overflow-x-auto">
              <table className="w-full min-w-[1000px] border-collapse">
                <TableHead />
                <UsersTableSkeleton rows={8} />
              </table>
            </div>
          ) : users.length === 0 ? (
            <Status
              icon={<UsersIcon className="h-6 w-6" />}
              iconClassName="bg-primary/10 text-primary"
              title={t("pages.admin.userManagementStates.empty")}
              subtitle="Once people sign up, they'll show up here."
              primaryButton={{ label: "Add User", onClick: () => openModal("add") }}
            />
          ) : filtered.length === 0 ? (
            <Status
              icon={<SearchX className="h-6 w-6" />}
              iconClassName="bg-muted/12 text-muted"
              title={t("pages.admin.userManagementStates.noResults")}
              subtitle="Try adjusting your search or filters."
              primaryButton={{ label: "Clear filters", onClick: clearFilters }}
            />
          ) : (
            <>
              <div className="overflow-x-auto">
                <table className="w-full min-w-[1000px] border-collapse">
                  <TableHead />
                  <UserTable users={pageItems} onView={(user) => navigate(`/admin/users/${user.id}`)} onEdit={(user) => openModal("edit", user)} onChangeRole={(user) => openModal("changeRole", user)} onActivate={(user) => openModal("activate", user)} onDeactivate={(user) => openModal("deactivate", user)} onDelete={(user) => openModal("delete", user)} />
                </table>
              </div>

              {/* Pagination */}
              <Pagination page={page} totalPages={totalPages} onPageChange={setPage} />
            </>
          )}
        </div>

        {/* Legend */}
        {pageState === "loaded" && (
          <div className="mt-3.5 flex flex-wrap items-center gap-4 text-[12.5px] text-muted">
            <RoleBadge role="candidate" />
            <RoleBadge role="recruiter" />
            <RoleBadge role="admin" />
            <span className="ml-1"><StatusPill status="active" /></span>
            <StatusPill status="inactive" />
            <StatusPill status="suspended" />
          </div>
        )}
      </div>

      {/* Modals */}
      <AnimatePresence>
        {modal?.type === "add" && (
          <Modal onClose={closeModal}>
            <UserFormBody
              busy={modalBusy}
              onCancel={closeModal}
              onSubmit={handleAddUser}
              title={t("pages.admin.userManagementStates.addUser")}
              subtitle="Create a new account on the platform."
              submitLabel="Add User"
            />
          </Modal>
        )}

        {modal?.type === "edit" && (
          <Modal onClose={closeModal}>
            <UserFormBody
              busy={modalBusy}
              onCancel={closeModal}
              onSubmit={handleEditUser}
              title={t("pages.admin.userManagementStates.editUser")}
              subtitle={`Update ${modal.user.fullName}'s details.`}
              submitLabel="Save Changes"
              initialValues={{ fullName: modal.user.fullName, email: modal.user.email, role: modal.user.role }}
            />
          </Modal>
        )}

        {modal?.type === "changeRole" && (
          <Modal onClose={closeModal} maxWidth="max-w-[400px]">
            <ChangeRoleBody busy={modalBusy} user={modal.user} onCancel={closeModal} onConfirm={handleChangeRole} />
          </Modal>
        )}

        {modal?.type === "activate" && (
          <Modal onClose={closeModal} maxWidth="max-w-[400px]">
            <Status
              icon={<Power className="h-6 w-6" />}
              iconClassName="bg-success/10 text-success"
              title={t("pages.admin.userManagementStates.activateUser")}
              subtitle={`${modal.user.fullName} will regain full access to their account.`}
              secondaryButton={{ label: "Cancel", onClick: closeModal, disabled: modalBusy }}
              primaryButton={{ label: modalBusy ? "Activating…" : "Activate", onClick: () => handleSetStatus("active"), disabled: modalBusy }}
            />
          </Modal>
        )}

        {modal?.type === "deactivate" && (
          <Modal onClose={closeModal} maxWidth="max-w-[400px]">
            <Status
              icon={<Ban className="h-6 w-6" />}
              iconClassName="bg-error/10 text-error"
              title={t("pages.admin.userManagementStates.deactivateUser")}
              subtitle={`${modal.user.fullName} will lose access until reactivated.`}
              secondaryButton={{ label: "Cancel", onClick: closeModal, disabled: modalBusy }}
              primaryButton={{ label: modalBusy ? "Deactivating…" : "Deactivate", onClick: () => handleSetStatus("inactive"), disabled: modalBusy }}
            />
          </Modal>
        )}

        {modal?.type === "delete" && (
          <Modal onClose={closeModal} maxWidth="max-w-[400px]">
            <Status
              icon={<Trash2 className="h-6 w-6" />}
              iconClassName="bg-error/10 text-error"
              title={t("pages.admin.userManagementStates.deleteUser")}
              subtitle={`This permanently removes ${modal.user.fullName}'s account. This can't be undone.`}
              secondaryButton={{ label: "Cancel", onClick: closeModal, disabled: modalBusy }}
              primaryButton={{ label: modalBusy ? "Deleting…" : "Delete User", onClick: handleDelete, disabled: modalBusy }}
            />
          </Modal>
        )}
      </AnimatePresence>
    </div>
  );
}

