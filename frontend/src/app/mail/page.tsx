'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, MailAccountItem } from '@/lib/api';
import { Mail, CheckCircle2, XCircle, RefreshCw, Plus, HardDrive, Inbox } from 'lucide-react';

export default function MailAccountsPage() {
  const [accounts, setAccounts] = useState<MailAccountItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchMail = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await apiFetch<{ accounts: MailAccountItem[] }>('/mail/accounts');
      setAccounts(data.accounts);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch mail accounts');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchMail();
  }, []);

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
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors"
            >
              <RefreshCw className="h-4 w-4" />
              Refresh
            </button>
          </div>
        </div>

        {error && (
          <div className="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">
            {error}
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
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </Shell>
  );
}
