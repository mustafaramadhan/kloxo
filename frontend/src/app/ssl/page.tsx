'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, SslCertItem, DomainItem } from '@/lib/api';
import { Lock, Plus, RefreshCw, Trash2, X, AlertCircle, CheckCircle2, ShieldCheck, Key, FileCheck } from 'lucide-react';

export default function SslPage() {
  const [certs, setCerts] = useState<SslCertItem[]>([]);
  const [domains, setDomains] = useState<DomainItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Modal
  const [showAddModal, setShowAddModal] = useState(false);
  const [selectedDomain, setSelectedDomain] = useState('');
  const [certname, setCertname] = useState('');
  const [isSelfSigned, setIsSelfSigned] = useState(true);
  const [crtText, setCrtText] = useState('');
  const [keyText, setKeyText] = useState('');

  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    try {
      const [certRes, domRes] = await Promise.all([
        apiFetch<{ certificates: SslCertItem[] }>('/ssl/certificates'),
        apiFetch<{ domains: DomainItem[] }>('/domains'),
      ]);
      setCerts(certRes.certificates);
      setDomains(domRes.domains);
      if (domRes.domains.length > 0 && !selectedDomain) {
        setSelectedDomain(domRes.domains[0].domain);
      }
    } catch (err: any) {
      setError(err.message || 'Failed to fetch SSL certificates');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchData();
  }, []);

  const handleInstallCert = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedDomain.trim()) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/ssl/certificates', {
        method: 'POST',
        body: JSON.stringify({
          domain: selectedDomain.trim(),
          certname: certname.trim() || selectedDomain.trim(),
          generate_self_signed: isSelfSigned,
          crt: crtText,
          key: keyText,
        }),
      });
      setMessage({ text: res.message || 'SSL certificate installed successfully', success: true });
      setShowAddModal(false);
      setCrtText('');
      setKeyText('');
      await fetchData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to configure SSL certificate', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteCert = async (id: string) => {
    if (!confirm(`Are you sure you want to delete SSL certificate "${id}"?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/ssl/certificates', {
        method: 'DELETE',
        body: JSON.stringify({ id }),
      });
      setMessage({ text: res.message || 'Certificate deleted successfully', success: true });
      await fetchData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete certificate', success: false });
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
              <Lock className="h-6 w-6 text-sky-400" />
              SSL / TLS Certificates
            </h1>
            <p className="text-sm text-slate-400">
              Manage HTTPS security, SSL certificates, and TLS cryptographic keys
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
              Install / Generate SSL
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

        {/* Certificates Table */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Loading certificates...</div>
          ) : certs.length === 0 ? (
            <div className="p-16 text-center">
              <ShieldCheck className="mx-auto h-12 w-12 text-slate-600 mb-3" />
              <p className="text-slate-400 font-medium">No SSL certificates configured yet</p>
              <p className="text-xs text-slate-500 mt-1">
                Click &quot;Install / Generate SSL&quot; to secure your domains with HTTPS.
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Certificate Name</th>
                    <th className="px-6 py-4">Domain</th>
                    <th className="px-6 py-4">Status</th>
                    <th className="px-6 py-4">Files</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {certs.map((c) => (
                    <tr key={c.id} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-medium text-white flex items-center gap-2">
                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                          <Lock className="h-4 w-4" />
                        </div>
                        <span className="font-mono text-sm">{c.certname}</span>
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">{c.domain}</td>
                      <td className="px-6 py-4">
                        <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-xs font-medium text-emerald-400">
                          <CheckCircle2 className="h-3 w-3" /> Active
                        </span>
                      </td>
                      <td className="px-6 py-4">
                        <div className="flex items-center gap-2 text-xs text-slate-400">
                          <span className={`inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[11px] ${c.has_crt ? 'bg-sky-500/10 text-sky-400 border border-sky-500/20' : 'bg-slate-800 text-slate-500'}`}>
                            <FileCheck className="h-3 w-3" /> CRT
                          </span>
                          <span className={`inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[11px] ${c.has_key ? 'bg-indigo-500/10 text-indigo-400 border border-indigo-500/20' : 'bg-slate-800 text-slate-500'}`}>
                            <Key className="h-3 w-3" /> KEY
                          </span>
                        </div>
                      </td>
                      <td className="px-6 py-4 text-right">
                        <button
                          onClick={() => handleDeleteCert(c.id)}
                          disabled={actionLoading}
                          className="inline-flex items-center p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition-colors"
                          title="Delete Certificate"
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

      {/* Install SSL Modal */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
          <div className="w-full max-w-lg rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Lock className="h-5 w-5 text-sky-400" />
                Configure SSL / TLS Certificate
              </h2>
              <button onClick={() => setShowAddModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleInstallCert} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Target Domain
                </label>
                {domains.length > 0 ? (
                  <select
                    value={selectedDomain}
                    onChange={(e) => setSelectedDomain(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                  >
                    {domains.map((d) => (
                      <option key={d.domain} value={d.domain}>
                        {d.domain}
                      </option>
                    ))}
                  </select>
                ) : (
                  <input
                    type="text"
                    required
                    placeholder="e.g. mysite.com"
                    value={selectedDomain}
                    onChange={(e) => setSelectedDomain(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                  />
                )}
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Certificate Name / Label
                </label>
                <input
                  type="text"
                  placeholder="e.g. cert_mysite"
                  value={certname}
                  onChange={(e) => setCertname(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                />
              </div>

              {/* Mode Selector */}
              <div className="flex gap-4 p-3 rounded-2xl bg-slate-950/60 border border-white/5">
                <label className="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                  <input
                    type="radio"
                    checked={isSelfSigned}
                    onChange={() => setIsSelfSigned(true)}
                    className="text-sky-500 focus:ring-sky-500"
                  />
                  <span>1-Click Self-Signed (Instant RSA 2048)</span>
                </label>
                <label className="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                  <input
                    type="radio"
                    checked={!isSelfSigned}
                    onChange={() => setIsSelfSigned(false)}
                    className="text-sky-500 focus:ring-sky-500"
                  />
                  <span>Upload Custom CRT & KEY</span>
                </label>
              </div>

              {!isSelfSigned && (
                <div className="space-y-3">
                  <div>
                    <label className="block text-xs font-medium text-slate-400 mb-1">Certificate (.CRT)</label>
                    <textarea
                      rows={4}
                      placeholder="-----BEGIN CERTIFICATE-----..."
                      value={crtText}
                      onChange={(e) => setCrtText(e.target.value)}
                      className="w-full rounded-xl border border-white/10 bg-slate-950 p-3 font-mono text-xs text-white placeholder-slate-600 focus:border-sky-500 focus:outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-xs font-medium text-slate-400 mb-1">Private Key (.KEY)</label>
                    <textarea
                      rows={4}
                      placeholder="-----BEGIN PRIVATE KEY-----..."
                      value={keyText}
                      onChange={(e) => setKeyText(e.target.value)}
                      className="w-full rounded-xl border border-white/10 bg-slate-950 p-3 font-mono text-xs text-white placeholder-slate-600 focus:border-sky-500 focus:outline-none"
                    />
                  </div>
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
                  {actionLoading ? 'Installing...' : 'Install SSL Certificate'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </Shell>
  );
}
