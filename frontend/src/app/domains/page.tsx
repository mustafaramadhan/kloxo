'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { Globe, Plus, RefreshCw, ExternalLink, Trash2, X, AlertCircle, CheckCircle2 } from 'lucide-react';
import { apiFetch, DomainItem } from '@/lib/api';

export default function DomainsPage() {
  const [domains, setDomains] = useState<DomainItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddModal, setShowAddModal] = useState(false);
  const [newDomain, setNewDomain] = useState('');
  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const loadDomains = async () => {
    try {
      const res = await apiFetch<{ success: boolean; domains: DomainItem[] }>('/domains');
      setDomains(res.domains);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadDomains();
  }, []);

  const handleCreateDomain = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newDomain.trim()) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/domains', {
        method: 'POST',
        body: JSON.stringify({ domain: newDomain.trim() }),
      });
      setMessage({ text: res.message || 'Domain created successfully', success: true });
      setShowAddModal(false);
      setNewDomain('');
      await loadDomains();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create domain', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteDomain = async (domain: string) => {
    if (!confirm(`Are you sure you want to delete domain "${domain}" and its configuration?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/domains', {
        method: 'DELETE',
        body: JSON.stringify({ domain }),
      });
      setMessage({ text: res.message || 'Domain deleted successfully', success: true });
      await loadDomains();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete domain', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <Shell>
      <div className="space-y-6">
        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white md:text-3xl">Domains & Virtual Hosts</h1>
            <p className="text-sm text-slate-400">Configure websites, docroots, and Nginx virtual hosts</p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={loadDomains}
              disabled={loading}
              className="flex items-center gap-2 rounded-xl border border-white/10 bg-slate-900/60 px-4 py-2 text-xs font-medium text-slate-200 hover:bg-white/10 transition-all"
            >
              <RefreshCw className={`h-3.5 w-3.5 ${loading ? 'animate-spin text-sky-400' : ''}`} />
              Refresh
            </button>
            <button
              onClick={() => setShowAddModal(true)}
              className="flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" /> Add Domain
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

        {/* Domains Table or Empty State */}
        {domains.length === 0 && !loading ? (
          <div className="glass-panel rounded-2xl p-12 text-center">
            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-sky-500/10 border border-sky-500/20 text-sky-400">
              <Globe className="h-8 w-8" />
            </div>
            <h3 className="text-lg font-bold text-white">No Domains Added Yet</h3>
            <p className="mt-1 text-sm text-slate-400 max-w-sm mx-auto">
              You haven&apos;t added any virtual hosts or domains to this server cluster.
            </p>
            <button
              onClick={() => setShowAddModal(true)}
              className="mt-6 inline-flex items-center gap-2 rounded-xl bg-sky-500/10 border border-sky-500/20 px-4 py-2 text-sm font-semibold text-sky-400 hover:bg-sky-500/20 transition-all"
            >
              <Plus className="h-4 w-4" /> Add Your First Domain
            </button>
          </div>
        ) : (
          <div className="glass-panel overflow-hidden rounded-2xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase tracking-wider text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Domain Name</th>
                    <th className="px-6 py-4">Owner</th>
                    <th className="px-6 py-4">Web Server</th>
                    <th className="px-6 py-4">Status</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {domains.map((dom) => (
                    <tr key={dom.domain} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-2 font-semibold text-white">
                          <Globe className="h-4 w-4 text-sky-400" />
                          <a
                            href={`http://${dom.domain}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="hover:underline flex items-center gap-1 text-sky-300"
                          >
                            {dom.domain} <ExternalLink className="h-3 w-3 text-slate-500" />
                          </a>
                        </div>
                      </td>
                      <td className="px-6 py-4 text-slate-400">{dom.owner}</td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">{dom.web_server}</td>
                      <td className="px-6 py-4">
                        <span
                          className={`inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold uppercase tracking-wider ${
                            dom.status
                              ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                              : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                          }`}
                        >
                          {dom.status ? 'Active' : 'Disabled'}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-right">
                        <button
                          onClick={() => handleDeleteDomain(dom.domain)}
                          disabled={actionLoading}
                          title="Delete Domain"
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
          </div>
        )}

        {/* Add Domain Modal */}
        {showAddModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-md p-4">
            <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-950 p-6 shadow-2xl">
              <div className="flex items-center justify-between pb-4 border-b border-white/10 mb-4">
                <h3 className="text-lg font-bold text-white flex items-center gap-2">
                  <Globe className="h-5 w-5 text-sky-400" />
                  Add New Domain
                </h3>
                <button
                  onClick={() => setShowAddModal(false)}
                  className="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white"
                >
                  <X className="h-4 w-4" />
                </button>
              </div>

              <form onSubmit={handleCreateDomain} className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Domain FQDN
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="example.com"
                    value={newDomain}
                    onChange={(e) => setNewDomain(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                  />
                  <p className="mt-1 text-xs text-slate-400">
                    Will configure docroot at <code className="text-sky-300">/home/admin/[domain]/public_html</code>
                  </p>
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
                    {actionLoading ? 'Creating...' : 'Create Domain'}
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
