'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, LogResponse, PhpInfoData } from '@/lib/api';
import { Terminal, RefreshCw, Cpu, Layers, CheckCircle, FileText, Code2 } from 'lucide-react';

export default function LogsPage() {
  const [activeTab, setActiveTab] = useState<'logs' | 'php'>('logs');
  const [selectedService, setSelectedService] = useState<'nginx' | 'php' | 'kloxo'>('nginx');
  const [logData, setLogData] = useState<LogResponse | null>(null);
  const [phpInfo, setPhpInfo] = useState<PhpInfoData | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchLogs = async (service: string) => {
    setLoading(true);
    setError(null);
    try {
      const data = await apiFetch<LogResponse>(`/system/logs?service=${service}`);
      setLogData(data);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch logs');
    } finally {
      setLoading(false);
    }
  };

  const fetchPhpInfo = async () => {
    setLoading(true);
    setError(null);
    try {
      const data = await apiFetch<{ php: PhpInfoData }>('/system/phpinfo');
      setPhpInfo(data.php);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch PHP configuration');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    if (activeTab === 'logs') {
      fetchLogs(selectedService);
    } else {
      fetchPhpInfo();
    }
  }, [activeTab, selectedService]);

  return (
    <Shell>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
              <Terminal className="h-6 w-6 text-sky-400" />
              Diagnostics & Server Environment
            </h1>
            <p className="text-sm text-slate-400">
              Live daemon error logs, PHP runtime metrics, and system extensions
            </p>
          </div>
          <button
            onClick={() => (activeTab === 'logs' ? fetchLogs(selectedService) : fetchPhpInfo())}
            className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors"
          >
            <RefreshCw className="h-4 w-4" />
            Refresh
          </button>
        </div>

        {/* Tab Selector */}
        <div className="flex items-center gap-2 border-b border-white/5 pb-2">
          <button
            onClick={() => setActiveTab('logs')}
            className={`flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all ${
              activeTab === 'logs'
                ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30 shadow-sm'
                : 'text-slate-400 hover:text-white hover:bg-white/5'
            }`}
          >
            <Terminal className="h-4 w-4" />
            Live Logs
          </button>
          <button
            onClick={() => setActiveTab('php')}
            className={`flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all ${
              activeTab === 'php'
                ? 'bg-sky-500/20 text-sky-400 border border-sky-500/30 shadow-sm'
                : 'text-slate-400 hover:text-white hover:bg-white/5'
            }`}
          >
            <Code2 className="h-4 w-4" />
            PHP Engine Info
          </button>
        </div>

        {error && (
          <div className="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">
            {error}
          </div>
        )}

        {/* LOGS TAB */}
        {activeTab === 'logs' && (
          <div className="space-y-4">
            {/* Service selector pills */}
            <div className="flex items-center gap-2">
              {(['nginx', 'php', 'kloxo'] as const).map((s) => (
                <button
                  key={s}
                  onClick={() => setSelectedService(s)}
                  className={`rounded-lg px-3 py-1.5 text-xs font-semibold uppercase tracking-wider transition-colors ${
                    selectedService === s
                      ? 'bg-gradient-to-r from-sky-500 to-indigo-600 text-white shadow-md'
                      : 'bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white'
                  }`}
                >
                  {s.toUpperCase()} Log
                </button>
              ))}
              {logData && (
                <span className="text-xs font-mono text-slate-500 ml-auto truncate hidden md:inline">
                  {logData.file}
                </span>
              )}
            </div>

            {/* Terminal Window */}
            <div className="rounded-3xl border border-white/5 bg-slate-950 p-4 font-mono text-xs shadow-2xl overflow-hidden">
              <div className="flex items-center gap-2 border-b border-white/5 pb-3 mb-3">
                <span className="h-3 w-3 rounded-full bg-rose-500/80" />
                <span className="h-3 w-3 rounded-full bg-amber-500/80" />
                <span className="h-3 w-3 rounded-full bg-emerald-500/80" />
                <span className="text-xs text-slate-500 ml-2">Console Output (Last 100 entries)</span>
              </div>
              <div className="h-[480px] overflow-y-auto space-y-1 text-slate-300 select-text">
                {loading ? (
                  <div className="text-slate-500 animate-pulse">Reading log stream...</div>
                ) : !logData || logData.lines.length === 0 || (logData.lines.length === 1 && !logData.lines[0]) ? (
                  <div className="text-slate-500">Log file is currently clean (no errors logged).</div>
                ) : (
                  logData.lines.map((line, idx) => (
                    <div
                      key={idx}
                      className={`py-0.5 leading-relaxed break-all ${
                        line.toLowerCase().includes('error') || line.toLowerCase().includes('fatal')
                          ? 'text-rose-400'
                          : line.toLowerCase().includes('warning') || line.toLowerCase().includes('warn')
                          ? 'text-amber-400'
                          : line.toLowerCase().includes('notice')
                          ? 'text-sky-300'
                          : 'text-slate-300'
                      }`}
                    >
                      {line}
                    </div>
                  ))
                )}
              </div>
            </div>
          </div>
        )}

        {/* PHP INFO TAB */}
        {activeTab === 'php' && (
          <div className="space-y-6">
            {loading || !phpInfo ? (
              <div className="p-12 text-center text-slate-400 animate-pulse">
                Fetching PHP environment...
              </div>
            ) : (
              <>
                {/* Metrics Cards */}
                <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                  <div className="rounded-2xl border border-white/5 bg-slate-900/60 p-5 backdrop-blur-xl">
                    <p className="text-xs font-medium text-slate-400 uppercase tracking-wider">
                      PHP Version
                    </p>
                    <p className="mt-2 text-xl font-bold text-white">{phpInfo.version}</p>
                    <span className="text-xs text-sky-400 font-mono mt-1 block">{phpInfo.sapi}</span>
                  </div>
                  <div className="rounded-2xl border border-white/5 bg-slate-900/60 p-5 backdrop-blur-xl">
                    <p className="text-xs font-medium text-slate-400 uppercase tracking-wider">
                      Memory Limit
                    </p>
                    <p className="mt-2 text-xl font-bold text-white">{phpInfo.memory_limit}</p>
                    <span className="text-xs text-slate-500 mt-1 block">per worker request</span>
                  </div>
                  <div className="rounded-2xl border border-white/5 bg-slate-900/60 p-5 backdrop-blur-xl">
                    <p className="text-xs font-medium text-slate-400 uppercase tracking-wider">
                      Upload Limit
                    </p>
                    <p className="mt-2 text-xl font-bold text-white">{phpInfo.upload_max_filesize}</p>
                    <span className="text-xs text-slate-500 mt-1 block">Post: {phpInfo.post_max_size}</span>
                  </div>
                  <div className="rounded-2xl border border-white/5 bg-slate-900/60 p-5 backdrop-blur-xl">
                    <p className="text-xs font-medium text-slate-400 uppercase tracking-wider">
                      OPcache
                    </p>
                    <p className="mt-2 text-xl font-bold text-emerald-400 flex items-center gap-1.5">
                      <CheckCircle className="h-5 w-5" />
                      {phpInfo.opcache_enabled ? 'Active' : 'Disabled'}
                    </p>
                    <span className="text-xs text-slate-500 mt-1 block">Bytecode accelerator</span>
                  </div>
                </div>

                {/* Extensions List */}
                <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl p-6 shadow-2xl">
                  <h3 className="text-sm font-semibold text-white mb-4 flex items-center gap-2">
                    <Layers className="h-4 w-4 text-sky-400" />
                    Compiled & Loaded Extensions ({phpInfo.extensions.length})
                  </h3>
                  <div className="flex flex-wrap gap-2">
                    {phpInfo.extensions.map((ext) => (
                      <span
                        key={ext}
                        className="rounded-lg border border-white/5 bg-slate-950/60 px-3 py-1.5 text-xs font-mono text-slate-300 hover:border-sky-500/30 hover:text-sky-400 transition-colors"
                      >
                        {ext}
                      </span>
                    ))}
                  </div>
                </div>
              </>
            )}
          </div>
        )}
      </div>
    </Shell>
  );
}
