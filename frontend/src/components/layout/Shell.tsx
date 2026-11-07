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
  Lock,
  Shield,
  Archive,
  Sliders,
  SlidersHorizontal,
  Layers,
  Wrench,
  UserCheck,
  ArrowLeft,
} from 'lucide-react';
import { getStoredToken, setStoredToken } from '@/lib/api';

interface ShellProps {
  children: React.ReactNode;
}

export default function Shell({ children }: ShellProps) {
  const pathname = usePathname();
  const router = useRouter();
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [user, setUser] = useState<{ username?: string; sub?: string; role?: string; type?: string } | null>(null);
  const [impersonatingAdmin, setImpersonatingAdmin] = useState<string | null>(null);

  useEffect(() => {
    const token = getStoredToken();
    if (!token && pathname !== '/login') {
      router.push('/login');
      return;
    }
    // Extract user claims
    try {
      if (token) {
        const payload = JSON.parse(atob(token.split('.')[1]));
        setUser(payload);
      }
    } catch {
      // ignore
    }

    if (typeof window !== 'undefined') {
      const adminTok = sessionStorage.getItem('admin_impersonator_token');
      const adminUsr = sessionStorage.getItem('admin_impersonator_user') || 'admin';
      if (adminTok) {
        setImpersonatingAdmin(adminUsr);
      } else {
        setImpersonatingAdmin(null);
      }
    }
  }, [pathname, router]);

  const handleReturnToAdmin = () => {
    if (typeof window !== 'undefined') {
      const adminTok = sessionStorage.getItem('admin_impersonator_token');
      if (adminTok) {
        setStoredToken(adminTok);
        sessionStorage.removeItem('admin_impersonator_token');
        sessionStorage.removeItem('admin_impersonator_user');
        window.location.href = '/clients';
      }
    }
  };

  const handleLogout = () => {
    if (typeof window !== 'undefined') {
      sessionStorage.removeItem('admin_impersonator_token');
      sessionStorage.removeItem('admin_impersonator_user');
    }
    setStoredToken(null);
    router.push('/login');
  };

  const handleLegacyClick = (e: React.MouseEvent) => {
    const token = getStoredToken();
    if (token) {
      e.preventDefault();
      window.open(`/legacy/?token=${encodeURIComponent(token)}`, '_blank');
    }
  };

  const isSuperadmin =
    user?.role === 'superadmin' ||
    user?.role === 'admin' ||
    user?.sub === 'admin' ||
    user?.username === 'admin';
  const isReseller = user?.role === 'reseller' || user?.type === 'reseller';

  const navGroups = [
    {
      title: 'Hosting & Core',
      items: [
        { name: 'Overview', href: '/', icon: LayoutDashboard },
        ...(isSuperadmin ? [{ name: 'Services', href: '/services', icon: Server }] : []),
        { name: 'Domains', href: '/domains', icon: Globe },
        { name: 'Subdomains', href: '/subdomains', icon: Network },
        { name: 'DNS Zones', href: '/dns', icon: Layers },
        { name: 'Databases', href: '/databases', icon: Database },
      ],
    },
    {
      title: 'Files & Mail',
      items: [
        { name: 'File Manager', href: '/files', icon: FolderTree },
        { name: 'Mail Accounts', href: '/mail', icon: Mail },
        { name: 'FTP Users', href: '/ftp', icon: Share2 },
        { name: 'Backups', href: '/backups', icon: Archive },
      ],
    },
    {
      title: 'Web Apps & Portals',
      items: [
        { name: 'phpMyAdmin', href: '/phpmyadmin/', icon: Database, external: true, badge: 'v5.2' },
        { name: 'Roundcube Webmail', href: '/webmail/', icon: Mail, external: true, badge: 'v1.6' },
      ],
    },
    {
      title: 'Security & SSL',
      items: [
        { name: 'SSL Certificates', href: '/ssl', icon: Lock },
        ...(isSuperadmin ? [{ name: 'Firewall & Security', href: '/security', icon: Shield }] : []),
      ],
    },
    {
      title: 'Tools & Automation',
      items: [
        { name: 'Cron Tasks', href: '/cron', icon: Clock },
      ],
    },
    ...(isSuperadmin || isReseller
      ? [
          {
            title: isSuperadmin ? 'System & Server' : 'Reseller Tools',
            items: [
              ...(isSuperadmin ? [{ name: 'CustomBuild', href: '/custombuild', icon: Wrench, badge: 'DirectAdmin' }] : []),
              { name: 'Clients', href: '/clients', icon: Users },
              ...(isSuperadmin
                ? [
                    { name: 'PHP Directives', href: '/php-config', icon: Sliders },
                    { name: 'Advanced PHP', href: '/php-advanced', icon: SlidersHorizontal },
                    { name: 'Server Logs', href: '/logs', icon: Terminal },
                  ]
                : []),
            ],
          },
        ]
      : []),
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
              {group.items.map((item: any) => {
                const Icon = item.icon;
                const active = pathname === item.href;
                const isExternal = item.external || item.href.startsWith('/phpmyadmin') || item.href.startsWith('/webmail') || item.href.startsWith('http');

                if (isExternal) {
                  return (
                    <a
                      key={item.href}
                      href={item.href}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-medium text-slate-400 hover:bg-white/5 hover:text-slate-200 transition-all border border-transparent hover:border-white/5"
                    >
                      <span className="flex items-center gap-3">
                        <Icon className="h-4 w-4 text-slate-400" />
                        {item.name}
                      </span>
                      {item.badge ? (
                        <span className="rounded bg-sky-500/10 px-1.5 py-0.5 text-[10px] font-semibold text-sky-400 border border-sky-500/20">
                          {item.badge}
                        </span>
                      ) : (
                        <ExternalLink className="h-3.5 w-3.5 text-slate-500" />
                      )}
                    </a>
                  );
                }

                return (
                  <Link
                    key={item.href}
                    href={item.href}
                    onClick={() => setSidebarOpen(false)}
                    className={`flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all ${
                      active
                        ? 'bg-gradient-to-r from-sky-500/20 to-indigo-500/20 text-sky-400 border border-sky-500/30 shadow-sm'
                        : 'text-slate-400 hover:bg-white/5 hover:text-slate-200'
                    }`}
                  >
                    <span className="flex items-center gap-3">
                      <Icon className={`h-4 w-4 ${active ? 'text-sky-400' : 'text-slate-400'}`} />
                      {item.name}
                    </span>
                    {item.badge && (
                      <span className="rounded bg-slate-800 px-1.5 py-0.5 text-[10px] font-medium text-slate-300">
                        {item.badge}
                      </span>
                    )}
                  </Link>
                );
              })}
            </div>
          ))}
        </nav>

        {/* User Card in bottom of sidebar */}
        <div className="absolute bottom-0 left-0 right-0 p-4 border-t border-white/5 bg-slate-950/40">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div
                className={`flex h-9 w-9 items-center justify-center rounded-xl font-bold border ${
                  isSuperadmin
                    ? 'bg-purple-500/10 border-purple-500/20 text-purple-400'
                    : 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400'
                }`}
              >
                {(user?.sub || user?.username || 'A')[0]?.toUpperCase()}
              </div>
              <div className="overflow-hidden">
                <p className="truncate text-xs font-bold text-white">{user?.sub || user?.username || 'admin'}</p>
                <div className="flex items-center gap-1.5 mt-0.5">
                  <span className={`h-1.5 w-1.5 rounded-full ${isSuperadmin ? 'bg-purple-400' : 'bg-emerald-400'}`} />
                  <p className="truncate text-[10px] font-semibold uppercase tracking-wider text-slate-400">
                    {isSuperadmin ? 'Superadmin' : isReseller ? 'Reseller' : 'Client Account'}
                  </p>
                </div>
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
        {/* Impersonation Warning Banner */}
        {impersonatingAdmin && (
          <div className="bg-gradient-to-r from-amber-500/25 via-orange-500/20 to-amber-500/25 border-b border-amber-500/30 px-6 py-2.5 backdrop-blur-xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-amber-200 z-30 shadow-lg">
            <div className="flex items-center gap-2.5 font-medium">
              <span className="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-500/20 text-amber-300 font-bold border border-amber-500/30">
                <UserCheck className="h-3.5 w-3.5" />
              </span>
              <span>
                You are currently viewing this panel as <strong className="text-white font-mono bg-amber-500/20 px-1.5 py-0.5 rounded border border-amber-500/30">{user?.sub || user?.username}</strong> (Client View, Impersonated by {impersonatingAdmin})
              </span>
            </div>
            <button
              onClick={handleReturnToAdmin}
              className="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500 text-slate-950 font-bold hover:bg-amber-400 transition-all shadow-md shadow-amber-500/20 hover:scale-[1.02] active:scale-[0.98]"
            >
              <ArrowLeft className="h-3.5 w-3.5" />
              <span>Return to Administrator</span>
            </button>
          </div>
        )}

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
            <div className="hidden lg:flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs text-slate-300">
              <span className="h-2 w-2 rounded-full bg-emerald-400 animate-pulse" />
              REST API v1.0
            </div>
            <a
              href="/phpmyadmin/"
              target="_blank"
              rel="noopener noreferrer"
              className="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-amber-500/20 bg-amber-500/10 px-3 py-1.5 text-xs font-semibold text-amber-300 hover:bg-amber-500/20 transition-colors shadow-sm"
            >
              <Database className="h-3.5 w-3.5" />
              phpMyAdmin
            </a>
            <a
              href="/webmail/"
              target="_blank"
              rel="noopener noreferrer"
              className="hidden sm:inline-flex items-center gap-1.5 rounded-lg border border-indigo-500/20 bg-indigo-500/10 px-3 py-1.5 text-xs font-semibold text-indigo-300 hover:bg-indigo-500/20 transition-colors shadow-sm"
            >
              <Mail className="h-3.5 w-3.5" />
              Webmail
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
