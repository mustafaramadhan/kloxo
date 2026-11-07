'use client';

import React, { useState, useEffect, useRef } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, FileItem, FileListResponse, formatBytes, getStoredToken } from '@/lib/api';
import {
  FolderTree,
  Folder,
  FileText,
  FileCode,
  FileArchive,
  FileImage,
  ArrowUp,
  RefreshCw,
  Save,
  X,
  Plus,
  Trash2,
  FolderPlus,
  FilePlus,
  Upload,
  Download,
  Archive,
  PackageOpen,
  Edit3,
  Shield,
  Search,
  CheckSquare,
  Square,
  AlertCircle,
  CheckCircle2,
  ChevronRight,
  Home,
  Check,
} from 'lucide-react';

export default function FileManagerPage() {
  const [currentPath, setCurrentPath] = useState('');
  const [userRootPath, setUserRootPath] = useState('');
  const [parentPath, setParentPath] = useState<string | null>(null);
  const [items, setItems] = useState<FileItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [message, setMessage] = useState<{ text: string; success: boolean } | null>(null);

  // Search / Filter
  const [searchQuery, setSearchQuery] = useState('');

  // Selection
  const [selectedPaths, setSelectedPaths] = useState<string[]>([]);

  // Modals
  const [showCreateModal, setShowCreateModal] = useState<'file' | 'folder' | null>(null);
  const [newItemName, setNewItemName] = useState('');

  const [showUploadModal, setShowUploadModal] = useState(false);
  const [uploadFile, setUploadFile] = useState<File | null>(null);
  const [uploadLoading, setUploadLoading] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const [showExtractModal, setShowExtractModal] = useState<string | null>(null);
  const [extractDest, setExtractDest] = useState('');

  const [showCompressModal, setShowCompressModal] = useState(false);
  const [compressName, setCompressName] = useState('archive');
  const [compressFormat, setCompressFormat] = useState<'zip' | 'tar.gz'>('zip');

  const [showRenameModal, setShowRenameModal] = useState<string | null>(null);
  const [renameNewName, setRenameNewName] = useState('');

  const [showPermsModal, setShowPermsModal] = useState<{ path: string; name: string; perms: string } | null>(null);
  const [permUserR, setPermUserR] = useState(true);
  const [permUserW, setPermUserW] = useState(true);
  const [permUserX, setPermUserX] = useState(true);
  const [permGroupR, setPermGroupR] = useState(true);
  const [permGroupW, setPermGroupW] = useState(false);
  const [permGroupX, setPermGroupX] = useState(true);
  const [permWorldR, setPermWorldR] = useState(true);
  const [permWorldW, setPermWorldW] = useState(false);
  const [permWorldX, setPermWorldX] = useState(true);

  // Editor Modal
  const [activeFile, setActiveFile] = useState<string | null>(null);
  const [fileContent, setFileContent] = useState('');
  const [fileLoading, setFileLoading] = useState(false);
  const [saveStatus, setSaveStatus] = useState<string | null>(null);

  const [actionLoading, setActionLoading] = useState(false);

  const fetchDirectory = async (path?: string) => {
    setLoading(true);
    setError(null);
    setSelectedPaths([]);
    try {
      const url = path ? `/files?path=${encodeURIComponent(path)}` : '/files';
      const data = await apiFetch<any>(url);
      const resolvedPath = data.current_path || data.path || path || '/';
      setCurrentPath(resolvedPath);
      setParentPath(data.parent_path || null);
      setItems(data.items || data.files || []);
      if (!userRootPath && resolvedPath) {
        setUserRootPath(resolvedPath);
      }
    } catch (err: any) {
      setError(err.message || 'Failed to load directory');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    const token = getStoredToken();
    let initialPath = '';
    if (token) {
      try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        const user = payload.sub || payload.username;
        if (user && user !== 'admin') {
          initialPath = `/home/${user}`;
        } else {
          initialPath = '/home/admin';
        }
      } catch {}
    }
    if (initialPath) {
      setUserRootPath(initialPath);
    }
    fetchDirectory(initialPath || undefined);
  }, []);

  // Compute octal permissions string from checkboxes
  const computeOctal = () => {
    const u = (permUserR ? 4 : 0) + (permUserW ? 2 : 0) + (permUserX ? 1 : 0);
    const g = (permGroupR ? 4 : 0) + (permGroupW ? 2 : 0) + (permGroupX ? 1 : 0);
    const w = (permWorldR ? 4 : 0) + (permWorldW ? 2 : 0) + (permWorldX ? 1 : 0);
    return `0${u}${g}${w}`;
  };

  const openPermsModal = (item: FileItem) => {
    const p = item.permissions.slice(-3);
    const u = parseInt(p[0] || '7', 10);
    const g = parseInt(p[1] || '5', 10);
    const w = parseInt(p[2] || '5', 10);

    setPermUserR(Boolean(u & 4));
    setPermUserW(Boolean(u & 2));
    setPermUserX(Boolean(u & 1));

    setPermGroupR(Boolean(g & 4));
    setPermGroupW(Boolean(g & 2));
    setPermGroupX(Boolean(g & 1));

    setPermWorldR(Boolean(w & 4));
    setPermWorldW(Boolean(w & 2));
    setPermWorldX(Boolean(w & 1));

    setShowPermsModal({ path: item.path, name: item.name, perms: item.permissions });
  };

  const handleSavePermissions = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!showPermsModal) return;
    setActionLoading(true);
    try {
      const newPerm = computeOctal();
      await apiFetch('/files/permissions', {
        method: 'POST',
        body: JSON.stringify({ path: showPermsModal.path, permissions: newPerm }),
      });
      setMessage({ text: `Permissions updated to ${newPerm}`, success: true });
      setShowPermsModal(null);
      await fetchDirectory(currentPath);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to update permissions', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleCreate = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newItemName.trim() || !showCreateModal) return;
    setActionLoading(true);
    try {
      await apiFetch('/files/create', {
        method: 'POST',
        body: JSON.stringify({
          path: currentPath,
          name: newItemName.trim(),
          type: showCreateModal === 'folder' ? 'directory' : 'file',
        }),
      });
      setMessage({
        text: `${showCreateModal === 'folder' ? 'Folder' : 'File'} "${newItemName}" created`,
        success: true,
      });
      setShowCreateModal(null);
      setNewItemName('');
      await fetchDirectory(currentPath);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to create item', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleUpload = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!uploadFile) return;
    setUploadLoading(true);
    try {
      const formData = new FormData();
      formData.append('target_dir', currentPath);
      formData.append('file', uploadFile);

      const token = getStoredToken();
      const res = await fetch('/api/v1/files/upload', {
        method: 'POST',
        headers: token ? { Authorization: `Bearer ${token}` } : {},
        body: formData,
      });
      const text = await res.text();
      let data: any = {};
      if (text && text.trim().length > 0) {
        try {
          data = JSON.parse(text);
        } catch {
          throw new Error('Upload response could not be parsed');
        }
      }
      if (!res.ok) throw new Error(data.message || 'Upload failed');

      setMessage({ text: `File "${uploadFile.name}" uploaded successfully`, success: true });
      setShowUploadModal(false);
      setUploadFile(null);
      await fetchDirectory(currentPath);
    } catch (err: any) {
      setMessage({ text: err.message || 'Upload failed', success: false });
    } finally {
      setUploadLoading(false);
    }
  };

  const handleDownload = (path: string) => {
    const token = getStoredToken();
    const url = `/api/v1/files/download?path=${encodeURIComponent(path)}${token ? `&token=${encodeURIComponent(token)}` : ''}`;
    window.open(url, '_blank');
  };

  const handleExtract = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!showExtractModal) return;
    setActionLoading(true);
    try {
      await apiFetch('/files/extract', {
        method: 'POST',
        body: JSON.stringify({
          path: showExtractModal,
          destination: extractDest || currentPath,
        }),
      });
      setMessage({ text: 'Archive extracted successfully', success: true });
      setShowExtractModal(null);
      await fetchDirectory(currentPath);
    } catch (err: any) {
      setMessage({ text: err.message || 'Extract failed', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleCompress = async (e: React.FormEvent) => {
    e.preventDefault();
    if (selectedPaths.length === 0) return;
    setActionLoading(true);
    try {
      await apiFetch('/files/compress', {
        method: 'POST',
        body: JSON.stringify({
          files: selectedPaths,
          archive_name: compressName,
          format: compressFormat,
          destination: currentPath,
        }),
      });
      setMessage({ text: 'Archive created successfully', success: true });
      setShowCompressModal(false);
      setSelectedPaths([]);
      await fetchDirectory(currentPath);
    } catch (err: any) {
      setMessage({ text: err.message || 'Compression failed', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleRename = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!showRenameModal || !renameNewName.trim()) return;
    setActionLoading(true);
    try {
      await apiFetch('/files/rename', {
        method: 'POST',
        body: JSON.stringify({
          path: showRenameModal,
          new_name: renameNewName.trim(),
        }),
      });
      setMessage({ text: 'Renamed successfully', success: true });
      setShowRenameModal(null);
      setRenameNewName('');
      await fetchDirectory(currentPath);
    } catch (err: any) {
      setMessage({ text: err.message || 'Rename failed', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleDelete = async (path: string, name: string) => {
    if (!confirm(`Are you sure you want to permanently delete "${name}"?`)) return;
    setActionLoading(true);
    try {
      await apiFetch('/files', {
        method: 'DELETE',
        body: JSON.stringify({ path }),
      });
      setMessage({ text: `"${name}" deleted`, success: true });
      await fetchDirectory(currentPath);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete item', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const handleBatchDelete = async () => {
    if (selectedPaths.length === 0) return;
    if (!confirm(`Are you sure you want to permanently delete ${selectedPaths.length} selected items?`)) return;
    setActionLoading(true);
    try {
      for (const p of selectedPaths) {
        await apiFetch('/files', {
          method: 'DELETE',
          body: JSON.stringify({ path: p }),
        });
      }
      setMessage({ text: `${selectedPaths.length} items deleted`, success: true });
      setSelectedPaths([]);
      await fetchDirectory(currentPath);
    } catch (err: any) {
      setMessage({ text: err.message || 'Failed to delete some items', success: false });
    } finally {
      setActionLoading(false);
    }
  };

  const openFileEditor = async (file: FileItem) => {
    setActiveFile(file.path);
    setFileLoading(true);
    setSaveStatus(null);
    try {
      const data = await apiFetch<{ content: string }>('/files/content', {
        method: 'POST',
        body: JSON.stringify({ path: file.path }),
      });
      setFileContent(data.content);
    } catch (err: any) {
      setMessage({ text: err.message || 'Could not load file content', success: false });
      setActiveFile(null);
    } finally {
      setFileLoading(false);
    }
  };

  const saveFileContent = async () => {
    if (!activeFile) return;
    setFileLoading(true);
    setSaveStatus(null);
    try {
      await apiFetch('/files/save', {
        method: 'POST',
        body: JSON.stringify({ path: activeFile, content: fileContent }),
      });
      setSaveStatus('Saved successfully!');
      setTimeout(() => setSaveStatus(null), 3000);
    } catch (err: any) {
      setSaveStatus(`Failed to save: ${err.message}`);
    } finally {
      setFileLoading(false);
    }
  };

  const toggleSelect = (path: string) => {
    setSelectedPaths((prev) =>
      prev.includes(path) ? prev.filter((p) => p !== path) : [...prev, path]
    );
  };

  const toggleSelectAll = () => {
    if (selectedPaths.length === filteredItems.length) {
      setSelectedPaths([]);
    } else {
      setSelectedPaths(filteredItems.map((i) => i.path));
    }
  };

  const getFileIcon = (item: FileItem) => {
    if (item.is_directory) return <Folder className="h-5 w-5 text-amber-400 fill-amber-400/20 flex-shrink-0" />;
    const ext = item.name.split('.').pop()?.toLowerCase();
    if (['zip', 'tar', 'gz', 'tgz', 'rar', '7z'].includes(ext || '')) {
      return <FileArchive className="h-5 w-5 text-rose-400 flex-shrink-0" />;
    }
    if (['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'ico'].includes(ext || '')) {
      return <FileImage className="h-5 w-5 text-emerald-400 flex-shrink-0" />;
    }
    if (['php', 'js', 'ts', 'jsx', 'tsx', 'html', 'css', 'json', 'sql', 'sh', 'py'].includes(ext || '')) {
      return <FileCode className="h-5 w-5 text-sky-400 flex-shrink-0" />;
    }
    return <FileText className="h-5 w-5 text-slate-400 flex-shrink-0" />;
  };

  const isArchive = (name: string) => /\.(zip|tar\.gz|tgz|tar)$/i.test(name);

  // Filtered items
  const filteredItems = items.filter((i) =>
    i.name.toLowerCase().includes(searchQuery.toLowerCase())
  );

  // Breadcrumbs
  const breadcrumbSegments = currentPath.split('/').filter(Boolean);

  return (
    <Shell>
      <div className="space-y-4">
        {/* cPanel-Style Main Action Toolbar */}
        <div className="rounded-2xl border border-white/10 bg-slate-900/80 backdrop-blur-xl p-3 shadow-xl">
          <div className="flex flex-wrap items-center justify-between gap-3">
            {/* Primary Action Buttons */}
            <div className="flex flex-wrap items-center gap-1.5 sm:gap-2">
              <button
                onClick={() => {
                  setShowCreateModal('file');
                  setNewItemName('');
                }}
                className="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10 transition-colors"
                title="Create New File"
              >
                <FilePlus className="h-4 w-4 text-sky-400" />
                <span>+ File</span>
              </button>

              <button
                onClick={() => {
                  setShowCreateModal('folder');
                  setNewItemName('');
                }}
                className="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10 transition-colors"
                title="Create New Folder"
              >
                <FolderPlus className="h-4 w-4 text-amber-400" />
                <span>+ Folder</span>
              </button>

              <button
                onClick={() => setShowUploadModal(true)}
                className="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 transition-all"
                title="Upload File"
              >
                <Upload className="h-4 w-4" />
                <span>Upload</span>
              </button>

              {selectedPaths.length === 1 && !items.find((i) => i.path === selectedPaths[0])?.is_directory && (
                <button
                  onClick={() => handleDownload(selectedPaths[0])}
                  className="inline-flex items-center gap-1.5 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-xs font-semibold text-slate-200 hover:bg-white/10 transition-colors"
                  title="Download File"
                >
                  <Download className="h-4 w-4 text-emerald-400" />
                  <span>Download</span>
                </button>
              )}

              {selectedPaths.length > 0 && (
                <>
                  <button
                    onClick={() => {
                      setCompressName('backup_' + new Date().toISOString().slice(0, 10));
                      setShowCompressModal(true);
                    }}
                    className="inline-flex items-center gap-1.5 rounded-xl border border-purple-500/30 bg-purple-500/10 px-3 py-2 text-xs font-semibold text-purple-300 hover:bg-purple-500/20 transition-colors"
                    title="Compress Selected"
                  >
                    <Archive className="h-4 w-4" />
                    <span>Compress ({selectedPaths.length})</span>
                  </button>

                  <button
                    onClick={handleBatchDelete}
                    disabled={actionLoading}
                    className="inline-flex items-center gap-1.5 rounded-xl border border-rose-500/30 bg-rose-500/10 px-3 py-2 text-xs font-semibold text-rose-300 hover:bg-rose-500/20 transition-colors"
                    title="Delete Selected"
                  >
                    <Trash2 className="h-4 w-4" />
                    <span>Delete ({selectedPaths.length})</span>
                  </button>
                </>
              )}
            </div>

            {/* Quick Search & Reload */}
            <div className="flex items-center gap-2">
              <div className="relative">
                <Search className="absolute left-3 top-2.5 h-3.5 w-3.5 text-slate-500" />
                <input
                  type="text"
                  placeholder="Filter files..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="w-40 sm:w-56 rounded-xl border border-white/10 bg-slate-950 pl-8 pr-3 py-1.5 text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                />
              </div>

              <button
                onClick={() => fetchDirectory(currentPath)}
                disabled={loading}
                className="inline-flex items-center p-2 rounded-xl border border-white/10 bg-white/5 text-slate-300 hover:bg-white/10 transition-colors disabled:opacity-50"
                title="Reload Directory"
              >
                <RefreshCw className={`h-4 w-4 ${loading ? 'animate-spin' : ''}`} />
              </button>
            </div>
          </div>
        </div>

        {/* Breadcrumb Path Navigation Bar */}
        <div className="flex items-center justify-between rounded-xl border border-white/5 bg-slate-950/60 px-4 py-2.5 text-xs">
          <div className="flex items-center gap-1.5 overflow-x-auto text-slate-400">
            <button
              onClick={() => fetchDirectory(userRootPath || '/home')}
              className="flex items-center gap-1 hover:text-white transition-colors"
              title="Home directory"
            >
              <Home className="h-3.5 w-3.5 text-sky-400" />
              <span>~</span>
            </button>
            {breadcrumbSegments.map((seg, idx) => {
              const segPath = '/' + breadcrumbSegments.slice(0, idx + 1).join('/');
              const isLast = idx === breadcrumbSegments.length - 1;
              const isAccessible = !userRootPath || userRootPath === '/home/admin' || segPath.startsWith(userRootPath) || userRootPath.startsWith(segPath);
              return (
                <React.Fragment key={segPath}>
                  <ChevronRight className="h-3.5 w-3.5 text-slate-600 flex-shrink-0" />
                  <button
                    disabled={!isAccessible}
                    onClick={() => {
                      if (!isAccessible) return;
                      // If clicking a parent like /home while jailed to /home/mehdi, open userRootPath
                      if (userRootPath && userRootPath !== '/home/admin' && !segPath.startsWith(userRootPath)) {
                        fetchDirectory(userRootPath);
                      } else {
                        fetchDirectory(segPath);
                      }
                    }}
                    className={`font-mono transition-colors ${
                      isLast
                        ? 'text-white font-semibold'
                        : isAccessible
                        ? 'text-slate-400 hover:text-white'
                        : 'text-slate-600 cursor-default'
                    }`}
                  >
                    {seg}
                  </button>
                </React.Fragment>
              );
            })}
          </div>

          {parentPath && (
            <button
              onClick={() => fetchDirectory(parentPath)}
              className="inline-flex items-center gap-1 text-[11px] font-medium text-sky-400 hover:text-sky-300 ml-4 flex-shrink-0"
            >
              <ArrowUp className="h-3.5 w-3.5" />
              Up One Level
            </button>
          )}
        </div>

        {/* Notifications */}
        {message && (
          <div
            className={`flex items-center justify-between rounded-2xl border p-3.5 text-xs ${
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
              <X className="h-3.5 w-3.5" />
            </button>
          </div>
        )}

        {/* Main File Table */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-16 text-center text-slate-400 animate-pulse text-sm">Loading directory items...</div>
          ) : filteredItems.length === 0 ? (
            <div className="p-16 text-center">
              <FolderTree className="mx-auto h-12 w-12 text-slate-600 mb-3" />
              <p className="text-slate-400 font-medium">Directory is empty</p>
              <p className="text-xs text-slate-500 mt-1">
                Upload files or use &quot;+ File&quot; to start adding content.
              </p>
            </div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/60 font-semibold uppercase text-slate-400 tracking-wider">
                  <tr>
                    <th className="px-4 py-3 w-8">
                      <button onClick={toggleSelectAll} className="text-slate-400 hover:text-white">
                        {selectedPaths.length === filteredItems.length && filteredItems.length > 0 ? (
                          <CheckSquare className="h-4 w-4 text-sky-400" />
                        ) : (
                          <Square className="h-4 w-4 text-slate-600" />
                        )}
                      </button>
                    </th>
                    <th className="px-4 py-3">Name</th>
                    <th className="px-4 py-3">Size</th>
                    <th className="px-4 py-3">Permissions</th>
                    <th className="px-4 py-3">Last Modified</th>
                    <th className="px-4 py-3 text-right">Actions</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {filteredItems.map((item) => {
                    const isSelected = selectedPaths.includes(item.path);
                    return (
                      <tr
                        key={item.path}
                        className={`transition-colors ${
                          isSelected ? 'bg-sky-500/5' : 'hover:bg-white/[0.02]'
                        }`}
                      >
                        <td className="px-4 py-3">
                          <button
                            onClick={() => toggleSelect(item.path)}
                            className="text-slate-400 hover:text-white"
                          >
                            {isSelected ? (
                              <CheckSquare className="h-4 w-4 text-sky-400" />
                            ) : (
                              <Square className="h-4 w-4 text-slate-600" />
                            )}
                          </button>
                        </td>

                        <td className="px-4 py-3 font-medium text-white">
                          <div className="flex items-center gap-2.5">
                            {getFileIcon(item)}
                            {item.is_directory ? (
                              <button
                                onClick={() => fetchDirectory(item.path)}
                                className="font-semibold text-slate-100 hover:text-sky-400 transition-colors text-left truncate max-w-sm"
                              >
                                {item.name}
                              </button>
                            ) : (
                              <button
                                onClick={() => openFileEditor(item)}
                                className="text-slate-200 hover:text-sky-400 transition-colors text-left truncate max-w-sm"
                              >
                                {item.name}
                              </button>
                            )}
                          </div>
                        </td>

                        <td className="px-4 py-3 font-mono text-slate-400">
                          {item.is_directory ? '—' : formatBytes(item.size_bytes)}
                        </td>

                        <td className="px-4 py-3">
                          <button
                            onClick={() => openPermsModal(item)}
                            className="inline-flex items-center gap-1 font-mono text-sky-400 hover:underline"
                            title="Click to edit permissions"
                          >
                            <Shield className="h-3 w-3" />
                            {item.permissions}
                          </button>
                        </td>

                        <td className="px-4 py-3 font-mono text-slate-400">{item.modified || '—'}</td>

                        <td className="px-4 py-3 text-right">
                          <div className="flex items-center justify-end gap-1">
                            {!item.is_directory && (
                              <button
                                onClick={() => openFileEditor(item)}
                                className="p-1.5 rounded-lg text-slate-400 hover:text-sky-400 hover:bg-sky-500/10 transition-colors"
                                title="Edit File"
                              >
                                <Edit3 className="h-3.5 w-3.5" />
                              </button>
                            )}

                            {!item.is_directory && (
                              <button
                                onClick={() => handleDownload(item.path)}
                                className="p-1.5 rounded-lg text-slate-400 hover:text-emerald-400 hover:bg-emerald-500/10 transition-colors"
                                title="Download File"
                              >
                                <Download className="h-3.5 w-3.5" />
                              </button>
                            )}

                            {isArchive(item.name) && (
                              <button
                                onClick={() => {
                                  setShowExtractModal(item.path);
                                  setExtractDest(currentPath);
                                }}
                                className="p-1.5 rounded-lg text-slate-400 hover:text-purple-400 hover:bg-purple-500/10 transition-colors"
                                title="Extract Archive"
                              >
                                <PackageOpen className="h-3.5 w-3.5" />
                              </button>
                            )}

                            <button
                              onClick={() => {
                                setShowRenameModal(item.path);
                                setRenameNewName(item.name);
                              }}
                              className="p-1.5 rounded-lg text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 transition-colors"
                              title="Rename"
                            >
                              <Edit3 className="h-3.5 w-3.5" />
                            </button>

                            <button
                              onClick={() => handleDelete(item.path, item.name)}
                              className="p-1.5 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition-colors"
                              title="Delete"
                            >
                              <Trash2 className="h-3.5 w-3.5" />
                            </button>
                          </div>
                        </td>
                      </tr>
                    );
                  })}
                </tbody>
              </table>
            </div>
          )}
        </div>
      </div>

      {/* Upload File Modal */}
      {showUploadModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Upload className="h-5 w-5 text-sky-400" />
                Upload File to Current Directory
              </h2>
              <button onClick={() => setShowUploadModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleUpload} className="mt-4 space-y-4">
              <div className="text-xs text-slate-400">
                Destination: <span className="font-mono text-sky-400">{currentPath}</span>
              </div>

              <div
                onClick={() => fileInputRef.current?.click()}
                className="flex flex-col items-center justify-center p-8 border-2 border-dashed border-white/10 hover:border-sky-500/50 rounded-2xl cursor-pointer bg-slate-950/40 hover:bg-slate-950/60 transition-all text-center"
              >
                <Upload className="h-8 w-8 text-sky-400 mb-2" />
                <p className="text-xs font-semibold text-white">Click or drag file here to upload</p>
                <p className="text-[11px] text-slate-500 mt-1">Supports any file format (code, zip, media, docs)</p>
                <input
                  ref={fileInputRef}
                  type="file"
                  onChange={(e) => setUploadFile(e.target.files?.[0] || null)}
                  className="hidden"
                />
              </div>

              {uploadFile && (
                <div className="flex items-center justify-between p-3 rounded-xl bg-slate-950 border border-white/10 text-xs">
                  <span className="font-mono text-white truncate max-w-xs">{uploadFile.name}</span>
                  <span className="text-slate-400">{formatBytes(uploadFile.size)}</span>
                </div>
              )}

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowUploadModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={!uploadFile || uploadLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {uploadLoading ? 'Uploading...' : 'Upload Now'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Permissions Modal (chmod) */}
      {showPermsModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Shield className="h-5 w-5 text-sky-400" />
                Change Permissions (chmod)
              </h2>
              <button onClick={() => setShowPermsModal(null)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleSavePermissions} className="mt-4 space-y-4">
              <div className="text-xs text-slate-300 truncate">
                File: <span className="font-mono text-sky-400">{showPermsModal.name}</span>
              </div>

              {/* cPanel style permission matrix */}
              <div className="rounded-2xl border border-white/10 bg-slate-950 p-4">
                <table className="w-full text-center text-xs">
                  <thead>
                    <tr className="border-b border-white/5 text-slate-400 pb-2">
                      <th className="text-left pb-2">Scope</th>
                      <th className="pb-2">Read (4)</th>
                      <th className="pb-2">Write (2)</th>
                      <th className="pb-2">Execute (1)</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-white/5">
                    <tr>
                      <td className="text-left py-2.5 font-semibold text-white">User</td>
                      <td>
                        <input
                          type="checkbox"
                          checked={permUserR}
                          onChange={(e) => setPermUserR(e.target.checked)}
                          className="rounded bg-slate-900 text-sky-500 focus:ring-0"
                        />
                      </td>
                      <td>
                        <input
                          type="checkbox"
                          checked={permUserW}
                          onChange={(e) => setPermUserW(e.target.checked)}
                          className="rounded bg-slate-900 text-sky-500 focus:ring-0"
                        />
                      </td>
                      <td>
                        <input
                          type="checkbox"
                          checked={permUserX}
                          onChange={(e) => setPermUserX(e.target.checked)}
                          className="rounded bg-slate-900 text-sky-500 focus:ring-0"
                        />
                      </td>
                    </tr>
                    <tr>
                      <td className="text-left py-2.5 font-semibold text-white">Group</td>
                      <td>
                        <input
                          type="checkbox"
                          checked={permGroupR}
                          onChange={(e) => setPermGroupR(e.target.checked)}
                          className="rounded bg-slate-900 text-sky-500 focus:ring-0"
                        />
                      </td>
                      <td>
                        <input
                          type="checkbox"
                          checked={permGroupW}
                          onChange={(e) => setPermGroupW(e.target.checked)}
                          className="rounded bg-slate-900 text-sky-500 focus:ring-0"
                        />
                      </td>
                      <td>
                        <input
                          type="checkbox"
                          checked={permGroupX}
                          onChange={(e) => setPermGroupX(e.target.checked)}
                          className="rounded bg-slate-900 text-sky-500 focus:ring-0"
                        />
                      </td>
                    </tr>
                    <tr>
                      <td className="text-left py-2.5 font-semibold text-white">World</td>
                      <td>
                        <input
                          type="checkbox"
                          checked={permWorldR}
                          onChange={(e) => setPermWorldR(e.target.checked)}
                          className="rounded bg-slate-900 text-sky-500 focus:ring-0"
                        />
                      </td>
                      <td>
                        <input
                          type="checkbox"
                          checked={permWorldW}
                          onChange={(e) => setPermWorldW(e.target.checked)}
                          className="rounded bg-slate-900 text-sky-500 focus:ring-0"
                        />
                      </td>
                      <td>
                        <input
                          type="checkbox"
                          checked={permWorldX}
                          onChange={(e) => setPermWorldX(e.target.checked)}
                          className="rounded bg-slate-900 text-sky-500 focus:ring-0"
                        />
                      </td>
                    </tr>
                  </tbody>
                </table>

                <div className="mt-4 pt-3 border-t border-white/5 flex items-center justify-between text-xs">
                  <span className="text-slate-400">Calculated Octal:</span>
                  <span className="font-mono text-base font-bold text-sky-400">{computeOctal()}</span>
                </div>
              </div>

              <div className="flex justify-end gap-3 pt-2">
                <button
                  type="button"
                  onClick={() => setShowPermsModal(null)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  Save Permissions
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Extract Modal */}
      {showExtractModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <PackageOpen className="h-5 w-5 text-purple-400" />
                Extract Compressed Archive
              </h2>
              <button onClick={() => setShowExtractModal(null)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleExtract} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Target Destination Directory
                </label>
                <input
                  type="text"
                  required
                  value={extractDest}
                  onChange={(e) => setExtractDest(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-xs text-white focus:border-purple-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowExtractModal(null)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-5 py-2 text-xs font-semibold text-white shadow-lg shadow-purple-500/20 hover:from-purple-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Extracting...' : 'Extract Files'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Compress Modal */}
      {showCompressModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Archive className="h-5 w-5 text-purple-400" />
                Compress Selected Files ({selectedPaths.length})
              </h2>
              <button onClick={() => setShowCompressModal(false)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCompress} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Archive Name
                </label>
                <input
                  type="text"
                  required
                  value={compressName}
                  onChange={(e) => setCompressName(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-xs text-white focus:border-purple-500 focus:outline-none"
                />
              </div>

              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  Compression Format
                </label>
                <div className="flex gap-4">
                  <label className="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                    <input
                      type="radio"
                      checked={compressFormat === 'zip'}
                      onChange={() => setCompressFormat('zip')}
                      className="text-purple-500 focus:ring-purple-500"
                    />
                    <span>.zip Archive</span>
                  </label>
                  <label className="flex items-center gap-2 text-xs text-slate-300 cursor-pointer">
                    <input
                      type="radio"
                      checked={compressFormat === 'tar.gz'}
                      onChange={() => setCompressFormat('tar.gz')}
                      className="text-purple-500 focus:ring-purple-500"
                    />
                    <span>.tar.gz Archive</span>
                  </label>
                </div>
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowCompressModal(false)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-purple-500 to-indigo-600 px-5 py-2 text-xs font-semibold text-white shadow-lg shadow-purple-500/20 hover:from-purple-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Compressing...' : 'Compress Files'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Rename Modal */}
      {showRenameModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                <Edit3 className="h-5 w-5 text-amber-400" />
                Rename Item
              </h2>
              <button onClick={() => setShowRenameModal(null)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleRename} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  New Name
                </label>
                <input
                  type="text"
                  required
                  value={renameNewName}
                  onChange={(e) => setRenameNewName(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-xs text-white focus:border-amber-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowRenameModal(null)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-2 text-xs font-semibold text-white shadow-lg shadow-amber-500/20 hover:from-amber-400 hover:to-orange-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Renaming...' : 'Rename'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Create File / Folder Modal */}
      {showCreateModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
          <div className="w-full max-w-md rounded-3xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
            <div className="flex items-center justify-between pb-4 border-b border-white/5">
              <h2 className="text-lg font-bold text-white flex items-center gap-2">
                {showCreateModal === 'folder' ? (
                  <FolderPlus className="h-5 w-5 text-amber-400" />
                ) : (
                  <FilePlus className="h-5 w-5 text-sky-400" />
                )}
                Create New {showCreateModal === 'folder' ? 'Folder' : 'File'}
              </h2>
              <button onClick={() => setShowCreateModal(null)} className="text-slate-400 hover:text-white">
                <X className="h-5 w-5" />
              </button>
            </div>

            <form onSubmit={handleCreate} className="mt-4 space-y-4">
              <div>
                <label className="block text-xs font-medium uppercase tracking-wider text-slate-400 mb-1">
                  {showCreateModal === 'folder' ? 'Folder Name' : 'File Name (with extension)'}
                </label>
                <input
                  type="text"
                  required
                  placeholder={showCreateModal === 'folder' ? 'new_directory' : 'index.php'}
                  value={newItemName}
                  onChange={(e) => setNewItemName(e.target.value)}
                  className="w-full rounded-xl border border-white/10 bg-slate-950 px-4 py-2.5 font-mono text-xs text-white placeholder-slate-500 focus:border-sky-500 focus:outline-none"
                />
              </div>

              <div className="flex justify-end gap-3 pt-3">
                <button
                  type="button"
                  onClick={() => setShowCreateModal(null)}
                  className="rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-xs text-slate-300 hover:bg-white/10"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  disabled={actionLoading}
                  className="rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-5 py-2 text-xs font-semibold text-white shadow-lg shadow-sky-500/20 hover:from-sky-400 hover:to-indigo-500 disabled:opacity-50"
                >
                  {actionLoading ? 'Creating...' : 'Create Item'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

      {/* Online Code Editor Modal */}
      {activeFile && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
          <div className="flex h-[90vh] w-full max-w-5xl flex-col rounded-3xl border border-white/10 bg-slate-900 shadow-2xl overflow-hidden">
            <div className="flex items-center justify-between border-b border-white/5 bg-slate-950 px-6 py-3">
              <div className="flex items-center gap-3">
                <FileCode className="h-5 w-5 text-sky-400" />
                <span className="font-mono text-xs text-slate-300 truncate max-w-lg">{activeFile}</span>
              </div>
              <div className="flex items-center gap-3">
                {saveStatus && (
                  <span className="text-xs font-medium text-emerald-400 animate-fade-in flex items-center gap-1">
                    <Check className="h-3.5 w-3.5" />
                    {saveStatus}
                  </span>
                )}
                <button
                  onClick={saveFileContent}
                  disabled={fileLoading}
                  className="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500 px-3.5 py-1.5 text-xs font-semibold text-white hover:bg-emerald-400 transition-colors disabled:opacity-50"
                >
                  <Save className="h-4 w-4" />
                  Save
                </button>
                <button
                  onClick={() => setActiveFile(null)}
                  className="p-1.5 text-slate-400 hover:text-white transition-colors"
                >
                  <X className="h-5 w-5" />
                </button>
              </div>
            </div>

            <div className="relative flex-1 bg-slate-950">
              {fileLoading && (
                <div className="absolute inset-0 flex items-center justify-center bg-slate-950/60 backdrop-blur-sm z-10 text-sky-400 text-xs animate-pulse">
                  Loading file...
                </div>
              )}
              <textarea
                value={fileContent}
                onChange={(e) => setFileContent(e.target.value)}
                onKeyDown={(e) => {
                  if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    saveFileContent();
                  }
                }}
                spellCheck={false}
                className="h-full w-full resize-none bg-transparent p-4 font-mono text-xs text-slate-200 outline-none leading-relaxed"
                placeholder="Empty file..."
              />
            </div>
          </div>
        </div>
      )}
    </Shell>
  );
}
