'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, FtpUserItem } from '@/lib/api';
import { Share2, CheckCircle2, XCircle, RefreshCw, Folder, UserCheck, Plus, Trash2, X, AlertCircle } from 'lucide-react';

export default function FtpUsersPage() {
  const [users, setUsers] = useState<FtpUserItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddModal, setShowAddModal] = useState(false);
  const [formData, setFormData] = useState({ username: '', password: '', directory: '/home/admin' });
  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchFtp = async () => {
    setLoading(true);
    try {
      const data = await apiFetch<{ users: FtpUserItem[] }>('/ftp/users');
      setUsers(data.users);
    } catch (err: any) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchFtp();
  }, []);

  const handleCreateFtp = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!formData.username.trim() || !formData.password.trim()) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/ftp/users', {
        method: 'POST',
        body: JSON.stringify(formData),
      });
      setMessage({ text: res.message || 'FTP user created successfully', success: true });
      setShowAddModal(false);
      setFormData({ username: '', password: '', directory: '/home/admin' });
      await fetchFtp();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create FTP user', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteFtp = async (username: string) => {
    if (!confirm(`Are you sure you want to delete FTP user "${username}"?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/ftp/users', {
        method: 'DELETE',
        body: JSON.stringify({ username }),
      });
      setMessage({ text: res.message || 'FTP user deleted successfully', success: true });
      await fetchFtp();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete FTP user', success: false });
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
              <Share2 className="h-6 w-6 text-sky-400" />
              FTP Users
            </h1>
            <p className="text-sm text-slate-400">
              Manage FTP access, root directories, and account permissions
            </p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={fetchFtp}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors"
            >
              <RefreshCw className="h-4 w-4" />
              Refresh
            </button>
            <button
              onClick={() => setShowAddModal(true)}
              className="flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" /> Add FTP User
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

        {/* FTP List */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Loading FTP accounts...</div>
          ) : users.length === 0 ? (
            <div className="p-16 text-center">
              <UserCheck className="mx-auto h-12 w-12 text-slate-600 mb-3" />
              <p className="text-slate-400 font-medium">No secondary FTP users configured</p>
              <p className="text-xs text-slate-500 mt-1">
                You can create virtual FTP credentials bound to any directory.
              </p>
              <button
                onClick={() => setShowAddModal(true)}
                className="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20 px-4 py-2 text-sm font-semibold text-indigo-400 hover:bg-indigo-500/20 transition-all"
              >
                <Plus className="h-4 w-4" /> Create First FTP User
              </button>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Username</th>
                    <th className="px-6 py-4">Directory</th>
                    <th className="px-6 py-4">Owner</th>
                    <th className="px-6 py-4">Status</th>
                    <th className="px-6 py-4">Disk Usage</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {users.map((u) => (
                    <tr key={u.username} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-medium text-white flex items-center gap-3">
                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400">
                          <Share2 className="h-4 w-4" />
                        </div>
                        <span>{u.username}</span>
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-400 flex items-center gap-2">
                        <Folder className="h-4 w-4 text-amber-400/80" />
                        {u.directory}
                      </td>
                      <td className="px-6 py-4 text-slate-400">{u.owner}</td>
                      <td className="px-6 py-4">
                        <span
                          className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ${
                            u.status
                              ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                              : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                          }`}
                        >
                          {u.status ? (
                            <>
                              <CheckCircle2 className="h-3 w-3" /> Enabled
                            </>
                          ) : (
                            <>
                              <XCircle className="h-3 w-3" /> Disabled
                            </>
                          )}
                        </span>
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">
                        {u.disk_usage_mb} MB
                      </td>
                      <td className="px-6 py-4 text-right">
                        <button
                          onClick={() => handleDeleteFtp(u.username)}
                          disabled={actionLoading}
                          title="Delete FTP User"
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
          )}
        </div>

        {/* Add FTP Modal */}
        {showAddModal && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-md p-4">
            <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-950 p-6 shadow-2xl">
              <div className="flex items-center justify-between pb-4 border-b border-white/10 mb-4">
                <h3 className="text-lg font-bold text-white flex items-center gap-2">
                  <Share2 className="h-5 w-5 text-indigo-400" />
                  Add FTP User
                </h3>
                <button
                  onClick={() => setShowAddModal(false)}
                  className="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white"
                >
                  <X className="h-4 w-4" />
                </button>
              </div>

              <form onSubmit={handleCreateFtp} className="space-y-4">
                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    FTP Username
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="ftp_user"
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

                <div>
                  <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                    Directory Path
                  </label>
                  <input
                    type="text"
                    required
                    placeholder="/home/admin"
                    value={formData.directory}
                    onChange={(e) => setFormData({ ...formData, directory: e.target.value })}
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
                    {actionLoading ? 'Creating...' : 'Create FTP User'}
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
