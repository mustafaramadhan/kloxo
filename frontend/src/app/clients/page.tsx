'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, ClientItem, PlanItem, getStoredToken, setStoredToken } from '@/lib/api';
import {
  Users,
  CheckCircle2,
  XCircle,
  RefreshCw,
  Shield,
  Plus,
  Trash2,
  X,
  AlertCircle,
  LogIn,
  Package,
  Layers,
  HardDrive,
  Activity,
  Globe,
  Mail,
  Database,
  Key,
} from 'lucide-react';

export default function ClientsPage() {
  const [activeTab, setActiveTab] = useState<'clients' | 'plans'>('clients');

  // Clients state
  const [clients, setClients] = useState<ClientItem[]>([]);
  const [loadingClients, setLoadingClients] = useState(true);
  const [clientSearch, setClientSearch] = useState('');

  // Plans state
  const [plans, setPlans] = useState<PlanItem[]>([]);
  const [loadingPlans, setLoadingPlans] = useState(true);
  const [planFilter, setPlanFilter] = useState<'all' | 'customer' | 'reseller'>('all');

  // Shared state
  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  // Client Modal
  const [showAddClientModal, setShowAddClientModal] = useState(false);
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [email, setEmail] = useState('');
  const [clientType, setClientType] = useState('customer');
  const [selectedPlan, setSelectedPlan] = useState('');

  // Plan Modal
  const [showAddPlanModal, setShowAddPlanModal] = useState(false);
  const [planName, setPlanName] = useState('');
  const [newPlanType, setNewPlanType] = useState<'customer' | 'reseller'>('customer');
  const [diskMb, setDiskMb] = useState(10240);
  const [trafficMb, setTrafficMb] = useState(102400);
  const [domains, setDomains] = useState(5);
  const [subdomains, setSubdomains] = useState(10);
  const [mailAccounts, setMailAccounts] = useState(10);
  const [mysqlDbs, setMysqlDbs] = useState(5);
  const [ftpUsers, setFtpUsers] = useState(5);
  const [isDefaultPlan, setIsDefaultPlan] = useState(false);

  const fetchClients = async () => {
    setLoadingClients(true);
    try {
      const data = await apiFetch<{ clients: ClientItem[] }>('/clients');
      setClients(data.clients || []);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to fetch clients', success: false });
    } finally {
      setLoadingClients(false);
    }
  };

  const fetchPlans = async () => {
    setLoadingPlans(true);
    try {
      const data = await apiFetch<{ plans: PlanItem[] }>('/system/plans');
      const loadedPlans = data.plans || [];
      setPlans(loadedPlans);
      if (loadedPlans.length > 0 && !selectedPlan) {
        setSelectedPlan(loadedPlans[0].name);
      }
    } catch (err: any) {
      console.error('Failed to load plans', err);
    } finally {
      setLoadingPlans(false);
    }
  };

  useEffect(() => {
    fetchClients();
    fetchPlans();
  }, []);

  // When clientType changes in add modal, auto-suggest first matching plan
  useEffect(() => {
    const matching = plans.filter((p) => (p.plan_type || 'customer') === clientType);
    if (matching.length > 0) {
      setSelectedPlan(matching[0].name);
    } else if (plans.length > 0) {
      setSelectedPlan(plans[0].name);
    }
  }, [clientType, plans]);

  // Handle client creation
  const handleCreateClient = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!username.trim() || !password) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/clients', {
        method: 'POST',
        body: JSON.stringify({
          username: username.trim(),
          password,
          email: email.trim(),
          type: clientType,
          plan_name: selectedPlan || undefined,
        }),
      });
      setMessage({ text: res.message || 'Client created successfully', success: true });
      setShowAddClientModal(false);
      setUsername('');
      setPassword('');
      setEmail('');
      setClientType('customer');
      await fetchClients();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create client', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  // Handle client deletion
  const handleDeleteClient = async (clientUser: string) => {
    if (clientUser === 'admin') {
      alert('The root administrator account cannot be deleted.');
      return;
    }
    if (!confirm(`Are you sure you want to delete user account "${clientUser}"?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/clients', {
        method: 'DELETE',
        body: JSON.stringify({ username: clientUser }),
      });
      setMessage({ text: res.message || 'Client deleted successfully', success: true });
      await fetchClients();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete client', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  // Handle Login As / Impersonate
  const handleLoginAsClient = async (targetUsername: string) => {
    setActionLoading(true);
    setMessage(null);
    try {
      const currentToken = getStoredToken();
      if (currentToken) {
        sessionStorage.setItem('admin_impersonator_token', currentToken);
        sessionStorage.setItem('admin_impersonator_user', 'admin');
      }

      const res = await apiFetch<{ success: boolean; token: string; user: any }>('/auth/impersonate', {
        method: 'POST',
        body: JSON.stringify({ username: targetUsername }),
      });

      if (res.token) {
        setStoredToken(res.token);
        window.location.href = '/';
      }
    } catch (err: any) {
      setMessage({ text: err.message || `Failed to login as ${targetUsername}`, success: false });
    } finally {
      setActionLoading(false);
    }
  };

  // Handle plan creation
  const handleCreatePlan = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!planName.trim()) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/system/plans', {
        method: 'POST',
        body: JSON.stringify({
          name: planName.trim(),
          plan_type: newPlanType,
          disk_mb: Number(diskMb),
          traffic_mb: Number(trafficMb),
          domains: Number(domains),
          subdomains: Number(subdomains),
          mail_accounts: Number(mailAccounts),
          mysql_dbs: Number(mysqlDbs),
          ftp_users: Number(ftpUsers),
          is_default: isDefaultPlan,
        }),
      });
      setMessage({ text: res.message || 'Resource plan created successfully', success: true });
      setShowAddPlanModal(false);
      setPlanName('');
      setNewPlanType('customer');
      setDiskMb(10240);
      setTrafficMb(102400);
      setDomains(5);
      setSubdomains(10);
      setMailAccounts(10);
      setMysqlDbs(5);
      setFtpUsers(5);
      setIsDefaultPlan(false);
      await fetchPlans();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create plan', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  // Handle plan deletion
  const handleDeletePlan = async (name: string) => {
    if (!confirm(`Are you sure you want to delete package/plan "${name}"?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/system/plans', {
        method: 'DELETE',
        body: JSON.stringify({ name }),
      });
      setMessage({ text: res.message || `Plan ${name} deleted successfully`, success: true });
      await fetchPlans();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete plan', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const getRoleBadge = (cType: string) => {
    switch (cType.toLowerCase()) {
      case 'admin':
      case 'superadmin':
        return 'bg-purple-500/10 text-purple-400 border-purple-500/20';
      case 'reseller':
        return 'bg-amber-500/10 text-amber-400 border-amber-500/20';
      default:
        return 'bg-sky-500/10 text-sky-400 border-sky-500/20';
    }
  };

  const filteredClients = clients.filter(
    (c) =>
      c.username.toLowerCase().includes(clientSearch.toLowerCase()) ||
      c.email.toLowerCase().includes(clientSearch.toLowerCase()) ||
      c.type.toLowerCase().includes(clientSearch.toLowerCase())
  );

  const filteredPlans = plans.filter((p) => {
    if (planFilter === 'all') return true;
    return (p.plan_type || 'customer') === planFilter;
  });

  return (
    <Shell>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
              <Users className="h-6 w-6 text-sky-400" />
              Clients, Resellers & Packages
            </h1>
            <p className="text-sm text-slate-400">
              Manage panel user accounts, hosting packages, and reseller quota allocations
            </p>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={() => {
                fetchClients();
                fetchPlans();
              }}
              disabled={loadingClients || loadingPlans}
              className="flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm font-medium text-slate-300 hover:bg-white/10 hover:text-white transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-4 w-4 ${loadingClients || loadingPlans ? 'animate-spin' : ''}`} />
              Refresh
            </button>
            {activeTab === 'clients' ? (
              <button
                onClick={() => setShowAddClientModal(true)}
                className="flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
              >
                <Plus className="h-4 w-4" />
                Add User / Reseller
              </button>
            ) : (
              <button
                onClick={() => setShowAddPlanModal(true)}
                className="flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-indigo-500/20 hover:from-indigo-400 hover:to-purple-500 transition-all"
              >
                <Plus className="h-4 w-4" />
                Create New Package
              </button>
            )}
          </div>
        </div>

        {/* Global Toast Alert */}
        {message && (
          <div
            className={`flex items-center justify-between rounded-2xl border p-4 ${
              message.success
                ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400'
                : 'border-rose-500/20 bg-rose-500/10 text-rose-400'
            }`}
          >
            <div className="flex items-center gap-3">
              {message.success ? <CheckCircle2 className="h-5 w-5" /> : <AlertCircle className="h-5 w-5" />}
              <span className="text-sm font-medium">{message.text}</span>
            </div>
            <button
              onClick={() => setMessage(null)}
              className="text-slate-400 hover:text-white transition-colors"
            >
              <X className="h-4 w-4" />
            </button>
          </div>
        )}

        {/* Navigation Tabs */}
        <div className="flex border-b border-white/10">
          <button
            onClick={() => setActiveTab('clients')}
            className={`flex items-center gap-2 px-6 py-3 border-b-2 text-sm font-medium transition-all ${
              activeTab === 'clients'
                ? 'border-sky-500 text-sky-400'
                : 'border-transparent text-slate-400 hover:text-slate-200'
            }`}
          >
            <Users className="h-4 w-4" />
            Users & Accounts ({clients.length})
          </button>
          <button
            onClick={() => setActiveTab('plans')}
            className={`flex items-center gap-2 px-6 py-3 border-b-2 text-sm font-medium transition-all ${
              activeTab === 'plans'
                ? 'border-indigo-500 text-indigo-400'
                : 'border-transparent text-slate-400 hover:text-slate-200'
            }`}
          >
            <Package className="h-4 w-4" />
            Hosting & Reseller Packages ({plans.length})
          </button>
        </div>

        {/* TAB 1: USERS & ACCOUNTS */}
        {activeTab === 'clients' && (
          <div className="space-y-4">
            <div className="flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
              <input
                type="text"
                placeholder="Search by username, email or role..."
                value={clientSearch}
                onChange={(e) => setClientSearch(e.target.value)}
                className="w-full sm:w-80 rounded-xl border border-white/10 bg-slate-900/50 px-4 py-2 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
              />
              <div className="text-xs text-slate-400">
                Total accounts: <span className="text-white font-medium">{filteredClients.length}</span>
              </div>
            </div>

            {loadingClients ? (
              <div className="flex h-64 items-center justify-center rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-xl">
                <RefreshCw className="h-8 w-8 animate-spin text-sky-400" />
              </div>
            ) : filteredClients.length === 0 ? (
              <div className="flex flex-col items-center justify-center rounded-3xl border border-white/5 bg-slate-900/50 p-12 text-center backdrop-blur-xl">
                <Users className="h-12 w-12 text-slate-600 mb-3" />
                <h3 className="text-lg font-semibold text-white">No Accounts Found</h3>
                <p className="text-sm text-slate-400 mt-1 max-w-sm">
                  {clientSearch ? 'No clients match your filter.' : 'Create your first hosting client or reseller account.'}
                </p>
              </div>
            ) : (
              <div className="overflow-hidden rounded-3xl border border-white/5 bg-slate-900/50 shadow-2xl backdrop-blur-xl">
                <div className="overflow-x-auto">
                  <table className="w-full text-left text-sm text-slate-400">
                    <thead className="bg-white/[0.02] text-xs uppercase text-slate-300 border-b border-white/5">
                      <tr>
                        <th className="px-6 py-4">Account</th>
                        <th className="px-6 py-4">Type</th>
                        <th className="px-6 py-4">Assigned Package</th>
                        <th className="px-6 py-4">Domains</th>
                        <th className="px-6 py-4">Disk Usage</th>
                        <th className="px-6 py-4">Status</th>
                        <th className="px-6 py-4 text-right">Actions</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-white/5">
                      {filteredClients.map((c) => (
                        <tr key={c.username} className="hover:bg-white/[0.02] transition-colors">
                          <td className="px-6 py-4">
                            <div className="flex items-center gap-3">
                              <div className="h-9 w-9 rounded-full bg-slate-800 flex items-center justify-center border border-white/10 text-white font-bold text-sm">
                                {c.username.substring(0, 2).toUpperCase()}
                              </div>
                              <div>
                                <div className="font-medium text-white">{c.username}</div>
                                <div className="text-xs text-slate-500">{c.email || 'No email set'}</div>
                              </div>
                            </div>
                          </td>
                          <td className="px-6 py-4">
                            <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border ${getRoleBadge(c.type)}`}>
                              <Shield className="h-3 w-3" />
                              {c.type}
                            </span>
                          </td>
                          <td className="px-6 py-4">
                            <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                              <Package className="h-3 w-3 text-indigo-400" />
                              {c.plan || 'Standard Hosting'}
                            </span>
                          </td>
                          <td className="px-6 py-4">
                            <span className="text-white font-medium">{c.domains_used}</span>
                            <span className="text-xs text-slate-500"> / {c.domains_quota || 'Unlimited'}</span>
                          </td>
                          <td className="px-6 py-4">
                            <span className="text-white font-medium">{c.disk_used_mb.toFixed(1)}</span>
                            <span className="text-xs text-slate-500"> MB</span>
                          </td>
                          <td className="px-6 py-4">
                            {c.status ? (
                              <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <CheckCircle2 className="h-3 w-3" />
                                Active
                              </span>
                            ) : (
                              <span className="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-rose-500/10 text-rose-400 border border-rose-500/20">
                                <XCircle className="h-3 w-3" />
                                Suspended
                              </span>
                            )}
                          </td>
                          <td className="px-6 py-4 text-right">
                            <div className="flex items-center justify-end gap-2">
                              {c.username !== 'admin' && (
                                <button
                                  onClick={() => handleLoginAsClient(c.username)}
                                  disabled={actionLoading}
                                  title={`Login as ${c.username}`}
                                  className="flex items-center gap-1 rounded-lg border border-sky-500/20 bg-sky-500/10 px-2.5 py-1.5 text-xs font-medium text-sky-400 hover:bg-sky-500/20 transition-colors disabled:opacity-50"
                                >
                                  <LogIn className="h-3.5 w-3.5" />
                                  <span>Login As</span>
                                </button>
                              )}
                              {c.username !== 'admin' && (
                                <button
                                  onClick={() => handleDeleteClient(c.username)}
                                  disabled={actionLoading}
                                  title="Delete Account"
                                  className="rounded-lg p-1.5 text-slate-500 hover:bg-rose-500/10 hover:text-rose-400 transition-colors"
                                >
                                  <Trash2 className="h-4 w-4" />
                                </button>
                              )}
                            </div>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
          </div>
        )}

        {/* TAB 2: PACKAGES & PLANS */}
        {activeTab === 'plans' && (
          <div className="space-y-4">
            <div className="flex flex-col sm:flex-row gap-3 sm:items-center justify-between">
              <div className="flex items-center gap-2">
                <button
                  onClick={() => setPlanFilter('all')}
                  className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-colors ${
                    planFilter === 'all'
                      ? 'bg-indigo-600 text-white shadow-sm'
                      : 'bg-white/5 text-slate-400 hover:text-white'
                  }`}
                >
                  All Packages ({plans.length})
                </button>
                <button
                  onClick={() => setPlanFilter('customer')}
                  className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-colors ${
                    planFilter === 'customer'
                      ? 'bg-sky-600 text-white shadow-sm'
                      : 'bg-white/5 text-slate-400 hover:text-white'
                  }`}
                >
                  Hosting Packages ({plans.filter((p) => (p.plan_type || 'customer') === 'customer').length})
                </button>
                <button
                  onClick={() => setPlanFilter('reseller')}
                  className={`px-3 py-1.5 rounded-lg text-xs font-medium transition-colors ${
                    planFilter === 'reseller'
                      ? 'bg-amber-600 text-white shadow-sm'
                      : 'bg-white/5 text-slate-400 hover:text-white'
                  }`}
                >
                  Reseller Packages ({plans.filter((p) => p.plan_type === 'reseller').length})
                </button>
              </div>
            </div>

            {loadingPlans ? (
              <div className="flex h-64 items-center justify-center rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-xl">
                <RefreshCw className="h-8 w-8 animate-spin text-indigo-400" />
              </div>
            ) : filteredPlans.length === 0 ? (
              <div className="flex flex-col items-center justify-center rounded-3xl border border-white/5 bg-slate-900/50 p-12 text-center backdrop-blur-xl">
                <Package className="h-12 w-12 text-slate-600 mb-3" />
                <h3 className="text-lg font-semibold text-white">No Packages Found</h3>
                <p className="text-sm text-slate-400 mt-1 max-w-sm">
                  Create custom resource packages to allocate disk, traffic, domains, and mail limits.
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                {filteredPlans.map((plan) => {
                  const isReseller = plan.plan_type === 'reseller';
                  return (
                    <div
                      key={plan.name}
                      className={`relative flex flex-col justify-between rounded-3xl border bg-slate-900/60 p-6 shadow-xl backdrop-blur-xl transition-all hover:border-white/20 ${
                        isReseller ? 'border-amber-500/20 hover:shadow-amber-500/5' : 'border-sky-500/20 hover:shadow-sky-500/5'
                      }`}
                    >
                      <div>
                        <div className="flex items-start justify-between">
                          <div>
                            <span
                              className={`inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium border mb-2 ${
                                isReseller
                                  ? 'bg-amber-500/10 text-amber-400 border-amber-500/20'
                                  : 'bg-sky-500/10 text-sky-400 border-sky-500/20'
                              }`}
                            >
                              <Layers className="h-3 w-3" />
                              {isReseller ? 'Reseller Package' : 'Hosting Package'}
                            </span>
                            <h3 className="text-lg font-bold text-white flex items-center gap-2">
                              {plan.name}
                              {plan.is_default && (
                                <span className="rounded bg-indigo-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-300">
                                  Default
                                </span>
                              )}
                            </h3>
                          </div>
                          <button
                            onClick={() => handleDeletePlan(plan.name)}
                            disabled={actionLoading}
                            title="Delete Plan"
                            className="rounded-lg p-1.5 text-slate-500 hover:bg-rose-500/10 hover:text-rose-400 transition-colors"
                          >
                            <Trash2 className="h-4 w-4" />
                          </button>
                        </div>

                        {/* Quotas grid */}
                        <div className="mt-6 space-y-3">
                          <div className="flex items-center justify-between text-xs py-1.5 border-b border-white/5">
                            <span className="flex items-center gap-2 text-slate-400">
                              <HardDrive className="h-3.5 w-3.5 text-sky-400" /> Disk Quota
                            </span>
                            <span className="font-semibold text-white">
                              {plan.disk_mb >= 1024 ? `${(plan.disk_mb / 1024).toFixed(0)} GB` : `${plan.disk_mb} MB`}
                            </span>
                          </div>

                          <div className="flex items-center justify-between text-xs py-1.5 border-b border-white/5">
                            <span className="flex items-center gap-2 text-slate-400">
                              <Activity className="h-3.5 w-3.5 text-emerald-400" /> Monthly Traffic
                            </span>
                            <span className="font-semibold text-white">
                              {plan.traffic_mb >= 1024 ? `${(plan.traffic_mb / 1024).toFixed(0)} GB` : `${plan.traffic_mb} MB`}
                            </span>
                          </div>

                          <div className="flex items-center justify-between text-xs py-1.5 border-b border-white/5">
                            <span className="flex items-center gap-2 text-slate-400">
                              <Globe className="h-3.5 w-3.5 text-purple-400" /> Domains / Subdomains
                            </span>
                            <span className="font-semibold text-white">
                              {plan.domains} / {plan.subdomains || 10}
                            </span>
                          </div>

                          <div className="flex items-center justify-between text-xs py-1.5 border-b border-white/5">
                            <span className="flex items-center gap-2 text-slate-400">
                              <Mail className="h-3.5 w-3.5 text-amber-400" /> Mail Accounts
                            </span>
                            <span className="font-semibold text-white">{plan.mail_accounts}</span>
                          </div>

                          <div className="flex items-center justify-between text-xs py-1.5 border-b border-white/5">
                            <span className="flex items-center gap-2 text-slate-400">
                              <Database className="h-3.5 w-3.5 text-blue-400" /> MySQL Databases
                            </span>
                            <span className="font-semibold text-white">{plan.mysql_dbs}</span>
                          </div>

                          <div className="flex items-center justify-between text-xs py-1.5">
                            <span className="flex items-center gap-2 text-slate-400">
                              <Key className="h-3.5 w-3.5 text-teal-400" /> FTP Users
                            </span>
                            <span className="font-semibold text-white">{plan.ftp_users || 5}</span>
                          </div>
                        </div>
                      </div>

                      <div className="mt-6 pt-4 border-t border-white/5 flex items-center justify-between text-xs text-slate-400">
                        <span>Applicable tier</span>
                        <span className="font-medium text-slate-300 capitalize">{plan.plan_type || 'customer'}</span>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        )}
      </div>

      {/* Modal 1: Add Client / Reseller */}
      {showAddClientModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Users className="h-5 w-5 text-sky-400" />
                Add New Client / Reseller
              </h2>
              <button
                onClick={() => setShowAddClientModal(false)}
                className="text-slate-400 hover:text-white transition-colors"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreateClient} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Username
                </label>
                <input
                  type="text"
                  required
                  placeholder="e.g. john_doe"
                  value={username}
                  onChange={(e) => setUsername(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Password
                </label>
                <input
                  type="password"
                  required
                  placeholder="Strong password"
                  value={password}
                  onChange={(e) => setPassword(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Contact Email
                </label>
                <input
                  type="email"
                  placeholder="client@example.com"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    Account Type
                  </label>
                  <select
                    value={clientType}
                    onChange={(e) => setClientType(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                  >
                    <option value="customer">Customer (Web Hosting)</option>
                    <option value="reseller">Reseller (Reseller Hub)</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    Resource Package
                  </label>
                  <select
                    value={selectedPlan}
                    onChange={(e) => setSelectedPlan(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                  >
                    {plans.map((p) => (
                      <option key={p.name} value={p.name}>
                        {p.name} ({p.disk_mb >= 1024 ? `${(p.disk_mb / 1024).toFixed(0)}GB` : `${p.disk_mb}MB`})
                      </option>
                    ))}
                  </select>
                </div>
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowAddClientModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/10 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all disabled:opacity-50"
                >
                  {actionLoading ? 'Creating...' : 'Create Account'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Modal 2: Create Package / Resource Plan */}
      {showAddPlanModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-lg rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Package className="h-5 w-5 text-indigo-400" />
                Create New Resource Package
              </h2>
              <button
                onClick={() => setShowAddPlanModal(false)}
                className="text-slate-400 hover:text-white transition-colors"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreatePlan} className="mt-4 space-y-4">
              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    Package Name
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. Starter Hosting, Reseller Gold"
                    value={planName}
                    onChange={(e) => setPlanName(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    Package Type
                  </label>
                  <select
                    value={newPlanType}
                    onChange={(e) => setNewPlanType(e.target.value as 'customer' | 'reseller')}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  >
                    <option value="customer">Web Hosting (Customer)</option>
                    <option value="reseller">Reseller Hosting (Sub-accounts)</option>
                  </select>
                </div>
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    Disk Quota (MB)
                  </label>
                  <input
                    type="number"
                    min="100"
                    required
                    value={diskMb}
                    onChange={(e) => setDiskMb(Number(e.target.value))}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                  <span className="text-[10px] text-slate-500">{(diskMb / 1024).toFixed(1)} GB</span>
                </div>

                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    Monthly Traffic (MB)
                  </label>
                  <input
                    type="number"
                    min="1000"
                    required
                    value={trafficMb}
                    onChange={(e) => setTrafficMb(Number(e.target.value))}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                  <span className="text-[10px] text-slate-500">{(trafficMb / 1024).toFixed(1)} GB</span>
                </div>
              </div>

              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    Domains
                  </label>
                  <input
                    type="number"
                    min="1"
                    required
                    value={domains}
                    onChange={(e) => setDomains(Number(e.target.value))}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    Subdomains
                  </label>
                  <input
                    type="number"
                    min="0"
                    required
                    value={subdomains}
                    onChange={(e) => setSubdomains(Number(e.target.value))}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    Mailboxes
                  </label>
                  <input
                    type="number"
                    min="0"
                    required
                    value={mailAccounts}
                    onChange={(e) => setMailAccounts(Number(e.target.value))}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    MySQL DBs
                  </label>
                  <input
                    type="number"
                    min="0"
                    required
                    value={mysqlDbs}
                    onChange={(e) => setMysqlDbs(Number(e.target.value))}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>
              </div>

              <div className="flex items-center justify-between">
                <div>
                  <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                    FTP Accounts
                  </label>
                  <input
                    type="number"
                    min="0"
                    required
                    value={ftpUsers}
                    onChange={(e) => setFtpUsers(Number(e.target.value))}
                    className="w-32 rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                  />
                </div>

                <div className="flex items-center gap-2 pt-4">
                  <input
                    type="checkbox"
                    id="isDefaultPlan"
                    checked={isDefaultPlan}
                    onChange={(e) => setIsDefaultPlan(e.target.checked)}
                    className="h-4 w-4 rounded border-white/10 bg-slate-950 text-indigo-600 focus:ring-indigo-500"
                  />
                  <label htmlFor="isDefaultPlan" className="text-xs text-slate-300 cursor-pointer">
                    Set as default package for this type
                  </label>
                </div>
              </div>

              <div className="flex justify-end gap-3 pt-4 border-t border-white/5">
                <button
                  type="button"
                  onClick={() => setShowAddPlanModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/10 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-indigo-500/20 hover:from-indigo-400 hover:to-purple-500 transition-all disabled:opacity-50"
                >
                  {actionLoading ? 'Saving...' : 'Save Package'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </Shell>
  );
}
