'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, ClientItem } from '@/lib/api';
import { Users, CheckCircle2, XCircle, RefreshCw, Shield, Globe, HardDrive } from 'lucide-react';

export default function ClientsPage() {
  const [clients, setClients] = useState<ClientItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchClients = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await apiFetch<{ clients: ClientItem[] }>('/clients');
      setClients(data.clients);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch clients');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchClients();
  }, []);

  const getRoleBadge = (type: string) => {
    switch (type.toLowerCase()) {
      case 'admin':
      case 'superadmin':
        return 'bg-purple-500/10 text-purple-400 border-purple-500/20';
      case 'reseller':
        return 'bg-sky-500/10 text-sky-400 border-sky-500/20';
      default:
        return 'bg-slate-500/10 text-slate-300 border-slate-500/20';
    }
  };

  return (
    <Shell>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
              <Users className="h-6 w-6 text-sky-400" />
              Clients & Resellers
            </h1>
            <p className="text-sm text-slate-400">
              Manage panel user accounts, quotas, and privilege tiers
            </p>
          </div>
          <button
            onClick={fetchClients}
            className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors"
          >
            <RefreshCw className="h-4 w-4" />
            Refresh
          </button>
        </div>

        {error && (
          <div className="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">
            {error}
          </div>
        )}

        {/* Clients Table */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Loading clients...</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Username</th>
                    <th className="px-6 py-4">Account Type</th>
                    <th className="px-6 py-4">Contact Email</th>
                    <th className="px-6 py-4">Status</th>
                    <th className="px-6 py-4">Domains</th>
                    <th className="px-6 py-4">Disk Usage</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {clients.map((c) => (
                    <tr key={c.username} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-medium text-white flex items-center gap-3">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500/20 to-indigo-500/20 border border-sky-500/20 text-sky-400 font-bold">
                          {c.username[0]?.toUpperCase() || 'U'}
                        </div>
                        <div>
                          <span>{c.username}</span>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <span
                          className={`inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-semibold uppercase tracking-wider border ${getRoleBadge(
                            c.type
                          )}`}
                        >
                          <Shield className="h-3 w-3" />
                          {c.type}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-slate-400">{c.email || '—'}</td>
                      <td className="px-6 py-4">
                        <span
                          className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ${
                            c.status
                              ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                              : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                          }`}
                        >
                          {c.status ? (
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
                        {c.domains_used} / {c.domains_quota}
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">
                        {c.disk_used_mb} MB
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
