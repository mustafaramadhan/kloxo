'use client';

import React, { useState, useEffect, useRef } from 'react';
import Shell from '@/components/layout/Shell';
import {
  apiFetch,
  CustomBuildComponent,
  CustomBuildStatusResponse,
  CustomBuildOptions,
} from '@/lib/api';
import {
  Wrench,
  RefreshCw,
  Play,
  CheckCircle2,
  AlertCircle,
  Terminal,
  Settings,
  Layers,
  Server,
  Database,
  Globe,
  Mail,
  Shield,
  Sliders,
  Copy,
  Trash2,
  ExternalLink,
  Zap,
  Activity,
  ArrowUpRight,
  Cpu,
} from 'lucide-react';

export default function CustomBuildPage() {
  const [activeTab, setActiveTab] = useState<'components' | 'options' | 'terminal'>('components');
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // Status & Components
  const [statusData, setStatusData] = useState<CustomBuildStatusResponse | null>(null);

  // Options
  const [options, setOptions] = useState<CustomBuildOptions>({
    webserver: 'nginx_apache',
    php1_release: '8.5',
    php1_mode: 'php-fpm',
    mariadb_version: '11.8',
    phpmyadmin: true,
    roundcube: true,
    modsecurity: true,
    autoupdate: true,
  });
  const [optionsSaving, setOptionsSaving] = useState(false);

  // Actions & Execution
  const [executingAction, setExecutingAction] = useState<string | null>(null);
  const [terminalLogs, setTerminalLogs] = useState<string[]>([]);
  const terminalBottomRef = useRef<HTMLDivElement>(null);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  const fetchStatus = async () => {
    try {
      setLoading(true);
      const res = await apiFetch<CustomBuildStatusResponse>('/custombuild/status');
      setStatusData(res);
      setError(null);
    } catch (err: any) {
      setError(err.message || 'Failed to fetch CustomBuild status');
    } finally {
      setLoading(false);
    }
  };

  const fetchOptions = async () => {
    try {
      const res = await apiFetch<{ success: boolean; options: CustomBuildOptions }>('/custombuild/options');
      if (res.options) {
        setOptions(res.options);
      }
    } catch {
      // Use defaults if fetch fails
    }
  };

  useEffect(() => {
    fetchStatus();
    fetchOptions();
    appendLog('[CustomBuild] DirectAdmin-Compatible Stack Engine initialized.');
    appendLog('[CustomBuild] Ready for stack operations, conf rewrites, and package compilation.');
  }, []);

  useEffect(() => {
    if (activeTab === 'terminal' && terminalBottomRef.current) {
      terminalBottomRef.current.scrollIntoView({ behavior: 'smooth' });
    }
  }, [terminalLogs, activeTab]);

  const appendLog = (line: string) => {
    const timestamp = new Date().toLocaleTimeString();
    setTerminalLogs((prev) => [...prev, `[${timestamp}] ${line}`]);
  };

  const handleSaveOptions = async (e: React.FormEvent) => {
    e.preventDefault();
    setOptionsSaving(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string; options: CustomBuildOptions }>('/custombuild/options', {
        method: 'POST',
        body: JSON.stringify(options),
      });
      setMessage({ text: res.message || 'CustomBuild options updated', success: true });
      appendLog(`[CustomBuild] Configuration updated: webserver=${options.webserver}, php=${options.php1_release}`);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to save options', success: false });
    } finally {
      setOptionsSaving(false);
    }
  };

  const handleRunAction = async (action: string, label: string) => {
    if (executingAction) return;
    setExecutingAction(action);
    setMessage(null);
    appendLog(`>>> [CustomBuild] Triggering command: ./build ${action} (${label})...`);
    setActiveTab('terminal');

    try {
      const res = await apiFetch<{
        success: boolean;
        action: string;
        output: string;
        exit_code: number;
      }>('/custombuild/action', {
        method: 'POST',
        body: JSON.stringify({ action }),
      });

      if (res.output) {
        const lines = res.output.split('\n');
        lines.forEach((l) => appendLog(l));
      }

      if (res.success) {
        appendLog(`<<< [CustomBuild] Action "${action}" completed successfully with exit code 0.`);
        setMessage({ text: `Build action "${label}" completed successfully`, success: true });
        fetchStatus();
      } else {
        appendLog(`<<< [CustomBuild] Action "${action}" failed with exit code ${res.exit_code}.`);
        setMessage({ text: `Action "${label}" exited with code ${res.exit_code}`, success: false });
      }
    } catch (err: any) {
      appendLog(`<<< [CustomBuild] Execution error: ${err.message || 'Network/Server Error'}`);
      setMessage({ text: err.message || 'Execution failed', success: false });
    } finally {
      setExecutingAction(null);
    }
  };

  const copyTerminalOutput = () => {
    navigator.clipboard.writeText(terminalLogs.join('\n'));
    setMessage({ text: 'Terminal logs copied to clipboard', success: true });
  };

  const clearTerminal = () => {
    setTerminalLogs([`[${new Date().toLocaleTimeString()}] Console logs cleared.`]);
  };

  const getCategoryIcon = (category: string) => {
    switch (category) {
      case 'web':
        return Globe;
      case 'php':
        return Cpu;
      case 'database':
        return Database;
      case 'mail':
        return Mail;
      case 'apps':
        return Layers;
      default:
        return Wrench;
    }
  };

  return (
    <Shell>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <div className="flex items-center gap-3">
              <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500/20 to-orange-500/10 border border-amber-500/30 text-amber-400 shadow-lg shadow-amber-500/10">
                <Wrench className="h-6 w-6" />
              </div>
              <div>
                <div className="flex items-center gap-2">
                  <h1 className="text-2xl font-bold text-white tracking-tight">CustomBuild Studio</h1>
                  <span className="rounded-full bg-amber-500/15 border border-amber-500/30 px-2.5 py-0.5 text-xs font-semibold text-amber-400">
                    DirectAdmin Stack
                  </span>
                </div>
                <p className="text-xs text-slate-400">
                  Compile, reconfigure, rewrite VirtualHosts, and manage modern web stack components
                </p>
              </div>
            </div>
          </div>

          {/* Quick Actions Buttons */}
          <div className="flex flex-wrap items-center gap-2">
            <button
              onClick={() => handleRunAction('update', 'Update Versions')}
              disabled={!!executingAction}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-medium text-slate-200 backdrop-blur-md transition-all hover:bg-white/10 disabled:opacity-50"
            >
              <RefreshCw className={`h-3.5 w-3.5 ${executingAction === 'update' ? 'animate-spin' : ''}`} />
              Update Versions
            </button>

            <button
              onClick={() => handleRunAction('rewrite_confs', 'Rewrite Confs')}
              disabled={!!executingAction}
              className="inline-flex items-center gap-2 rounded-xl border border-sky-500/30 bg-sky-500/10 px-3.5 py-2 text-xs font-medium text-sky-300 backdrop-blur-md transition-all hover:bg-sky-500/20 disabled:opacity-50"
            >
              <Sliders className="h-3.5 w-3.5" />
              Rewrite Confs
            </button>

            <button
              onClick={() => handleRunAction('build_all', 'Build All Stack')}
              disabled={!!executingAction}
              className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-4 py-2 text-xs font-semibold text-white shadow-lg shadow-amber-500/20 transition-all hover:from-amber-400 hover:to-orange-500 disabled:opacity-50"
            >
              <Zap className="h-3.5 w-3.5" />
              Build All
            </button>
          </div>
        </div>

        {/* Global Feedback Alert */}
        {message && (
          <div
            className={`flex items-center gap-3 rounded-2xl border p-4 text-xs font-medium backdrop-blur-md ${
              message.success
                ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'
                : 'border-rose-500/30 bg-rose-500/10 text-rose-300'
            }`}
          >
            {message.success ? (
              <CheckCircle2 className="h-4 w-4 shrink-0" />
            ) : (
              <AlertCircle className="h-4 w-4 shrink-0" />
            )}
            <span>{message.text}</span>
          </div>
        )}

        {/* Overview Stats Bar */}
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <div className="rounded-2xl border border-white/5 bg-slate-900/60 p-4 backdrop-blur-md shadow-xl">
            <div className="flex items-center justify-between text-slate-400 text-xs">
              <span>Web Server Architecture</span>
              <Globe className="h-4 w-4 text-sky-400" />
            </div>
            <div className="mt-2 flex items-baseline gap-2">
              <span className="text-lg font-bold text-white">Nginx + Apache</span>
              <span className="rounded bg-sky-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-sky-300">
                Reverse Proxy
              </span>
            </div>
            <p className="mt-1 text-[11px] text-slate-400">Ports 80/443 (Edge) & 8080 (Backend)</p>
          </div>

          <div className="rounded-2xl border border-white/5 bg-slate-900/60 p-4 backdrop-blur-md shadow-xl">
            <div className="flex items-center justify-between text-slate-400 text-xs">
              <span>Primary PHP Runtime</span>
              <Cpu className="h-4 w-4 text-indigo-400" />
            </div>
            <div className="mt-2 flex items-baseline gap-2">
              <span className="text-lg font-bold text-white">PHP 8.5</span>
              <span className="rounded bg-indigo-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-300">
                PHP-FPM
              </span>
            </div>
            <p className="mt-1 text-[11px] text-slate-400">Unix Socket: /run/php/php-fpm.sock</p>
          </div>

          <div className="rounded-2xl border border-white/5 bg-slate-900/60 p-4 backdrop-blur-md shadow-xl">
            <div className="flex items-center justify-between text-slate-400 text-xs">
              <span>Relational Database</span>
              <Database className="h-4 w-4 text-emerald-400" />
            </div>
            <div className="mt-2 flex items-baseline gap-2">
              <span className="text-lg font-bold text-white">MariaDB 11.8</span>
              <span className="rounded bg-emerald-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-emerald-300">
                Active
              </span>
            </div>
            <p className="mt-1 text-[11px] text-slate-400">Port 3306 (Local Unix Socket)</p>
          </div>

          <div className="rounded-2xl border border-white/5 bg-slate-900/60 p-4 backdrop-blur-md shadow-xl">
            <div className="flex items-center justify-between text-slate-400 text-xs">
              <span>Security & Certificates</span>
              <Shield className="h-4 w-4 text-amber-400" />
            </div>
            <div className="mt-2 flex items-baseline gap-2">
              <span className="text-lg font-bold text-white">Certbot + ACME</span>
              <span className="rounded bg-amber-500/20 px-1.5 py-0.5 text-[10px] font-semibold text-amber-300">
                Let's Encrypt
              </span>
            </div>
            <p className="mt-1 text-[11px] text-slate-400">Automated systemd certbot.timer</p>
          </div>
        </div>

        {/* Tab Navigation */}
        <div className="flex border-b border-white/10">
          <button
            onClick={() => setActiveTab('components')}
            className={`flex items-center gap-2 border-b-2 px-4 py-3 text-xs font-semibold transition-all ${
              activeTab === 'components'
                ? 'border-amber-500 text-amber-400'
                : 'border-transparent text-slate-400 hover:text-slate-200'
            }`}
          >
            <Layers className="h-4 w-4" />
            Stack Components & Versions
          </button>

          <button
            onClick={() => setActiveTab('options')}
            className={`flex items-center gap-2 border-b-2 px-4 py-3 text-xs font-semibold transition-all ${
              activeTab === 'options'
                ? 'border-amber-500 text-amber-400'
                : 'border-transparent text-slate-400 hover:text-slate-200'
            }`}
          >
            <Settings className="h-4 w-4" />
            Build Options (options.conf)
          </button>

          <button
            onClick={() => setActiveTab('terminal')}
            className={`flex items-center gap-2 border-b-2 px-4 py-3 text-xs font-semibold transition-all relative ${
              activeTab === 'terminal'
                ? 'border-amber-500 text-amber-400'
                : 'border-transparent text-slate-400 hover:text-slate-200'
            }`}
          >
            <Terminal className="h-4 w-4" />
            Live Build Console
            {executingAction && (
              <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
            )}
          </button>
        </div>

        {/* Tab 1: Components List */}
        {activeTab === 'components' && (
          <div className="space-y-4">
            <div className="rounded-2xl border border-white/5 bg-slate-900/40 backdrop-blur-xl overflow-hidden shadow-2xl">
              <div className="overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead className="border-b border-white/10 bg-slate-950/60 font-semibold text-slate-400">
                    <tr>
                      <th className="px-5 py-3.5">Component & Purpose</th>
                      <th className="px-5 py-3.5">Category</th>
                      <th className="px-5 py-3.5">Installed Version</th>
                      <th className="px-5 py-3.5">Latest Available</th>
                      <th className="px-5 py-3.5">Status</th>
                      <th className="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-white/5">
                    {loading ? (
                      <tr>
                        <td colSpan={6} className="py-12 text-center text-slate-400">
                          <RefreshCw className="mx-auto h-6 w-6 animate-spin text-amber-400 mb-2" />
                          Inspecting installed system stack components...
                        </td>
                      </tr>
                    ) : statusData?.components?.length ? (
                      statusData.components.map((comp) => {
                        const Icon = getCategoryIcon(comp.category);
                        return (
                          <tr key={comp.name} className="hover:bg-white/[0.02] transition-colors">
                            <td className="px-5 py-4">
                              <div className="flex items-center gap-3">
                                <div className="flex h-9 w-9 items-center justify-center rounded-xl bg-slate-800 border border-white/5 text-amber-400">
                                  <Icon className="h-4 w-4" />
                                </div>
                                <div>
                                  <div className="font-semibold text-white">{comp.title}</div>
                                  <div className="text-[11px] text-slate-400 max-w-sm truncate">
                                    {comp.description}
                                  </div>
                                </div>
                              </div>
                            </td>
                            <td className="px-5 py-4">
                              <span className="rounded-lg bg-slate-800 px-2 py-0.5 text-[11px] font-medium text-slate-300 uppercase tracking-wider">
                                {comp.category}
                              </span>
                            </td>
                            <td className="px-5 py-4 font-mono text-slate-200">
                              <span className="rounded bg-slate-950 px-2 py-1 border border-white/5">
                                {comp.installed_version}
                              </span>
                            </td>
                            <td className="px-5 py-4 font-mono text-emerald-400">
                              {comp.latest_version}
                            </td>
                            <td className="px-5 py-4">
                              <span className="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 text-[11px] font-medium text-emerald-400">
                                <span className="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                                Up to date
                              </span>
                            </td>
                            <td className="px-5 py-4 text-right">
                              <button
                                onClick={() =>
                                  handleRunAction(
                                    comp.name === 'nginx' || comp.name === 'apache'
                                      ? 'build_webserver'
                                      : comp.name === 'php'
                                      ? 'build_php'
                                      : comp.name === 'phpmyadmin'
                                      ? 'build_phpmyadmin'
                                      : comp.name === 'roundcube'
                                      ? 'build_roundcube'
                                      : 'rewrite_confs',
                                    `Build ${comp.title}`
                                  )
                                }
                                disabled={!!executingAction}
                                className="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3 py-1.5 text-[11px] font-medium text-slate-300 hover:bg-white/10 hover:text-white transition-all disabled:opacity-50"
                              >
                                <Play className="h-3 w-3 text-amber-400" />
                                Rebuild
                              </button>
                            </td>
                          </tr>
                        );
                      })
                    ) : (
                      <tr>
                        <td colSpan={6} className="py-8 text-center text-slate-400">
                          No components found.
                        </td>
                      </tr>
                    )}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        )}

        {/* Tab 2: Build Options (options.conf) */}
        {activeTab === 'options' && (
          <div className="rounded-2xl border border-white/5 bg-slate-900/40 p-6 backdrop-blur-xl shadow-2xl">
            <div className="mb-6 flex items-center justify-between border-b border-white/10 pb-4">
              <div>
                <h2 className="text-base font-semibold text-white">CustomBuild Stack Configuration</h2>
                <p className="text-xs text-slate-400">
                  Settings stored in <code className="text-amber-300 font-mono">/usr/local/lxlabs/kloxo/etc/custombuild.json</code> (DirectAdmin options.conf equivalent)
                </p>
              </div>
              <button
                type="button"
                onClick={() => handleRunAction('rewrite_confs', 'Apply Configs')}
                className="inline-flex items-center gap-2 rounded-xl border border-sky-500/30 bg-sky-500/10 px-3 py-1.5 text-xs font-medium text-sky-300 hover:bg-sky-500/20"
              >
                <Sliders className="h-3.5 w-3.5" />
                Apply to Web Server
              </button>
            </div>

            <form onSubmit={handleSaveOptions} className="space-y-6">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1.5">
                    Web Server Stack (webserver)
                  </label>
                  <select
                    value={options.webserver}
                    onChange={(e) => setOptions({ ...options, webserver: e.target.value })}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3.5 py-2.5 text-xs text-white focus:border-amber-500 focus:outline-none"
                  >
                    <option value="nginx_apache">Nginx (Frontend Reverse Proxy) + Apache (Backend)</option>
                    <option value="nginx">Nginx Standalone (Fastest static & microservices)</option>
                    <option value="apache">Apache Standalone (Classic .htaccess support)</option>
                  </select>
                  <p className="mt-1 text-[11px] text-slate-400">
                    Recommended: Nginx + Apache reverse proxy for best performance & compatibility.
                  </p>
                </div>

                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1.5">
                    Default PHP Engine Version (php1_release)
                  </label>
                  <select
                    value={options.php1_release}
                    onChange={(e) => setOptions({ ...options, php1_release: e.target.value })}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3.5 py-2.5 text-xs text-white focus:border-amber-500 focus:outline-none"
                  >
                    <option value="8.5">PHP 8.5 (Active System Default)</option>
                    <option value="8.3">PHP 8.3 LTS</option>
                    <option value="8.2">PHP 8.2</option>
                    <option value="8.1">PHP 8.1</option>
                    <option value="7.4">PHP 7.4 (Legacy)</option>
                  </select>
                  <p className="mt-1 text-[11px] text-slate-400">
                    Switch default handler version for global CLI and default virtual hosts.
                  </p>
                </div>

                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1.5">
                    PHP Execution Mode (php1_mode)
                  </label>
                  <select
                    value={options.php1_mode}
                    onChange={(e) => setOptions({ ...options, php1_mode: e.target.value })}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3.5 py-2.5 text-xs text-white focus:border-amber-500 focus:outline-none"
                  >
                    <option value="php-fpm">PHP-FPM (FastCGI Process Manager - Recommended)</option>
                    <option value="fastcgi">mod_fcgid (FastCGI)</option>
                    <option value="lsphp">LiteSpeed PHP (lsphp)</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-medium text-slate-300 mb-1.5">
                    Database Server Branch (mariadb_version)
                  </label>
                  <select
                    value={options.mariadb_version}
                    onChange={(e) => setOptions({ ...options, mariadb_version: e.target.value })}
                    className="w-full rounded-xl border border-white/10 bg-slate-950 px-3.5 py-2.5 text-xs text-white focus:border-amber-500 focus:outline-none"
                  >
                    <option value="11.8">MariaDB 11.8 Series (Installed)</option>
                    <option value="10.11">MariaDB 10.11 LTS</option>
                    <option value="mysql8">MySQL 8.0</option>
                  </select>
                </div>
              </div>

              {/* Toggles */}
              <div className="space-y-3 pt-4 border-t border-white/10">
                <h3 className="text-xs font-semibold text-slate-300 uppercase tracking-wider">
                  Applications & Security Modules
                </h3>

                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <label className="flex items-center gap-3 p-3 rounded-xl border border-white/5 bg-slate-950/60 cursor-pointer hover:bg-slate-950">
                    <input
                      type="checkbox"
                      checked={options.phpmyadmin}
                      onChange={(e) => setOptions({ ...options, phpmyadmin: e.target.checked })}
                      className="rounded border-white/10 bg-slate-900 text-amber-500 focus:ring-amber-500"
                    />
                    <div>
                      <span className="text-xs font-medium text-white block">phpMyAdmin Portal</span>
                      <span className="text-[11px] text-slate-400">Available at /phpmyadmin/</span>
                    </div>
                  </label>

                  <label className="flex items-center gap-3 p-3 rounded-xl border border-white/5 bg-slate-950/60 cursor-pointer hover:bg-slate-950">
                    <input
                      type="checkbox"
                      checked={options.roundcube}
                      onChange={(e) => setOptions({ ...options, roundcube: e.target.checked })}
                      className="rounded border-white/10 bg-slate-900 text-amber-500 focus:ring-amber-500"
                    />
                    <div>
                      <span className="text-xs font-medium text-white block">Roundcube Webmail</span>
                      <span className="text-[11px] text-slate-400">Available at /webmail/</span>
                    </div>
                  </label>

                  <label className="flex items-center gap-3 p-3 rounded-xl border border-white/5 bg-slate-950/60 cursor-pointer hover:bg-slate-950">
                    <input
                      type="checkbox"
                      checked={options.modsecurity}
                      onChange={(e) => setOptions({ ...options, modsecurity: e.target.checked })}
                      className="rounded border-white/10 bg-slate-900 text-amber-500 focus:ring-amber-500"
                    />
                    <div>
                      <span className="text-xs font-medium text-white block">ModSecurity & LxGuard</span>
                      <span className="text-[11px] text-slate-400">WAF rules & brute-force IP blocking</span>
                    </div>
                  </label>

                  <label className="flex items-center gap-3 p-3 rounded-xl border border-white/5 bg-slate-950/60 cursor-pointer hover:bg-slate-950">
                    <input
                      type="checkbox"
                      checked={options.autoupdate}
                      onChange={(e) => setOptions({ ...options, autoupdate: e.target.checked })}
                      className="rounded border-white/10 bg-slate-900 text-amber-500 focus:ring-amber-500"
                    />
                    <div>
                      <span className="text-xs font-medium text-white block">Automatic Security Patches</span>
                      <span className="text-[11px] text-slate-400">Auto-update packages on release</span>
                    </div>
                  </label>
                </div>
              </div>

              <div className="flex justify-end pt-4 border-t border-white/10">
                <button
                  type="submit"
                  disabled={optionsSaving}
                  className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-2.5 text-xs font-semibold text-white shadow-lg shadow-amber-500/20 hover:from-amber-400 hover:to-orange-500 transition-all disabled:opacity-50"
                >
                  {optionsSaving ? (
                    <>
                      <RefreshCw className="h-4 w-4 animate-spin" />
                      Saving Options...
                    </>
                  ) : (
                    <>
                      <CheckCircle2 className="h-4 w-4" />
                      Save CustomBuild Options
                    </>
                  )}
                </button>
              </div>
            </form>
          </div>
        )}

        {/* Tab 3: Live Build Console */}
        {activeTab === 'terminal' && (
          <div className="rounded-2xl border border-white/10 bg-slate-950 p-4 shadow-2xl">
            {/* Terminal Top Bar */}
            <div className="flex items-center justify-between border-b border-white/10 pb-3 mb-3">
              <div className="flex items-center gap-2">
                <div className="flex gap-1.5">
                  <span className="h-3 w-3 rounded-full bg-rose-500/80 inline-block" />
                  <span className="h-3 w-3 rounded-full bg-amber-500/80 inline-block" />
                  <span className="h-3 w-3 rounded-full bg-emerald-500/80 inline-block" />
                </div>
                <span className="text-xs font-mono text-slate-400 ml-2">
                  bash: /usr/local/lxlabs/kloxo/bin/custombuild
                </span>
                {executingAction && (
                  <span className="rounded bg-amber-500/20 px-2 py-0.5 text-[10px] font-mono text-amber-300 animate-pulse">
                    RUNNING: {executingAction}
                  </span>
                )}
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={copyTerminalOutput}
                  className="inline-flex items-center gap-1 rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] text-slate-300 hover:bg-white/10"
                >
                  <Copy className="h-3 w-3" />
                  Copy Logs
                </button>
                <button
                  onClick={clearTerminal}
                  className="inline-flex items-center gap-1 rounded-lg border border-white/10 bg-white/5 px-2.5 py-1 text-[11px] text-slate-300 hover:bg-white/10"
                >
                  <Trash2 className="h-3 w-3" />
                  Clear
                </button>
              </div>
            </div>

            {/* Terminal Body */}
            <div className="h-96 overflow-y-auto font-mono text-xs text-slate-300 space-y-1 select-text bg-black/40 p-4 rounded-xl border border-white/5">
              {terminalLogs.map((log, idx) => (
                <div
                  key={idx}
                  className={`leading-relaxed whitespace-pre-wrap ${
                    log.includes('error') || log.includes('failed') || log.includes('Error')
                      ? 'text-rose-400 font-semibold'
                      : log.includes('success') || log.includes('Success') || log.includes('completed')
                      ? 'text-emerald-400'
                      : log.startsWith('>>>')
                      ? 'text-amber-300 font-bold'
                      : 'text-slate-300'
                  }`}
                >
                  {log}
                </div>
              ))}
              <div ref={terminalBottomRef} />
            </div>
          </div>
        )}
      </div>
    </Shell>
  );
}
