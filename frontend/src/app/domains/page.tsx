'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { Globe, Plus, RefreshCw, ExternalLink, ShieldCheck } from 'lucide-react';
import { apiFetch, DomainItem } from '@/lib/api';

export default function DomainsPage() {
  const [domains, setDomains] = useState<DomainItem[]>([]);
  const [loading, setLoading] = useState(true);

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

  return (
    <Shell>
      <div className="space-y-6">
        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white md:text-3xl">Domains & Virtual Hosts</h1>
            <p className="text-sm text-slate-400">Configure websites, DNS zones and web servers</p>
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
            <a
              href="/legacy/display.php?frm_action=addform&frm_o_cname=domain"
              target="_blank"
              rel="noopener noreferrer"
              className="flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" /> Add Domain
            </a>
          </div>
        </div>

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
            <a
              href="/legacy/display.php?frm_action=addform&frm_o_cname=domain"
              target="_blank"
              rel="noopener noreferrer"
              className="mt-6 inline-flex items-center gap-2 rounded-xl bg-sky-500/10 border border-sky-500/20 px-4 py-2 text-sm font-semibold text-sky-400 hover:bg-sky-500/20 transition-all"
            >
              <Plus className="h-4 w-4" /> Add Your First Domain
            </a>
          </div>
        ) : (
          <div className="glass-panel overflow-hidden rounded-2xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase tracking-wider text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Domain Name</th>
                    <th className="px-6 py-4">Client / Owner</th>
                    <th className="px-6 py-4">Web Server</th>
                    <th className="px-6 py-4">DNS Server</th>
                    <th className="px-6 py-4">Status</th>
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
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">{dom.dns_server}</td>
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
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}
      </div>
    </Shell>
  );
}
