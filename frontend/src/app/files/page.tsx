'use client';

import React, { useState, useEffect } from 'react';
import Shell from '@/components/layout/Shell';
import { apiFetch, FileItem, FileListResponse, formatBytes } from '@/lib/api';
import {
  FolderTree,
  Folder,
  FileText,
  FileCode,
  ArrowUp,
  RefreshCw,
  Eye,
  Save,
  X,
  FilePlus,
  Trash2,
  HardDrive,
} from 'lucide-react';

export default function FileManagerPage() {
  const [currentPath, setCurrentPath] = useState('/home/admin');
  const [parentPath, setParentPath] = useState<string | null>(null);
  const [items, setItems] = useState<FileItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  // File Editor Modal
  const [activeFile, setActiveFile] = useState<string | null>(null);
  const [fileContent, setFileContent] = useState('');
  const [fileLoading, setFileLoading] = useState(false);
  const [saveStatus, setSaveStatus] = useState<string | null>(null);

  const fetchDirectory = async (path: string) => {
    setLoading(true);
    setError(null);
    try {
      const data = await apiFetch<FileListResponse>(`/files?path=${encodeURIComponent(path)}`);
      setCurrentPath(data.current_path);
      setParentPath(data.parent_path);
      setItems(data.items);
    } catch (err: any) {
      setError(err.message || 'Failed to load directory');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchDirectory('/home/admin');
  }, []);

  const handleOpenFile = async (file: FileItem) => {
    if (file.is_directory) {
      fetchDirectory(file.path);
      return;
    }
    setActiveFile(file.path);
    setFileLoading(true);
    setSaveStatus(null);
    try {
      const res = await apiFetch<{ content: string }>(`/files/content`, {
        method: 'POST',
        body: JSON.stringify({ path: file.path }),
      });
      setFileContent(res.content);
    } catch (err: any) {
      setFileContent(`/* Error reading file: ${err.message} */`);
    } finally {
      setFileLoading(false);
    }
  };

  const handleSaveFile = async () => {
    if (!activeFile) return;
    setSaveStatus('Saving...');
    try {
      await apiFetch(`/files/save`, {
        method: 'POST',
        body: JSON.stringify({ path: activeFile, content: fileContent }),
      });
      setSaveStatus('Saved successfully');
      setTimeout(() => setSaveStatus(null), 3000);
    } catch (err: any) {
      setSaveStatus(`Save error: ${err.message}`);
    }
  };

  return (
    <Shell>
      <div className="space-y-6">
        {/* Header */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-2xl font-bold tracking-tight text-white flex items-center gap-2">
              <FolderTree className="h-6 w-6 text-sky-400" />
              File Manager
            </h1>
            <p className="text-sm text-slate-400">
              Browse, view, and edit files on your hosting server
            </p>
          </div>
          <button
            onClick={() => fetchDirectory(currentPath)}
            className="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/10 transition-colors w-fit"
          >
            <RefreshCw className="h-4 w-4" />
            Refresh
          </button>
        </div>

        {/* Path Bar */}
        <div className="flex items-center gap-3 rounded-2xl border border-white/5 bg-slate-900/60 p-3.5 backdrop-blur-xl">
          {parentPath && (
            <button
              onClick={() => fetchDirectory(parentPath)}
              title="Go Up"
              className="flex h-9 w-9 items-center justify-center rounded-xl bg-white/5 text-slate-300 hover:bg-white/10 hover:text-white transition-colors"
            >
              <ArrowUp className="h-4 w-4" />
            </button>
          )}
          <div className="flex-1 overflow-x-auto text-sm font-mono text-sky-400">
            {currentPath}
          </div>
        </div>

        {error && (
          <div className="rounded-2xl border border-rose-500/20 bg-rose-500/10 p-4 text-sm text-rose-300">
            {error}
          </div>
        )}

        {/* Files Grid / List */}
        <div className="rounded-3xl border border-white/5 bg-slate-900/50 backdrop-blur-2xl overflow-hidden shadow-2xl">
          {loading ? (
            <div className="p-12 text-center text-slate-400 animate-pulse">Loading directory...</div>
          ) : items.length === 0 ? (
            <div className="p-12 text-center text-slate-400">Directory is empty</div>
          ) : (
            <div className="overflow-x-auto">
              <table className="w-full text-left text-sm text-slate-300">
                <thead className="border-b border-white/5 bg-slate-950/40 text-xs font-semibold uppercase text-slate-400">
                  <tr>
                    <th className="px-6 py-4">Name</th>
                    <th className="px-6 py-4">Size</th>
                    <th className="px-6 py-4">Permissions</th>
                    <th className="px-6 py-4">Modified</th>
                    <th className="px-6 py-4 text-right">Action</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-white/5">
                  {items.map((item) => (
                    <tr
                      key={item.path}
                      className="hover:bg-white/[0.02] transition-colors group cursor-pointer"
                      onClick={() => handleOpenFile(item)}
                    >
                      <td className="px-6 py-3.5 flex items-center gap-3 font-medium text-white">
                        {item.is_directory ? (
                          <Folder className="h-5 w-5 text-amber-400 fill-amber-400/20" />
                        ) : (
                          <FileCode className="h-5 w-5 text-sky-400" />
                        )}
                        <span>{item.name}</span>
                      </td>
                      <td className="px-6 py-3.5 font-mono text-xs text-slate-400">
                        {item.is_directory ? '—' : formatBytes(item.size_bytes)}
                      </td>
                      <td className="px-6 py-3.5 font-mono text-xs text-slate-400">
                        {item.permissions}
                      </td>
                      <td className="px-6 py-3.5 text-xs text-slate-400">{item.modified}</td>
                      <td className="px-6 py-3.5 text-right">
                        {!item.is_directory && (
                          <button
                            onClick={(e) => {
                              e.stopPropagation();
                              handleOpenFile(item);
                            }}
                            className="inline-flex items-center gap-1.5 rounded-lg bg-sky-500/10 px-2.5 py-1 text-xs font-medium text-sky-400 hover:bg-sky-500/20 transition-colors"
                          >
                            <Eye className="h-3 w-3" />
                            Edit
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          )}
        </div>

        {/* File Editor Modal */}
        {activeFile && (
          <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/70 backdrop-blur-md p-4">
            <div className="w-full max-w-4xl rounded-3xl border border-white/10 bg-slate-950/95 p-6 shadow-2xl flex flex-col max-h-[85vh]">
              <div className="flex items-center justify-between border-b border-white/10 pb-4 mb-4">
                <div className="flex items-center gap-2 overflow-hidden">
                  <FileText className="h-5 w-5 text-sky-400 flex-shrink-0" />
                  <span className="font-mono text-sm text-white truncate">{activeFile}</span>
                </div>
                <div className="flex items-center gap-3">
                  {saveStatus && (
                    <span className="text-xs font-medium text-emerald-400">{saveStatus}</span>
                  )}
                  <button
                    onClick={handleSaveFile}
                    className="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-3.5 py-1.5 text-xs font-medium text-white shadow-md hover:opacity-90"
                  >
                    <Save className="h-3.5 w-3.5" />
                    Save
                  </button>
                  <button
                    onClick={() => setActiveFile(null)}
                    className="rounded-lg p-1.5 text-slate-400 hover:bg-white/10 hover:text-white"
                  >
                    <X className="h-4 w-4" />
                  </button>
                </div>
              </div>

              <div className="flex-1 overflow-hidden rounded-xl border border-white/5 bg-slate-900/60 p-2">
                {fileLoading ? (
                  <div className="p-8 text-center text-slate-400 animate-pulse">Loading content...</div>
                ) : (
                  <textarea
                    value={fileContent}
                    onChange={(e) => setFileContent(e.target.value)}
                    className="w-full h-full min-h-[380px] bg-transparent font-mono text-xs text-slate-200 outline-none resize-none p-2"
                    spellCheck="false"
                  />
                )}
              </div>
            </div>
          </div>
        )}
      </div>
    </Shell>
  );
}
