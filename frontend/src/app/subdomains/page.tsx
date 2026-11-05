'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, SubdomainItem, DomainItem } from '@/lib/api';
import { Network, Plus, RefreshCw, Trash2, X, AlertCircle, CheckCircle2, Globe, ExternalLink } from 'lucide-react';

export default function SubdomainsPage() {
  const [subdomains, setSubdomains] = useState<SubdomainItem[]>([]);
  const [domains, setDomains] = useState<DomainItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Modal
  const [showAddModal, setShowAddModal] = useState(false);
  const [prefix, setPrefix] = useState('');
  const [parentDomain, setParentDomain] = useState('');

  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    try {
      const [subRes, domRes] = await Promise.all([
        apiFetch<{ subdomains: SubdomainItem[] }>('/subdomains'),
        apiFetch<{ domains: DomainItem[] }>('/domains'),
      ]);
      setSubdomains(subRes.subdomains);
      setDomains(domRes.domains);
      if (domRes.domains.length > 0 && !parentDomain) {
        setParentDomain(domRes.domains[0].domain);
      }
    } catch (err: any) {
      setError(err.message || 'Failed to fetch subdomains');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleCreateSubdomain = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!prefix.trim() || !parentDomain) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/subdomains', {
        method: 'POST',
        body: JSON.stringify({
          prefix: prefix.trim(),
          parent_domain: parentDomain,
        }),
      });
      setMessage({ text: res.message || 'Subdomain created successfully', success: true });
      setShowAddModal(false);
      setPrefix('');
      await fetchData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create subdomain', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteSubdomain = async (subdomain: string) => {
    if (!confirm(`Are you sure you want to delete subdomain "${subdomain}" and its vhost config?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/subdomains', {
        method: 'DELETE',
        body: JSON.stringify({ subdomain }),
      });
      setMessage({ text: res.message || 'Subdomain deleted successfully', success: true });
      await fetchData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete subdomain', success: false });
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
              <Network className="h-6 w-6 text-sky-400" />
              Subdomains Management
            </h1>
            <p className="text-sm text-slate-400">
              Create and manage branch domains, isolated document roots, and web routing
            </p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={fetchData}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </button>
            <button
              onClick={() => setShowAddModal(true)}
              className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" />
              Add Subdomain
            </button>
          </div>
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

        {error && (
          <div className="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">
            {error}
          </div>
        )}

        {/* Subdomains Table */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Loading subdomains...</div>
          ) : subdomains.length === 0 ? (
            <div className="p-16 text-center">
              <Network className="mx-auto h-12 w-12 text-slate-600 mb-3" />
              <p className="text-slate-400 font-medium">No subdomains created yet</p>
              <p className="text-xs text-slate-500 mt-1">
                Click &quot;Add Subdomain&quot; to route blog, app, or api subdomains.
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Subdomain</th>
                    <th className="px-6 py-4">Parent Domain</th>
                    <th className="px-6 py-4">Document Root</th>
                    <th className="px-6 py-4">Status</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {subdomains.map((s) => (
                    <tr key={s.subdomain} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-medium text-white flex items-center gap-2">
                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-400">
                          <Network className="h-4 w-4" />
                        </div>
                        <a
                          href={`http://${s.subdomain}`}
                          target="_blank"
                          rel="noreferrer"
                          className="font-mono text-sm text-sky-400 hover:underline flex items-center gap-1"
                        >
                          {s.subdomain}
                          <ExternalLink className="h-3 w-3 text-slate-500" />
                        </a>
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">{s.parent_domain}</td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-400">{s.docroot}</td>
                      <td className="px-6 py-4">
                        <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-xs font-medium text-emerald-400">
                          <CheckCircle2 className="h-3 w-3" /> Active
                        </span>
                      </td>
                      <td className="px-6 py-4 text-right">
                        <button
                          onClick={() => handleDeleteSubdomain(s.subdomain)}
                          disabled={actionLoading}
                          className="inline-flex items-center p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition-colors"
                          title="Delete Subdomain"
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
      </div>

      {/* Add Subdomain Modal */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Network className="h-5 w-5 text-sky-400" />
                Add New Subdomain
              </h2>
              <button onClick={() => setShowAddModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreateSubdomain} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Subdomain Prefix
                </label>
                <div className="flex items-center">
                  <input
                    type="text"
                    required
                    placeholder="blog or api"
                    value={prefix}
                    onChange={(e) => setPrefix(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white focus:border-sky-500 focus:outline-none"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Parent Domain
                </label>
                {domains.length > 0 ? (
                  <select
                    value={parentDomain}
                    onChange={(e) => setParentDomain(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                  >
                    {domains.map((d) => (
                      <option key={d.domain} value={d.domain}>
                        .{d.domain}
                      </option>
                    ))}
                  </select>
                ) : (
                  <input
                    type="text"
                    required
                    placeholder="example.com"
                    value={parentDomain}
                    onChange={(e) => setParentDomain(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                  />
                )}
              </div>

              {prefix && (
                <div className="p-3 rounded-xl bg-slate-950 border border-white/5 text-xs text-slate-300">
                  Full Host: <span className="font-mono text-sky-400">{prefix}.{parentDomain}</span>
                </div>
              )}

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Creating...' : 'Create Subdomain'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </Shell>
  );
}
