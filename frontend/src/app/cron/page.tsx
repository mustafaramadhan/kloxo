'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, CronJobItem } from '@/lib/api';
import { Clock, RefreshCw, Terminal, Calendar, Plus, Trash2, X, AlertCircle, CheckCircle2 } from 'lucide-react';

export default function CronJobsPage() {
  const [jobs, setJobs] = useState<CronJobItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Modal & form states
  const [showAddModal, setShowAddModal] = useState(false);
  const [command, setCommand] = useState('');
  const [schedule, setSchedule] = useState('daily');
  const [actionLoading, setActionLoading] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchCron = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await apiFetch<{ jobs: CronJobItem[] }>('/cron/jobs');
      setJobs(data.jobs);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch cron tasks');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchCron();
  }, []);

  const handleCreateCron = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!command.trim()) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/cron/jobs', {
        method: 'POST',
        body: JSON.stringify({
          command: command.trim(),
          schedule,
        }),
      });
      setMessage({ text: res.message || 'Cron job created successfully', success: true });
      setShowAddModal(false);
      setCommand('');
      setSchedule('daily');
      await fetchCron();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create cron task', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDeleteCron = async (id: string) => {
    if (!confirm(`Are you sure you want to delete scheduled task "${id}"?`)) return;
    setActionLoading(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/cron/jobs', {
        method: 'DELETE',
        body: JSON.stringify({ id }),
      });
      setMessage({ text: res.message || 'Cron task deleted successfully', success: true });
      await fetchCron();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete cron task', success: false });
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
              <Clock className="h-6 w-6 text-sky-400" />
              Scheduled Tasks (Cron)
            </h1>
            <p className="text-sm text-slate-400">
              Automated system and client cron execution schedules
            </p>
          </div>
          <div className="flex items-center gap-3">
            <button
              onClick={fetchCron}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </button>
            <button
              onClick={() => setShowAddModal(true)}
              className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
            >
              <Plus className="h-4 w-4" />
              Add Cron Job
            </button>
          </div>
        </div>

        {/* Status Notification */}
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
            <button
              onClick={() => setMessage(null)}
              className="text-slate-400 hover:text-white"
            >
              <X className="h-4 w-4" />
            </button>
          </div>
        )}

        {error && (
          <div className="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">
            {error}
          </div>
        )}

        {/* Cron Jobs Table */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Loading cron jobs...</div>
          ) : jobs.length === 0 ? (
            <div className="p-16 text-center">
              <Calendar className="mx-auto h-12 w-12 text-slate-600 mb-3" />
              <p className="text-slate-400 font-medium">No scheduled tasks defined</p>
              <p className="text-xs text-slate-500 mt-1">
                Click &quot;Add Cron Job&quot; to configure automated commands and scripts.
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Job ID</th>
                    <th className="px-6 py-4">Command</th>
                    <th className="px-6 py-4">Schedule</th>
                    <th className="px-6 py-4">Type</th>
                    <th className="px-6 py-4">Owner</th>
                    <th className="px-6 py-4 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {jobs.map((job) => (
                    <tr key={job.id} className="hover:bg-white/[0.02] transition-colors">
                      <td className="px-6 py-4 font-mono text-xs text-sky-400 font-semibold">
                        {job.id}
                      </td>
                      <td className="px-6 py-4 font-mono text-xs text-slate-200 max-w-md truncate">
                        <div className="flex items-center gap-2">
                          <Terminal className="h-4 w-4 text-slate-500 flex-shrink-0" />
                          <span className="truncate">{job.command}</span>
                        </div>
                      </td>
                      <td className="px-6 py-4">
                        <span className="inline-flex items-center gap-1.5 rounded-lg bg-sky-500/10 border border-sky-500/20 px-2.5 py-1 text-xs font-mono text-sky-300">
                          <Clock className="h-3 w-3" />
                          {job.schedule}
                        </span>
                      </td>
                      <td className="px-6 py-4 capitalize text-xs text-slate-400">{job.type}</td>
                      <td className="px-6 py-4 text-slate-400">{job.owner}</td>
                      <td className="px-6 py-4 text-right">
                        <button
                          onClick={() => handleDeleteCron(job.id)}
                          disabled={actionLoading}
                          className="inline-flex items-center gap-1 text-xs text-rose-400 hover:text-rose-300 transition-colors p-1.5 rounded-lg hover:bg-rose-500/10 disabled:opacity-50"
                          title="Delete Cron Task"
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

      {/* Add Cron Modal */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl animate-in fade-in zoom-in-95 duration-150">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Clock className="h-5 w-5 text-sky-400" />
                Add Scheduled Task (Cron)
              </h2>
              <button
                onClick={() => setShowAddModal(false)}
                className="text-slate-400 hover:text-white transition-colors"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreateCron} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Command or Script
                </label>
                <input
                  type="text"
                  required
                  placeholder="e.g. php /home/admin/mysite.com/cron.php"
                  value={command}
                  onChange={(e) => setCommand(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                />
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Execution Schedule
                </label>
                <select
                  value={schedule}
                  onChange={(e) => setSchedule(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none focus:ring-1 focus:ring-sky-500"
                >
                  <option value="every_minute">Every minute (* * * * *)</option>
                  <option value="every_5_minutes">Every 5 minutes (*/5 * * * *)</option>
                  <option value="hourly">Hourly (0 * * * *)</option>
                  <option value="daily">Daily at midnight (0 0 * * *)</option>
                  <option value="weekly">Weekly on Sunday (0 0 * * 0)</option>
                  <option value="monthly">Monthly (0 0 1 * *)</option>
                </select>
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-300 hover:bg-white/10 transition-colors"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-sm font-medium text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all disabled:opacity-50"
                >
                  {actionLoading ? 'Creating...' : 'Create Cron Task'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </Shell>
  );
}
