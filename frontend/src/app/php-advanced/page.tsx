'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import Shell from '@/components/layout/Shell';
import {
  apiFetch,
  PhpVersionsResponse,
  PhpModulesResponse,
  PhpModuleItem,
  PhpIniResponse,
} from '@/lib/api';
import {
  SlidersHorizontal,
  Layers,
  Box,
  Cpu,
  FileCode,
  RefreshCw,
  CheckCircle2,
  AlertCircle,
  Save,
  Search,
  ExternalLink,
  Zap,
  Check,
  RotateCcw,
  Sliders,
  ShieldCheck,
  Power,
  Download,
  Sparkles,
  Filter,
  Package,
} from 'lucide-react';

export default function AdvancedPhpPage() {
  const [activeTab, setActiveTab] = useState<'versions' | 'modules' | 'opcache' | 'ini'>('versions');

  // Versions State
  const [versionsData, setVersionsData] = useState<PhpVersionsResponse | null>(null);
  const [selectedVersion, setSelectedVersion] = useState<string>('8.5');
  const [switchingVersion, setSwitchingVersion] = useState(false);

  // Modules State
  const [modules, setModules] = useState<PhpModuleItem[]>([]);
  const [modSearch, setModSearch] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [selectedStatus, setSelectedStatus] = useState<'all' | 'active' | 'disabled' | 'not_installed'>('all');
  const [togglingModule, setTogglingModule] = useState<string | null>(null);
  const [batchInstalling, setBatchInstalling] = useState(false);

  // INI State
  const [iniContent, setIniContent] = useState<string>('');
  const [savingIni, setSavingIni] = useState(false);

  // OPcache preset state
  const [opcacheEnabled, setOpcacheEnabled] = useState(true);
  const [opcacheMemory, setOpcacheMemory] = useState('128');
  const [opcacheMaxFiles, setOpcacheMaxFiles] = useState('10000');
  const [opcacheRevalidateFreq, setOpcacheRevalidateFreq] = useState('2');
  const [savingOpcache, setSavingOpcache] = useState(false);

  // Global UI State
  const [loading, setLoading] = useState(true);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  // Initial Load
  const fetchAll = async () => {
    setLoading(true);
    setMessage(null);
    try {
      // 1. Fetch Versions
      const vRes = await apiFetch<PhpVersionsResponse>('/php/versions');
      setVersionsData(vRes);
      const curVer = vRes.active || '8.5';
      setSelectedVersion(curVer);

      // 2. Fetch Modules for active version
      const mRes = await apiFetch<PhpModulesResponse>(`/php/modules?version=${encodeURIComponent(curVer)}`);
      setModules(mRes.modules || []);

      // 3. Fetch INI
      const iniRes = await apiFetch<PhpIniResponse>(`/php/ini?version=${encodeURIComponent(curVer)}`);
      setIniContent(iniRes.content || '');
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to load PHP Advanced configuration', success: false });
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchAll();
  }, []);

  // Fetch data when switching selected version tab
  const handleVersionSelect = async (ver: string) => {
    setSelectedVersion(ver);
    try {
      const mRes = await apiFetch<PhpModulesResponse>(`/php/modules?version=${encodeURIComponent(ver)}`);
      setModules(mRes.modules || []);
      const iniRes = await apiFetch<PhpIniResponse>(`/php/ini?version=${encodeURIComponent(ver)}`);
      setIniContent(iniRes.content || '');
    } catch {
      // ignore
    }
  };

  // Switch Active System PHP Version
  const handleSwitchActiveVersion = async (targetVer: string) => {
    if (!confirm(`Switch system and Apache webserver PHP version to ${targetVer}? This will gracefully reload the webserver.`)) {
      return;
    }
    setSwitchingVersion(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string; version: string }>('/php/switch-version', {
        method: 'POST',
        body: JSON.stringify({ version: targetVer }),
      });
      setMessage({ text: res.message || `Switched to PHP ${targetVer} successfully`, success: true });
      await fetchAll();
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to switch PHP version', success: false });
    } finally {
      setSwitchingVersion(false);
    }
  };

  // Toggle or Install Module
  const handleToggleModule = async (modName: string, currentStatus: boolean) => {
    setTogglingModule(modName);
    setMessage(null);
    try {
      const nextState = !currentStatus;
      const res = await apiFetch<{ success: boolean; message: string; module: string; enabled: boolean; installed?: boolean }>('/php/modules/toggle', {
        method: 'POST',
        body: JSON.stringify({
          module: modName,
          enable: nextState,
          version: selectedVersion,
        }),
      });

      // Update state locally
      setModules((prev) =>
        prev.map((m) => (m.name === modName ? { ...m, enabled: nextState, installed: true } : m))
      );
      setMessage({ text: res.message || `Module ${modName} updated`, success: true });
    } catch (err: any) {
      setMessage({ text: err.message || `Failed to toggle module ${modName}`, success: false });
    } finally {
      setTogglingModule(null);
    }
  };

  // Batch Install Standard Modules
  const handleBatchInstall = async (modList: string[]) => {
    setBatchInstalling(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/php/modules/batch-install', {
        method: 'POST',
        body: JSON.stringify({
          version: selectedVersion,
          modules: modList,
        }),
      });
      setMessage({ text: res.message || 'Modules installed and activated successfully', success: true });
      const mRes = await apiFetch<PhpModulesResponse>(`/php/modules?version=${encodeURIComponent(selectedVersion)}`);
      setModules(mRes.modules || []);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to install modules batch', success: false });
    } finally {
      setBatchInstalling(false);
    }
  };

  // Save Custom INI
  const handleSaveIni = async (e: React.FormEvent) => {
    e.preventDefault();
    setSavingIni(true);
    setMessage(null);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/php/ini', {
        method: 'POST',
        body: JSON.stringify({
          version: selectedVersion,
          content: iniContent,
        }),
      });
      setMessage({ text: res.message || 'Custom php.ini saved and webserver reloaded', success: true });
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to save php.ini', success: false });
    } finally {
      setSavingIni(false);
    }
  };

  // Save OPcache preset
  const handleSaveOpcache = async (e: React.FormEvent) => {
    e.preventDefault();
    setSavingOpcache(true);
    setMessage(null);
    try {
      const opcacheBlock = `
; --- OPcache High Performance Preset (Mfklixo) ---
opcache.enable=${opcacheEnabled ? '1' : '0'}
opcache.enable_cli=1
opcache.memory_consumption=${opcacheMemory}
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=${opcacheMaxFiles}
opcache.revalidate_freq=${opcacheRevalidateFreq}
opcache.validate_timestamps=1
opcache.save_comments=1
opcache.fast_shutdown=1
`;
      // Append or replace OPcache section in ini
      let updatedIni = iniContent;
      if (updatedIni.includes('opcache.enable')) {
        updatedIni = updatedIni.replace(/;?opcache\.enable[\s\S]*?(?=\n\n|$)/, opcacheBlock.trim());
      } else {
        updatedIni = updatedIni + '\n' + opcacheBlock.trim();
      }
      setIniContent(updatedIni);

      const res = await apiFetch<{ success: boolean; message: string }>('/php/ini', {
        method: 'POST',
        body: JSON.stringify({
          version: selectedVersion,
          content: updatedIni,
        }),
      });
      setMessage({ text: res.message || 'OPcache settings applied and webserver reloaded', success: true });
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to apply OPcache settings', success: false });
    } finally {
      setSavingOpcache(false);
    }
  };

  const categories = ['all', ...Array.from(new Set(modules.map((m) => m.category || 'General').filter(Boolean)))];

  const filteredModules = modules.filter((m) => {
    const q = modSearch.toLowerCase();
    const matchesSearch =
      !modSearch ||
      m.name.toLowerCase().includes(q) ||
      (m.description && m.description.toLowerCase().includes(q)) ||
      (m.category && m.category.toLowerCase().includes(q));

    const matchesCategory = selectedCategory === 'all' || (m.category || 'General') === selectedCategory;

    const matchesStatus =
      selectedStatus === 'all' ||
      (selectedStatus === 'active' && m.enabled) ||
      (selectedStatus === 'disabled' && m.installed && !m.enabled) ||
      (selectedStatus === 'not_installed' && !m.installed && !m.builtin);

    return matchesSearch && matchesCategory && matchesStatus;
  });

  const enabledCount = modules.filter((m) => m.enabled).length;
  const installedCount = modules.filter((m) => m.installed || m.builtin).length;

  return (
    <Shell>
      <div className="space-y-6">
        {/* Top Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <div className="flex items-center gap-3">
              <div className="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-sky-500/20 to-indigo-500/20 border border-sky-500/30 text-sky-400 shadow-lg shadow-sky-500/10">
                <SlidersHorizontal className="h-6 w-6" />
              </div>
              <div>
                <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
                  Advanced PHP Configure
                </h1>
                <p className="text-xs text-slate-400">
                  Switch PHP versions, toggle extension modules, tune OPcache, and customize ini directives
                </p>
              </div>
            </div>
          </div>

          <div className="flex flex-wrap items-center gap-2.5">
            <Link
              href="/php-config"
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-medium text-slate-200 hover:bg-white/10 transition-colors"
            >
              <Sliders className="h-3.5 w-3.5 text-sky-400" />
              Standard Directives
            </Link>
            <a
              href="/api/v1/system/phpinfo"
              target="_blank"
              rel="noreferrer"
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-medium text-slate-200 hover:bg-white/10 transition-colors"
            >
              <ExternalLink className="h-3.5 w-3.5 text-indigo-400" />
              phpinfo()
            </a>
            <button
              onClick={fetchAll}
              disabled={loading}
              className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3.5 py-2 text-xs font-medium text-slate-200 hover:bg-white/10 transition-colors disabled:opacity-50"
            >
              <RefreshCw className={`h-3.5 w-3.5 ${loading ? 'animate-spin' : ''}`} />
              Refresh
            </button>
          </div>
        </div>

        {/* Global Alert Notification */}
        {message && (
          <div
            className={`flex items-center justify-between rounded-2xl border p-4 text-sm transition-all ${
              message.success
                ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300'
                : 'border-rose-500/30 bg-rose-500/10 text-rose-300'
            }`}
          >
            <div className="flex items-center gap-2.5">
              {message.success ? (
                <CheckCircle2 className="h-4 w-4 flex-shrink-0 text-emerald-400" />
              ) : (
                <AlertCircle className="h-4 w-4 flex-shrink-0 text-rose-400" />
              )}
              <span className="font-medium text-xs sm:text-sm">{message.text}</span>
            </div>
            <button
              onClick={() => setMessage(null)}
              className="text-slate-400 hover:text-white px-2 py-1 text-xs"
            >
              ✕
            </button>
          </div>
        )}

        {/* Quick Stats Bar */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div className="rounded-2xl border border-white/5 bg-slate-900/60 backdrop-blur-xl p-4 flex items-center justify-between">
            <div>
              <span className="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Active Engine</span>
              <p className="text-xl font-bold font-mono text-white mt-0.5">
                PHP {versionsData?.active || '8.5'}
              </p>
            </div>
            <span className="rounded-xl bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 text-xs font-semibold text-emerald-400 flex items-center gap-1.5">
              <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
              CLI & Apache
            </span>
          </div>

          <div className="rounded-2xl border border-white/5 bg-slate-900/60 backdrop-blur-xl p-4 flex items-center justify-between">
            <div>
              <span className="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Target Config Version</span>
              <p className="text-xl font-bold font-mono text-sky-400 mt-0.5">
                PHP {selectedVersion}
              </p>
            </div>
            <select
              value={selectedVersion}
              onChange={(e) => handleVersionSelect(e.target.value)}
              className="rounded-xl border border-white/10 bg-slate-950 px-3 py-1.5 text-xs font-mono text-slate-200 focus:outline-none focus:border-sky-500"
            >
              {(versionsData?.installed || [selectedVersion]).map((v) => (
                <option key={v} value={v}>
                  v{v} {v === versionsData?.active ? '(Active)' : ''}
                </option>
              ))}
            </select>
          </div>

          <div className="rounded-2xl border border-white/5 bg-slate-900/60 backdrop-blur-xl p-4 flex items-center justify-between">
            <div>
              <span className="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Modules Status</span>
              <p className="text-xl font-bold text-white mt-0.5">
                {enabledCount} <span className="text-xs text-emerald-400 font-semibold">Active</span>{' '}
                <span className="text-xs text-slate-400 font-normal">/ {installedCount} Installed / {modules.length} Catalog</span>
              </p>
            </div>
            <span className="rounded-xl bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 text-xs font-semibold text-indigo-400">
              Native phpenmod & apt
            </span>
          </div>
        </div>

        {/* Tab Navigation */}
        <div className="flex border-b border-white/10 gap-2 overflow-x-auto pb-px">
          <button
            onClick={() => setActiveTab('versions')}
            className={`flex items-center gap-2 px-4 py-3 text-sm font-semibold border-b-2 transition-all whitespace-nowrap ${
              activeTab === 'versions'
                ? 'border-sky-500 text-sky-400 bg-sky-500/5 rounded-t-xl'
                : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700'
            }`}
          >
            <Layers className="h-4 w-4" />
            PHP Versions ({versionsData?.installed.length || 1})
          </button>

          <button
            onClick={() => setActiveTab('modules')}
            className={`flex items-center gap-2 px-4 py-3 text-sm font-semibold border-b-2 transition-all whitespace-nowrap ${
              activeTab === 'modules'
                ? 'border-sky-500 text-sky-400 bg-sky-500/5 rounded-t-xl'
                : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700'
            }`}
          >
            <Box className="h-4 w-4" />
            Modules & Extensions ({enabledCount})
          </button>

          <button
            onClick={() => setActiveTab('opcache')}
            className={`flex items-center gap-2 px-4 py-3 text-sm font-semibold border-b-2 transition-all whitespace-nowrap ${
              activeTab === 'opcache'
                ? 'border-sky-500 text-sky-400 bg-sky-500/5 rounded-t-xl'
                : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700'
            }`}
          >
            <Cpu className="h-4 w-4" />
            OPcache & Performance
          </button>

          <button
            onClick={() => setActiveTab('ini')}
            className={`flex items-center gap-2 px-4 py-3 text-sm font-semibold border-b-2 transition-all whitespace-nowrap ${
              activeTab === 'ini'
                ? 'border-sky-500 text-sky-400 bg-sky-500/5 rounded-t-xl'
                : 'border-transparent text-slate-400 hover:text-slate-200 hover:border-slate-700'
            }`}
          >
            <FileCode className="h-4 w-4" />
            Custom php.ini Editor
          </button>
        </div>

        {/* Tab 1: PHP Versions */}
        {activeTab === 'versions' && (
          <div className="space-y-6">
            <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl p-6 shadow-2xl">
              <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-white/5 mb-6">
                <div>
                  <h2 className="text-lg font-bold text-white flex items-center gap-2">
                    <Layers className="h-5 w-5 text-sky-400" />
                    Multi-PHP Version Switcher
                  </h2>
                  <p className="text-xs text-slate-400 mt-1">
                    Select default CLI and Apache module PHP version. Changes take effect instantly across all hosted virtual hosts.
                  </p>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {(versionsData?.supported || ['7.4', '8.0', '8.1', '8.2', '8.3', '8.4', '8.5']).map((ver) => {
                  const isInstalled = versionsData?.installed.includes(ver);
                  const isActive = versionsData?.active === ver;

                  return (
                    <div
                      key={ver}
                      className={`relative rounded-2xl border p-5 transition-all flex flex-col justify-between ${
                        isActive
                          ? 'border-sky-500/50 bg-sky-500/10 shadow-lg shadow-sky-500/10'
                          : isInstalled
                          ? 'border-white/10 bg-slate-950/60 hover:border-white/20'
                          : 'border-white/5 bg-slate-950/20 opacity-60'
                      }`}
                    >
                      <div>
                        <div className="flex items-center justify-between mb-3">
                          <span className="text-lg font-bold font-mono text-white">PHP {ver}</span>
                          {isActive ? (
                            <span className="rounded-lg bg-emerald-500/20 border border-emerald-500/40 px-2 py-0.5 text-[11px] font-semibold text-emerald-300 flex items-center gap-1">
                              <Check className="h-3 w-3" /> Active Default
                            </span>
                          ) : isInstalled ? (
                            <span className="rounded-lg bg-slate-800 border border-slate-700 px-2 py-0.5 text-[11px] font-semibold text-slate-300">
                              Installed
                            </span>
                          ) : (
                            <span className="rounded-lg bg-white/5 border border-white/5 px-2 py-0.5 text-[10px] text-slate-500">
                              Available in PPA
                            </span>
                          )}
                        </div>

                        <p className="text-xs text-slate-400 mb-4">
                          {ver === '8.5'
                            ? 'Latest development & high-performance release.'
                            : ver === '8.3' || ver === '8.4'
                            ? 'Recommended for modern frameworks (Laravel 11, Symfony 7, WordPress).'
                            : 'Legacy compatibility runtime for older PHP codebases.'}
                        </p>
                      </div>

                      <div className="pt-3 border-t border-white/5">
                        {isActive ? (
                          <button
                            disabled
                            className="w-full rounded-xl bg-sky-500/20 border border-sky-500/30 px-3 py-2 text-xs font-semibold text-sky-300 cursor-default"
                          >
                            Currently Active
                          </button>
                        ) : isInstalled ? (
                          <button
                            onClick={() => handleSwitchActiveVersion(ver)}
                            disabled={switchingVersion}
                            className="w-full rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow-md hover:from-sky-400 hover:to-indigo-500 transition-all disabled:opacity-50 flex items-center justify-center gap-1.5"
                          >
                            <Zap className="h-3.5 w-3.5" />
                            {switchingVersion ? 'Switching...' : `Activate PHP ${ver}`}
                          </button>
                        ) : (
                          <button
                            disabled
                            className="w-full rounded-xl border border-white/5 bg-white/5 px-3 py-2 text-xs font-medium text-slate-500 cursor-not-allowed"
                          >
                            Install via `apt-get install php{ver}`
                          </button>
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            </div>
          </div>
        )}

        {/* Tab 2: Modules & Extensions */}
        {activeTab === 'modules' && (
          <div className="space-y-6">
            {/* Quick 1-Click Recommended Web Stack Banner */}
            <div className="rounded-3xl border border-sky-500/20 bg-gradient-to-r from-sky-950/40 via-indigo-950/30 to-purple-950/20 backdrop-blur-2xl p-6 shadow-2xl flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div className="space-y-1">
                <div className="flex items-center gap-2">
                  <span className="flex h-7 w-7 items-center justify-center rounded-xl bg-sky-500/20 text-sky-400 border border-sky-500/30">
                    <Sparkles className="h-4 w-4" />
                  </span>
                  <h3 className="text-base font-bold text-white">
                    Recommended Web & CMS Stack (1-Click Setup)
                  </h3>
                </div>
                <p className="text-xs text-slate-300 max-w-2xl leading-relaxed">
                  Quickly installs and enables all standard modules required for WordPress, Laravel, Excel/XLSX exports, and API services: <code className="text-sky-300 font-mono">zip (ZipArchive)</code>, <code className="text-sky-300 font-mono">gd</code>, <code className="text-sky-300 font-mono">intl</code>, <code className="text-sky-300 font-mono">soap</code>, <code className="text-sky-300 font-mono">bcmath</code>, <code className="text-sky-300 font-mono">sqlite3</code>, <code className="text-sky-300 font-mono">redis</code>, and <code className="text-sky-300 font-mono">imagick</code>.
                </p>
              </div>

              <button
                type="button"
                onClick={() => handleBatchInstall(['zip', 'gd', 'intl', 'soap', 'bcmath', 'sqlite3', 'redis', 'imagick'])}
                disabled={batchInstalling}
                className="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2.5 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all disabled:opacity-50 whitespace-nowrap flex-shrink-0"
              >
                <Download className={`h-4 w-4 ${batchInstalling ? 'animate-bounce' : ''}`} />
                {batchInstalling ? 'Installing Stack...' : 'Install & Enable Recommended Stack'}
              </button>
            </div>

            {/* Modules Filter & Search Container */}
            <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl p-6 shadow-2xl space-y-5">
              <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-white/5">
                <div>
                  <h2 className="text-lg font-bold text-white flex items-center gap-2">
                    <Box className="h-5 w-5 text-indigo-400" />
                    PHP Modules & Extensions Manager (PHP {selectedVersion})
                  </h2>
                  <p className="text-xs text-slate-400 mt-1">
                    Manage, install, and toggle all PHP extensions dynamically with <code className="text-sky-300">phpenmod</code>, <code className="text-sky-300">phpdismod</code>, and OS package management.
                  </p>
                </div>

                <div className="flex flex-wrap items-center gap-3">
                  {/* Status Filter */}
                  <div className="inline-flex rounded-xl bg-slate-950 p-1 border border-white/10 text-xs">
                    <button
                      type="button"
                      onClick={() => setSelectedStatus('all')}
                      className={`px-3 py-1 rounded-lg font-medium transition-all ${
                        selectedStatus === 'all'
                          ? 'bg-sky-500 text-white shadow-sm'
                          : 'text-slate-400 hover:text-white'
                      }`}
                    >
                      All ({modules.length})
                    </button>
                    <button
                      type="button"
                      onClick={() => setSelectedStatus('active')}
                      className={`px-3 py-1 rounded-lg font-medium transition-all ${
                        selectedStatus === 'active'
                          ? 'bg-emerald-500 text-white shadow-sm'
                          : 'text-slate-400 hover:text-white'
                      }`}
                    >
                      Active ({enabledCount})
                    </button>
                    <button
                      type="button"
                      onClick={() => setSelectedStatus('disabled')}
                      className={`px-3 py-1 rounded-lg font-medium transition-all ${
                        selectedStatus === 'disabled'
                          ? 'bg-amber-500 text-slate-950 font-semibold shadow-sm'
                          : 'text-slate-400 hover:text-white'
                      }`}
                    >
                      Disabled ({modules.filter((m) => m.installed && !m.enabled).length})
                    </button>
                    <button
                      type="button"
                      onClick={() => setSelectedStatus('not_installed')}
                      className={`px-3 py-1 rounded-lg font-medium transition-all ${
                        selectedStatus === 'not_installed'
                          ? 'bg-slate-700 text-white shadow-sm'
                          : 'text-slate-400 hover:text-white'
                      }`}
                    >
                      Available ({modules.filter((m) => !m.installed && !m.builtin).length})
                    </button>
                  </div>

                  {/* Search Bar */}
                  <div className="w-full sm:w-64">
                    <div className="relative">
                      <Search className="absolute left-3 top-2.5 h-4 w-4 text-slate-500" />
                      <input
                        type="text"
                        placeholder="Search modules (e.g. zip, gd, redis)..."
                        value={modSearch}
                        onChange={(e) => setModSearch(e.target.value)}
                        className="w-full rounded-xl border border-white/10 bg-slate-950 pl-9 pr-3 py-2 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                      />
                    </div>
                  </div>
                </div>
              </div>

              {/* Category Pills */}
              <div className="flex items-center gap-1.5 overflow-x-auto pb-2 scrollbar-none">
                <span className="text-[11px] font-semibold uppercase tracking-wider text-slate-500 mr-1 flex items-center gap-1">
                  <Filter className="h-3 w-3" /> Categories:
                </span>
                {categories.map((cat) => (
                  <button
                    key={cat}
                    type="button"
                    onClick={() => setSelectedCategory(cat)}
                    className={`px-2.5 py-1 rounded-xl text-xs font-medium whitespace-nowrap transition-colors ${
                      selectedCategory === cat
                        ? 'bg-indigo-500/20 text-indigo-300 border border-indigo-500/40'
                        : 'bg-white/5 text-slate-400 hover:text-white hover:bg-white/10 border border-transparent'
                    }`}
                  >
                    {cat === 'all' ? 'All Categories' : cat}
                  </button>
                ))}
              </div>

              {/* Module Cards Grid */}
              {loading ? (
                <div className="p-16 text-center text-slate-400 animate-pulse">Loading PHP modules catalog...</div>
              ) : filteredModules.length === 0 ? (
                <div className="p-16 text-center text-slate-500 text-sm">
                  No modules found matching &quot;{modSearch}&quot; (Category: {selectedCategory}, Status: {selectedStatus})
                </div>
              ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                  {filteredModules.map((mod) => {
                    const isBusy = togglingModule === mod.name;
                    const isInstalled = mod.installed || mod.builtin;

                    return (
                      <div
                        key={mod.name}
                        className={`flex flex-col justify-between p-4 rounded-2xl border transition-all ${
                          mod.enabled
                            ? 'border-emerald-500/25 bg-emerald-950/10 hover:border-emerald-500/40 shadow-sm'
                            : isInstalled
                            ? 'border-amber-500/20 bg-amber-950/10 hover:border-amber-500/30'
                            : 'border-white/5 bg-slate-950/40 hover:border-white/15 opacity-80'
                        }`}
                      >
                        <div>
                          {/* Header: Name + Category + Status Badge */}
                          <div className="flex items-start justify-between gap-2 mb-2">
                            <div className="flex flex-col">
                              <span className="font-mono text-sm font-bold text-white tracking-wide">
                                {mod.name}
                              </span>
                              {mod.category && (
                                <span className="text-[10px] text-indigo-400 font-medium">
                                  {mod.category}
                                </span>
                              )}
                            </div>

                            {/* Status Badge */}
                            {mod.builtin ? (
                              <span className="rounded-lg bg-sky-500/10 border border-sky-500/20 px-2 py-0.5 text-[10px] font-semibold text-sky-400">
                                Core Built-in
                              </span>
                            ) : mod.enabled ? (
                              <span className="rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-2 py-0.5 text-[10px] font-semibold text-emerald-400 flex items-center gap-1">
                                <span className="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse" />
                                Active
                              </span>
                            ) : isInstalled ? (
                              <span className="rounded-lg bg-amber-500/10 border border-amber-500/20 px-2 py-0.5 text-[10px] font-semibold text-amber-400">
                                Disabled
                              </span>
                            ) : (
                              <span className="rounded-lg bg-slate-800 border border-slate-700 px-2 py-0.5 text-[10px] font-medium text-slate-400">
                                Not Installed
                              </span>
                            )}
                          </div>

                          {/* Description */}
                          <p className="text-[11px] text-slate-400 line-clamp-2 min-h-[32px] mb-3">
                            {mod.description || 'PHP extension module'}
                          </p>
                        </div>

                        {/* Action Row */}
                        <div className="pt-3 border-t border-white/5 flex items-center justify-between">
                          <span className="font-mono text-[10px] text-slate-500 truncate max-w-[120px]">
                            {mod.package || mod.file || `${mod.name}.ini`}
                          </span>

                          {mod.builtin ? (
                            <span className="text-[10px] text-slate-500 italic">Always Active</span>
                          ) : !isInstalled ? (
                            <button
                              type="button"
                              disabled={isBusy}
                              onClick={() => handleToggleModule(mod.name, false)}
                              className="inline-flex items-center gap-1 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow hover:from-sky-400 hover:to-indigo-500 transition-all disabled:opacity-50"
                            >
                              <Download className={`h-3 w-3 ${isBusy ? 'animate-bounce' : ''}`} />
                              {isBusy ? 'Installing...' : 'Install & Enable'}
                            </button>
                          ) : (
                            <div className="flex items-center gap-2">
                              <span className="text-[10px] text-slate-400 font-medium">
                                {mod.enabled ? 'Enabled' : 'Disabled'}
                              </span>
                              <button
                                type="button"
                                role="switch"
                                aria-checked={mod.enabled}
                                disabled={isBusy}
                                onClick={() => handleToggleModule(mod.name, mod.enabled)}
                                className={`relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none ${
                                  mod.enabled ? 'bg-sky-500' : 'bg-slate-800'
                                } ${isBusy ? 'opacity-50 cursor-wait' : ''}`}
                              >
                                <span
                                  className={`pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out ${
                                    mod.enabled ? 'translate-x-4' : 'translate-x-0'
                                  }`}
                                />
                              </button>
                            </div>
                          )}
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          </div>
        )}

        {/* Tab 3: OPcache & Performance Tuning */}
        {activeTab === 'opcache' && (
          <div className="space-y-6">
            <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl p-6 shadow-2xl">
              <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-white/5 mb-6">
                <div>
                  <h2 className="text-lg font-bold text-white flex items-center gap-2">
                    <Cpu className="h-5 w-5 text-sky-400" />
                    Zend OPcache & Accelerator Settings
                  </h2>
                  <p className="text-xs text-slate-400 mt-1">
                    Precompile and cache PHP bytecode in RAM to dramatically reduce server response latency and CPU usage.
                  </p>
                </div>
                <span className="rounded-lg bg-sky-500/10 border border-sky-500/20 px-2.5 py-1 text-xs font-mono font-semibold text-sky-300">
                  Target: PHP {selectedVersion}
                </span>
              </div>

              <form onSubmit={handleSaveOpcache} className="space-y-6 max-w-2xl">
                <div className="flex items-center justify-between p-4 rounded-2xl border border-white/5 bg-slate-950/50">
                  <div>
                    <span className="text-sm font-semibold text-white">Enable Zend OPcache</span>
                    <p className="text-xs text-slate-400 mt-0.5">Enables opcode caching for all incoming PHP execution requests</p>
                  </div>
                  <button
                    type="button"
                    role="switch"
                    aria-checked={opcacheEnabled}
                    onClick={() => setOpcacheEnabled(!opcacheEnabled)}
                    className={`relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none ${
                      opcacheEnabled ? 'bg-emerald-500' : 'bg-slate-800'
                    }`}
                  >
                    <span
                      className={`pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-lg ring-0 transition duration-200 ease-in-out ${
                        opcacheEnabled ? 'translate-x-5' : 'translate-x-0'
                      }`}
                    />
                  </button>
                </div>

                <div className="grid grid-cols-1 sm:grid-cols-3 gap-5">
                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                      Memory Consumption (MB)
                    </label>
                    <input
                      type="number"
                      required
                      value={opcacheMemory}
                      onChange={(e) => setOpcacheMemory(e.target.value)}
                      placeholder="128"
                      className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white focus:border-sky-500 focus:outline-none"
                    />
                    <p className="text-[11px] text-slate-500 mt-1">Default: 128MB</p>
                  </div>

                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                      Max Accelerated Files
                    </label>
                    <input
                      type="number"
                      required
                      value={opcacheMaxFiles}
                      onChange={(e) => setOpcacheMaxFiles(e.target.value)}
                      placeholder="10000"
                      className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white focus:border-sky-500 focus:outline-none"
                    />
                    <p className="text-[11px] text-slate-500 mt-1">Recommended: 10000</p>
                  </div>

                  <div>
                    <label className="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-1.5">
                      Revalidate Frequency (s)
                    </label>
                    <input
                      type="number"
                      required
                      value={opcacheRevalidateFreq}
                      onChange={(e) => setOpcacheRevalidateFreq(e.target.value)}
                      placeholder="2"
                      className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-sm text-white focus:border-sky-500 focus:outline-none"
                    />
                    <p className="text-[11px] text-slate-500 mt-1">Check file modification (2s)</p>
                  </div>
                </div>

                <div className="pt-4 border-t border-white/5 flex justify-end">
                  <button
                    type="submit"
                    disabled={savingOpcache}
                    className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all disabled:opacity-50"
                  >
                    <Save className="h-4 w-4" />
                    {savingOpcache ? 'Applying OPcache...' : 'Apply OPcache & Reload'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}

        {/* Tab 4: Raw Custom php.ini Editor */}
        {activeTab === 'ini' && (
          <div className="space-y-6">
            <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl p-6 shadow-2xl">
              <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-4 border-b border-white/5 mb-4 gap-2">
                <div>
                  <h2 className="text-lg font-bold text-white flex items-center gap-2">
                    <FileCode className="h-5 w-5 text-indigo-400" />
                    Custom php.ini Directives File
                  </h2>
                  <p className="text-xs text-slate-400 mt-1">
                    Live editor for <code className="text-sky-300">/etc/php/{selectedVersion}/conf.d/99-kloxo-custom.ini</code>. Applied on top of global php.ini.
                  </p>
                </div>

                <div className="flex items-center gap-2">
                  <button
                    onClick={async () => {
                      const res = await apiFetch<PhpIniResponse>(`/php/ini?version=${encodeURIComponent(selectedVersion)}`);
                      setIniContent(res.content || '');
                    }}
                    className="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-300 hover:bg-white/10"
                  >
                    <RotateCcw className="h-3.5 w-3.5" /> Revert
                  </button>
                </div>
              </div>

              <form onSubmit={handleSaveIni} className="space-y-4">
                <div className="rounded-2xl border border-white/10 bg-slate-950 p-3 shadow-inner">
                  <textarea
                    rows={16}
                    value={iniContent}
                    onChange={(e) => setIniContent(e.target.value)}
                    placeholder="; Enter custom php.ini directives here..."
                    className="w-full bg-transparent font-mono text-xs leading-relaxed text-sky-200 focus:outline-none resize-y"
                    spellCheck={false}
                  />
                </div>

                <div className="flex items-center justify-between pt-2">
                  <span className="text-xs text-slate-400">
                    Lines: {iniContent.split('\n').length} | Target: PHP {selectedVersion}
                  </span>

                  <button
                    type="submit"
                    disabled={savingIni}
                    className="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all disabled:opacity-50"
                  >
                    <Save className="h-4 w-4" />
                    {savingIni ? 'Saving...' : 'Save & Reload Webserver'}
                  </button>
                </div>
              </form>
            </div>
          </div>
        )}
      </div>
    </Shell>
  );
}
