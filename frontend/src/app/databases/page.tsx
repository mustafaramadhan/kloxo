'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { Database, Plus, RefreshCw, Trash2, X, AlertCircle, CheckCircle2, User, Key, Terminal, Play, Table } from 'lucide-react';
import { apiFetch, DatabaseItem, SqlQueryResult } from '@/lib/api';

export default function DatabasesPage() {
  const [databases, setDatabases] = useState<DatabaseItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [showAddModal, setShowAddModal] = useState(false);
  const [formData, setFormData] = useState({ name: '', username: '', password: '' });
  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  // SQL Console Modal
  const [showSqlModal, setShowSqlModal] = useState(false);
  const [selectedDbForSql, setSelectedDbForSql] = useState('');
  const [sqlQuery, setSqlQuery] = useState('SHOW TABLES;');
  const [sqlLoading, setSqlLoading] = useState(false);
  const [sqlResult, setSqlResult] = useState<SqlQueryResult | null>(null);
  const [sqlError, setSqlError] = useState<string | null>(null);

  const loadDatabases = async () => {
    try {
      const res = await apiFetch<{ success: boolean; databases: DatabaseItem[] }>('/databases');
      setDatabases(res.databases);
      if (res.databases.length > 0 && !selectedDbForSql) {
        setSelectedDbForSql(res.databases[0].name);
      }
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

  const handleExecuteSql = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedDbForSql || !sqlQuery.trim()) return;
    setSqlLoading(true);
    setSqlError(null);
    setSqlResult(null);
    try {
      const res = await apiFetch<SqlQueryResult>('/databases/query', {
        method: 'POST',
        body: JSON.stringify({
          database: selectedDbForSql,
          query: sqlQuery.trim(),
        }),
      });
      setSqlResult(res);
    } catch (err: any) {
      setSqlError(err.message || 'Failed to execute query');
    } finally {
      setSqlLoading(false);
    }
  };

  const openSqlForDb = (dbName: string) => {
    setSelectedDbForSql(dbName);
    setSqlQuery('SHOW TABLES;');
    setSqlResult(null);
    setSqlError(null);
    setShowSqlModal(true);
  };

  return (
    <Shell>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
              <Database className="h-6 w-6 text-sky-400" />
              MySQL / MariaDB Databases
            </h1>
            <p className="text-sm text-slate-400">
              Manage database instances, users, permissions, and run live SQL queries
            </p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={loadDatabases}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </button>
            <button
              onClick={() => {
                setShowSqlModal(true);
                setSqlQuery('SHOW DATABASES;');
                setSqlResult(null);
              }}
              className="inline-flex items-center gap-2 rounded-xl border border-purple-500/30 bg-purple-500/10 px-4 py-2 text-sm font-medium text-purple-300 hover:bg-purple-500/20 transition-all"
            >
              <Terminal className="h-4 w-4" />
              SQL Console
            </button>
            <button
              onClick={() => setShowAddModal(true)}
              className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" />
              Create Database
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

        {/* Databases Table */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Loading databases...</div>
          ) : databases.length === 0 ? (
            <div className="p-16 text-center">
              <Database className="mx-auto h-12 w-12 text-slate-600 mb-3" />
              <p className="text-slate-400 font-medium">No databases created yet</p>
              <p className="text-xs text-slate-500 mt-1">
                Click &quot;Create Database&quot; to provision a new MariaDB database instance.
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
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
                      <td className="px-6 py-4 font-medium text-white flex items-center gap-2">
                        <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-400">
                          <Database className="h-4 w-4" />
                        </div>
                        <span className="font-mono">{db.name}</span>
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-400">{db.username}</td>
                      <td className="px-6 py-4 text-slate-400">{db.owner}</td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-300">{db.usage_mb} MB</td>
                      <td className="px-6 py-4 text-right">
                        <div className="flex items-center justify-end gap-2">
                          <button
                            onClick={() => openSqlForDb(db.name)}
                            className="inline-flex items-center gap-1.5 rounded-lg border border-purple-500/30 bg-purple-500/10 px-2.5 py-1 text-xs font-medium text-purple-300 hover:bg-purple-500/20 transition-colors"
                            title="Run SQL Query"
                          >
                            <Terminal className="h-3.5 w-3.5" />
                            Query
                          </button>
                          <button
                            onClick={() => handleDeleteDatabase(db.name)}
                            disabled={actionLoading}
                            className="inline-flex items-center p-1.5 text-rose-400 hover:text-rose-300 hover:bg-rose-500/10 rounded-lg transition-colors"
                            title="Delete Database"
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

      {/* SQL Query Console Modal */}
      {showSqlModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/75 backdrop-blur-md p-4">
          <div className="w-full max-w-4xl rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl max-h-[90vh] flex flex-col">
            <div className="flex items-center justify-between pb-4 border-b border-white/5 flex-shrink-0">
              <div className="flex items-center gap-2">
                <Terminal className="h-5 w-5 text-purple-400" />
                <h2 className="text-lg font-bold text-white">Interactive Web SQL Console</h2>
              </div>
              <button onClick={() => setShowSqlModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleExecuteSql} className="mt-4 space-y-3 flex-shrink-0">
              <div className="flex items-center gap-3">
                <label className="text-xs font-semibold uppercase text-slate-400 flex-shrink-0">Database:</label>
                <select
                  value={selectedDbForSql}
                  onChange={(e) => setSelectedDbForSql(e.target.value)}
                  className="rounded-xl border border-white/10 bg-slate-950 px-3 py-1.5 text-xs text-white focus:border-purple-500 focus:outline-none"
                >
                  <option value="kloxo">kloxo (System Database)</option>
                  {databases.map((d) => (
                    <option key={d.name} value={d.name}>
                      {d.name}
                    </option>
                  ))}
                </select>
                <div className="flex-1" />
                <button
                  type="submit"
                  disabled={sqlLoading}
                  className="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-4 py-1.5 text-xs font-semibold text-white hover:from-purple-400 hover:to-indigo-500 transition-all disabled:opacity-50"
                >
                  <Play className="h-3.5 w-3.5 fill-white" />
                  {sqlLoading ? 'Executing...' : 'Run Query'}
                </button>
              </div>

              <div>
                <textarea
                  rows={4}
                  value={sqlQuery}
                  onChange={(e) => setSqlQuery(e.target.value)}
                  placeholder="SELECT * FROM table LIMIT 10;"
                  className="w-full rounded-2xl border border-white/10 bg-slate-950 p-3 font-mono text-xs text-white placeholder-slate-600 focus:border-purple-500 focus:outline-none leading-relaxed"
                />
              </div>
            </form>

            {/* SQL Results Viewer */}
            <div className="mt-4 flex-1 overflow-y-auto rounded-2xl border border-white/5 bg-slate-950/60 p-4 min-h-[160px]">
              {sqlLoading ? (
                <div className="p-8 text-center text-slate-400 animate-pulse text-xs">Executing SQL query...</div>
              ) : sqlError ? (
                <div className="p-4 rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-300 font-mono text-xs">
                  {sqlError}
                </div>
              ) : sqlResult ? (
                <div>
                  <div className="flex items-center justify-between pb-3 border-b border-white/5 mb-3 text-xs text-slate-400">
                    <span>
                      {sqlResult.type === 'select'
                        ? `Returned ${sqlResult.total_rows} rows in ${sqlResult.duration_ms} ms`
                        : sqlResult.message}
                    </span>
                  </div>

                  {sqlResult.type === 'select' && sqlResult.columns && sqlResult.rows && (
                    <div className="overflow-x-auto">
                      <table className="w-full text-left text-xs text-slate-300 font-mono">
                        <thead className="border-b border-white/10 bg-slate-900 text-sky-400 uppercase">
                          <tr>
                            {sqlResult.columns.map((col) => (
                              <th key={col} className="px-3 py-2">
                                {col}
                              </th>
                            ))}
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-white/5">
                          {sqlResult.rows.map((row, idx) => (
                            <tr key={idx} className="hover:bg-white/[0.02]">
                              {sqlResult.columns!.map((col) => (
                                <td key={col} className="px-3 py-1.5 truncate max-w-xs">
                                  {row[col] !== null ? String(row[col]) : <span className="text-slate-600 italic">NULL</span>}
                                </td>
                              ))}
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  )}
                </div>
              ) : (
                <div className="p-8 text-center text-slate-600 text-xs">
                  Enter an SQL statement above and click &quot;Run Query&quot;.
                </div>
              )}
            </div>
          </div>
        </div>
      )}

      {/* Create Database Modal */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Database className="h-5 w-5 text-sky-400" />
                Create New MySQL Database
              </h2>
              <button onClick={() => setShowAddModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreateDatabase} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Database Name
                </label>
                <input
                  type="text"
                  required
                  placeholder="e.g. myapp_db"
                  value={formData.name}
                  onChange={(e) =>
                    setFormData({
                      ...formData,
                      name: e.target.value,
                      username: formData.username || e.target.value,
                    })
                  }
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1 flex items-center gap-1">
                  <User className="h-3 w-3" />
                  Database Username
                </label>
                <input
                  type="text"
                  required
                  placeholder="e.g. myapp_user"
                  value={formData.username}
                  onChange={(e) => setFormData({ ...formData, username: e.target.value })}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1 flex items-center gap-1">
                  <Key className="h-3 w-3" />
                  Password
                </label>
                <input
                  type="password"
                  required
                  placeholder="Strong database password"
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Creating...' : 'Create Database'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </Shell>
  );
}
