'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, BackupItem } from '@/lib/api';
import { Archive, Plus, RefreshCw, Trash2, X, AlertCircle, CheckCircle2, HardDrive, Download, Calendar } from 'lucide-react';

export default function BackupsPage() {
  const [backups, setBackups] = useState<BackupItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Modal
  const [showCreateModal, setShowCreateModal] = useState(false);
  const [backupType, setBackupType] = useState<'all' | 'databases' | 'web'>('all');

  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchBackups = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await apiFetch<{ backups: BackupItem[] }>('/backups');
      setBackups(data.backups);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch backup archives');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchBackups();
  }, []);

  const handleCreateBackup = async (e: React.FormEvent) => {
    e.preventDefault();
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string; filename: string; size_formatted: string }>('/backups/create', {
        method: 'POST',
        body: JSON.stringify({ type: backupType }),
      });
      setMessage({
        text: `${res.message} (${res.size_formatted})`,
        success: true,
      });
      setShowCreateModal(false);
      await fetchBackups();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create backup archive', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteBackup = async (filename: string) => {
    if (!confirm(`Are you sure you want to delete backup file "${filename}"?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      await apiFetch('/backups', {
        method: 'DELETE',
        body: JSON.stringify({ filename }),
      });
      setMessage({ text: `Backup ${filename} deleted`, success: true });
      await fetchBackups();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete backup', success: false });
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
              <Archive className="h-6 w-6 text-sky-400" />
              Server Backups & Disaster Recovery
            </h1>
            <p className="text-sm text-slate-400">
              Create and manage compressed snapshots of websites, configs, and MariaDB databases
            </p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={fetchBackups}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </button>
            <button
              onClick={() => setShowCreateModal(true)}
              className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" />
              Create Snapshot
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

        {/* Backups Table */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Scanning backup archives...</div>
          ) : backups.length === 0 ? (
            <div className="p-16 text-center">
              <Archive className="mx-auto h-12 w-12 text-slate-600 mb-3" />
              <p className="text-slate-400 font-medium">No backup archives created yet</p>
              <p className="text-xs text-slate-500 mt-1">
                Click &quot;Create Snapshot&quot; to build an on-demand compressed archive.
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Archive Filename</th>
                    <th className="px-6 py-4">Created Date</th>
                    <th className="px-6 py-4">File Size</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {backups.map((b) => (
                    <tr key={b.filename} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-mono font-medium text-white flex items-center gap-3">
                        <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400">
                          <Archive className="h-4 w-4" />
                        </div>
                        <div>
                          <span className="block text-xs font-semibold">{b.filename}</span>
                          <span className="text-[11px] text-slate-500 font-normal">tar.gz compressed archive</span>
                        </div>
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300 flex items-center gap-1.5 pt-5">
                        <Calendar className="h-3.5 w-3.5 text-slate-500" />
                        {b.created_at}
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-sky-400 font-bold">{b.size_formatted}</td>
                      <td className="px-6 py-4 text-right">
                        <button
                          onClick={() => handleDeleteBackup(b.filename)}
                          disabled={actionLoading}
                          className="inline-flex items-center p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition-colors"
                          title="Delete Archive"
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

      {/* Create Backup Modal */}
      {showCreateModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Archive className="h-5 w-5 text-sky-400" />
                Generate Snapshot Archive
              </h2>
              <button onClick={() => setShowCreateModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreateBackup} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-2">
                  Backup Scope
                </label>
                <div className="space-y-2">
                  <label className="flex items-center gap-3 p-3 rounded-2xl bg-slate-950/60 border border-white/5 cursor-pointer hover:border-sky-500/40">
                    <input
                      type="radio"
                      name="btype"
                      checked={backupType === 'all'}
                      onChange={() => setBackupType('all')}
                      className="text-sky-500 focus:ring-sky-500"
                    />
                    <div>
                      <div className="text-xs font-semibold text-white">Full Snapshot (Recommended)</div>
                      <div className="text-[11px] text-slate-400">All websites, web configs, and public_html contents</div>
                    </div>
                  </label>
                  <label className="flex items-center gap-3 p-3 rounded-2xl bg-slate-950/60 border border-white/5 cursor-pointer hover:border-sky-500/40">
                    <input
                      type="radio"
                      name="btype"
                      checked={backupType === 'databases'}
                      onChange={() => setBackupType('databases')}
                      className="text-sky-500 focus:ring-sky-500"
                    />
                    <div>
                      <div className="text-xs font-semibold text-white">Databases Only (mysqldump)</div>
                      <div className="text-[11px] text-slate-400">All MariaDB / MySQL user databases in SQL format</div>
                    </div>
                  </label>
                </div>
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowCreateModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Archiving...' : 'Start Backup'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </Shell>
  );
}
