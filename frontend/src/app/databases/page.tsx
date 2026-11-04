'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { Database, Plus, RefreshCw, Trash2, X, AlertCircle, CheckCircle2, User, Key } from 'lucide-react';
import { apiFetch, DatabaseItem } from '@/lib/api';

export default function DatabasesPage() {
  const [databases, setDatabases] = useState<DatabaseItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddModal, setShowAddModal] = useState(false);
  const [formData, setFormData] = useState({ name: '', username: '', password: '' });
  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const loadDatabases = async () => {
    try {
      const res = await apiFetch<{ success: boolean; databases: DatabaseItem[] }>('/databases');
      setDatabases(res.databases);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadDatabases();
  }, []);

  const handleCreateDatabase = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.name.trim() || !formData.password.trim()) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/databases', {
        method: 'POST',
        body: JSON.stringify(formData),
      });
      setMessage({ text: res.message || 'Database created successfully', success: true });
      setShowAddModal(false);
      setFormData({ name: '', username: '', password: '' });
      await loadDatabases();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create database', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteDatabase = async (name: string) => {
    if (!confirm(`Are you sure you want to delete database "${name}"? This action cannot be undone!`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/databases', {
        method: 'DELETE',
        body: JSON.stringify({ name }),
      });
      setMessage({ text: res.message || 'Database deleted successfully', success: true });
      await loadDatabases();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete database', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  return (
    <Shell>
      <div className="space-y-6">
        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white md:text-3xl">MySQL Databases</h1>
            <p className="text-sm text-slate-400">Manage MariaDB / MySQL relational database instances</p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={loadDatabases}
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
              <Plus className="h-4 w-4" /> Create Database
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

        {/* Databases Table or Empty State */}
        {databases.length === 0 && !loading ? (
          <div className="glass-panel rounded-2xl p-12 text-center">
            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
              <Database className="h-8 w-8" />
            </div>
            <h3 className="text-lg font-bold text-white">No Databases Configured</h3>
            <p className="mt-1 text-sm text-slate-400 max-w-sm mx-auto">
              Create a new MariaDB database and user to connect with your web applications.
            </p>
            <button
              onClick={() => setShowAddModal(true)}
              className="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20 px-4 py-2 text-sm font-semibold text-indigo-400 hover:bg-indigo-500/20 transition-all"
            >
              <Plus className="h-4 w-4" /> Create First Database
            </button>
          </div>
        ) : (
          <div className="glass-panel overflow-hidden rounded-2xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase tracking-wider text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Database Name</th>
                    <th className="px-6 py-4">Associated User</th>
                    <th className="px-6 py-4">Owner</th>
                    <th className="px-6 py-4">Disk Usage</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {databases.map((db) => (
                    <tr key={db.name} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-semibold text-white flex items-center gap-2">
                        <Database className="h-4 w-4 text-indigo-400" />
                        <span>{db.name}</span>
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">{db.username}</td>
                      <td className="px-6 py-4 text-slate-400">{db.owner}</td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">{db.usage_mb} MB</td>
                      <td className="px-6 py-4 text-right">
                        <button
                          onClick={() => handleDeleteDatabase(db.name)}
                          disabled={actionLoading}
                          title="Delete Database"
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

        {/* Add Database Modal */}
        {showAddModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-md p-4">
            <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-950 p-6 shadow-2xl">
              <div className="flex items-center justify-between pb-4 border-b border-white/10 mb-4">
                <h3 className="text-lg font-bold text-white flex items-center gap-2">
                  <Database className="h-5 w-5 text-indigo-400" />
                  Create MySQL Database
                </h3>
                <button
                  onClick={() => setShowAddModal(false)}
                  className="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white"
                >
                  <X className="h-4 w-4" />
                </button>
              </div>

              <form onSubmit={handleCreateDatabase} className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Database Name
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="app_production"
                    value={formData.name}
                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Database Username
                  </label>
                  <input
                    type="text"
                    placeholder="Leave empty to use DB name"
                    value={formData.username}
                    onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none"
                  />
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Password
                  </label>
                  <input
                    type="password"
                    required
                    placeholder="••••••••••••"
                    value={formData.password}
                    onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-indigo-500 focus:outline-none"
                  />
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
                    {actionLoading ? 'Creating...' : 'Create Database'}
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
