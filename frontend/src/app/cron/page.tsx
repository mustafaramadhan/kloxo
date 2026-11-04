'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, CronJobItem } from '@/lib/api';
import { Clock, CheckCircle2, RefreshCw, Terminal, Calendar } from 'lucide-react';

export default function CronJobsPage() {
  const [jobs, setJobs] = useState<CronJobItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

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
          <button
            onClick={fetchCron}
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

        {/* Cron Jobs Table */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Loading cron jobs...</div>
          ) : jobs.length === 0 ? (
            <div className="p-16 text-center">
              <Calendar className="mx-auto h-12 w-12 text-slate-600 mb-3" />
              <p className="text-slate-400 font-medium">No scheduled tasks defined</p>
              <p className="text-xs text-slate-500 mt-1">
                Background maintenance runs through the core wrapper daemon.
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
