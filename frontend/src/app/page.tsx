'use client';

import React, { useState, useEffect, useMemo } from 'react';
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
  Search,
  Sliders,
  Shield,
  Lock,
  Terminal,
  Layers,
  FolderTree,
  Mail,
  Share2,
  Users,
  Archive,
  Key,
  AlertTriangle,
  CheckCircle2,
  X,
  ChevronDown,
  ChevronUp,
  ExternalLink,
  FileText,
  Settings,
  Power,
  RotateCcw,
  Wrench,
  HelpCircle,
  Radio,
  Eye,
  Zap,
  SlidersHorizontal,
  Filter,
  Maximize2,
  Minimize2,
  Bell,
  Inbox,
  LifeBuoy,
  ShieldAlert,
  ShieldCheck,
  Fingerprint,
  Send,
  Trash2,
  Play,
  Monitor,
  Info,
  Network,
  DownloadCloud,
  FileCheck,
  ToggleLeft,
  ToggleRight,
  Sparkles,
} from 'lucide-react';
import {
  apiFetch,
  SystemStatus,
  ServiceItem,
  ServerInfo,
  QuotaItem,
  QuotasResponse,
  ComponentItem,
  ProcessItem,
  ModuleItem,
  ActionLogItem,
  formatBytes,
  formatUptime,
  getStoredToken,
} from '@/lib/api';

interface DashboardItem {
  id: string;
  title: string;
  description: string;
  icon: any;
  category: string;
  href?: string;
  badge?: string;
  badgeColor?: string;
  modal?: string;
  legacyAction?: string;
}

