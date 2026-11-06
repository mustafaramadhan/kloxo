'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, DnsZoneItem, DnsRecordItem } from '@/lib/api';
import { Layers, Plus, RefreshCw, Trash2, X, AlertCircle, CheckCircle2, Settings2, FileText, ArrowLeft } from 'lucide-react';

export default function DnsPage() {
  const [zones, setZones] = useState<DnsZoneItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Add Zone Modal
  const [showAddZoneModal, setShowAddZoneModal] = useState(false);
  const [newDomain, setNewDomain] = useState('');
  const [newTtl, setNewTtl] = useState(3600);

  // Manage Records Modal
  const [activeZone, setActiveZone] = useState<string | null>(null);
  const [records, setRecords] = useState<DnsRecordItem[]>([]);
  const [recordsLoading, setRecordsLoading] = useState(false);
  const [recType, setRecType] = useState('A');
  const [recHostname, setRecHostname] = useState('@');
  const [recValue, setRecValue] = useState('');
  const [recPriority, setRecPriority] = useState(10);
  const [recTtl, setRecTtl] = useState(3600);

  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchZones = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await apiFetch<{ zones: DnsZoneItem[] }>('/dns/zones');
      setZones(data.zones);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch DNS zones');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchZones();
  }, []);

  const handleCreateZone = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newDomain.trim()) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/dns/zones', {
        method: 'POST',
        body: JSON.stringify({ domain: newDomain.trim(), ttl: newTtl }),
      });
      setMessage({ text: res.message || 'DNS Zone created successfully', success: true });
      setShowAddZoneModal(false);
      setNewDomain('');
      await fetchZones();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create DNS zone', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteZone = async (domain: string) => {
    if (!confirm(`Are you sure you want to delete the DNS zone for "${domain}"?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/dns/zones', {
        method: 'DELETE',
        body: JSON.stringify({ domain }),
      });
      setMessage({ text: res.message || 'DNS Zone deleted successfully', success: true });
      if (activeZone === domain) setActiveZone(null);
      await fetchZones();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete DNS zone', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const openManageRecords = async (domain: string) => {
    setActiveZone(domain);
    setRecordsLoading(true);
    try {
      const res = await apiFetch<{ records: DnsRecordItem[] }>(`/dns/records?zone=${encodeURIComponent(domain)}`);
      setRecords(res.records);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to fetch DNS records', success: false });
    } finally {
      setRecordsLoading(false);
    }
  };

  const handleAddRecord = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!activeZone || !recValue.trim()) return;
    setActionLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/dns/records', {
        method: 'POST',
        body: JSON.stringify({
          zone: activeZone,
          type: recType,
          hostname: recHostname.trim(),
          value: recValue.trim(),
          priority: recPriority,
          ttl: recTtl,
        }),
      });
      setMessage({ text: res.message || 'DNS record added', success: true });
      setRecHostname('@');
      setRecValue('');
      // Reload records
      const updated = await apiFetch<{ records: DnsRecordItem[] }>(`/dns/records?zone=${encodeURIComponent(activeZone)}`);
      setRecords(updated.records);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to add record', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteRecord = async (id: string) => {
    if (!activeZone) return;
    setActionLoading(true);
    try {
      await apiFetch('/dns/records', {
        method: 'DELETE',
        body: JSON.stringify({ zone: activeZone, id }),
      });
      const updated = await apiFetch<{ records: DnsRecordItem[] }>(`/dns/records?zone=${encodeURIComponent(activeZone)}`);
      setRecords(updated.records);
      setMessage({ text: 'Record deleted successfully', success: true });
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete record', success: false });
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
              <Layers className="h-6 w-6 text-sky-400" />
              DNS Zone & Record Management
            </h1>
            <p className="text-sm text-slate-400">
              Configure DNS zones, A, CNAME, MX, and TXT resource records
            </p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={fetchZones}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </button>
            <button
              onClick={() => setShowAddZoneModal(true)}
              className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" />
              Add DNS Zone
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

        {/* Zones Table */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Loading DNS zones...</div>
          ) : zones.length === 0 ? (
            <div className="p-16 text-center">
              <Layers className="mx-auto h-12 w-12 text-slate-600 mb-3" />
              <p className="text-slate-400 font-medium">No DNS zones configured</p>
              <p className="text-xs text-slate-500 mt-1">Add a domain to automatically generate DNS records.</p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Zone Domain</th>
                    <th className="px-6 py-4">SOA Nameserver</th>
                    <th className="px-6 py-4">Default TTL</th>
                    <th className="px-6 py-4">Type</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {zones.map((z) => (
                    <tr key={z.domain} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-medium text-white flex items-center gap-2">
                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-400">
                          <Layers className="h-4 w-4" />
                        </div>
                        <span className="font-mono text-sm">{z.domain}</span>
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-400">{z.soanameserver}</td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">{z.ttl}s</td>
                      <td className="px-6 py-4">
                        <span className="inline-flex items-center rounded-md bg-purple-500/10 border border-purple-500/20 px-2 py-0.5 text-xs font-semibold text-purple-300 uppercase">
                          {z.zone_type}
                        </span>
                      </td>
                      <td className="px-6 py-4 text-right">
                        <div className="flex items-center justify-end gap-2">
                          <button
                            onClick={() => openManageRecords(z.domain)}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-sky-500/30 bg-sky-500/10 px-3 py-1 text-xs font-medium text-sky-300 hover:bg-sky-500/20 transition-colors"
                          >
                            <Settings2 className="h-3.5 w-3.5" />
                            Manage Records
                          </button>
                          <button
                            onClick={() => handleDeleteZone(z.domain)}
                            disabled={actionLoading}
                            className="inline-flex items-center p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition-colors"
                            title="Delete Zone"
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>

      {/* Add Zone Modal */}
      {showAddZoneModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Layers className="h-5 w-5 text-sky-400" />
                Add New DNS Zone
              </h2>
              <button onClick={() => setShowAddZoneModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreateZone} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Zone Domain Name
                </label>
                <input
                  type="text"
                  required
                  placeholder="example.com"
                  value={newDomain}
                  onChange={(e) => setNewDomain(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Default TTL (Seconds)
                </label>
                <input
                  type="number"
                  value={newTtl}
                  onChange={(e) => setNewTtl(Number(e.target.value))}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowAddZoneModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Creating...' : 'Create Zone'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Manage Records Modal */}
      {activeZone && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-md p-4">
          <div className="w-full max-w-3xl rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl max-h-[90vh] flex flex-col">
            <div className="flex items-center justify-between pb-4 border-b border-white/5 flex-shrink-0">
              <div>
                <h2 className="text-lg font-bold text-white flex items-center gap-2">
                  <Settings2 className="h-5 w-5 text-sky-400" />
                  DNS Records: <span className="font-mono text-sky-400">{activeZone}</span>
                </h2>
                <p className="text-xs text-slate-400 mt-0.5">Manage resource records for this zone</p>
              </div>
              <button onClick={() => setActiveZone(null)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            {/* Quick Add Form */}
            <form onSubmit={handleAddRecord} className="mt-4 p-4 rounded-2xl bg-slate-950/60 border border-white/5 space-y-3 flex-shrink-0">
              <div className="flex items-center justify-between">
                <div className="text-xs font-semibold uppercase tracking-wider text-slate-400">Add Resource Record</div>
                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => {
                      setRecType('A');
                      setRecHostname('*');
                      setRecValue('18.222.232.63');
                    }}
                    className="inline-flex items-center gap-1 rounded-lg border border-purple-500/30 bg-purple-500/10 px-2 py-1 text-[11px] font-medium text-purple-300 hover:bg-purple-500/20 transition-all"
                  >
                    ⚡ 1-Click Wildcard (*)
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setRecType('A');
                      setRecHostname('@');
                      setRecValue('18.222.232.63');
                    }}
                    className="inline-flex items-center gap-1 rounded-lg border border-sky-500/30 bg-sky-500/10 px-2 py-1 text-[11px] font-medium text-sky-300 hover:bg-sky-500/20 transition-all"
                  >
                    🌐 Root (@)
                  </button>
                  <button
                    type="button"
                    onClick={() => {
                      setRecType('TXT');
                      setRecHostname('_acme-challenge');
                    }}
                    className="inline-flex items-center gap-1 rounded-lg border border-amber-500/30 bg-amber-500/10 px-2 py-1 text-[11px] font-medium text-amber-300 hover:bg-amber-500/20 transition-all"
                  >
                    🔒 ACME TXT
                  </button>
                </div>
              </div>
              <div className="grid grid-cols-1 sm:grid-cols-5 gap-3">
                <div>
                  <label className="block text-[11px] text-slate-400 mb-1">Type</label>
                  <select
                    value={recType}
                    onChange={(e) => setRecType(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:border-sky-500 focus:outline-none"
                  >
                    <option value="A">A</option>
                    <option value="CNAME">CNAME</option>
                    <option value="MX">MX</option>
                    <option value="TXT">TXT</option>
                  </select>
                </div>
                <div>
                  <label className="block text-[11px] text-slate-400 mb-1">Host (@, *, www)</label>
                  <input
                    type="text"
                    required
                    placeholder="@ or * or sub"
                    value={recHostname}
                    onChange={(e) => setRecHostname(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 font-mono text-xs text-white focus:border-sky-500 focus:outline-none"
                  />
                </div>
                <div className="sm:col-span-2">
                  <label className="block text-[11px] text-slate-400 mb-1">Value / Target IP</label>
                  <input
                    type="text"
                    required
                    placeholder="18.222.232.63 or mail.domain.com"
                    value={recValue}
                    onChange={(e) => setRecValue(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 font-mono text-xs text-white focus:border-sky-500 focus:outline-none"
                  />
                </div>
                <div className="flex items-end">
                  <button
                    type="submit"
                    disabled={actionLoading}
                    className="w-full rounded-xl bg-sky-500 px-3 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-colors disabled:opacity-50"
                  >
                    Add Record
                  </button>
                </div>
              </div>
            </form>

            {/* Records List */}
            <div className="mt-4 flex-1 overflow-y-auto rounded-2xl border border-white/5 bg-slate-950/40">
              {recordsLoading ? (
                <div className="p-8 text-center text-slate-400 animate-pulse text-xs">Loading records...</div>
              ) : records.length === 0 ? (
                <div className="p-8 text-center text-slate-500 text-xs">No records found.</div>
              ) : (
                <table className="w-full text-left text-xs text-slate-300">
                  <thead className="border-b border-white/5 bg-slate-950/80 uppercase font-semibold text-slate-400 sticky top-0">
                    <tr>
                      <th className="px-4 py-2.5">Type</th>
                      <th className="px-4 py-2.5">Host</th>
                      <th className="px-4 py-2.5">Value / Target</th>
                      <th className="px-4 py-2.5">TTL</th>
                      <th className="px-4 py-2.5 text-right">Delete</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-white/5">
                    {records.map((r) => {
                      const isWild = r.hostname === '*' || r.hostname?.startsWith('*.') || r.is_wildcard;
                      return (
                        <tr key={r.id} className="hover:bg-white/[0.02]">
                          <td className="px-4 py-2.5 font-bold text-sky-400">{r.type}</td>
                          <td className="px-4 py-2.5 font-mono text-slate-200">
                            <span className="font-semibold">{r.hostname}</span>
                            {isWild && (
                              <span className="ml-2 inline-flex items-center rounded-md bg-purple-500/20 border border-purple-500/30 px-1.5 py-0.5 text-[10px] font-bold text-purple-300">
                                Wildcard (*.{activeZone})
                              </span>
                            )}
                          </td>
                          <td className="px-4 py-2.5 font-mono text-slate-400 max-w-xs truncate">{r.value}</td>
                          <td className="px-4 py-2.5 text-slate-500">{r.ttl}s</td>
                          <td className="px-4 py-2.5 text-right">
                            <button
                              onClick={() => handleDeleteRecord(r.id)}
                              className="text-rose-400 hover:text-rose-300 p-1 rounded hover:bg-rose-500/10"
                            >
                              <Trash2 className="h-3.5 w-3.5" />
                            </button>
                          </td>
                        </tr>
                      );
                    })}
                  </tbody>
                </table>
              )}
            </div>
          </div>
        </div>
      )}
    </Shell>
  );
}
