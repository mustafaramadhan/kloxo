'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { usePathname, useRouter } from 'next/navigation';
import {
  LayoutDashboard,
  Globe,
  Database,
  Cpu,
  Server,
  Network,
  LogOut,
  ExternalLink,
  ShieldCheck,
  Menu,
  X,
  RefreshCw,
  FolderTree,
  Mail,
  Share2,
  Users,
  Clock,
  Terminal,
} from 'lucide-react';
import { getStoredToken, setStoredToken } from '@/lib/api';

interface ShellProps {
  children: React.ReactNode;
}

export default function Shell({ children }: ShellProps) {
  const pathname = usePathname();
  const router = useRouter();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [user, setUser] = useState<{ username: string; role: string } | null>(null);

  useEffect(() => {
    const token = getStoredToken();
    if (!token && pathname !== '/login') {
      router.push('/login');
      return;
    }
    // Simple user extraction
    try {
      if (token) {
        const payload = JSON.parse(atob(token.split('.')[1]));
        setUser(payload);
      }
    } catch {
      // ignore
    }
  }, [pathname, router]);

  const handleLogout = () => {
    setStoredToken(null);
    router.push('/login');
  };

  const navGroups = [
    {
      title: 'Hosting & Core',
      items: [
        { name: 'Overview', href: '/', icon: LayoutDashboard },
        { name: 'Services', href: '/services', icon: Server },
        { name: 'Domains', href: '/domains', icon: Globe },
        { name: 'Databases', href: '/databases', icon: Database },
      ],
    },
    {
      title: 'Files & Communication',
      items: [
        { name: 'File Manager', href: '/files', icon: FolderTree },
        { name: 'Mail Accounts', href: '/mail', icon: Mail },
        { name: 'FTP Users', href: '/ftp', icon: Share2 },
      ],
    },
    {
      title: 'System & Management',
      items: [
        { name: 'Clients', href: '/clients', icon: Users },
        { name: 'Cron Tasks', href: '/cron', icon: Clock },
        { name: 'Server Logs & PHP', href: '/logs', icon: Terminal },
      ],
    },
  ];

  return (
    <div className="flex h-screen overflow-hidden bg-canvas">
      {/* Mobile Sidebar Backdrop */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm md:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Sidebar */}
      <aside
        className={`fixed inset-y-0 left-0 z-50 w-64 transform border-r border-white/5 bg-slate-950/80 backdrop-blur-2xl transition-transform duration-300 md:static md:translate-x-0 ${
          sidebarOpen ? 'translate-x-0' : '-translate-x-full'
        }`}
      >
        <div className="flex h-16 items-center justify-between px-6 border-b border-white/5">
          <Link href="/" className="flex items-center gap-3">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-sky-500 to-indigo-600 shadow-lg shadow-sky-500/20">
              <Cpu className="h-5 w-5 text-white" />
            </div>
            <div>
              <span className="text-lg font-bold tracking-tight text-white">Mfklixo</span>
              <span className="block text-[10px] font-semibold text-sky-400">NEXT-GEN PANEL</span>
            </div>
          </Link>
          <button
            onClick={() => setSidebarOpen(false)}
            className="md:hidden text-slate-400 hover:text-white"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        <nav className="p-4 space-y-4 overflow-y-auto max-h-[calc(100vh-140px)]">
          {navGroups.map((group) => (
            <div key={group.title} className="space-y-1">
              <div className="px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                {group.title}
              </div>
              {group.items.map((item) => {
                const Icon = item.icon;
                const active = pathname === item.href;
                return (
                  <Link
                    key={item.href}
                    href={item.href}
                    onClick={() => setSidebarOpen(false)}
                    className={`flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all ${
                      active
                        ? 'bg-gradient-to-r from-sky-500/20 to-indigo-500/20 text-sky-400 border border-sky-500/30 shadow-sm'
                        : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'
                    }`}
                  >
                    <Icon className={`h-4 w-4 ${active ? 'text-sky-400' : 'text-slate-400'}`} />
                    {item.name}
                  </Link>
                );
              })}
            </div>
          ))}

          <div className="pt-2">
            <div className="px-3 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
              Legacy Support
            </div>
            <a
              href="/legacy/"
              target="_blank"
              rel="noopener noreferrer"
              className="flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-400 hover:bg-white/5 hover:text-slate-200 transition-all border border-transparent hover:border-white/5"
            >
              <span className="flex items-center gap-3">
                <ExternalLink className="h-4 w-4 text-amber-400" />
                Legacy Panel
              </span>
              <span className="rounded bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-medium text-amber-300">
                Classic
              </span>
            </a>
          </div>
        </nav>

        {/* User Card in bottom of sidebar */}
        <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-white/5 bg-slate-950/40">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-500/10 border border-sky-500/20 text-sky-400 font-bold">
                {user?.username?.[0]?.toUpperCase() || 'A'}
              </div>
              <div className="overflow-hidden">
                <p className="truncate text-sm font-medium text-white">{user?.username || 'admin'}</p>
                <p className="truncate text-xs text-slate-400">{user?.role || 'superadmin'}</p>
              </div>
            </div>
            <button
              onClick={handleLogout}
              title="Logout"
              className="rounded-lg p-2 text-slate-400 hover:bg-rose-500/10 hover:text-rose-400 transition-colors"
            >
              <LogOut className="h-4 w-4" />
            </button>
          </div>
        </div>
      </aside>

      {/* Main Content Area */}
      <div className="flex flex-1 flex-col overflow-hidden">
        {/* Top Navbar */}
        <header className="flex h-16 items-center justify-between border-b border-white/5 bg-slate-950/60 px-6 backdrop-blur-xl">
          <div className="flex items-center gap-4">
            <button
              onClick={() => setSidebarOpen(true)}
              className="rounded-lg p-2 text-slate-400 hover:bg-white/5 hover:text-white md:hidden"
            >
              <Menu className="h-5 w-5" />
            </button>
            <div className="flex items-center gap-2">
              <ShieldCheck className="h-5 w-5 text-emerald-400" />
              <span className="text-sm font-semibold text-slate-300">Server Cluster Online</span>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <div className="hidden sm:flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">
              <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
              REST API v1.0
            </div>
            <a
              href="/legacy/"
              className="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 py-1.5 text-xs font-medium text-amber-300 hover:bg-amber-500/20 transition-colors"
            >
              <ExternalLink className="h-3.5 w-3.5" />
              Classic View
            </a>
          </div>
        </header>

        {/* Main Body */}
        <main className="flex-1 overflow-y-auto p-6 md:p-8">
          <div className="mx-auto max-w-7xl">{children}</div>
        </main>
      </div>
    </div>
  );
}