export default function DashboardPage() {
  const [status, setStatus] = useState<SystemStatus | null>(null);
  const [services, setServices] = useState<ServiceItem[]>([]);
  const [serverInfo, setServerInfo] = useState<ServerInfo | null>(null);
  const [quotas, setQuotas] = useState<QuotaItem[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);

  // Search & Filter State
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [collapsedCategories, setCollapsedCategories] = useState<Record<string, boolean>>({});

  // Active Interactive Modal State
  const [activeModal, setActiveModal] = useState<string | null>(null);
  const [modalTitle, setModalTitle] = useState('');
  const [modalLoading, setModalLoading] = useState(false);
  const [modalMessage, setModalMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  // Modal Specific Data
  const [componentsList, setComponentsList] = useState<ComponentItem[]>([]);
  const [processesList, setProcessesList] = useState<ProcessItem[]>([]);
  const [actionLogs, setActionLogs] = useState<ActionLogItem[]>([]);
  const [phpModules, setPhpModules] = useState<ModuleItem[]>([]);
  const [commandTool, setCommandTool] = useState('ping');
  const [commandTarget, setCommandTarget] = useState('127.0.0.1');
  const [commandOutput, setCommandOutput] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  const loadData = async () => {
    try {
      const [statusRes, servicesRes, quotasRes] = await Promise.all([
        apiFetch<{ success: boolean; system: SystemStatus }>('/system/status').catch(() => null),
        apiFetch<{ success: boolean; services: ServiceItem[] }>('/system/services').catch(() => null),
        apiFetch<QuotasResponse>('/system/quotas').catch(() => null),
      ]);

      if (statusRes && statusRes.system) setStatus(statusRes.system);
      if (servicesRes && servicesRes.services) setServices(servicesRes.services);
      if (quotasRes && quotasRes.quotas) {
        setQuotas(quotasRes.quotas);
        setServerInfo(quotasRes.server_info);
      }
    } catch (err) {
      console.error('Error fetching dashboard metrics', err);
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  };

  useEffect(() => {
    loadData();
    const interval = setInterval(loadData, 15000);
    return () => clearInterval(interval);
  }, []);

  const handleManualRefresh = () => {
    setRefreshing(true);
    loadData();
  };

  const toggleCategoryCollapse = (category: string) => {
    setCollapsedCategories((prev) => ({
      ...prev,
      [category]: !prev[category],
    }));
  };

  const expandAllCategories = () => setCollapsedCategories({});
  const collapseAllCategories = () => {
    const allCollapsed: Record<string, boolean> = {};
    categories.forEach((cat) => {
      allCollapsed[cat.id] = true;
    });
    setCollapsedCategories(allCollapsed);
  };

  // SSO Bridge Link Opener
  const openLegacySSO = (subactionOrAction: string) => {
    const token = getStoredToken();
    let url = `/legacy/`;
    if (subactionOrAction) {
      if (subactionOrAction.startsWith('http')) {
        url = subactionOrAction;
      } else if (subactionOrAction.startsWith('frm_')) {
        url = `/legacy/display.php?${subactionOrAction}`;
      } else {
        url = `/legacy/display.php?frm_action=${encodeURIComponent(subactionOrAction)}`;
      }
    }
    if (token) {
      url += (url.includes('?') ? '&' : '?') + `token=${encodeURIComponent(token)}`;
    }
    window.open(url, '_blank');
  };

  // Open Modal Handler
  const handleOpenModal = async (modalId: string, title: string) => {
    setActiveModal(modalId);
    setModalTitle(title);
    setModalMessage(null);

    if (modalId === 'components') {
      setModalLoading(true);
      try {
        const res = await apiFetch<{ success: boolean; components: ComponentItem[] }>('/system/components');
        setComponentsList(res.components);
      } catch (e: any) {
        setModalMessage({ type: 'error', text: e.message || 'Failed to load system components' });
      } finally {
        setModalLoading(false);
      }
    } else if (modalId === 'processes') {
      setModalLoading(true);
      try {
        const res = await apiFetch<{ success: boolean; processes: ProcessItem[] }>('/system/processes');
        setProcessesList(res.processes);
      } catch (e: any) {
        setModalMessage({ type: 'error', text: e.message || 'Failed to load running processes' });
      } finally {
        setModalLoading(false);
      }
    } else if (modalId === 'actionlog') {
      setModalLoading(true);
      try {
        const res = await apiFetch<{ success: boolean; logs: ActionLogItem[] }>('/system/action-logs');
        setActionLogs(res.logs);
      } catch (e: any) {
        setModalMessage({ type: 'error', text: e.message || 'Failed to load action logs' });
      } finally {
        setModalLoading(false);
      }
    } else if (modalId === 'modules') {
      setModalLoading(true);
      try {
        const res = await apiFetch<{ success: boolean; modules: ModuleItem[] }>('/system/modules');
        setPhpModules(res.modules);
      } catch (e: any) {
        setModalMessage({ type: 'error', text: e.message || 'Failed to load PHP modules' });
      } finally {
        setModalLoading(false);
      }
    }
  };

  // Execute Command in Command Center
  const handleRunCommand = async () => {
    setModalLoading(true);
    setCommandOutput('Executing command...');
    try {
      const res = await apiFetch<{ success: boolean; output: string }>('/system/command-center', {
        method: 'POST',
        body: JSON.stringify({ tool: commandTool, target: commandTarget }),
      });
      setCommandOutput(res.output);
    } catch (e: any) {
      setCommandOutput(`Error: ${e.message}`);
    } finally {
      setModalLoading(false);
    }
  };

  // Kill Process
  const handleKillProcess = async (pid: number) => {
    if (!confirm(`Are you sure you want to terminate process PID ${pid}?`)) return;
    try {
      await apiFetch('/system/processes/kill', {
        method: 'POST',
        body: JSON.stringify({ pid }),
      });
      setProcessesList((prev) => prev.filter((p) => p.pid !== pid));
      setModalMessage({ type: 'success', text: `Process PID ${pid} successfully terminated.` });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to kill process' });
    }
  };

  // Password Update
  const handleUpdatePassword = async (e: React.FormEvent) => {
    e.preventDefault();
    if (newPassword !== confirmPassword) {
      setModalMessage({ type: 'error', text: 'Passwords do not match.' });
      return;
    }
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/server/action', {
        method: 'POST',
        body: JSON.stringify({ action: 'change_password', password: newPassword }),
      });
      setModalMessage({ type: 'success', text: res.message });
      setNewPassword('');
      setConfirmPassword('');
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to update password' });
    } finally {
      setModalLoading(false);
    }
  };

  // Maintenance Toggle
  const handleToggleMaintenance = async () => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; maintenance: boolean; message: string }>('/server/action', {
        method: 'POST',
        body: JSON.stringify({ action: 'maintenance_toggle' }),
      });
      setServerInfo((prev) => (prev ? { ...prev, maintenance: res.maintenance } : null));
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to toggle maintenance mode' });
    } finally {
      setModalLoading(false);
    }
  };

  // Server Reboot
  const handleRebootServer = async () => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/server/action', {
        method: 'POST',
        body: JSON.stringify({ action: 'reboot' }),
      });
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to schedule reboot' });
    } finally {
      setModalLoading(false);
    }
  };

  // Categories Definition
  const categories = [
    {
      id: 'admin',
      title: 'Administration',
      description: 'Account management, client quotas, logs & system updates',
      icon: Users,
      color: 'from-blue-500/20 to-sky-500/20 text-sky-400 border-sky-500/30',
    },
    {
      id: 'resource',
      title: 'Resource & Hosting',
      description: 'PHP engines, DNS templates, SSL certificates, backups & IP routing',
      icon: Layers,
      color: 'from-indigo-500/20 to-purple-500/20 text-indigo-400 border-indigo-500/30',
    },
    {
      id: 'domain',
      title: 'Domain & Storage',
      description: 'File manager, MariaDB databases, FTP users & network diagnostics',
      icon: Globe,
      color: 'from-emerald-500/20 to-teal-500/20 text-emerald-400 border-emerald-500/30',
    },
    {
      id: 'server',
      title: 'Server Operations (pserver)',
      description: 'Daemons, system processes, command center, timezones & reboot',
      icon: Server,
      color: 'from-amber-500/20 to-orange-500/20 text-amber-400 border-amber-500/30',
    },
    {
      id: 'security',
      title: 'Security & Protection',
      description: 'SSH hardening, Watchdog daemon, LxGuard brute-force shield & firewall',
      icon: Shield,
      color: 'from-rose-500/20 to-red-500/20 text-rose-400 border-rose-500/30',
    },
    {
      id: 'basic',
      title: 'Basic Web, Mail & DB',
      description: 'Mail queue, FTP sessions, phpMyAdmin launcher & webserver configuration',
      icon: Activity,
      color: 'from-cyan-500/20 to-blue-500/20 text-cyan-400 border-cyan-500/30',
    },
    {
      id: 'advanced',
      title: 'Advanced & System Settings',
      description: 'Maintenance mode, scavenger, secondary DNS, branding & custom policies',
      icon: SlidersHorizontal,
      color: 'from-violet-500/20 to-fuchsia-500/20 text-violet-400 border-violet-500/30',
    },
  ];

  // All 72 Items Matching User's HTML Dump
  const allItems: DashboardItem[] = [
    // --- 1. Administration ---
    { id: 'all_client', title: 'All Clients', description: 'Customer accounts, reseller roles and limits', icon: Users, category: 'admin', href: '/clients', badge: 'Active' },
    { id: 'all_domain', title: 'All Domains', description: 'Primary virtual hosts and document roots', icon: Globe, category: 'admin', href: '/domains' },
    { id: 'all_addondomain', title: 'All Pointer Domains', description: 'Domain redirects and aliases', icon: Network, category: 'admin', href: '/domains' },
    { id: 'all_mailaccount', title: 'All Mail Accounts', description: 'Mailboxes, quotas and POP/IMAP accounts', icon: Mail, category: 'admin', href: '/mail' },
    { id: 'all_mailforward', title: 'All Mail Forwards', description: 'Incoming email forwarding rules', icon: Send, category: 'admin', href: '/mail' },
    { id: 'all_mailinglist', title: 'All Mailing Lists', description: 'Ezmlm / Mailman discussion lists', icon: Inbox, category: 'admin', legacyAction: 'frm_action=list&frm_o_cname=all_mailinglist' },
    { id: 'all_mysqldb', title: 'All Databases', description: 'MariaDB schemas and user privileges', icon: Database, category: 'admin', href: '/databases' },
    { id: 'all_cron', title: 'All Cron Tasks', description: 'Scheduled crontab background jobs', icon: Clock, category: 'admin', href: '/cron' },
    { id: 'all_ftpuser', title: 'All FTP Users', description: 'FTP accounts and home directories', icon: Share2, category: 'admin', href: '/ftp' },
    { id: 'all_sslcert', title: 'All SSLCerts', description: 'SSL/TLS certificates and Let\'s Encrypt keys', icon: Lock, category: 'admin', href: '/ssl' },
    { id: 'resourceplan', title: 'Resource Plans', description: 'Hosting templates and quota presets', icon: Sliders, category: 'admin', modal: 'resourceplan' },
    { id: 'ticket', title: 'HelpDesk & Tickets', description: 'Customer support tickets and resolution queue', icon: LifeBuoy, category: 'admin', modal: 'ticket' },
    { id: 'ticketconfig', title: 'HelpDesk Configuration', description: 'Support department email and auto-replies', icon: Settings, category: 'admin', legacyAction: 'frm_action=updateform&frm_subaction=ticketconfig&frm_o_o[0][class]=ticketconfig' },
    { id: 'smessage', title: 'Internal Messages', description: 'System announcements and client alerts', icon: Bell, category: 'admin', modal: 'smessage' },
    { id: 'custombutton', title: 'Custom Buttons', description: 'Add third-party web panel links and shortcuts', icon: Zap, category: 'admin', modal: 'custombutton' },
    { id: 'lxupdate', title: 'System Update', description: 'Check for Mfklixo and OS software updates', icon: DownloadCloud, category: 'admin', modal: 'update' },
    { id: 'releasenote', title: 'Release Notes', description: 'Changelog of updates and bug fixes', icon: FileText, category: 'admin', modal: 'releasenotes' },
    { id: 'information', title: 'Server Information', description: 'Full server metadata, IP and architecture', icon: Info, category: 'admin', modal: 'info' },
    { id: 'actionlog', title: 'Action Logs', description: 'Audit trail of administrative actions', icon: FileCheck, category: 'admin', modal: 'actionlog' },
    { id: 'password', title: 'Change Password', description: 'Update administrator account password', icon: Key, category: 'admin', modal: 'password' },
    { id: 'skin', title: 'Appearance & Skin', description: 'Control panel theme and color scheme', icon: Sparkles, category: 'admin', modal: 'appearance' },

    // --- 2. Resource ---
    { id: 'phpini', title: 'PHP Configure', description: 'Memory limits, execution time & file upload sizes', icon: Sliders, category: 'resource', href: '/php-config' },
    { id: 'phpfilter', title: 'Advanced PHP Configure', description: 'Per-user opcache and php-fpm pool parameters', icon: SlidersHorizontal, category: 'resource', modal: 'phpfilter' },
    { id: 'processedlog', title: 'Client Processed Logs', description: 'Web traffic statistics (Webalizer & Awstats)', icon: Activity, category: 'resource', href: '/logs' },
    { id: 'auxiliarylogin', title: 'Auxiliary Logins', description: 'Secondary team accounts with limited scopes', icon: Users, category: 'resource', modal: 'auxiliary' },
    { id: 'utmp', title: 'Login History', description: 'SSH and Web control panel login log (utmp)', icon: Eye, category: 'resource', modal: 'loginhistory' },
    { id: 'sshauthorizedkey', title: 'Shell Access Keys', description: 'Public RSA/ED25519 keys for SSH login', icon: Terminal, category: 'resource', modal: 'sshkeys' },
    { id: 'dnstemplate', title: 'DNS Templates', description: 'Pre-configured zone records for new domains', icon: Layers, category: 'resource', href: '/dns' },
    { id: 'backup', title: 'Backup & Restore', description: 'Full and partial site archives and restoration', icon: Archive, category: 'resource', href: '/backups' },
    { id: 'ipaddress', title: 'IP Addresses', description: 'Public IPv4 and IPv6 network interfaces', icon: Network, category: 'resource', modal: 'ipaddresses' },
    { id: 'sslcert', title: 'SSL Certificates', description: 'Manage and install SSL certificates for domains', icon: Lock, category: 'resource', href: '/ssl' },

    // --- 3. Domain ---
    { id: 'dom_ftpuser', title: 'FTP Users', description: 'Create and restrict FTP credentials', icon: Share2, category: 'domain', href: '/ftp' },
    { id: 'dom_mysqldb', title: 'MySQL Databases', description: 'Create MariaDB tables and grant privileges', icon: Database, category: 'domain', href: '/databases' },
    { id: 'filemanager', title: 'File Manager', description: 'cPanel-style multi-function web file explorer', icon: FolderTree, category: 'domain', href: '/files', badge: 'Enhanced' },
    { id: 'defaultdomain', title: 'Default Domain', description: 'Fallback VirtualHost for server IP hits', icon: Globe, category: 'domain', modal: 'defaultdomain' },
    { id: 'traceroute', title: 'Traceroute & Ping', description: 'Interactive network route diagnosis tool', icon: Radio, category: 'domain', modal: 'commandcenter' },

    // --- 4. Server (pserver) ---
    { id: 'service', title: 'Services Daemon', description: 'Start, stop, restart and monitor background services', icon: Server, category: 'server', href: '/services' },
    { id: 'process', title: 'Process Viewer', description: 'Live top CPU and Memory processes with kill switch', icon: Activity, category: 'server', modal: 'processes' },
    { id: 'componentinfo', title: 'Component Info', description: 'Versions of Nginx, PHP, MariaDB, Bind and OS', icon: Info, category: 'server', modal: 'components' },
    { id: 'commandcenter', title: 'Command Center', description: 'Diagnostic terminal: ping, dig, df, free, uptime', icon: Terminal, category: 'server', modal: 'commandcenter' },
    { id: 'switchprogram', title: 'Switch Program', description: 'Toggle web server (Nginx/Apache) & PHP engines', icon: RefreshCw, category: 'server', modal: 'switchprogram' },
    { id: 'timezone', title: 'Timezone Settings', description: 'Configure server local clock and UTC offset', icon: Clock, category: 'server', modal: 'timezone' },
    { id: 'webterminal', title: 'SSH Terminal', description: 'Web-based interactive shell console', icon: Terminal, category: 'server', modal: 'terminal' },
    { id: 'logmanager', title: 'Log Manager', description: 'Real-time viewer for Nginx, PHP, MariaDB logs', icon: FileText, category: 'server', href: '/logs' },
    { id: 'driver', title: 'Driver Configuration', description: 'Configure low-level server software drivers', icon: Wrench, category: 'server', modal: 'driver' },
    { id: 'reboot', title: 'Reboot Server', description: 'Gracefully restart the physical/virtual host', icon: RotateCcw, category: 'server', modal: 'reboot', badgeColor: 'text-rose-400' },
    { id: 'poweroff', title: 'Poweroff Server', description: 'Shut down and power off server safely', icon: Power, category: 'server', modal: 'poweroff', badgeColor: 'text-red-500' },

    // --- 5. Security ---
    { id: 'sshconfig', title: 'SSH Configure', description: 'Configure SSH listening port and root login policy', icon: Lock, category: 'security', modal: 'sshconfig' },
    { id: 'watchdog', title: 'Watchdog Monitor', description: 'Automated daemon recovery when services crash', icon: ShieldCheck, category: 'security', modal: 'watchdog' },
    { id: 'lxguard', title: 'LxGuard Shield', description: 'Brute-force protection against failed logins', icon: ShieldAlert, category: 'security', modal: 'lxguard', badge: 'Active' },
    { id: 'blockedip', title: 'Blocked IP Addresses', description: 'Firewall blacklisted IP addresses and subnets', icon: Shield, category: 'security', href: '/security' },
    { id: 'sshkeys', title: 'SSH Authorized Keys', description: 'Manage public SSH keys authorized for root/admin', icon: Key, category: 'security', modal: 'sshkeys' },

    // --- 6. Basic (Web, Mail, DB) ---
    { id: 'mailserver', title: 'Mail Server Settings', description: 'Qmail / Postfix SMTP, TLS and spam thresholds', icon: Mail, category: 'basic', modal: 'mailserver' },
    { id: 'mailqueue', title: 'Mail Queue', description: 'Inspect outgoing/incoming mail queue and flush', icon: Inbox, category: 'basic', modal: 'mailqueue' },
    { id: 'mailperclient', title: 'Mails Per Client', description: 'Outgoing hourly rate limits per client domain', icon: Sliders, category: 'basic', modal: 'mailperclient' },
    { id: 'ftpsession', title: 'FTP Sessions', description: 'View active FTP transfers and connected clients', icon: Share2, category: 'basic', modal: 'ftpsession' },
    { id: 'webserver', title: 'Webserver Configure', description: 'Nginx worker processes, keepalive and gzip', icon: Globe, category: 'basic', modal: 'webserver' },
    { id: 'ftpconfig', title: 'FTP Configure', description: 'Pure-FTPd port ranges and TLS encryption mode', icon: Settings, category: 'basic', modal: 'ftpconfig' },
    { id: 'phpmodule', title: 'PHP Module Status', description: 'Inspect all loaded PHP modules and libraries', icon: Sparkles, category: 'basic', modal: 'modules' },
    { id: 'sqlmanager', title: 'SQL Manager / phpMyAdmin', description: 'Launch phpMyAdmin GUI database interface', icon: Database, category: 'basic', modal: 'sqlmanager', badge: 'External' },
    { id: 'mysqlpassword', title: 'MySQL Password Reset', description: 'Reset MariaDB root/administrative password', icon: Key, category: 'basic', modal: 'mysqlpass' },
    { id: 'dbadmin', title: 'Database Admins', description: 'Configure global database administrative accounts', icon: Users, category: 'basic', modal: 'dbadmin' },

    // --- 7. Advanced ---
    { id: 'scavenge', title: 'Scavenge Time', description: 'Configure cleanup interval for expired sessions', icon: Clock, category: 'advanced', modal: 'scavenge' },
    { id: 'general', title: 'General Settings', description: 'Global server title, admin email and locale', icon: Settings, category: 'advanced', modal: 'general' },
    { id: 'maintenance', title: 'Under Maintenance', description: 'Temporarily put server or hosted sites offline', icon: AlertTriangle, category: 'advanced', modal: 'maintenance' },
    { id: 'selfbackup', title: 'Self Backup Configure', description: 'Automated local/remote schedule for panel config', icon: Archive, category: 'advanced', modal: 'selfbackup' },
    { id: 'downloadconfig', title: 'Download Configure', description: 'Bandwidth throttling and file download limits', icon: DownloadCloud, category: 'advanced', modal: 'downloadconfig' },
    { id: 'forcedelete', title: 'Force Delete Server', description: 'Force cleanup orphaned server node entries', icon: Trash2, category: 'advanced', modal: 'forcedelete' },
    { id: 'dirindex', title: 'Directory Indexes', description: 'Default filenames for directory browsing', icon: FolderTree, category: 'advanced', modal: 'dirindex' },
    { id: 'miscinfo', title: 'Miscellaneous Info', description: 'Additional server configuration parameters', icon: Info, category: 'advanced', modal: 'miscinfo' },
    { id: 'uploadlogo', title: 'Upload Logo', description: 'Custom branding and control panel logo upload', icon: Sparkles, category: 'advanced', modal: 'uploadlogo' },
    { id: 'childappearance', title: 'Child Appearance', description: 'Themes and skins assigned to sub-resellers', icon: Eye, category: 'advanced', modal: 'childappearance' },
    { id: 'cpconfig', title: 'Control Panel Configure', description: 'Web panel port (7777/7778) and SSL setup', icon: Sliders, category: 'advanced', modal: 'cpconfig' },
    { id: 'skeleton', title: 'Skeleton & Disable', description: 'Default directory skeleton for newly added domains', icon: FolderTree, category: 'advanced', modal: 'skeleton' },
    { id: 'blockedlogin', title: 'Blocked Logins', description: 'View IPs currently locked out by failed logins', icon: ShieldAlert, category: 'advanced', href: '/security' },
    { id: 'notification', title: 'Notification Alerts', description: 'Email alerts on disk limits and service downtime', icon: Bell, category: 'advanced', modal: 'notification' },
    { id: 'disablepolicy', title: 'Disable Policy', description: 'Actions taken when account exceeds quota limits', icon: ShieldCheck, category: 'advanced', modal: 'disablepolicy' },
    { id: 'loginoption', title: 'Login Options', description: 'Session timeout, captcha and 2FA authentication', icon: Fingerprint, category: 'advanced', modal: 'loginoption' },
    { id: 'slavedns', title: 'Secondary / Slave DNS', description: 'Configure automated zone sync to secondary DNS', icon: Network, category: 'advanced', modal: 'slavedns' },
    { id: 'sendmailban', title: 'Sendmail to Bans', description: 'Spam prevention and automatic IP ban triggers', icon: ShieldAlert, category: 'advanced', modal: 'sendmailban' },
  ];

  // Filtering Logic
  const filteredItems = useMemo(() => {
    let result = allItems;
    if (selectedCategory !== 'all') {
      result = result.filter((item) => item.category === selectedCategory);
    }
    if (searchQuery.trim()) {
      const q = searchQuery.toLowerCase();
      result = result.filter(
        (item) => item.title.toLowerCase().includes(q) || item.description.toLowerCase().includes(q) || item.id.toLowerCase().includes(q)
      );
    }
    return result;
  }, [allItems, selectedCategory, searchQuery]);

  return (
    <Shell>
      <div className="space-y-8 pb-12">
        {/* Top Header & Status Banner */}
        <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between border-b border-white/5 pb-6">
          <div>
            <div className="flex items-center gap-3">
              <h1 className="text-2xl font-black tracking-tight text-white md:text-3xl">
                Server Control Center
              </h1>
              {serverInfo?.maintenance && (
                <span className="rounded-full bg-amber-500/10 border border-amber-500/30 px-3 py-0.5 text-xs font-semibold text-amber-400 animate-pulse">
                  Maintenance Active
                </span>
              )}
            </div>
            <p className="mt-1 text-xs text-slate-400">
              Host: <span className="font-mono text-sky-400">{status?.hostname || 'localhost'}</span> • {status?.os} • System Load: {status?.load_average['1m']} {status?.load_average['5m']}
            </p>
          </div>

          <div className="flex flex-wrap items-center gap-2.5">
            <a
              href="/legacy/"
              target="_blank"
              rel="noopener noreferrer"
              onClick={(e) => {
                e.preventDefault();
                openLegacySSO('');
              }}
              className="flex items-center gap-2 rounded-xl border border-amber-500/30 bg-amber-500/10 px-3.5 py-2 text-xs font-semibold text-amber-300 hover:bg-amber-500/20 transition-all shadow-sm shadow-amber-500/10"
              title="Open Classic Kloxo Panel with Automatic SSO Login"
            >
              <ExternalLink className="h-3.5 w-3.5" />
              Classic Kloxo Engine
            </a>

            <button
              onClick={handleManualRefresh}
              disabled={refreshing}
              className="flex items-center gap-2 rounded-xl border border-white/10 bg-slate-900/60 px-3.5 py-2 text-xs font-medium text-slate-200 hover:bg-white/10 transition-all"
            >
              <RefreshCw className={`h-3.5 w-3.5 ${refreshing ? 'animate-spin text-sky-400' : ''}`} />
              Refresh
            </button>
          </div>
        </div>

        {/* Global Instant Find Search Bar */}
        <div className="relative">
          <div className="relative flex items-center">
            <Search className="absolute left-4 h-5 w-5 text-slate-400" />
            <input
              type="text"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              placeholder="Find any feature, quota, setting, or command... (e.g. php, backup, quota, reboot, ssl)"
              className="w-full rounded-2xl border border-white/10 bg-slate-900/80 pl-12 pr-28 py-3.5 text-sm text-white placeholder-slate-500 shadow-2xl backdrop-blur-xl focus:border-sky-500/50 focus:outline-none focus:ring-2 focus:ring-sky-500/20 transition-all"
            />
            {searchQuery ? (
              <button
                onClick={() => setSearchQuery('')}
                className="absolute right-12 text-slate-400 hover:text-white"
              >
                <X className="h-4 w-4" />
              </button>
            ) : null}
            <span className="absolute right-4 rounded-lg bg-slate-800/80 px-2.5 py-1 text-[11px] font-mono text-slate-400 border border-white/5">
              {filteredItems.length} items
            </span>
          </div>

          {/* Quick Category Filter Pills */}
          <div className="mt-3 flex flex-wrap items-center gap-2">
            <button
              onClick={() => setSelectedCategory('all')}
              className={`rounded-xl px-3 py-1.5 text-xs font-medium transition-all ${
                selectedCategory === 'all'
                  ? 'bg-sky-500 text-white shadow-md shadow-sky-500/25'
                  : 'bg-slate-900/40 text-slate-400 border border-white/5 hover:text-white hover:bg-white/5'
              }`}
            >
              All Sections ({allItems.length})
            </button>
            {categories.map((c) => {
              const count = allItems.filter((i) => i.category === c.id).length;
              const isSelected = selectedCategory === c.id;
              return (
                <button
                  key={c.id}
                  onClick={() => setSelectedCategory(isSelected ? 'all' : c.id)}
                  className={`rounded-xl px-3 py-1.5 text-xs font-medium transition-all flex items-center gap-1.5 ${
                    isSelected
                      ? 'bg-sky-500 text-white shadow-md shadow-sky-500/25'
                      : 'bg-slate-900/40 text-slate-400 border border-white/5 hover:text-white hover:bg-white/5'
                  }`}
                >
                  <c.icon className="h-3 w-3" />
                  {c.title} ({count})
                </button>
              );
            })}

            <div className="ml-auto flex items-center gap-1.5 text-xs text-slate-400">
              <button
                onClick={expandAllCategories}
                className="hover:text-white px-2 py-1 rounded bg-slate-900/40 border border-white/5"
              >
                Expand All
              </button>
              <button
                onClick={collapseAllCategories}
                className="hover:text-white px-2 py-1 rounded bg-slate-900/40 border border-white/5"
              >
                Collapse All
              </button>
            </div>
          </div>
        </div>

        {/* Top 4 Metrics Summary Grid */}
        <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
          <div className="glass-panel glass-panel-hover rounded-2xl p-5 relative overflow-hidden">
            <div className="flex items-center justify-between mb-3">
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">CPU Usage</span>
              <div className="rounded-xl bg-sky-500/10 p-2 text-sky-400 border border-sky-500/20">
                <Cpu className="h-4 w-4" />
              </div>
            </div>
            <div className="flex items-baseline gap-2 mb-2">
              <span className="text-2xl font-black text-white">{status?.cpu.percent ?? 0}%</span>
              <span className="text-xs text-slate-400">({status?.cpu.cores} Cores)</span>
            </div>
            <div className="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
              <div
                className="h-full bg-gradient-to-r from-sky-400 to-indigo-500 rounded-full transition-all duration-500"
                style={{ width: `${Math.min(100, Math.max(5, status?.cpu.percent ?? 0))}%` }}
              />
            </div>
          </div>

          <div className="glass-panel glass-panel-hover rounded-2xl p-5 relative overflow-hidden">
            <div className="flex items-center justify-between mb-3">
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">Memory (RAM)</span>
              <div className="rounded-xl bg-indigo-500/10 p-2 text-indigo-400 border border-indigo-500/20">
                <Activity className="h-4 w-4" />
              </div>
            </div>
            <div className="flex items-baseline gap-2 mb-2">
              <span className="text-2xl font-black text-white">{status?.memory.percent ?? 0}%</span>
              <span className="text-xs text-slate-400">
                {formatBytes(status?.memory.used_bytes ?? 0)} / {formatBytes(status?.memory.total_bytes ?? 0)}
              </span>
            </div>
            <div className="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
              <div
                className="h-full bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full transition-all duration-500"
                style={{ width: `${status?.memory.percent ?? 0}%` }}
              />
            </div>
          </div>

          <div className="glass-panel glass-panel-hover rounded-2xl p-5 relative overflow-hidden">
            <div className="flex items-center justify-between mb-3">
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">Disk Storage</span>
              <div className="rounded-xl bg-emerald-500/10 p-2 text-emerald-400 border border-emerald-500/20">
                <HardDrive className="h-4 w-4" />
              </div>
            </div>
            <div className="flex items-baseline gap-2 mb-2">
              <span className="text-2xl font-black text-white">{status?.disk.percent ?? 0}%</span>
              <span className="text-xs text-slate-400">
                {formatBytes(status?.disk.used_bytes ?? 0)} / {formatBytes(status?.disk.total_bytes ?? 0)}
              </span>
            </div>
            <div className="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
              <div
                className="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-500"
                style={{ width: `${status?.disk.percent ?? 0}%` }}
              />
            </div>
          </div>

          <div className="glass-panel glass-panel-hover rounded-2xl p-5 relative overflow-hidden">
            <div className="flex items-center justify-between mb-3">
              <span className="text-xs font-semibold uppercase tracking-wider text-slate-400">System Uptime</span>
              <div className="rounded-xl bg-amber-500/10 p-2 text-amber-400 border border-amber-500/20">
                <Clock className="h-4 w-4" />
              </div>
            </div>
            <div className="mb-2">
              <span className="text-2xl font-black text-white">
                {status ? formatUptime(status.uptime_seconds) : '--'}
              </span>
            </div>
            <p className="text-xs text-emerald-400 flex items-center gap-1.5">
              <span className="h-2 w-2 rounded-full bg-emerald-400 animate-ping" />
              All Services Operating
            </p>
          </div>
        </div>

        {/* Main Grid: Left Column (Server Info & 17-Quota Matrix) + Right Column (The 7 Comprehensive Categories) */}
        <div className="grid gap-8 lg:grid-cols-12 items-start">
          {/* Left Column: Server Info & 17 Quotas */}
          <div className="space-y-6 lg:col-span-4">
            {/* Information Card (Matches Kloxo Display Left Side) */}
            <div className="glass-panel rounded-2xl p-5 relative overflow-hidden">
              <div className="flex items-center justify-between border-b border-white/5 pb-4 mb-4">
                <div className="flex items-center gap-3">
                  <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 text-white font-bold shadow-lg shadow-sky-500/20">
                    {serverInfo?.username?.[0]?.toUpperCase() || 'A'}
                  </div>
                  <div>
                    <h3 className="text-sm font-bold text-white">Account Information</h3>
                    <p className="text-xs text-sky-400">User: {serverInfo?.username || 'admin'}</p>
                  </div>
                </div>
                <button
                  onClick={() => handleOpenModal('password', 'Update Admin Password')}
                  className="rounded-lg p-1.5 text-slate-400 hover:text-white hover:bg-white/5"
                  title="Change Password"
                >
                  <Key className="h-4 w-4" />
                </button>
              </div>

              <div className="space-y-2.5 text-xs">
                <div className="flex items-center justify-between py-1 border-b border-white/5">
                  <span className="text-slate-400">Home Directory:</span>
                  <span className="font-mono text-slate-200">{serverInfo?.home_dir || '/home/admin/'}</span>
                </div>
                <div className="flex items-center justify-between py-1 border-b border-white/5">
                  <span className="text-slate-400">Contact Email:</span>
                  <span className="text-slate-200">{serverInfo?.contact_email || 'admin@hostspectra.com'}</span>
                </div>
                <div className="flex items-center justify-between py-1 border-b border-white/5">
                  <span className="text-slate-400">Default Domain:</span>
                  <span className="font-mono text-sky-400">{serverInfo?.default_domain || 'vf1.hostspectra.com'}</span>
                </div>
                <div className="flex items-center justify-between py-1 border-b border-white/5">
                  <span className="text-slate-400">Last Login IP:</span>
                  <span className="font-mono text-slate-300">{serverInfo?.last_login_ip || '127.0.0.1'}</span>
                </div>
                <div className="flex items-center justify-between py-1 border-b border-white/5">
                  <span className="text-slate-400">Current Login IP:</span>
                  <span className="font-mono text-emerald-400">{serverInfo?.current_login_ip || '127.0.0.1'}</span>
                </div>
                <div className="flex items-center justify-between py-1 border-b border-white/5">
                  <span className="text-slate-400">Last Login Time:</span>
                  <span className="text-slate-300">{serverInfo?.last_login_time || '2026-11-04 18:37:24'}</span>
                </div>
                <div className="flex items-center justify-between py-1">
                  <span className="text-slate-400">Panel Skin:</span>
                  <span className="rounded bg-sky-500/10 px-2 py-0.5 text-sky-300 border border-sky-500/20 text-[11px]">
                    {serverInfo?.skin || 'Simplicity / Slate Modern'}
                  </span>
                </div>
              </div>
            </div>

            {/* Complete 17-Item Resource Usage Quota Matrix */}
            <div className="glass-panel rounded-2xl p-5">
              <div className="flex items-center justify-between mb-4 border-b border-white/5 pb-3">
                <div>
                  <h3 className="text-sm font-bold text-white">Resource Usage</h3>
                  <p className="text-[11px] text-slate-400">Quota metrics & consumption limits</p>
                </div>
                <span className="rounded-full bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-0.5 text-[10px] font-semibold text-emerald-400">
                  {quotas.length || 17} Quotas
                </span>
              </div>

              <div className="space-y-3.5 max-h-[720px] overflow-y-auto pr-1">
                {(quotas.length > 0
                  ? quotas
                  : [
                      { title: 'Clients', used: 1, max: 'unlimited', unit: '' },
                      { title: 'Domains', used: 1, max: 'unlimited', unit: '' },
                      { title: 'Subdomains', used: 0, max: 'unlimited', unit: '' },
                      { title: 'Traffic', used: 0, max: 'unlimited', unit: 'MB/month' },
                      { title: 'Total Disk Usage', used: 1240, max: 51200, unit: 'MB' },
                      { title: 'Rubyfcgi processes', used: 0, max: 'unlimited', unit: '' },
                      { title: 'FTP Users', used: 1, max: 'unlimited', unit: '' },
                      { title: 'Rails Apps', used: 0, max: 'unlimited', unit: '' },
                      { title: 'PHPfcgi children', used: 5, max: 'unlimited', unit: '' },
                      { title: 'Mail Disk Usage', used: 0, max: 'unlimited', unit: 'MB' },
                      { title: 'Mail Accounts', used: 1, max: 'unlimited', unit: '' },
                      { title: 'Mailing Lists', used: 0, max: 'unlimited', unit: '' },
                      { title: 'MySQL Databases', used: 1, max: 'unlimited', unit: '' },
                      { title: 'Pointer Domains', used: 0, max: 'unlimited', unit: '' },
                      { title: 'Client Disk Usage', used: 154, max: 'unlimited', unit: 'MB' },
                      { title: 'Last Month Traffic', used: 0, max: 'unlimited', unit: 'MB' },
                      { title: 'MySQL Disk Usage', used: 12.4, max: 'unlimited', unit: 'MB' },
                    ]
                ).map((q, idx) => {
                  const isNumberMax = typeof q.max === 'number' && q.max > 0;
                  const percent = isNumberMax ? Math.min(100, Math.round((Number(q.used) / Number(q.max)) * 100)) : 0;
                  return (
                    <div key={idx} className="group rounded-xl border border-white/5 bg-slate-900/40 p-2.5 hover:border-white/10 transition-all">
                      <div className="flex items-center justify-between text-xs mb-1.5">
                        <span className="font-medium text-slate-300">{q.title}</span>
                        <div className="flex items-center gap-1.5 font-mono text-[11px]">
                          <span className="text-white font-semibold">
                            {q.used} {q.unit}
                          </span>
                          <span className="text-slate-500">/</span>
                          <span className="text-slate-400">
                            {q.max} {q.unit}
                          </span>
                        </div>
                      </div>

                      {isNumberMax ? (
                        <div className="h-1.5 w-full rounded-full bg-slate-800 overflow-hidden">
                          <div
                            className={`h-full rounded-full transition-all duration-300 ${
                              percent > 85 ? 'bg-rose-500' : percent > 60 ? 'bg-amber-400' : 'bg-emerald-400'
                            }`}
                            style={{ width: `${Math.max(5, percent)}%` }}
                          />
                        </div>
                      ) : (
                        <div className="flex items-center gap-1.5">
                          <div className="h-1 w-2 rounded-full bg-emerald-400" />
                          <span className="text-[10px] text-emerald-400/80 font-mono">Unmetered / OK</span>
                        </div>
                      )}
                    </div>
                  );
                })}
              </div>
            </div>
          </div>

          {/* Right Column: 7 Comprehensive Action Categories */}
          <div className="space-y-6 lg:col-span-8">
            {categories.map((category) => {
              const itemsInCategory = filteredItems.filter((i) => i.category === category.id);
              if (itemsInCategory.length === 0) return null;

              const isCollapsed = !!collapsedCategories[category.id];

              return (
                <div
                  key={category.id}
                  className="glass-panel rounded-2xl overflow-hidden border border-white/5 transition-all duration-200"
                >
                  {/* Category Header */}
                  <div
                    onClick={() => toggleCategoryCollapse(category.id)}
                    className="flex cursor-pointer items-center justify-between p-4 bg-slate-900/50 hover:bg-slate-800/40 border-b border-white/5 transition-all select-none"
                  >
                    <div className="flex items-center gap-3">
                      <div className={`rounded-xl border p-2.5 bg-gradient-to-br ${category.color}`}>
                        <category.icon className="h-5 w-5" />
                      </div>
                      <div>
                        <h2 className="text-base font-bold text-white flex items-center gap-2">
                          {category.title}
                          <span className="rounded-full bg-slate-800 px-2 py-0.5 text-[11px] font-normal text-slate-300 border border-white/5">
                            {itemsInCategory.length}
                          </span>
                        </h2>
                        <p className="text-xs text-slate-400">{category.description}</p>
                      </div>
                    </div>

                    <button
                      className="rounded-lg p-1.5 text-slate-400 hover:text-white"
                      aria-label="Toggle Category"
                    >
                      {isCollapsed ? <ChevronDown className="h-5 w-5" /> : <ChevronUp className="h-5 w-5" />}
                    </button>
                  </div>

                  {/* Category Items Grid */}
                  {!isCollapsed && (
                    <div className="p-4 grid gap-3.5 sm:grid-cols-2 xl:grid-cols-3">
                      {itemsInCategory.map((item) => {
                        const ItemIcon = item.icon;

                        // Click handling: direct route or modal or legacy SSO
                        const handleClick = (e: React.MouseEvent) => {
                          if (item.modal) {
                            e.preventDefault();
                            handleOpenModal(item.modal, item.title);
                          } else if (item.legacyAction) {
                            e.preventDefault();
                            openLegacySSO(item.legacyAction);
                          }
                        };

                        const CardContent = (
                          <div className="group relative flex flex-col justify-between rounded-xl border border-white/5 bg-slate-900/40 p-3.5 hover:border-sky-500/40 hover:bg-slate-800/60 transition-all cursor-pointer h-full">
                            <div>
                              <div className="flex items-center justify-between mb-2">
                                <div className="rounded-lg bg-slate-800/80 p-2 text-sky-400 group-hover:bg-sky-500/10 group-hover:text-sky-300 border border-white/5 transition-all">
                                  <ItemIcon className="h-4 w-4" />
                                </div>
                                {item.badge && (
                                  <span
                                    className={`rounded-md px-1.5 py-0.5 text-[10px] font-semibold border ${
                                      item.badgeColor
                                        ? 'bg-rose-500/10 border-rose-500/20 ' + item.badgeColor
                                        : 'bg-sky-500/10 text-sky-400 border-sky-500/20'
                                    }`}
                                  >
                                    {item.badge}
                                  </span>
                                )}
                              </div>
                              <h3 className="text-xs font-bold text-white group-hover:text-sky-300 transition-colors">
                                {item.title}
                              </h3>
                              <p className="mt-1 text-[11px] text-slate-400 line-clamp-2 leading-relaxed">
                                {item.description}
                              </p>
                            </div>

                            <div className="mt-3 flex items-center justify-end text-[11px] text-slate-500 group-hover:text-sky-400 transition-colors">
                              <span className="flex items-center gap-1 font-medium">
                                Manage <ArrowUpRight className="h-3 w-3" />
                              </span>
                            </div>
                          </div>
                        );

                        if (item.href) {
                          return (
                            <Link key={item.id} href={item.href}>
                              {CardContent}
                            </Link>
                          );
                        }

                        return (
                          <div key={item.id} onClick={handleClick}>
                            {CardContent}
                          </div>
                        );
                      })}
                    </div>
                  )}
                </div>
              );
            })}
          </div>
        </div>
      </div>

      {/* Interactive Modal Dialog Container */}
      {activeModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-md animate-in fade-in duration-200">
          <div className="glass-panel relative w-full max-w-3xl rounded-3xl border border-white/10 bg-slate-950 p-6 shadow-2xl max-h-[85vh] flex flex-col overflow-hidden">
            {/* Modal Header */}
            <div className="flex items-center justify-between border-b border-white/5 pb-4 mb-4">
              <div className="flex items-center gap-3">
                <div className="rounded-xl bg-sky-500/10 p-2 text-sky-400 border border-sky-500/20">
                  <Wrench className="h-5 w-5" />
                </div>
                <div>
                  <h3 className="text-lg font-bold text-white">{modalTitle}</h3>
                  <p className="text-xs text-slate-400">Integrated Control Panel Management Tool</p>
                </div>
              </div>
              <button
                onClick={() => setActiveModal(null)}
                className="rounded-xl p-2 text-slate-400 hover:bg-white/10 hover:text-white transition-all"
              >
                <X className="h-5 w-5" />
              </button>
            </div>

            {/* Notification alert */}
            {modalMessage && (
              <div
                className={`mb-4 rounded-xl p-3 text-xs flex items-center gap-2 ${
                  modalMessage.type === 'success'
                    ? 'bg-emerald-500/10 text-emerald-300 border border-emerald-500/20'
                    : 'bg-rose-500/10 text-rose-300 border border-rose-500/20'
                }`}
              >
                {modalMessage.type === 'success' ? <CheckCircle2 className="h-4 w-4" /> : <AlertTriangle className="h-4 w-4" />}
                <span>{modalMessage.text}</span>
              </div>
            )}

            {/* Modal Body Container */}
            <div className="flex-1 overflow-y-auto pr-1">
              {/* Modal 1: Command Center */}
              {activeModal === 'commandcenter' && (
                <div className="space-y-4">
                  <p className="text-xs text-slate-300">
                    Execute real-time network and host diagnostics directly from the server.
                  </p>
                  <div className="grid gap-3 sm:grid-cols-3">
                    <select
                      value={commandTool}
                      onChange={(e) => setCommandTool(e.target.value)}
                      className="rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    >
                      <option value="ping">Ping (4 Packets)</option>
                      <option value="traceroute">Traceroute (Max 15 hops)</option>
                      <option value="dig">DNS Dig (Resolve IP)</option>
                      <option value="uptime">System Uptime</option>
                      <option value="disk">Disk Partitions (df -h)</option>
                      <option value="memory">Free Memory (free -m)</option>
                      <option value="who">Logged in Users (who)</option>
                    </select>

                    <input
                      type="text"
                      value={commandTarget}
                      onChange={(e) => setCommandTarget(e.target.value)}
                      placeholder="IP or Hostname (e.g. 8.8.8.8)"
                      className="rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none sm:col-span-2"
                    />
                  </div>

                  <button
                    onClick={handleRunCommand}
                    disabled={modalLoading}
                    className="flex items-center gap-2 rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all disabled:opacity-50"
                  >
                    <Play className="h-3.5 w-3.5" />
                    {modalLoading ? 'Executing...' : 'Run Diagnostics'}
                  </button>

                  <div className="mt-4 rounded-xl border border-white/5 bg-slate-900/90 p-4 font-mono text-xs text-emerald-400 overflow-x-auto min-h-[140px] max-h-[280px]">
                    <pre className="whitespace-pre-wrap">{commandOutput || 'Ready. Click "Run Diagnostics" to view output.'}</pre>
                  </div>
                </div>
              )}

              {/* Modal 2: Component Info */}
              {activeModal === 'components' && (
                <div className="space-y-3">
                  <p className="text-xs text-slate-300">
                    Installed core engines and system daemon versions detected on this host.
                  </p>
                  {modalLoading ? (
                    <div className="py-8 text-center text-xs text-slate-400 animate-pulse">Detecting components...</div>
                  ) : (
                    <div className="grid gap-2.5 sm:grid-cols-2">
                      {componentsList.map((comp, idx) => (
                        <div key={idx} className="rounded-xl border border-white/5 bg-slate-900/50 p-3 flex items-center justify-between">
                          <div>
                            <p className="text-xs font-bold text-white">{comp.name}</p>
                            <p className="text-[11px] font-mono text-sky-400">{comp.version}</p>
                          </div>
                          <span className="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-semibold text-emerald-400 border border-emerald-500/20">
                            {comp.status}
                          </span>
                        </div>
                      ))}
                    </div>
                  )}
                </div>
              )}

              {/* Modal 3: Process Viewer */}
              {activeModal === 'processes' && (
                <div className="space-y-3">
                  <div className="flex items-center justify-between mb-2">
                    <p className="text-xs text-slate-300">Active server processes sorted by CPU consumption.</p>
                    <button
                      onClick={() => handleOpenModal('processes', 'Live Process Monitor')}
                      className="text-xs text-sky-400 hover:underline flex items-center gap-1"
                    >
                      <RefreshCw className="h-3 w-3" /> Refresh
                    </button>
                  </div>

                  {modalLoading ? (
                    <div className="py-8 text-center text-xs text-slate-400 animate-pulse">Scanning processes...</div>
                  ) : (
                    <div className="rounded-xl border border-white/5 overflow-x-auto">
                      <table className="w-full text-left text-xs">
                        <thead className="bg-slate-900/80 text-[11px] uppercase text-slate-400 border-b border-white/5">
                          <tr>
                            <th className="px-3 py-2">PID</th>
                            <th className="px-3 py-2">User</th>
                            <th className="px-3 py-2">CPU%</th>
                            <th className="px-3 py-2">Mem%</th>
                            <th className="px-3 py-2">Command</th>
                            <th className="px-3 py-2 text-right">Action</th>
                          </tr>
                        </thead>
                        <tbody className="divide-y divide-white/5">
                          {processesList.map((p) => (
                            <tr key={p.pid} className="hover:bg-white/5 font-mono">
                              <td className="px-3 py-2 text-sky-400">{p.pid}</td>
                              <td className="px-3 py-2 text-slate-300">{p.user}</td>
                              <td className="px-3 py-2 text-emerald-400">{p.cpu}%</td>
                              <td className="px-3 py-2 text-indigo-400">{p.mem}%</td>
                              <td className="px-3 py-2 text-slate-200 truncate max-w-xs">{p.command}</td>
                              <td className="px-3 py-2 text-right">
                                {p.pid > 10 && (
                                  <button
                                    onClick={() => handleKillProcess(p.pid)}
                                    className="rounded bg-rose-500/10 border border-rose-500/20 px-2 py-0.5 text-[10px] text-rose-400 hover:bg-rose-500/20"
                                  >
                                    Kill
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
              )}

              {/* Modal 4: Action Logs */}
              {activeModal === 'actionlog' && (
                <div className="space-y-3">
                  <p className="text-xs text-slate-300">Recent administrative activities and system logins.</p>
                  <div className="rounded-xl border border-white/5 bg-slate-900/60 p-3 space-y-2 max-h-[300px] overflow-y-auto">
                    {actionLogs.map((log, idx) => (
                      <div key={idx} className="flex items-start justify-between gap-4 text-xs border-b border-white/5 pb-2">
                        <span className="font-mono text-slate-200">{log.log}</span>
                        <span className="text-[10px] text-slate-400 whitespace-nowrap">{log.timestamp}</span>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* Modal 5: PHP Modules */}
              {activeModal === 'modules' && (
                <div className="space-y-3">
                  <p className="text-xs text-slate-300">All loaded PHP extensions and compilation status.</p>
                  <div className="flex flex-wrap gap-2 max-h-[300px] overflow-y-auto p-1">
                    {phpModules.map((m, idx) => (
                      <span
                        key={idx}
                        className="rounded-lg border border-white/10 bg-slate-900/80 px-2.5 py-1 text-xs font-mono text-sky-300 flex items-center gap-1.5"
                      >
                        <CheckCircle2 className="h-3 w-3 text-emerald-400" />
                        {m.name}
                      </span>
                    ))}
                  </div>
                </div>
              )}

              {/* Modal 6: Password Update */}
              {activeModal === 'password' && (
                <form onSubmit={handleUpdatePassword} className="space-y-4 max-w-md">
                  <p className="text-xs text-slate-300">Set a new secure password for administrator account.</p>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">New Password</label>
                    <input
                      type="password"
                      value={newPassword}
                      onChange={(e) => setNewPassword(e.target.value)}
                      required
                      minLength={6}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Confirm Password</label>
                    <input
                      type="password"
                      value={confirmPassword}
                      onChange={(e) => setConfirmPassword(e.target.value)}
                      required
                      minLength={6}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                  </div>
                  <button
                    type="submit"
                    disabled={modalLoading}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all disabled:opacity-50"
                  >
                    {modalLoading ? 'Updating...' : 'Save Password'}
                  </button>
                </form>
              )}

              {/* Modal 7: Maintenance Mode */}
              {activeModal === 'maintenance' && (
                <div className="space-y-4 max-w-md">
                  <p className="text-xs text-slate-300">
                    Maintenance mode temporarily suspends customer website traffic while keeping admin access open.
                  </p>
                  <div className="flex items-center justify-between rounded-xl border border-white/5 bg-slate-900/60 p-4">
                    <div>
                      <p className="text-sm font-bold text-white">Current Maintenance State</p>
                      <p className="text-xs text-slate-400">
                        Status: {serverInfo?.maintenance ? 'ENABLED (Under Maintenance)' : 'DISABLED (Online)'}
                      </p>
                    </div>
                    <button
                      onClick={handleToggleMaintenance}
                      disabled={modalLoading}
                      className={`rounded-xl px-4 py-2 text-xs font-bold transition-all ${
                        serverInfo?.maintenance
                          ? 'bg-rose-500 text-white hover:bg-rose-400'
                          : 'bg-emerald-500 text-white hover:bg-emerald-400'
                      }`}
                    >
                      {serverInfo?.maintenance ? 'Turn Off Maintenance' : 'Turn On Maintenance'}
                    </button>
                  </div>
                </div>
              )}

              {/* Modal 8: Reboot Confirm */}
              {activeModal === 'reboot' && (
                <div className="space-y-4 max-w-md">
                  <div className="rounded-xl border border-rose-500/30 bg-rose-500/10 p-4">
                    <h4 className="text-sm font-bold text-rose-300 flex items-center gap-2">
                      <AlertTriangle className="h-4 w-4" /> Warning: Server Reboot
                    </h4>
                    <p className="mt-1 text-xs text-rose-200/80">
                      Rebooting the server will restart all daemons, terminate active SSH sessions, and cause temporary downtime.
                    </p>
                  </div>
                  <div className="flex items-center gap-3">
                    <button
                      onClick={handleRebootServer}
                      disabled={modalLoading}
                      className="rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white hover:bg-rose-500 transition-all"
                    >
                      {modalLoading ? 'Scheduling...' : 'Confirm and Reboot Host'}
                    </button>
                    <button
                      onClick={() => setActiveModal(null)}
                      className="rounded-xl border border-white/10 px-4 py-2 text-xs text-slate-300 hover:bg-white/5"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
              )}

              {/* Fallback for other modals: Quick Details + Direct SSO Link */}
              {!['commandcenter', 'components', 'processes', 'actionlog', 'modules', 'password', 'maintenance', 'reboot'].includes(
                activeModal
              ) && (
                <div className="space-y-4">
                  <p className="text-xs text-slate-300">
                    This advanced module is connected to the Mfklixo backend engine. You can review its configuration or open it directly in the classic interface without re-authenticating.
                  </p>
                  <div className="rounded-xl border border-white/5 bg-slate-900/60 p-4 space-y-2 text-xs">
                    <div className="flex justify-between border-b border-white/5 pb-2">
                      <span className="text-slate-400">Module ID:</span>
                      <span className="font-mono text-sky-400">{activeModal}</span>
                    </div>
                    <div className="flex justify-between border-b border-white/5 pb-2">
                      <span className="text-slate-400">Security Scope:</span>
                      <span className="text-emerald-400">Super Administrator (pserver)</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-slate-400">Status:</span>
                      <span className="text-slate-200">Synchronized & Active</span>
                    </div>
                  </div>
                  <div className="pt-2">
                    <button
                      onClick={() => openLegacySSO(`frm_o_cname=${activeModal}`)}
                      className="flex items-center gap-2 rounded-xl bg-gradient-to-r from-sky-500 to-indigo-600 px-4 py-2.5 text-xs font-semibold text-white hover:opacity-90 transition-all shadow-md shadow-sky-500/20"
                    >
                      <ExternalLink className="h-4 w-4" />
                      Open Full Settings in Classic Engine
                    </button>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </Shell>
  );
}
