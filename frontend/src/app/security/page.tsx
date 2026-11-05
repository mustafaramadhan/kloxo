'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, FirewallRuleItem, BlockedIpItem } from '@/lib/api';
import { Shield, Plus, RefreshCw, Trash2, X, AlertCircle, CheckCircle2, Ban, ShieldCheck, Flame } from 'lucide-react';

export default function SecurityPage() {
  const [activeTab, setActiveTab] = useState<'firewall' | 'blocked_ips'>('firewall');
  const [rules, setRules] = useState<FirewallRuleItem[]>([]);
  const [blockedIps, setBlockedIps] = useState<BlockedIpItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Firewall Rule Modal
  const [showAddRuleModal, setShowAddRuleModal] = useState(false);
  const [rulePort, setRulePort] = useState('');
  const [ruleProtocol, setRuleProtocol] = useState('TCP');
  const [ruleAction, setRuleAction] = useState<'allow' | 'deny'>('allow');
  const [ruleDesc, setRuleDesc] = useState('');

  // Block IP Modal
  const [showBlockIpModal, setShowBlockIpModal] = useState(false);
  const [newIp, setNewIp] = useState('');

  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchSecurityData = async () => {
    setLoading(true);
    setError(null);
    try {
      const [fwRes, ipRes] = await Promise.all([
        apiFetch<{ rules: FirewallRuleItem[] }>('/security/firewall'),
        apiFetch<{ blocked_ips: BlockedIpItem[] }>('/security/blocked-ips'),
      ]);
      setRules(fwRes.rules);
      setBlockedIps(ipRes.blocked_ips);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch security configurations');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchSecurityData();
  }, []);

  const handleCreateRule = async (e: React.FormEvent) => {
    e.preventDefault();
    const portNum = parseInt(rulePort);
    if (!portNum || portNum < 1 || portNum > 65535) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/security/firewall', {
        method: 'POST',
        body: JSON.stringify({
          port: portNum,
          protocol: ruleProtocol,
          action: ruleAction,
          description: ruleDesc.trim() || `Port ${portNum} Traffic`,
        }),
      });
      setMessage({ text: res.message || 'Firewall rule created', success: true });
      setShowAddRuleModal(false);
      setRulePort('');
      setRuleDesc('');
      await fetchSecurityData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create firewall rule', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteRule = async (id: string) => {
    if (!confirm(`Are you sure you want to remove rule "${id}"?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      await apiFetch('/security/firewall', {
        method: 'DELETE',
        body: JSON.stringify({ id }),
      });
      setMessage({ text: 'Firewall rule removed', success: true });
      await fetchSecurityData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete rule', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleBlockIp = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newIp.trim()) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/security/blocked-ips', {
        method: 'POST',
        body: JSON.stringify({ ip: newIp.trim() }),
      });
      setMessage({ text: res.message || `IP ${newIp} blocked successfully`, success: true });
      setShowBlockIpModal(false);
      setNewIp('');
      await fetchSecurityData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to block IP', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleUnblockIp = async (ip: string) => {
    if (!confirm(`Are you sure you want to unblock IP "${ip}"?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      await apiFetch('/security/blocked-ips', {
        method: 'DELETE',
        body: JSON.stringify({ ip }),
      });
      setMessage({ text: `IP ${ip} unblocked successfully`, success: true });
      await fetchSecurityData();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to unblock IP', success: false });
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
              <Shield className="h-6 w-6 text-sky-400" />
              Firewall & Security Center
            </h1>
            <p className="text-sm text-slate-400">
              Manage network port access policies, IP ban lists, and brute force defenses
            </p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={fetchSecurityData}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </button>
            {activeTab === 'firewall' ? (
              <button
                onClick={() => setShowAddRuleModal(true)}
                className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
              >
                <Plus className="h-4 w-4" />
                Add Port Rule
              </button>
            ) : (
              <button
                onClick={() => setShowBlockIpModal(true)}
                className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-rose-500 to-red-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-rose-500/20 hover:from-rose-400 hover:to-red-500 transition-all"
              >
                <Ban className="h-4 w-4" />
                Block IP Address
              </button>
            )}
          </div>
        </div>

        {/* Tab Switcher */}
        <div className="flex items-center gap-2 border-b border-white/5 pb-2">
          <button
            onClick={() => setActiveTab('firewall')}
            className={`inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all ${
              activeTab === 'firewall'
                ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30'
                : 'text-slate-400 hover:text-white hover:bg-white/5'
            }`}
          >
            <Flame className="h-4 w-4" />
            Firewall Rules ({rules.length})
          </button>
          <button
            onClick={() => setActiveTab('blocked_ips')}
            className={`inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all ${
              activeTab === 'blocked_ips'
                ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30'
                : 'text-slate-400 hover:text-white hover:bg-white/5'
            }`}
          >
            <Ban className="h-4 w-4" />
            Blocked IPs ({blockedIps.length})
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

        {error && (
          <div className="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">
            {error}
          </div>
        )}

        {/* Tab 1: Firewall Rules */}
        {activeTab === 'firewall' && (
          <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
            {loading ? (
              <div className="p-12 text-center text-slate-400 animate-pulse">Loading firewall rules...</div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm text-slate-300">
                  <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                    <tr>
                      <th className="px-6 py-4">Port</th>
                      <th className="px-6 py-4">Protocol</th>
                      <th className="px-6 py-4">Policy</th>
                      <th className="px-6 py-4">Description</th>
                      <th className="px-6 py-4">Status</th>
                      <th className="px-6 py-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-white/5">
                    {rules.map((r) => (
                      <tr key={r.id} className="hover:bg-white/[0.02] transition-colors">
                        <td className="px-6 py-4 font-mono font-bold text-white flex items-center gap-2">
                          <Flame className="h-4 w-4 text-amber-400" />
                          <span>{r.port}</span>
                        </td>
                        <td className="px-6 py-4 font-mono text-xs uppercase text-slate-400">{r.protocol}</td>
                        <td className="px-6 py-4">
                          <span
                            className={`inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-semibold uppercase tracking-wider ${
                              r.action === 'allow'
                                ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                                : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                            }`}
                          >
                            {r.action}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-slate-300 text-xs">{r.description}</td>
                        <td className="px-6 py-4">
                          <span className="inline-flex items-center gap-1 text-xs text-emerald-400">
                            <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
                            Active
                          </span>
                        </td>
                        <td className="px-6 py-4 text-right">
                          <button
                            onClick={() => handleDeleteRule(r.id)}
                            disabled={actionLoading}
                            className="inline-flex items-center p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition-colors"
                            title="Remove Rule"
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

        {/* Tab 2: Blocked IPs */}
        {activeTab === 'blocked_ips' && (
          <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
            {loading ? (
              <div className="p-12 text-center text-slate-400 animate-pulse">Loading ban list...</div>
            ) : blockedIps.length === 0 ? (
              <div className="p-16 text-center">
                <ShieldCheck className="mx-auto h-12 w-12 text-emerald-500/60 mb-3" />
                <p className="text-slate-300 font-medium">No IP addresses currently blocked</p>
                <p className="text-xs text-slate-500 mt-1">All incoming connections are evaluated by firewall rules.</p>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <table className="w-full text-left text-sm text-slate-300">
                  <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                    <tr>
                      <th className="px-6 py-4">IP Address</th>
                      <th className="px-6 py-4">Target Drop Action</th>
                      <th className="px-6 py-4 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-white/5">
                    {blockedIps.map((b) => (
                      <tr key={b.id} className="hover:bg-white/[0.02]">
                        <td className="px-6 py-4 font-mono font-medium text-white flex items-center gap-2">
                          <Ban className="h-4 w-4 text-rose-400" />
                          <span>{b.ip}</span>
                        </td>
                        <td className="px-6 py-4 font-mono text-xs text-rose-400">DROP / IPTABLES DENY</td>
                        <td className="px-6 py-4 text-right">
                          <button
                            onClick={() => handleUnblockIp(b.ip)}
                            disabled={actionLoading}
                            className="inline-flex items-center gap-1 text-xs text-sky-400 hover:text-sky-300 p-1.5 rounded-lg hover:bg-sky-500/10 transition-colors"
                          >
                            Unblock IP
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

      {/* Add Rule Modal */}
      {showAddRuleModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Flame className="h-5 w-5 text-amber-400" />
                Add Firewall Port Policy
              </h2>
              <button onClick={() => setShowAddRuleModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreateRule} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Port Number (1 - 65535)
                </label>
                <input
                  type="number"
                  required
                  placeholder="e.g. 8080"
                  value={rulePort}
                  onChange={(e) => setRulePort(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Protocol</label>
                  <select
                    value={ruleProtocol}
                    onChange={(e) => setRuleProtocol(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                  >
                    <option value="TCP">TCP</option>
                    <option value="UDP">UDP</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Action</label>
                  <select
                    value={ruleAction}
                    onChange={(e) => setRuleAction(e.target.value as 'allow' | 'deny')}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                  >
                    <option value="allow">ALLOW (Accept)</option>
                    <option value="deny">DENY (Drop)</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">Description / Service</label>
                <input
                  type="text"
                  placeholder="e.g. Custom Node.js App"
                  value={ruleDesc}
                  onChange={(e) => setRuleDesc(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowAddRuleModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Saving...' : 'Apply Rule'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Block IP Modal */}
      {showBlockIpModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Ban className="h-5 w-5 text-rose-400" />
                Block Incoming IP Address
              </h2>
              <button onClick={() => setShowBlockIpModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleBlockIp} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  IPv4 or IPv6 Address
                </label>
                <input
                  type="text"
                  required
                  placeholder="e.g. 192.168.1.100"
                  value={newIp}
                  onChange={(e) => setNewIp(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white focus:border-rose-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowBlockIpModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-rose-500 to-red-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-rose-500/20 hover:from-rose-400 hover:to-red-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Blocking...' : 'Block IP Now'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </Shell>
  );
}
