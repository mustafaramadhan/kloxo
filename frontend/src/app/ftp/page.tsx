'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, FtpUserItem } from '@/lib/api';
import { Share2, CheckCircle2, XCircle, RefreshCw, Folder, UserCheck } from 'lucide-react';

export default function FtpUsersPage() {
  const [users, setUsers] = useState<FtpUserItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchFtp = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await apiFetch<{ users: FtpUserItem[] }>('/ftp/users');
      setUsers(data.users);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch FTP accounts');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchFtp();
  }, []);

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
          <button
            onClick={fetchFtp}
            className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors"
          >
            <RefreshCw className="h-4 w-4" />
            Refresh
          </button>
        </div>

        {error && (
          <div className="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">
            {error}
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
                Default system credentials can access via SFTP/SSH.
              </p>
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
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>
    </Shell>
  );
}
