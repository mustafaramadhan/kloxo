'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import Shell from '@/components/layout/Shell';
import {
  Cpu,
  HardDrive,
  Activity,
  Clock,
  Server,
  Globe,
  Database,
  ArrowUpRight,
  RefreshCw,
  CheckCircle2,
  AlertTriangle,
  Terminal,
} from 'lucide-react';
import { apiFetch, SystemStatus, ServiceItem, formatBytes, formatUptime } from '@/lib/api';

export default function DashboardPage() {
  const [status, setStatus] = useState<SystemStatus | null>(null);
  const [services, setServices] = useState<ServiceItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  const loadData = async () => {
    try {
      const [statusRes, servicesRes] = await Promise.all([
        apiFetch<{ success: boolean; system: SystemStatus }>('/system/status'),
        apiFetch<{ success: boolean; services: ServiceItem[] }>('/system/services'),
      ]);
      setStatus(statusRes.system);
      setServices(servicesRes.services);
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadData();
    const interval = setInterval(loadData, 10000);
    return () => clearInterval(interval);
  }, []);

  const handleManualRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  return (
    <Shell>
      <div className="space-y-8">
        {/* Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white md:text-3xl">
              Server Overview
            </h1>
            <p className="text-sm text-slate-400">
              Host: <span className="font-mono text-sky-400">{status?.hostname || 'localhost'}</span> • {status?.os}
            </p>
          </div>
          <button
            onClick={handleManualRefresh}
            disabled={refreshing}
            className="flex items-center gap-2 self-start rounded-xl border border-white/10 bg-slate-900/60 px-4 py-2 text-xs font-medium text-slate-200 hover:bg-white/10 hover:border-white/20 transition-all"
          >
            <RefreshCw className={`h-3.5 w-3.5 ${refreshing ? 'animate-spin text-sky-400' : ''}`} />
            Refresh
          </button>
        </div>

        {/* Resource Metric Cards Grid */}
        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          {/* CPU Card */}
          <div className="glass-panel glass-panel-hover rounded-2xl p-6 relative overflow-hidden">
            <div className="flex items-center justify-between mb-4">
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">CPU Usage</span>
              <div className="rounded-xl bg-sky-500/10 p-2.5 text-sky-400 border border-sky-500/20">
                <Cpu className="h-5 w-5" />
              </div>
            </div>
            <div className="flex items-baseline gap-2 mb-2">
              <span className="text-3xl font-extrabold text-white">{status?.cpu.percent ?? 0}%</span>
              <span className="text-xs text-slate-400">({status?.cpu.cores} Cores)</span>
            </div>
            <div className="h-2 w-full rounded-full bg-slate-800 overflow-hidden">
              <div
                className="h-full bg-gradient-to-r from-sky-400 to-indigo-500 rounded-full transition-all duration-500"
                style={{ width: `${Math.min(100, Math.max(5, status?.cpu.percent ?? 0))}%` }}
              />
            </div>
            <p className="mt-3 text-xs text-slate-400">
              Load: {status?.load_average['1m']} • {status?.load_average['5m']} • {status?.load_average['15m']}
            </p>
          </div>

          {/* Memory Card */}
          <div className="glass-panel glass-panel-hover rounded-2xl p-6 relative overflow-hidden">
            <div className="flex items-center justify-between mb-4">
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">Memory (RAM)</span>
              <div className="rounded-xl bg-indigo-500/10 p-2.5 text-indigo-400 border border-indigo-500/20">
                <Activity className="h-5 w-5" />
              </div>
            </div>
            <div className="flex items-baseline gap-2 mb-2">
              <span className="text-3xl font-extrabold text-white">{status?.memory.percent ?? 0}%</span>
              <span className="text-xs text-slate-400">
                {formatBytes(status?.memory.used_bytes ?? 0)} / {formatBytes(status?.memory.total_bytes ?? 0)}
              </span>
            </div>
            <div className="h-2 w-full rounded-full bg-slate-800 overflow-hidden">
              <div
                className="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500"
                style={{ width: `${status?.memory.percent ?? 0}%` }}
              />
            </div>
            <p className="mt-3 text-xs text-slate-400">
              Free: {formatBytes(status?.memory.free_bytes ?? 0)}
            </p>
          </div>

          {/* Disk Card */}
          <div className="glass-panel glass-panel-hover rounded-2xl p-6 relative overflow-hidden">
            <div className="flex items-center justify-between mb-4">
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">Disk Storage</span>
              <div className="rounded-xl bg-emerald-500/10 p-2.5 text-emerald-400 border border-emerald-500/20">
                <HardDrive className="h-5 w-5" />
              </div>
            </div>
            <div className="flex items-baseline gap-2 mb-2">
              <span className="text-3xl font-extrabold text-white">{status?.disk.percent ?? 0}%</span>
              <span className="text-xs text-slate-400">
                {formatBytes(status?.disk.used_bytes ?? 0)} / {formatBytes(status?.disk.total_bytes ?? 0)}
              </span>
            </div>
            <div className="h-2 w-full rounded-full bg-slate-800 overflow-hidden">
              <div
                className="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-500"
                style={{ width: `${status?.disk.percent ?? 0}%` }}
              />
            </div>
            <p className="mt-3 text-xs text-slate-400">
              Available: {formatBytes(status?.disk.free_bytes ?? 0)}
            </p>
          </div>

          {/* Uptime Card */}
          <div className="glass-panel glass-panel-hover rounded-2xl p-6 relative overflow-hidden">
            <div className="flex items-center justify-between mb-4">
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">System Uptime</span>
              <div className="rounded-xl bg-amber-500/10 p-2.5 text-amber-400 border border-amber-500/20">
                <Clock className="h-5 w-5" />
              </div>
            </div>
            <div className="mb-2">
              <span className="text-3xl font-extrabold text-white">
                {status ? formatUptime(status.uptime_seconds) : '--'}
              </span>
            </div>
            <div className="mt-4 flex items-center gap-2">
              <span className="h-2.5 w-2.5 rounded-full bg-emerald-400 animate-ping" />
              <span className="text-xs font-medium text-emerald-400">All Nodes Responsive</span>
            </div>
          </div>
        </div>

        {/* Services & Quick Navigation Grid */}
        <div className="grid gap-6 lg:grid-cols-3">
          {/* Services Health */}
          <div className="glass-panel rounded-2xl p-6 lg:col-span-2">
            <div className="flex items-center justify-between mb-6">
              <div>
                <h2 className="text-lg font-bold text-white">Core Services</h2>
                <p className="text-xs text-slate-400">Live operational status of system daemons</p>
              </div>
              <Link
                href="/services"
                className="flex items-center gap-1 text-xs font-medium text-sky-400 hover:text-sky-300"
              >
                Manage Services <ArrowUpRight className="h-4 w-4" />
              </Link>
            </div>

            <div className="grid gap-3 sm:grid-cols-2">
              {services.slice(0, 6).map((svc) => (
                <div
                  key={svc.service}
                  className="flex items-center justify-between rounded-xl border border-white/5 bg-slate-900/50 p-3.5 hover:border-white/10 transition-all"
                >
                  <div className="flex items-center gap-3">
                    <div
                      className={`h-2.5 w-2.5 rounded-full ${
                        svc.active ? 'bg-emerald-400 shadow-sm shadow-emerald-400/50' : 'bg-rose-500'
                      }`}
                    />
                    <div>
                      <p className="text-sm font-medium text-white">{svc.title}</p>
                      <p className="text-xs text-slate-500 font-mono">{svc.service}</p>
                    </div>
                  </div>
                  <span
                    className={`rounded-lg px-2.5 py-1 text-xs font-semibold uppercase tracking-wider ${
                      svc.active
                        ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                        : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                    }`}
                  >
                    {svc.state}
                  </span>
                </div>
              ))}
            </div>
          </div>

          {/* Quick Actions Panel */}
          <div className="glass-panel rounded-2xl p-6 flex flex-col justify-between">
            <div>
              <h2 className="text-lg font-bold text-white mb-1">Quick Actions</h2>
              <p className="text-xs text-slate-400 mb-6">Essential control panel management</p>

              <div className="space-y-3">
                <Link
                  href="/domains"
                  className="flex items-center justify-between rounded-xl border border-white/5 bg-slate-900/40 p-3 text-sm text-slate-200 hover:bg-white/5 hover:border-sky-500/30 transition-all"
                >
                  <span className="flex items-center gap-3">
                    <Globe className="h-4 w-4 text-sky-400" />
                    Domains & VHosts
                  </span>
                  <ArrowUpRight className="h-4 w-4 text-slate-400" />
                </Link>

                <Link
                  href="/databases"
                  className="flex items-center justify-between rounded-xl border border-white/5 bg-slate-900/40 p-3 text-sm text-slate-200 hover:bg-white/5 hover:border-indigo-500/30 transition-all"
                >
                  <span className="flex items-center gap-3">
                    <Database className="h-4 w-4 text-indigo-400" />
                    MariaDB Databases
                  </span>
                  <ArrowUpRight className="h-4 w-4 text-slate-400" />
                </Link>

                <a
                  href="/legacy/"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="flex items-center justify-between rounded-xl border border-amber-500/20 bg-amber-500/5 p-3 text-sm text-amber-200 hover:bg-amber-500/10 transition-all"
                >
                  <span className="flex items-center gap-3">
                    <Terminal className="h-4 w-4 text-amber-400" />
                    Classic Kloxo Engine
                  </span>
                  <ArrowUpRight className="h-4 w-4 text-amber-400" />
                </a>
              </div>
            </div>

            <div className="mt-6 rounded-xl border border-sky-500/20 bg-sky-500/5 p-3.5">
              <p className="text-xs font-medium text-sky-300">Modern Architecture Active</p>
              <p className="text-[11px] text-slate-400 mt-0.5">
                Next.js 15 App Router interacting via native FastCGI JSON Gateway.
              </p>
            </div>
          </div>
        </div>
      </div>
    </Shell>
  );
}
