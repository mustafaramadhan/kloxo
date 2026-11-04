'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { Database, Plus, RefreshCw, HardDrive, User } from 'lucide-react';
import { apiFetch, DatabaseItem } from '@/lib/api';

export default function DatabasesPage() {
  const [databases, setDatabases] = useState<DatabaseItem[]>([]);
  const [loading, setLoading] = useState(true);

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

  return (
    <Shell>
      <div className="space-y-6">
        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white md:text-3xl">MariaDB Databases</h1>
            <p className="text-sm text-slate-400">Manage MySQL & MariaDB schemas and database users</p>
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
            <a
              href="/legacy/display.php?frm_action=addform&frm_o_cname=mysqldb"
              target="_blank"
              rel="noopener noreferrer"
              className="flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" /> Create Database
            </a>
          </div>
        </div>

        {/* Databases Table or Empty State */}
        {databases.length === 0 && !loading ? (
          <div className="glass-panel rounded-2xl p-12 text-center">
            <div className="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
              <Database className="h-8 w-8" />
            </div>
            <h3 className="text-lg font-bold text-white">No Databases Configured</h3>
            <p className="mt-1 text-sm text-slate-400 max-w-sm mx-auto">
              Create your first MariaDB database to host dynamic web applications like WordPress.
            </p>
            <a
              href="/legacy/display.php?frm_action=addform&frm_o_cname=mysqldb"
              target="_blank"
              rel="noopener noreferrer"
              className="mt-6 inline-flex items-center gap-2 rounded-xl bg-indigo-500/10 border border-indigo-500/20 px-4 py-2 text-sm font-semibold text-indigo-400 hover:bg-indigo-500/20 transition-all"
            >
              <Plus className="h-4 w-4" /> Create Database
            </a>
          </div>
        ) : (
          <div className="glass-panel overflow-hidden rounded-2xl">
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase tracking-wider text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Database Name</th>
                    <th className="px-6 py-4">DB User</th>
                    <th className="px-6 py-4">Owner</th>
                    <th className="px-6 py-4">Disk Usage</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {databases.map((db) => (
                    <tr key={db.name} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-semibold text-white">
                        <div className="flex items-center gap-2">
                          <Database className="h-4 w-4 text-indigo-400" />
                          <span className="font-mono">{db.name}</span>
                        </div>
                      </td>
                      <td className="px-6 py-4 text-slate-300 font-mono text-xs">
                        <div className="flex items-center gap-1.5">
                          <User className="h-3.5 w-3.5 text-slate-500" />
                          {db.username || 'N/A'}
                        </div>
                      </td>
                      <td className="px-6 py-4 text-slate-400">{db.owner}</td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">
                        {db.usage_mb > 0 ? `${db.usage_mb} MB` : '< 1 MB'}
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
