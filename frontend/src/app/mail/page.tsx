'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, MailAccountItem, MailForwarderItem } from '@/lib/api';
import { Mail, CheckCircle2, XCircle, RefreshCw, Plus, Trash2, X, AlertCircle, Inbox, Send, ArrowRight } from 'lucide-react';

export default function MailAccountsPage() {
  const [activeTab, setActiveTab] = useState<'accounts' | 'forwarders'>('accounts');
  const [accounts, setAccounts] = useState<MailAccountItem[]>([]);
  const [forwarders, setForwarders] = useState<MailForwarderItem[]>([]);
  const [loading, setLoading] = useState(true);

  // Mailbox Modal
  const [showAddMailboxModal, setShowAddMailboxModal] = useState(false);
  const [formData, setFormData] = useState({ email: '', password: '', quota_mb: 500 });

  // Forwarder Modal
  const [showAddForwarderModal, setShowAddForwarderModal] = useState(false);
  const [fwdSource, setFwdSource] = useState('');
  const [fwdDest, setFwdDest] = useState('');

  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchMailData = async () => {
    setLoading(true);
    try {
      const [accRes, fwdRes] = await Promise.all([
        apiFetch<{ accounts: MailAccountItem[] }>('/mail/accounts'),
        apiFetch<{ forwarders: MailForwarderItem[] }>('/mail/forwarders'),
      ]);
      setAccounts(accRes.accounts);
      setForwarders(fwdRes.forwarders);
    } catch (err: any) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchMailData();
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
      setShowAddMailboxModal(false);
      setFormData({ email: '', password: '', quota_mb: 500 });
      await fetchMailData();
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
      await fetchMailData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete mailbox', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleCreateForwarder = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!fwdSource.trim() || !fwdDest.trim()) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/mail/forwarders', {
        method: 'POST',
        body: JSON.stringify({ source: fwdSource.trim(), destination: fwdDest.trim() }),
      });
      setMessage({ text: res.message || 'Forwarder created', success: true });
      setShowAddForwarderModal(false);
      setFwdSource('');
      setFwdDest('');
      await fetchMailData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create forwarder', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteForwarder = async (id: string) => {
    if (!confirm(`Are you sure you want to delete this email forwarder?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      await apiFetch('/mail/forwarders', {
        method: 'DELETE',
        body: JSON.stringify({ id }),
      });
      setMessage({ text: 'Forwarder deleted', success: true });
      await fetchMailData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete forwarder', success: false });
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
              Mail Accounts & Routing
            </h1>
            <p className="text-sm text-slate-400">
              Manage custom domain mailboxes, storage quotas, and email forwarding rules
            </p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={fetchMailData}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </button>
            {activeTab === 'accounts' ? (
              <button
                onClick={() => setShowAddMailboxModal(true)}
                className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
              >
                <Plus className="h-4 w-4" />
                Create Mailbox
              </button>
            ) : (
              <button
                onClick={() => setShowAddForwarderModal(true)}
                className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-purple-500/20 hover:from-purple-400 hover:to-indigo-500 transition-all"
              >
                <Plus className="h-4 w-4" />
                Add Forwarder
              </button>
            )}
          </div>
        </div>

        {/* Tab Switcher */}
        <div className="flex items-center gap-2 border-b border-white/5 pb-2">
          <button
            onClick={() => setActiveTab('accounts')}
            className={`inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all ${
              activeTab === 'accounts'
                ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30'
                : 'text-slate-400 hover:text-white hover:bg-white/5'
            }`}
          >
            <Inbox className="h-4 w-4" />
            Mailboxes ({accounts.length})
          </button>
          <button
            onClick={() => setActiveTab('forwarders')}
            className={`inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all ${
              activeTab === 'forwarders'
                ? 'bg-purple-500/20 text-purple-400 border border-purple-500/30'
                : 'text-slate-400 hover:text-white hover:bg-white/5'
            }`}
          >
            <Send className="h-4 w-4" />
            Forwarders ({forwarders.length})
          </button>
        </div>

        {/* Notifications */}
        {message && (
          <div
            className={`flex items-center justify-between rounded-2xl border p-4 text-sm ${
              message.success
                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-300'
                : 'border-rose-500/20 bg-rose-500/10 text-rose-300'
            }`}
          >
            <div className="flex items-center gap-2">
              {message.success ? (
                <CheckCircle2 className="h-4 w-4 flex-shrink-0" />
              ) : (
                <AlertCircle className="h-4 w-4 flex-shrink-0" />
              )}
              <span>{message.text}</span>
            </div>
            <button onClick={() => setMessage(null)} className="text-slate-400 hover:text-white">
              <X className="h-4 w-4" />
            </button>
          </div>
        )}

        {/* Tab 1: Mailboxes Table */}
        {activeTab === 'accounts' && (
          <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
            {loading ? (
              <div className="p-12 text-center text-slate-400 animate-pulse">Loading mail accounts...</div>
            ) : accounts.length === 0 ? (
              <div className="p-16 text-center">
                <Inbox className="mx-auto h-12 w-12 text-slate-600 mb-3" />
                <p className="text-slate-400 font-medium">No mail accounts configured</p>
                <p className="text-xs text-slate-500 mt-1">
                  Click &quot;Create Mailbox&quot; to set up your domain email addresses.
                </p>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm text-slate-300">
                  <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                    <tr>
                      <th className="px-6 py-4">Email Address</th>
                      <th className="px-6 py-4">Account Owner</th>
                      <th className="px-6 py-4">Disk Usage</th>
                      <th className="px-6 py-4">Status</th>
                      <th className="px-6 py-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-white/5">
                    {accounts.map((acc) => (
                      <tr key={acc.email} className="hover:bg-white/[0.02] transition-colors">
                        <td className="px-6 py-4 font-medium text-white flex items-center gap-2">
                          <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-400">
                            <Mail className="h-4 w-4" />
                          </div>
                          <span className="font-mono">{acc.email}</span>
                        </td>
                        <td className="px-6 py-4 text-slate-400">{acc.owner}</td>
                        <td className="px-6 py-4 font-mono text-xs text-slate-300">
                          {acc.used_mb} MB / {acc.quota_mb} MB
                        </td>
                        <td className="px-6 py-4">
                          <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-xs font-medium text-emerald-400">
                            <CheckCircle2 className="h-3 w-3" /> Active
                          </span>
                        </td>
                        <td className="px-6 py-4 text-right">
                          <button
                            onClick={() => handleDeleteMail(acc.email)}
                            disabled={actionLoading}
                            className="inline-flex items-center p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition-colors"
                            title="Delete Mailbox"
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
        )}

        {/* Tab 2: Forwarders Table */}
        {activeTab === 'forwarders' && (
          <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
            {loading ? (
              <div className="p-12 text-center text-slate-400 animate-pulse">Loading forwarders...</div>
            ) : forwarders.length === 0 ? (
              <div className="p-16 text-center">
                <Send className="mx-auto h-12 w-12 text-slate-600 mb-3" />
                <p className="text-slate-400 font-medium">No email forwarders configured</p>
                <p className="text-xs text-slate-500 mt-1">
                  Forward emails sent to your domain address directly to Gmail, Outlook, or another mailbox.
                </p>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm text-slate-300">
                  <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                    <tr>
                      <th className="px-6 py-4">Source Address</th>
                      <th className="px-6 py-4">Routing</th>
                      <th className="px-6 py-4">Destination Target</th>
                      <th className="px-6 py-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-white/5">
                    {forwarders.map((f) => (
                      <tr key={f.id} className="hover:bg-white/[0.02]">
                        <td className="px-6 py-4 font-mono font-medium text-white">{f.source}</td>
                        <td className="px-6 py-4">
                          <ArrowRight className="h-4 w-4 text-purple-400" />
                        </td>
                        <td className="px-6 py-4 font-mono text-purple-300">{f.destination}</td>
                        <td className="px-6 py-4 text-right">
                          <button
                            onClick={() => handleDeleteForwarder(f.id)}
                            disabled={actionLoading}
                            className="inline-flex items-center p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition-colors"
                            title="Delete Forwarder"
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
        )}
      </div>

      {/* Create Mailbox Modal */}
      {showAddMailboxModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Mail className="h-5 w-5 text-sky-400" />
                Create New Mailbox
              </h2>
              <button onClick={() => setShowAddMailboxModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreateMail} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Email Address
                </label>
                <input
                  type="email"
                  required
                  placeholder="contact@yourdomain.com"
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Password
                </label>
                <input
                  type="password"
                  required
                  placeholder="Strong mailbox password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Mailbox Quota (MB)
                </label>
                <input
                  type="number"
                  value={formData.quota_mb}
                  onChange={(e) => setFormData({ ...formData, quota_mb: Number(e.target.value) })}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowAddMailboxModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Creating...' : 'Create Mailbox'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Add Forwarder Modal */}
      {showAddForwarderModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Send className="h-5 w-5 text-purple-400" />
                Add Email Forwarding Rule
              </h2>
              <button onClick={() => setShowAddForwarderModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreateForwarder} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Source Email (Address to forward from)
                </label>
                <input
                  type="email"
                  required
                  placeholder="info@yourdomain.com"
                  value={fwdSource}
                  onChange={(e) => setFwdSource(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-purple-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Destination Target (Forward to)
                </label>
                <input
                  type="email"
                  required
                  placeholder="personal@gmail.com"
                  value={fwdDest}
                  onChange={(e) => setFwdDest(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-purple-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowAddForwarderModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-purple-500/20 hover:from-purple-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Creating...' : 'Create Forwarder'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </Shell>
  );
}
