'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import Shell from '@/components/layout/Shell';
import { apiFetch, PhpConfigResponse } from '@/lib/api';
import { Sliders, SlidersHorizontal, RefreshCw, CheckCircle2, AlertCircle, Save, Cpu, Box, Search } from 'lucide-react';

export default function PhpConfigPage() {
  const [phpVersion, setPhpVersion] = useState<string>('');
  const [memoryLimit, setMemoryLimit] = useState('256M');
  const [uploadMaxFilesize, setUploadMaxFilesize] = useState('64M');
  const [postMaxSize, setPostMaxSize] = useState('64M');
  const [maxExecutionTime, setMaxExecutionTime] = useState('300');
  const [maxInputTime, setMaxInputTime] = useState('300');
  const [displayErrors, setDisplayErrors] = useState('Off');
  const [extensions, setExtensions] = useState<string[]>([]);
  const [extSearch, setExtSearch] = useState('');

  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchConfig = async () => {
    setLoading(true);
    setMessage(null);
    try {
      const data = await apiFetch<PhpConfigResponse>('/system/php-config');
      setPhpVersion(data.php_version);
      setMemoryLimit(data.directives.memory_limit || '256M');
      setUploadMaxFilesize(data.directives.upload_max_filesize || '64M');
      setPostMaxSize(data.directives.post_max_size || '64M');
      setMaxExecutionTime(data.directives.max_execution_time || '300');
      setMaxInputTime(data.directives.max_input_time || '300');
      setDisplayErrors(data.directives.display_errors || 'Off');
      setExtensions(data.extensions || []);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to fetch PHP configuration', success: false });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchConfig();
  }, []);

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/system/php-config', {
        method: 'POST',
        body: JSON.stringify({
          memory_limit: memoryLimit,
          upload_max_filesize: uploadMaxFilesize,
          post_max_size: postMaxSize,
          max_execution_time: maxExecutionTime,
          max_input_time: maxInputTime,
          display_errors: displayErrors,
        }),
      });
      setMessage({ text: res.message || 'PHP directives saved and reloaded', success: true });
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to update PHP configuration', success: false });
    } finally {
      setSaving(false);
    }
  };

  const filteredExtensions = extensions.filter((ext) =>
    ext.toLowerCase().includes(extSearch.toLowerCase())
  );

  return (
    <Shell>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
              <Sliders className="h-6 w-6 text-sky-400" />
              PHP Engine & Core Directives
            </h1>
            <p className="text-sm text-slate-400">
              Tune PHP runtime parameters, limits, error reporting, and active extensions
            </p>
          </div>
          <div className="flex items-center gap-3">
            <Link
              href="/php-advanced"
              className="inline-flex items-center gap-2 rounded-xl border border-sky-500/30 bg-sky-500/10 px-4 py-2 text-sm font-medium text-sky-300 hover:bg-sky-500/20 transition-colors"
            >
              <SlidersHorizontal className="h-4 w-4" />
              Advanced PHP Configure
            </Link>
            <button
              onClick={fetchConfig}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
              Refresh
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
              ✕
            </button>
          </div>
        )}

        {/* Main Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Directive Tuning Form */}
          <div className="lg:col-span-2 rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5 mb-6">
              <div className="flex items-center gap-2">
                <Cpu className="h-5 w-5 text-sky-400" />
                <h2 className="text-lg font-bold text-white">PHP Runtime Directives</h2>
              </div>
              <span className="rounded-lg bg-sky-500/10 border border-sky-500/20 px-2.5 py-1 text-xs font-mono font-semibold text-sky-300">
                v{phpVersion || '8.5'}
              </span>
            </div>

            {loading ? (
              <div className="p-12 text-center text-slate-400 animate-pulse">Loading directives...</div>
            ) : (
              <form onSubmit={handleSave} className="space-y-5">
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5">
                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                      Memory Limit
                    </label>
                    <input
                      type="text"
                      required
                      value={memoryLimit}
                      onChange={(e) => setMemoryLimit(e.target.value)}
                      placeholder="256M"
                      className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white focus:border-sky-500 focus:outline-none"
                    />
                    <p className="text-[11px] text-slate-500 mt-1">e.g. 128M, 256M, 512M</p>
                  </div>

                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                      Upload Max Filesize
                    </label>
                    <input
                      type="text"
                      required
                      value={uploadMaxFilesize}
                      onChange={(e) => setUploadMaxFilesize(e.target.value)}
                      placeholder="64M"
                      className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white focus:border-sky-500 focus:outline-none"
                    />
                    <p className="text-[11px] text-slate-500 mt-1">Max upload per individual file</p>
                  </div>

                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                      Post Max Size
                    </label>
                    <input
                      type="text"
                      required
                      value={postMaxSize}
                      onChange={(e) => setPostMaxSize(e.target.value)}
                      placeholder="64M"
                      className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white focus:border-sky-500 focus:outline-none"
                    />
                    <p className="text-[11px] text-slate-500 mt-1">Total POST payload size limit</p>
                  </div>

                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                      Max Execution Time (Sec)
                    </label>
                    <input
                      type="number"
                      required
                      value={maxExecutionTime}
                      onChange={(e) => setMaxExecutionTime(e.target.value)}
                      placeholder="300"
                      className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white focus:border-sky-500 focus:outline-none"
                    />
                    <p className="text-[11px] text-slate-500 mt-1">Script execution timeout in seconds</p>
                  </div>

                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                      Max Input Time (Sec)
                    </label>
                    <input
                      type="number"
                      required
                      value={maxInputTime}
                      onChange={(e) => setMaxInputTime(e.target.value)}
                      placeholder="300"
                      className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white focus:border-sky-500 focus:outline-none"
                    />
                    <p className="text-[11px] text-slate-500 mt-1">Data parse timeout</p>
                  </div>

                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                      Display Errors
                    </label>
                    <select
                      value={displayErrors}
                      onChange={(e) => setDisplayErrors(e.target.value)}
                      className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 text-sm text-white focus:border-sky-500 focus:outline-none"
                    >
                      <option value="Off">Off (Production Safe)</option>
                      <option value="On">On (Development Mode)</option>
                    </select>
                    <p className="text-[11px] text-slate-500 mt-1">Output errors directly to browser</p>
                  </div>
                </div>

                <div className="pt-4 border-t border-white/5 flex justify-end">
                  <button
                    type="submit"
                    disabled={saving}
                    className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all disabled:opacity-50"
                  >
                    <Save className="h-4 w-4" />
                    {saving ? 'Applying...' : 'Save & Reload PHP'}
                  </button>
                </div>
              </form>
            )}
          </div>

          {/* Installed Extensions List */}
          <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl p-6 shadow-2xl flex flex-col h-[520px]">
            <div className="flex items-center justify-between pb-3 border-b border-white/5 flex-shrink-0">
              <div className="flex items-center gap-2">
                <Box className="h-5 w-5 text-indigo-400" />
                <h3 className="text-md font-bold text-white">Active Modules ({extensions.length})</h3>
              </div>
            </div>

            <div className="my-3 flex-shrink-0">
              <div className="relative">
                <Search className="absolute left-3 top-2.5 h-4 w-4 text-slate-500" />
                <input
                  type="text"
                  placeholder="Filter extensions..."
                  value={extSearch}
                  onChange={(e) => setExtSearch(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                />
              </div>
            </div>

            <div className="flex-1 overflow-y-auto space-y-1.5 pr-1">
              {filteredExtensions.map((ext) => (
                <div
                  key={ext}
                  className="flex items-center justify-between rounded-xl border border-white/5 bg-slate-950/40 px-3 py-2 text-xs font-mono text-slate-300 hover:bg-white/5 transition-colors"
                >
                  <span>{ext}</span>
                  <span className="h-1.5 w-1.5 rounded-full bg-emerald-400" />
                </div>
              ))}
            </div>
          </div>
        </div>
      </div>
    </Shell>
  );
}
