'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { Server, Play, Square, RotateCw, AlertCircle, CheckCircle2, RefreshCw } from 'lucide-react';
import { apiFetch, ServiceItem } from '@/lib/api';

export default function ServicesPage() {
  const [services, setServices] = useState<ServiceItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [actionLoading, setActionLoading] = useState<string | null>(null);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const loadServices = async () => {
    try {
      const res = await apiFetch<{ success: boolean; services: ServiceItem[] }>('/system/services');
      setServices(res.services);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    loadServices();
  }, []);

  const handleAction = async (service: string, action: 'restart' | 'start' | 'stop') => {
    setActionLoading(`${service}-${action}`);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; service: string; action: string }>('/system/services/action', {
        method: 'POST',
        body: JSON.stringify({ service, action }),
      });
      setMessage({
        text: `Service ${service} ${action} command sent successfully.`,
        success: res.success,
      });
      await loadServices();
    } catch (err: unknown) {
      if (err instanceof Error) {
        setMessage({ text: err.message, success: false });
      }
    } finally {
      setActionLoading(null);
    }
  };

  return (
    <Shell>
      <div className="space-y-6">
        <div className="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white md:text-3xl">Service Daemons</h1>
            <p className="text-sm text-slate-400">Monitor and manage essential Linux background services</p>
          </div>
          <button
            onClick={loadServices}
            disabled={loading}
            className="flex items-center gap-2 self-start rounded-xl border border-white/10 bg-slate-900/60 px-4 py-2 text-xs font-medium text-slate-200 hover:bg-white/10 transition-all"
          >
            <RefreshCw className={`h-3.5 w-3.5 ${loading ? 'animate-spin text-sky-400' : ''}`} />
            Refresh
          </button>
        </div>

        {message && (
          <div
            className={`flex items-center gap-3 rounded-xl p-4 text-sm border ${
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

        {/* Services List Table */}
        <div className="glass-panel overflow-hidden rounded-2xl">
          <div className="overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase tracking-wider text-slate-400">
                <tr>
                  <th className="px-6 py-4">Service</th>
                  <th className="px-6 py-4">Daemon Name</th>
                  <th className="px-6 py-4">Status</th>
                  <th className="px-6 py-4 text-right">Actions</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-white/5">
                {services.map((svc) => (
                  <tr key={svc.service} className="hover:bg-white/[0.02] transition-colors">
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-3">
                        <div
                          className={`h-2.5 w-2.5 rounded-full ${
                            svc.active ? 'bg-emerald-400 shadow-sm shadow-emerald-400/50' : 'bg-rose-500'
                          }`}
                        />
                        <span className="font-semibold text-white">{svc.title}</span>
                      </div>
                    </td>
                    <td className="px-6 py-4 font-mono text-xs text-slate-400">{svc.service}</td>
                    <td className="px-6 py-4">
                      <span
                        className={`inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-semibold uppercase tracking-wider ${
                          svc.active
                            ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                            : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                        }`}
                      >
                        {svc.state}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <div className="flex items-center justify-end gap-2">
                        <button
                          onClick={() => handleAction(svc.service, 'restart')}
                          disabled={actionLoading !== null}
                          className="flex items-center gap-1.5 rounded-lg border border-sky-500/20 bg-sky-500/10 px-3 py-1.5 text-xs font-medium text-sky-300 hover:bg-sky-500/20 transition-all disabled:opacity-50"
                        >
                          <RotateCw
                            className={`h-3 w-3 ${actionLoading === `${svc.service}-restart` ? 'animate-spin' : ''}`}
                          />
                          Restart
                        </button>
                        {svc.active ? (
                          <button
                            onClick={() => handleAction(svc.service, 'stop')}
                            disabled={actionLoading !== null}
                            className="flex items-center gap-1.5 rounded-lg border border-rose-500/20 bg-rose-500/10 px-3 py-1.5 text-xs font-medium text-rose-300 hover:bg-rose-500/20 transition-all disabled:opacity-50"
                          >
                            <Square className="h-3 w-3" />
                            Stop
                          </button>
                        ) : (
                          <button
                            onClick={() => handleAction(svc.service, 'start')}
                            disabled={actionLoading !== null}
                            className="flex items-center gap-1.5 rounded-lg border border-emerald-500/20 bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-300 hover:bg-emerald-500/20 transition-all disabled:opacity-50"
                          >
                            <Play className="h-3 w-3" />
                            Start
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
      </div>
    </Shell>
  );
}
