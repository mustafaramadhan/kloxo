'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, SslCertItem, DomainItem } from '@/lib/api';
import {
  Lock,
  Plus,
  RefreshCw,
  Trash2,
  X,
  AlertCircle,
  CheckCircle2,
  ShieldCheck,
  Key,
  FileCheck,
  Globe,
  Sparkles,
  Zap,
  RotateCcw,
  Clock,
  ExternalLink,
  AlertTriangle,
} from 'lucide-react';

export default function SslPage() {
  const [certs, setCerts] = useState<SslCertItem[]>([]);
  const [domains, setDomains] = useState<DomainItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Modal State
  const [showAddModal, setShowAddModal] = useState(false);
  const [modalMode, setModalMode] = useState<'letsencrypt' | 'self_signed' | 'custom'>('letsencrypt');
  const [selectedDomain, setSelectedDomain] = useState('');
  const [certname, setCertname] = useState('');

  // Let's Encrypt specific
  const [includeWww, setIncludeWww] = useState(false);
  const [isWildcard, setIsWildcard] = useState(false);
  const [contactEmail, setContactEmail] = useState('admin@' + (domains[0]?.domain || 'domain.com'));
  const [forceHttps, setForceHttps] = useState(true);

  // Custom cert specific
  const [crtText, setCrtText] = useState('');
  const [keyText, setKeyText] = useState('');

  const [actionLoading, setActionLoading] = useState(false);
  const [renewingDomain, setRenewingDomain] = useState<string | null>(null);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);
  const [modalError, setModalError] = useState<string | null>(null);

  const fetchData = async () => {
    setLoading(true);
    setError(null);
    try {
      const [certRes, domRes] = await Promise.all([
        apiFetch<{ certificates: SslCertItem[] }>('/ssl/certificates'),
        apiFetch<{ domains: DomainItem[] }>('/domains'),
      ]);
      setCerts(certRes.certificates || []);
      setDomains(domRes.domains || []);
      if (domRes.domains?.length > 0 && !selectedDomain) {
        setSelectedDomain(domRes.domains[0].domain);
        setContactEmail('admin@' + domRes.domains[0].domain);
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

  const handleDomainChange = (dom: string) => {
    setSelectedDomain(dom);
    setCertname(`cert_${dom.replace(/\./g, '_')}`);
    setContactEmail(`admin@${dom}`);
  };

  const handleInstallCert = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedDomain.trim()) return;
    setActionLoading(true);
    setModalError(null);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/ssl/certificates', {
        method: 'POST',
        body: JSON.stringify({
          mode: modalMode,
          domain: selectedDomain.trim(),
          certname: certname.trim() || `cert_${selectedDomain.trim()}`,
          include_www: includeWww,
          is_wildcard: isWildcard,
          contact_email: contactEmail.trim(),
          force_https: forceHttps,
          crt: crtText,
          key: keyText,
        }),
      });
      setMessage({ text: res.message || 'SSL certificate configured successfully', success: true });
      setShowAddModal(false);
      setModalError(null);
      setCrtText('');
      setKeyText('');
      await fetchData();
    } catch (err: any) {
      setModalError(err.message || 'Failed to configure SSL certificate');
    } finally {
      setActionLoading(false);
    }
  };

  const handleRenewCert = async (domain: string) => {
    setRenewingDomain(domain);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/ssl/renew', {
        method: 'POST',
        body: JSON.stringify({ domain }),
      });
      setMessage({ text: res.message || `SSL renewed for ${domain}`, success: true });
      await fetchData();
    } catch (err: any) {
      setMessage({ text: err.message || `Failed to renew SSL for ${domain}`, success: false });
    } finally {
      setRenewingDomain(null);
    }
  };

  const handleDeleteCert = async (id: string, domain: string) => {
    if (!confirm(`Are you sure you want to delete SSL certificate for "${domain}"? HTTPS will be disabled for this host.`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const queryParams = new URLSearchParams({ id, domain });
      const res = await apiFetch<{ success: boolean; message: string }>(`/ssl/certificates?${queryParams.toString()}`, {
        method: 'DELETE',
        body: JSON.stringify({ id, domain }),
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
            <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2.5">
              <Lock className="h-6 w-6 text-sky-400" />
              SSL & Let&apos;s Encrypt Manager
            </h1>
            <p className="text-xs text-slate-400 mt-1">
              Issue free Let&apos;s Encrypt wildcard certificates, manage auto-renewals, and upload custom TLS keys
            </p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={fetchData}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-medium text-slate-200 hover:bg-white/10 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-3.5 w-3.5 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </button>
            <button
              onClick={() => {
                setModalMode('letsencrypt');
                setShowAddModal(true);
              }}
              className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-emerald-500/20 hover:from-emerald-400 hover:to-teal-500 transition-all"
            >
              <Sparkles className="h-4 w-4" />
              1-Click Let&apos;s Encrypt
            </button>
            <button
              onClick={() => {
                setModalMode('custom');
                setShowAddModal(true);
              }}
              className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" />
              Install / Upload SSL
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
                Click &quot;1-Click Let&apos;s Encrypt&quot; to issue a free, trusted TLS certificate automatically.
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Domain / Common Name</th>
                    <th className="px-6 py-4">Issuer Authority</th>
                    <th className="px-6 py-4">Expiry & Auto-Renew</th>
                    <th className="px-6 py-4">Files</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {certs.map((c) => {
                    const isLE = c.is_letsencrypt || c.certname.toLowerCase().includes('letsencrypt') || c.issuer?.includes("Let's Encrypt");
                    const isRenewing = renewingDomain === c.domain;

                    return (
                      <tr key={c.id} className="hover:bg-white/[0.02] transition-colors">
                        <td className="px-6 py-4 font-medium text-white">
                          <div className="flex items-center gap-3">
                            <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400">
                              <Lock className="h-4 w-4" />
                            </div>
                            <div>
                              <div className="flex items-center gap-2">
                                <span className="font-mono text-sm block">{c.domain}</span>
                                {(c.is_wildcard || c.domain.startsWith('*.')) && (
                                  <span className="rounded bg-purple-500/20 border border-purple-500/30 px-1.5 py-0.2 text-[10px] font-bold text-purple-300">
                                    Wildcard (*.{c.domain})
                                  </span>
                                )}
                              </div>
                              <span className="text-[11px] text-slate-500">{c.certname}</span>
                            </div>
                          </div>
                        </td>

                        <td className="px-6 py-4">
                          {isLE ? (
                            <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-xs font-semibold text-emerald-300">
                              <Sparkles className="h-3 w-3 text-emerald-400" /> Let&apos;s Encrypt Authority
                            </span>
                          ) : (
                            <span className="inline-flex items-center gap-1.5 rounded-full bg-slate-800 border border-slate-700 px-2.5 py-0.5 text-xs font-semibold text-slate-300">
                              {c.issuer || 'Custom / Self-Signed'}
                            </span>
                          )}
                        </td>

                        <td className="px-6 py-4">
                          <div className="flex flex-col gap-1">
                            <span className="text-xs font-mono text-slate-300 flex items-center gap-1">
                              <Clock className="h-3 w-3 text-slate-500" />
                              {c.expires_at || '90 days validity'}
                            </span>
                            <span className="text-[10px] text-emerald-400 font-medium">
                              ● Auto-Renew Enabled
                            </span>
                          </div>
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
                          <div className="flex items-center justify-end gap-2">
                            {isLE && (
                              <button
                                onClick={() => handleRenewCert(c.domain)}
                                disabled={isRenewing || actionLoading}
                                title="Force Renew Let's Encrypt Certificate"
                                className="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 rounded-lg hover:bg-emerald-500/20 transition-all disabled:opacity-50"
                              >
                                <RotateCcw className={`h-3 w-3 ${isRenewing ? 'animate-spin' : ''}`} />
                                {isRenewing ? 'Renewing...' : 'Renew'}
                              </button>
                            )}

                            <button
                              onClick={() => handleDeleteCert(c.id, c.domain)}
                              disabled={actionLoading}
                              className="inline-flex items-center p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition-colors"
                              title="Delete Certificate"
                            >
                              <Trash2 className="h-4 w-4" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>

      {/* Install SSL Modal */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
          <div className="w-full max-w-xl rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Lock className="h-5 w-5 text-sky-400" />
                Issue & Install SSL Certificate
              </h2>
              <button onClick={() => setShowAddModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            {modalError && (
              <div className="mt-3 flex items-start gap-2.5 rounded-2xl border border-rose-500/30 bg-rose-500/10 p-3.5 text-xs text-rose-300">
                <AlertCircle className="h-4 w-4 flex-shrink-0 text-rose-400 mt-0.5" />
                <div className="flex-1">
                  <span className="font-semibold block mb-0.5">Issuance Error</span>
                  <span className="leading-relaxed">{modalError}</span>
                </div>
                <button
                  type="button"
                  onClick={() => setModalError(null)}
                  className="text-rose-400 hover:text-white"
                >
                  <X className="h-4 w-4" />
                </button>
              </div>
            )}

            {/* Mode Tabs */}
            <div className="grid grid-cols-3 gap-2 p-1.5 bg-slate-950 rounded-2xl border border-white/5 mt-4">
              <button
                type="button"
                onClick={() => setModalMode('letsencrypt')}
                className={`py-2 text-xs font-semibold rounded-xl transition-all flex items-center justify-center gap-1.5 ${
                  modalMode === 'letsencrypt'
                    ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/20'
                    : 'text-slate-400 hover:text-white'
                }`}
              >
                <Sparkles className="h-3.5 w-3.5" /> Let&apos;s Encrypt (Free)
              </button>
              <button
                type="button"
                onClick={() => setModalMode('self_signed')}
                className={`py-2 text-xs font-semibold rounded-xl transition-all flex items-center justify-center gap-1.5 ${
                  modalMode === 'self_signed'
                    ? 'bg-sky-500 text-white shadow-md shadow-sky-500/20'
                    : 'text-slate-400 hover:text-white'
                }`}
              >
                <Zap className="h-3.5 w-3.5" /> Self-Signed
              </button>
              <button
                type="button"
                onClick={() => setModalMode('custom')}
                className={`py-2 text-xs font-semibold rounded-xl transition-all flex items-center justify-center gap-1.5 ${
                  modalMode === 'custom'
                    ? 'bg-indigo-500 text-white shadow-md shadow-indigo-500/20'
                    : 'text-slate-400 hover:text-white'
                }`}
              >
                <Key className="h-3.5 w-3.5" /> Custom CRT & KEY
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
                    onChange={(e) => handleDomainChange(e.target.value)}
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
                    onChange={(e) => handleDomainChange(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                  />
                )}
              </div>

              {/* Let's Encrypt specific fields */}
              {modalMode === 'letsencrypt' && (
                <div className="space-y-3.5 p-4 rounded-2xl bg-emerald-500/5 border border-emerald-500/20">
                  <div className="flex items-center gap-2 text-xs font-semibold text-emerald-400">
                    <Sparkles className="h-4 w-4" />
                    Automated ACME HTTP-01 Let&apos;s Encrypt Certificate
                  </div>

                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Certbot Notification Email</label>
                    <input
                      type="email"
                      required
                      value={contactEmail}
                      onChange={(e) => setContactEmail(e.target.value)}
                      placeholder="admin@example.com"
                      className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-xs text-white focus:border-emerald-500 focus:outline-none font-mono"
                    />
                  </div>

                  <div className="space-y-2.5 pt-1">
                    <label className="flex items-start gap-2.5 p-3 rounded-xl border border-purple-500/20 bg-purple-500/5 cursor-pointer hover:bg-purple-500/10 transition-colors">
                      <input
                        type="checkbox"
                        checked={isWildcard}
                        onChange={(e) => setIsWildcard(e.target.checked)}
                        className="mt-0.5 rounded border-white/10 bg-slate-950 text-purple-500 focus:ring-purple-500"
                      />
                      <div>
                        <div className="flex items-center gap-2">
                          <span className="text-xs font-semibold text-white">Wildcard SSL Certificate (*.{selectedDomain})</span>
                          <span className="rounded bg-purple-500/20 border border-purple-500/30 px-1.5 py-0.5 text-[10px] font-bold text-purple-300">
                            Wildcard
                          </span>
                        </div>
                        <p className="text-[11px] text-slate-400 mt-0.5">
                          Secures root domain ({selectedDomain}) and ALL subdomains (*.{selectedDomain}, e.g. api, app, blog, mail).
                        </p>
                      </div>
                    </label>

                    {isWildcard && (
                      <div className="rounded-xl border border-amber-500/30 bg-amber-500/10 p-3 text-xs text-amber-300">
                        <div className="flex items-center gap-1.5 font-bold mb-1">
                          <AlertTriangle className="h-3.5 w-3.5 text-amber-400" />
                          <span>Wildcard DNS Requirement (DNS-01)</span>
                        </div>
                        <p className="text-[11px] leading-relaxed text-amber-200/90">
                          Let&apos;s Encrypt requires a DNS TXT challenge (_acme-challenge) for wildcard certificates. If your domain ({selectedDomain}) uses Cloudflare or external nameservers, you must add the TXT record there, or uncheck Wildcard to issue a standard certificate instantly via automated HTTP-01 webroot challenge.
                        </p>
                      </div>
                    )}

                    <label className="flex items-center gap-2.5 text-xs text-slate-300 cursor-pointer">
                      <input
                        type="checkbox"
                        checked={includeWww}
                        onChange={(e) => setIncludeWww(e.target.checked)}
                        className="rounded border-white/10 bg-slate-950 text-emerald-500 focus:ring-emerald-500"
                      />
                      <span>Include <code className="text-emerald-300">www.{selectedDomain}</code> alias in certificate</span>
                    </label>

                    <label className="flex items-center gap-2.5 text-xs text-slate-300 cursor-pointer">
                      <input
                        type="checkbox"
                        checked={forceHttps}
                        onChange={(e) => setForceHttps(e.target.checked)}
                        className="rounded border-white/10 bg-slate-950 text-emerald-500 focus:ring-emerald-500"
                      />
                      <span>Enforce automatic HTTP to HTTPS redirect in Web Server (Nginx/Apache)</span>
                    </label>
                  </div>
                </div>
              )}

              {/* Custom cert fields */}
              {modalMode === 'custom' && (
                <div className="space-y-3">
                  <div>
                    <label className="block text-xs font-medium text-slate-400 mb-1">Certificate (.CRT / PEM)</label>
                    <textarea
                      rows={4}
                      required
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
                      required
                      placeholder="-----BEGIN PRIVATE KEY-----..."
                      value={keyText}
                      onChange={(e) => setKeyText(e.target.value)}
                      className="w-full rounded-xl border border-white/10 bg-slate-950 p-3 font-mono text-xs text-white placeholder-slate-600 focus:border-sky-500 focus:outline-none"
                    />
                  </div>
                </div>
              )}

              <div className="flex justify-end gap-3 pt-3 border-t border-white/5">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs font-medium text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className={`rounded-xl px-5 py-2 text-xs font-semibold text-white shadow-lg transition-all disabled:opacity-50 flex items-center gap-2 ${
                    modalMode === 'letsencrypt'
                      ? 'bg-gradient-to-r from-emerald-500 to-teal-600 shadow-emerald-500/20 hover:from-emerald-400 hover:to-teal-500'
                      : 'bg-gradient-to-r from-sky-500 to-indigo-600 shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500'
                  }`}
                >
                  {actionLoading ? (
                    <>
                      <RefreshCw className="h-3.5 w-3.5 animate-spin" />
                      {modalMode === 'letsencrypt' ? 'Issuing Let\'s Encrypt...' : 'Configuring SSL...'}
                    </>
                  ) : (
                    <>
                      <Lock className="h-3.5 w-3.5" />
                      {modalMode === 'letsencrypt' ? 'Issue Let\'s Encrypt Certificate' : 'Save & Activate SSL'}
                    </>
                  )}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </Shell>
  );
}
