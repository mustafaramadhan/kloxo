'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, MailAccountItem } from '@/lib/api';
import { Mail, CheckCircle2, XCircle, RefreshCw, Plus, Trash2, X, AlertCircle, Inbox } from 'lucide-react';

export default function MailAccountsPage() {
  const [accounts, setAccounts] = useState<MailAccountItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddModal, setShowAddModal] = useState(false);
  const [formData, setFormData] = useState({ email: '', password: '', quota_mb: 500 });
  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchMail = async () => {
    setLoading(true);
    try {
      const data = await apiFetch<{ accounts: MailAccountItem[] }>('/mail/accounts');
      setAccounts(data.accounts);
    } catch (err: any) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchMail();
  }, []);

  const handleCreateMail = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.email.trim() || !formData.password.trim()) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/mail/accounts', {
        method: 'POST',
        body: JSON.stringify(formData),
      });
      setMessage({ text: res.message || 'Mailbox created successfully', success: true });
      setShowAddModal(false);
      setFormData({ email: '', password: '', quota_mb: 500 });
      await fetchMail();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create mailbox', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteMail = async (email: string) => {
    if (!confirm(`Are you sure you want to delete mailbox "${email}"?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/mail/accounts', {
        method: 'DELETE',
        body: JSON.stringify({ email }),
      });
      setMessage({ text: res.message || 'Mailbox deleted successfully', success: true });
      await fetchMail();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete mailbox', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <Shell>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
              <Mail className="h-6 w-6 text-sky-400" />
              Mail Accounts
            </h1>
            <p className="text-sm text-slate-400">
              Manage mailboxes, quotas, and email routing
            </p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={fetchMail}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors"
            >
              <RefreshCw className="h-4 w-4" />
              Refresh
            </button>
            <button
              onClick={() => setShowAddModal(true)}
              className="flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" /> Add Mailbox
            </button>
          </div>
        </div>

        {message && (
          <div
            className={`flex items-center gap-3 rounded-2xl p-4 text-sm border ${
              message.success
                ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-300'
                : 'bg-rose-500/10 border-rose-500/20 text-rose-300'
            }`}
          >
            {message.success ? (
              <CheckCircle2 className="h-5 w-5 text-emerald-400 shrink-0" />
            ) : (
              <AlertCircle className="h-5 w-5 text-rose-400 shrink-0" />
            )}
            <span>{message.text}</span>
          </div>
        )}

        {/* Mailboxes List */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Loading mail accounts...</div>
          ) : accounts.length === 0 ? (
            <div className="p-16 text-center">
              <Inbox className="mx-auto h-12 w-12 text-slate-600 mb-3" />
              <p className="text-slate-400 font-medium">No mail accounts configured yet</p>
              <p className="text-xs text-slate-500 mt-1">
                You can create mail accounts for any of your active domains.
              </p>
              <button
                onClick={() => setShowAddModal(true)}
                className="mt-6 inline-flex items-center gap-2 rounded-xl bg-sky-500/10 border border-sky-500/20 px-4 py-2 text-sm font-semibold text-sky-400 hover:bg-sky-500/20 transition-all"
              >
                <Plus className="h-4 w-4" /> Create First Mailbox
              </button>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Email Address</th>
                    <th className="px-6 py-4">Owner</th>
                    <th className="px-6 py-4">Status</th>
                    <th className="px-6 py-4">Disk Usage</th>
                    <th className="px-6 py-4">Quota</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {accounts.map((acc) => (
                    <tr key={acc.email} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-medium text-white flex items-center gap-3">
                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 text-sky-400">
                          <Mail className="h-4 w-4" />
                        </div>
                        <span>{acc.email}</span>
                      </td>
                      <td className="px-6 py-4 text-slate-400">{acc.owner}</td>
                      <td className="px-6 py-4">
                        <span
                          className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ${
                            acc.status
                              ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                              : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                          }`}
                        >
                          {acc.status ? (
                            <>
                              <CheckCircle2 className="h-3 w-3" /> Active
                            </>
                          ) : (
                            <>
                              <XCircle className="h-3 w-3" /> Suspended
                            </>
                          )}
                        </span>
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">
                        {acc.used_mb} MB
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-400">
                        {acc.quota_mb === 'unlimited' ? 'Unlimited' : `${acc.quota_mb} MB`}
                      </td>
                      <td className="px-6 py-4 text-right">
                        <button
                          onClick={() => handleDeleteMail(acc.email)}
                          disabled={actionLoading}
                          title="Delete Mailbox"
                          className="rounded-lg p-2 text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition-colors"
                        >
                          <Trash2 className="h-4 w-4" />
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* Add Mailbox Modal */}
        {showAddModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-md p-4">
            <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-950 p-6 shadow-2xl">
              <div className="flex items-center justify-between pb-4 border-b border-white/10 mb-4">
                <h3 className="text-lg font-bold text-white flex items-center gap-2">
                  <Mail className="h-5 w-5 text-sky-400" />
                  Create New Mailbox
                </h3>
                <button
                  onClick={() => setShowAddModal(false)}
                  className="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white"
                >
                  <X className="h-4 w-4" />
                </button>
              </div>

              <form onSubmit={handleCreateMail} className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Email Address
                  </label>
                  <input
                    type="email"
                    required
                    placeholder="contact@yourdomain.com"
                    value={formData.email}
                    onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Mailbox Password
                  </label>
                  <input
                    type="password"
                    required
                    placeholder="••••••••••••"
                    value={formData.password}
                    onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Quota (MB)
                  </label>
                  <input
                    type="number"
                    min="50"
                    max="50000"
                    value={formData.quota_mb}
                    onChange={(e) => setFormData({ ...formData, quota_mb: parseInt(e.target.value) || 500 })}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                  />
                </div>

                <div className="flex justify-end gap-3 pt-4 border-t border-white/10">
                  <button
                    type="button"
                    onClick={() => setShowAddModal(false)}
                    className="rounded-xl border border-white/10 px-4 py-2 text-xs font-medium text-slate-300 hover:bg-white/5"
                  >
                    Cancel
                  </button>
                  <button
                    type="submit"
                    disabled={actionLoading}
                    className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-xs font-semibold text-white shadow-md hover:opacity-90 disabled:opacity-50"
                  >
                    {actionLoading ? 'Creating...' : 'Create Mailbox'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </Shell>
  );
}
