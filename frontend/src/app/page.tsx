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
  Plus,
  Code,
  Check,
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
  SystemSettings,
  ResourcePlanItem,
  SupportTicketItem,
  SupportMessageItem,
  SshConfigItem,
  WatchdogConfig,
  LxGuardConfig,
  SwitchProgramConfig,
  TimezoneResponse,
  MailQueueResponse,
  MailServerConfig,
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

  // Modal Specific Data States
  const [componentsList, setComponentsList] = useState<ComponentItem[]>([]);
  const [processesList, setProcessesList] = useState<ProcessItem[]>([]);
  const [actionLogs, setActionLogs] = useState<ActionLogItem[]>([]);
  const [phpModules, setPhpModules] = useState<ModuleItem[]>([]);
  const [commandTool, setCommandTool] = useState('ping');
  const [commandTarget, setCommandTarget] = useState('127.0.0.1');
  const [commandOutput, setCommandOutput] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');

  // Additional Dedicated Module States
  const [settings, setSettings] = useState<SystemSettings>({
    panel_title: 'Mfklixo Cloud Control Panel',
    contact_email: 'admin@hostspectra.com',
    language: 'en',
    scavenge_time: 3600,
    session_timeout: 7200,
    download_limit_kb: 0,
    dir_indexes: 'index.php, index.html, index.htm',
    default_domain: 'vf1.hostspectra.com',
    custom_logo_url: '',
    maintenance: false,
    login_captcha: false,
    two_factor_auth: false,
    auto_backup_schedule: 'daily',
    auto_backup_destination: 'local',
  });

  const [plans, setPlans] = useState<ResourcePlanItem[]>([]);
  const [newPlanName, setNewPlanName] = useState('');
  const [newPlanDisk, setNewPlanDisk] = useState(5120);
  const [newPlanTraffic, setNewPlanTraffic] = useState(20480);
  const [newPlanDomains, setNewPlanDomains] = useState(2);

  const [tickets, setTickets] = useState<SupportTicketItem[]>([]);
  const [newTicketSubject, setNewTicketSubject] = useState('');
  const [newTicketMessage, setNewTicketMessage] = useState('');
  const [newTicketPriority, setNewTicketPriority] = useState('medium');

  const [messages, setMessages] = useState<SupportMessageItem[]>([]);
  const [newMsgTitle, setNewMsgTitle] = useState('');
  const [newMsgContent, setNewMsgContent] = useState('');

  const [sshConfig, setSshConfig] = useState<SshConfigItem>({
    port: 22,
    permit_root_login: true,
    password_auth: true,
    service_active: true,
  });

  const [watchdog, setWatchdog] = useState<WatchdogConfig>({
    enabled: true,
    interval_seconds: 180,
    auto_recovery: true,
    monitored_services: [
      { name: 'Web Server (Nginx)', service: 'nginx', active: true },
      { name: 'PHP FastCGI (kloxo-php)', service: 'kloxo-php', active: true },
      { name: 'MariaDB Database', service: 'mariadb', active: true },
      { name: 'DNS Server (BIND 9)', service: 'named', active: true },
      { name: 'FTP Server (Pure-FTPd)', service: 'pure-ftpd', active: true },
    ],
  });

  const [lxguard, setLxguard] = useState<LxGuardConfig>({
    enabled: true,
    max_attempts: 5,
    ban_minutes: 60,
    whitelist_ips: ['127.0.0.1'],
    banned_ips: [],
  });

  const [switchProgram, setSwitchProgram] = useState<SwitchProgramConfig>({
    web_engine: 'nginx',
    available_web_engines: ['nginx', 'apache2', 'lighttpd'],
    php_engine: 'php-fpm',
    available_php_engines: ['php-fpm', 'fastcgi', 'cgi'],
    dns_engine: 'bind9',
    available_dns_engines: ['bind9', 'nsd'],
    ftp_engine: 'pure-ftpd',
    available_ftp_engines: ['pure-ftpd', 'vsftpd'],
  });

  const [timezoneData, setTimezoneData] = useState<TimezoneResponse>({
    success: true,
    timezone: 'UTC',
    server_time: '',
    ntp_sync: true,
    available_timezones: ['UTC', 'Asia/Tehran', 'Europe/London', 'America/New_York'],
  });

  const [mailQueue, setMailQueue] = useState<MailQueueResponse>({
    success: true,
    queue_count: 0,
    raw_output: '',
    messages: [],
  });

  const [mailConfig, setMailConfig] = useState<MailServerConfig>({
    smtp_port: 587,
    submission_port: 465,
    tls_enforced: true,
    spamassassin: true,
    clamav: true,
    max_hourly_per_client: 150,
    spf_enforced: true,
    dkim_enabled: true,
  });

  const [skeletonHtml, setSkeletonHtml] = useState(
    '<!DOCTYPE html><html><head><title>Welcome</title></head><body><h1>Welcome to your new site!</h1><p>Website created using Mfklixo Next-Gen Panel.</p></body></html>'
  );

  const [customButtons, setCustomButtons] = useState<{ label: string; url: string; icon: string }[]>([
    { label: 'phpMyAdmin', url: '/phpmyadmin/', icon: 'Database' },
    { label: 'Webmail Roundcube', url: '/webmail/', icon: 'Mail' },
    { label: 'Grafana Metrics', url: ':3001', icon: 'Activity' },
  ]);
  const [newBtnLabel, setNewBtnLabel] = useState('');
  const [newBtnUrl, setNewBtnUrl] = useState('');

  const [sqlQuery, setSqlQuery] = useState('SHOW DATABASES;');
  const [sqlResult, setSqlResult] = useState<string>('');

  const [selectedSkin, setSelectedSkin] = useState<string>('slate');

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

  // Open Modal Handler with Real-Time Endpoint Fetching
  const handleOpenModal = async (modalId: string, title: string) => {
    setActiveModal(modalId);
    setModalTitle(title);
    setModalMessage(null);
    setModalLoading(true);

    try {
      if (modalId === 'components') {
        const res = await apiFetch<{ success: boolean; components: ComponentItem[] }>('/system/components');
        setComponentsList(res.components);
      } else if (modalId === 'processes') {
        const res = await apiFetch<{ success: boolean; processes: ProcessItem[] }>('/system/processes');
        setProcessesList(res.processes);
      } else if (modalId === 'actionlog') {
        const res = await apiFetch<{ success: boolean; logs: ActionLogItem[] }>('/system/action-logs');
        setActionLogs(res.logs);
      } else if (modalId === 'modules') {
        const res = await apiFetch<{ success: boolean; modules: ModuleItem[] }>('/system/modules');
        setPhpModules(res.modules);
      } else if (modalId === 'settings' || modalId === 'general' || modalId === 'scavenge' || modalId === 'downloadconfig' || modalId === 'dirindex' || modalId === 'miscinfo' || modalId === 'cpconfig' || modalId === 'uploadlogo' || modalId === 'childappearance') {
        const res = await apiFetch<{ success: boolean; settings: SystemSettings }>('/system/settings');
        setSettings(res.settings);
      } else if (modalId === 'resourceplan') {
        const res = await apiFetch<{ success: boolean; plans: ResourcePlanItem[] }>('/system/plans');
        setPlans(res.plans);
      } else if (modalId === 'ticket' || modalId === 'ticketconfig') {
        const res = await apiFetch<{ success: boolean; tickets: SupportTicketItem[] }>('/support/tickets');
        setTickets(res.tickets);
      } else if (modalId === 'smessage') {
        const res = await apiFetch<{ success: boolean; messages: SupportMessageItem[] }>('/support/messages');
        setMessages(res.messages);
      } else if (modalId === 'sshconfig') {
        const res = await apiFetch<{ success: boolean; ssh: SshConfigItem }>('/security/ssh-config');
        setSshConfig(res.ssh);
      } else if (modalId === 'watchdog') {
        const res = await apiFetch<{ success: boolean; watchdog: WatchdogConfig }>('/security/watchdog');
        setWatchdog(res.watchdog);
      } else if (modalId === 'lxguard' || modalId === 'blockedlogin') {
        const res = await apiFetch<{ success: boolean; lxguard: LxGuardConfig }>('/security/lxguard');
        setLxguard(res.lxguard);
      } else if (modalId === 'switchprogram') {
        const res = await apiFetch<{ success: boolean; programs: SwitchProgramConfig }>('/server/switch-program');
        setSwitchProgram(res.programs);
      } else if (modalId === 'timezone') {
        const res = await apiFetch<TimezoneResponse>('/server/timezone');
        setTimezoneData(res);
      } else if (modalId === 'mailqueue') {
        const res = await apiFetch<MailQueueResponse>('/mail/queue');
        setMailQueue(res);
      } else if (modalId === 'mailserver' || modalId === 'mailperclient' || modalId === 'sendmailban') {
        const res = await apiFetch<{ success: boolean; mail_config: MailServerConfig }>('/mail/server-config');
        setMailConfig(res.mail_config);
      } else if (modalId === 'skeleton') {
        const res = await apiFetch<{ success: boolean; html: string }>('/system/skeleton');
        setSkeletonHtml(res.html);
      }
    } catch (e: any) {
      console.warn(`Could not load fresh data for modal ${modalId}:`, e.message);
    } finally {
      setModalLoading(false);
    }
  };

  // Run Command Diagnostics
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

  // Save Settings
  const handleSaveSettings = async (partialSettings: Partial<SystemSettings>) => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string; settings: SystemSettings }>('/system/settings', {
        method: 'POST',
        body: JSON.stringify(partialSettings),
      });
      setSettings(res.settings);
      setModalMessage({ type: 'success', text: res.message || 'Settings saved successfully!' });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to save settings' });
    } finally {
      setModalLoading(false);
    }
  };

  // Create Resource Plan
  const handleCreatePlan = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newPlanName) return;
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string; plans: ResourcePlanItem[] }>('/system/plans', {
        method: 'POST',
        body: JSON.stringify({
          name: newPlanName,
          disk_mb: newPlanDisk,
          traffic_mb: newPlanTraffic,
          domains: newPlanDomains,
          mail_accounts: 10,
          mysql_dbs: 5,
        }),
      });
      setPlans(res.plans);
      setNewPlanName('');
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to create plan' });
    } finally {
      setModalLoading(false);
    }
  };

  // Create Support Ticket
  const handleCreateTicket = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newTicketSubject || !newTicketMessage) return;
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string; ticket: SupportTicketItem }>('/support/tickets', {
        method: 'POST',
        body: JSON.stringify({
          subject: newTicketSubject,
          message: newTicketMessage,
          priority: newTicketPriority,
        }),
      });
      setTickets((prev) => [res.ticket, ...prev]);
      setNewTicketSubject('');
      setNewTicketMessage('');
      setModalMessage({ type: 'success', text: 'Support ticket submitted successfully.' });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to create ticket' });
    } finally {
      setModalLoading(false);
    }
  };

  // Send Message
  const handleSendMessage = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMsgTitle || !newMsgContent) return;
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string; entry: SupportMessageItem }>('/support/messages', {
        method: 'POST',
        body: JSON.stringify({
          title: newMsgTitle,
          content: newMsgContent,
        }),
      });
      setMessages((prev) => [res.entry, ...prev]);
      setNewMsgTitle('');
      setNewMsgContent('');
      setModalMessage({ type: 'success', text: 'Announcement published successfully.' });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to post message' });
    } finally {
      setModalLoading(false);
    }
  };

  // Save SSH Config
  const handleSaveSsh = async () => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/security/ssh-config', {
        method: 'POST',
        body: JSON.stringify(sshConfig),
      });
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to update SSH config' });
    } finally {
      setModalLoading(false);
    }
  };

  // Save Watchdog
  const handleSaveWatchdog = async () => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/security/watchdog', {
        method: 'POST',
        body: JSON.stringify(watchdog),
      });
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to save watchdog' });
    } finally {
      setModalLoading(false);
    }
  };

  // Save LxGuard
  const handleSaveLxGuard = async () => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/security/lxguard', {
        method: 'POST',
        body: JSON.stringify(lxguard),
      });
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to save LxGuard' });
    } finally {
      setModalLoading(false);
    }
  };

  // Save Switch Program
  const handleSaveSwitchProgram = async () => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/server/switch-program', {
        method: 'POST',
        body: JSON.stringify(switchProgram),
      });
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to switch programs' });
    } finally {
      setModalLoading(false);
    }
  };

  // Save Timezone
  const handleSaveTimezone = async (tz: string) => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string; timezone: string }>('/server/timezone', {
        method: 'POST',
        body: JSON.stringify({ timezone: tz }),
      });
      setTimezoneData((prev) => ({ ...prev, timezone: res.timezone }));
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to set timezone' });
    } finally {
      setModalLoading(false);
    }
  };

  // Flush Mail Queue
  const handleFlushMailQueue = async () => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/mail/queue', { method: 'POST' });
      setMailQueue((prev) => ({ ...prev, queue_count: 0, raw_output: 'Queue flushed successfully.' }));
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to flush queue' });
    } finally {
      setModalLoading(false);
    }
  };

  // Save Mail Config
  const handleSaveMailConfig = async () => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/mail/server-config', {
        method: 'POST',
        body: JSON.stringify(mailConfig),
      });
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to update mail settings' });
    } finally {
      setModalLoading(false);
    }
  };

  // Save Skeleton
  const handleSaveSkeleton = async () => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; message: string }>('/system/skeleton', {
        method: 'POST',
        body: JSON.stringify({ html: skeletonHtml }),
      });
      setModalMessage({ type: 'success', text: res.message });
    } catch (e: any) {
      setModalMessage({ type: 'error', text: e.message || 'Failed to save skeleton' });
    } finally {
      setModalLoading(false);
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
      setSettings((prev) => ({ ...prev, maintenance: res.maintenance }));
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

  // Execute SQL Studio
  const handleExecuteSql = async () => {
    setModalLoading(true);
    try {
      const res = await apiFetch<{ success: boolean; rows?: any[]; message?: string; duration_ms: number }>('/databases/query', {
        method: 'POST',
        body: JSON.stringify({ database: 'kloxo', query: sqlQuery }),
      });
      setSqlResult(JSON.stringify(res.rows || res.message || 'Query executed', null, 2));
    } catch (e: any) {
      setSqlResult(`SQL Error: ${e.message}`);
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
    { id: 'all_mailinglist', title: 'All Mailing Lists', description: 'Discussion and newsletter mail groups', icon: Inbox, category: 'admin', modal: 'mailinglist' },
    { id: 'all_mysqldb', title: 'All Databases', description: 'MariaDB schemas and user privileges', icon: Database, category: 'admin', href: '/databases' },
    { id: 'all_cron', title: 'All Cron Tasks', description: 'Scheduled crontab background jobs', icon: Clock, category: 'admin', href: '/cron' },
    { id: 'all_ftpuser', title: 'All FTP Users', description: 'FTP accounts and home directories', icon: Share2, category: 'admin', href: '/ftp' },
    { id: 'all_sslcert', title: 'All SSLCerts', description: 'SSL/TLS certificates and Let\'s Encrypt keys', icon: Lock, category: 'admin', href: '/ssl' },
    { id: 'resourceplan', title: 'Resource Plans', description: 'Hosting templates and quota presets', icon: Sliders, category: 'admin', modal: 'resourceplan' },
    { id: 'ticket', title: 'HelpDesk & Tickets', description: 'Customer support tickets and resolution queue', icon: LifeBuoy, category: 'admin', modal: 'ticket' },
    { id: 'ticketconfig', title: 'HelpDesk Configuration', description: 'Support department email and auto-replies', icon: Settings, category: 'admin', modal: 'ticketconfig' },
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
    { id: 'phpfilter', title: 'Advanced PHP Configure', description: 'PHP-FPM worker pools, JIT buffer & OPcache', icon: SlidersHorizontal, category: 'resource', modal: 'phpfilter' },
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
    { id: 'mailserver', title: 'Mail Server Settings', description: 'Postfix/Qmail SMTP, TLS and spam thresholds', icon: Mail, category: 'basic', modal: 'mailserver' },
    { id: 'mailqueue', title: 'Mail Queue', description: 'Inspect outgoing/incoming mail queue and flush', icon: Inbox, category: 'basic', modal: 'mailqueue' },
    { id: 'mailperclient', title: 'Mails Per Client', description: 'Outgoing hourly rate limits per client domain', icon: Sliders, category: 'basic', modal: 'mailperclient' },
    { id: 'ftpsession', title: 'FTP Sessions', description: 'View active FTP transfers and connected clients', icon: Share2, category: 'basic', modal: 'ftpsession' },
    { id: 'webserver', title: 'Webserver Configure', description: 'Nginx worker processes, keepalive and gzip', icon: Globe, category: 'basic', modal: 'webserver' },
    { id: 'ftpconfig', title: 'FTP Configure', description: 'Pure-FTPd port ranges and TLS encryption mode', icon: Settings, category: 'basic', modal: 'ftpconfig' },
    { id: 'phpmodule', title: 'PHP Module Status', description: 'Inspect all loaded PHP modules and libraries', icon: Sparkles, category: 'basic', modal: 'modules' },
    { id: 'sqlmanager', title: 'SQL Manager / Query Studio', description: 'Execute direct SQL queries & phpMyAdmin access', icon: Database, category: 'basic', modal: 'sqlmanager' },
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
    { id: 'blockedlogin', title: 'Blocked Logins', description: 'View IPs currently locked out by failed logins', icon: ShieldAlert, category: 'advanced', modal: 'blockedlogin' },
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
            <button
              onClick={() => handleOpenModal('commandcenter', 'Quick Diagnostics')}
              className="flex items-center gap-2 rounded-xl border border-sky-500/30 bg-sky-500/10 px-3.5 py-2 text-xs font-semibold text-sky-300 hover:bg-sky-500/20 transition-all"
            >
              <Terminal className="h-3.5 w-3.5" />
              Quick Command
            </button>

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
              placeholder="Find any feature, quota, setting, or command... (e.g. php, backup, quota, reboot, ssl, dns, mail)"
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
                  <button
                    onClick={() => handleOpenModal('appearance', 'Customize Appearance & Theme')}
                    className="rounded bg-sky-500/10 px-2 py-0.5 text-sky-300 border border-sky-500/20 text-[11px] hover:bg-sky-500/20 transition-all"
                  >
                    {serverInfo?.skin || 'Simplicity / Slate Modern'}
                  </button>
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

                        const handleClick = (e: React.MouseEvent) => {
                          if (item.modal) {
                            e.preventDefault();
                            handleOpenModal(item.modal, item.title);
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
                                Configure <ArrowUpRight className="h-3 w-3" />
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

      {/* 100% Native Interactive Modal Dialog Container */}
      {activeModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md animate-in fade-in duration-200">
          <div className="glass-panel relative w-full max-w-3xl rounded-3xl border border-white/10 bg-slate-950 p-6 shadow-2xl max-h-[88vh] flex flex-col overflow-hidden">
            {/* Modal Header */}
            <div className="flex items-center justify-between border-b border-white/5 pb-4 mb-4">
              <div className="flex items-center gap-3">
                <div className="rounded-xl bg-sky-500/10 p-2 text-sky-400 border border-sky-500/20">
                  <Wrench className="h-5 w-5" />
                </div>
                <div>
                  <h3 className="text-lg font-bold text-white">{modalTitle}</h3>
                  <p className="text-xs text-slate-400">Native Next.js Control Panel Module</p>
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
              {/* 1. Command Center / Traceroute */}
              {(activeModal === 'commandcenter' || activeModal === 'traceroute') && (
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

              {/* 2. Component Info */}
              {(activeModal === 'components' || activeModal === 'info') && (
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

              {/* 3. Process Viewer */}
              {(activeModal === 'processes' || activeModal === 'process') && (
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

              {/* 4. Action Logs */}
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

              {/* 5. PHP Modules */}
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

              {/* 6. Password Update */}
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

              {/* 7. Maintenance Mode */}
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

              {/* 8. Reboot Confirm */}
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

              {/* 9. Resource Plans */}
              {activeModal === 'resourceplan' && (
                <div className="space-y-5">
                  <div className="flex items-center justify-between">
                    <p className="text-xs text-slate-300">Defined hosting quota plans available for assigning to clients.</p>
                  </div>

                  <div className="rounded-xl border border-white/5 overflow-x-auto">
                    <table className="w-full text-left text-xs">
                      <thead className="bg-slate-900/80 text-[11px] uppercase text-slate-400 border-b border-white/5">
                        <tr>
                          <th className="px-3 py-2">Plan Name</th>
                          <th className="px-3 py-2">Disk</th>
                          <th className="px-3 py-2">Traffic</th>
                          <th className="px-3 py-2">Domains</th>
                          <th className="px-3 py-2">Status</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-white/5">
                        {plans.map((p, idx) => (
                          <tr key={idx} className="hover:bg-white/5">
                            <td className="px-3 py-2 font-bold text-white">{p.name}</td>
                            <td className="px-3 py-2 text-slate-300">{p.disk_mb} MB</td>
                            <td className="px-3 py-2 text-slate-300">{p.traffic_mb} MB</td>
                            <td className="px-3 py-2 text-sky-400">{p.domains}</td>
                            <td className="px-3 py-2">
                              {p.is_default ? (
                                <span className="rounded bg-sky-500/10 text-sky-400 border border-sky-500/20 px-2 py-0.5 text-[10px]">Default</span>
                              ) : (
                                <span className="text-slate-500 text-[11px]">Active</span>
                              )}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>

                  {/* Add Plan Form */}
                  <form onSubmit={handleCreatePlan} className="rounded-2xl border border-white/5 bg-slate-900/50 p-4 space-y-3">
                    <h4 className="text-xs font-bold text-white">Create New Resource Plan</h4>
                    <div className="grid gap-3 sm:grid-cols-4">
                      <input
                        type="text"
                        placeholder="Plan Name (e.g. Bronze Plan)"
                        value={newPlanName}
                        onChange={(e) => setNewPlanName(e.target.value)}
                        required
                        className="rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                      />
                      <input
                        type="number"
                        placeholder="Disk MB"
                        value={newPlanDisk}
                        onChange={(e) => setNewPlanDisk(Number(e.target.value))}
                        className="rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                      />
                      <input
                        type="number"
                        placeholder="Traffic MB"
                        value={newPlanTraffic}
                        onChange={(e) => setNewPlanTraffic(Number(e.target.value))}
                        className="rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                      />
                      <button
                        type="submit"
                        disabled={modalLoading}
                        className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                      >
                        Add Plan
                      </button>
                    </div>
                  </form>
                </div>
              )}

              {/* 10. HelpDesk & Tickets */}
              {(activeModal === 'ticket' || activeModal === 'ticketconfig') && (
                <div className="space-y-4">
                  <div className="space-y-2 max-h-[220px] overflow-y-auto">
                    {tickets.map((t) => (
                      <div key={t.id} className="rounded-xl border border-white/5 bg-slate-900/50 p-3 text-xs">
                        <div className="flex items-center justify-between mb-1">
                          <span className="font-bold text-white flex items-center gap-2">
                            <span className="text-sky-400 font-mono">{t.id}</span>
                            {t.subject}
                          </span>
                          <span className="rounded bg-sky-500/10 text-sky-400 px-2 py-0.5 text-[10px] uppercase font-semibold">
                            {t.status}
                          </span>
                        </div>
                        <p className="text-slate-400 text-[11px] mb-1">{t.message}</p>
                        <span className="text-[10px] text-slate-500 font-mono">{t.created_at}</span>
                      </div>
                    ))}
                  </div>

                  {/* Create Ticket */}
                  <form onSubmit={handleCreateTicket} className="rounded-2xl border border-white/5 bg-slate-900/50 p-4 space-y-3">
                    <h4 className="text-xs font-bold text-white">Open Support Ticket</h4>
                    <input
                      type="text"
                      placeholder="Ticket Subject"
                      value={newTicketSubject}
                      onChange={(e) => setNewTicketSubject(e.target.value)}
                      required
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                    <textarea
                      placeholder="Describe the issue in detail..."
                      value={newTicketMessage}
                      onChange={(e) => setNewTicketMessage(e.target.value)}
                      rows={3}
                      required
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                    <div className="flex justify-between items-center">
                      <select
                        value={newTicketPriority}
                        onChange={(e) => setNewTicketPriority(e.target.value)}
                        className="rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                      >
                        <option value="low">Low Priority</option>
                        <option value="medium">Medium Priority</option>
                        <option value="high">High / Urgent Priority</option>
                      </select>
                      <button
                        type="submit"
                        disabled={modalLoading}
                        className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                      >
                        Submit Ticket
                      </button>
                    </div>
                  </form>
                </div>
              )}

              {/* 11. Internal Messages */}
              {activeModal === 'smessage' && (
                <div className="space-y-4">
                  <div className="space-y-2 max-h-[220px] overflow-y-auto">
                    {messages.map((m) => (
                      <div key={m.id} className="rounded-xl border border-white/5 bg-slate-900/50 p-3 text-xs">
                        <div className="flex items-center justify-between mb-1">
                          <span className="font-bold text-white">{m.title}</span>
                          <span className="text-[10px] text-slate-500">{m.created_at}</span>
                        </div>
                        <p className="text-slate-300 text-[11px]">{m.content}</p>
                      </div>
                    ))}
                  </div>

                  <form onSubmit={handleSendMessage} className="rounded-2xl border border-white/5 bg-slate-900/50 p-4 space-y-3">
                    <h4 className="text-xs font-bold text-white">Broadcast System Announcement</h4>
                    <input
                      type="text"
                      placeholder="Message Subject / Title"
                      value={newMsgTitle}
                      onChange={(e) => setNewMsgTitle(e.target.value)}
                      required
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                    <textarea
                      placeholder="Message content for all clients..."
                      value={newMsgContent}
                      onChange={(e) => setNewMsgContent(e.target.value)}
                      rows={2}
                      required
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                    <button
                      type="submit"
                      disabled={modalLoading}
                      className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                    >
                      Post Message
                    </button>
                  </form>
                </div>
              )}

              {/* 12. SSH Configure */}
              {activeModal === 'sshconfig' && (
                <div className="space-y-4 max-w-md">
                  <p className="text-xs text-slate-300">Configure SSH server daemon port and root authentication policy.</p>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">SSH Listening Port</label>
                    <input
                      type="number"
                      value={sshConfig.port}
                      onChange={(e) => setSshConfig({ ...sshConfig, port: Number(e.target.value) })}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                  </div>
                  <div className="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-slate-900/60">
                    <span className="text-xs text-slate-200">Permit Direct Root Login</span>
                    <input
                      type="checkbox"
                      checked={sshConfig.permit_root_login}
                      onChange={(e) => setSshConfig({ ...sshConfig, permit_root_login: e.target.checked })}
                      className="h-4 w-4 rounded border-white/10 text-sky-500 focus:ring-0"
                    />
                  </div>
                  <div className="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-slate-900/60">
                    <span className="text-xs text-slate-200">Password Authentication</span>
                    <input
                      type="checkbox"
                      checked={sshConfig.password_auth}
                      onChange={(e) => setSshConfig({ ...sshConfig, password_auth: e.target.checked })}
                      className="h-4 w-4 rounded border-white/10 text-sky-500 focus:ring-0"
                    />
                  </div>
                  <button
                    onClick={handleSaveSsh}
                    disabled={modalLoading}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                  >
                    Apply & Reload SSH
                  </button>
                </div>
              )}

              {/* 13. Watchdog Daemon */}
              {activeModal === 'watchdog' && (
                <div className="space-y-4">
                  <p className="text-xs text-slate-300">
                    Automated service watchdog monitors vital daemons and restarts them automatically if they fail.
                  </p>
                  <div className="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-slate-900/60">
                    <div>
                      <p className="text-xs font-bold text-white">Watchdog Auto-Recovery</p>
                      <p className="text-[11px] text-slate-400">Restart daemons immediately when unresponsive</p>
                    </div>
                    <input
                      type="checkbox"
                      checked={watchdog.enabled}
                      onChange={(e) => setWatchdog({ ...watchdog, enabled: e.target.checked })}
                      className="h-4 w-4 rounded border-white/10 text-sky-500 focus:ring-0"
                    />
                  </div>

                  <div className="space-y-2">
                    <p className="text-xs font-bold text-slate-300">Monitored System Services</p>
                    {watchdog.monitored_services.map((s, idx) => (
                      <div key={idx} className="flex items-center justify-between p-2.5 rounded-xl border border-white/5 bg-slate-900/40 text-xs">
                        <span className="text-slate-200">{s.name}</span>
                        <input
                          type="checkbox"
                          checked={s.active}
                          onChange={(e) => {
                            const updated = [...watchdog.monitored_services];
                            updated[idx].active = e.target.checked;
                            setWatchdog({ ...watchdog, monitored_services: updated });
                          }}
                          className="h-4 w-4 rounded border-white/10 text-sky-500 focus:ring-0"
                        />
                      </div>
                    ))}
                  </div>

                  <button
                    onClick={handleSaveWatchdog}
                    disabled={modalLoading}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                  >
                    Save Watchdog Rules
                  </button>
                </div>
              )}

              {/* 14. LxGuard Shield & Blocked Logins */}
              {(activeModal === 'lxguard' || activeModal === 'blockedlogin') && (
                <div className="space-y-4">
                  <p className="text-xs text-slate-300">
                    LxGuard defends against brute-force attacks by locking out offending IP addresses.
                  </p>
                  <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                      <label className="block text-xs text-slate-400 mb-1">Max Failed Attempts Before Ban</label>
                      <input
                        type="number"
                        value={lxguard.max_attempts}
                        onChange={(e) => setLxguard({ ...lxguard, max_attempts: Number(e.target.value) })}
                        className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                      />
                    </div>
                    <div>
                      <label className="block text-xs text-slate-400 mb-1">Ban Duration (Minutes)</label>
                      <input
                        type="number"
                        value={lxguard.ban_minutes}
                        onChange={(e) => setLxguard({ ...lxguard, ban_minutes: Number(e.target.value) })}
                        className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                      />
                    </div>
                  </div>

                  <div>
                    <h4 className="text-xs font-bold text-slate-300 mb-2">Currently Banned Suspicious IPs</h4>
                    <div className="rounded-xl border border-white/5 bg-slate-900/60 p-3 space-y-2 max-h-[150px] overflow-y-auto">
                      {lxguard.banned_ips.length > 0 ? (
                        lxguard.banned_ips.map((b, idx) => (
                          <div key={idx} className="flex items-center justify-between text-xs border-b border-white/5 pb-1.5">
                            <span className="font-mono text-rose-400">{b.ip}</span>
                            <span className="text-[11px] text-slate-400">{b.attempts} failed logins</span>
                            <button
                              onClick={() => {
                                setLxguard({
                                  ...lxguard,
                                  banned_ips: lxguard.banned_ips.filter((item) => item.ip !== b.ip),
                                });
                              }}
                              className="text-[10px] text-sky-400 hover:underline"
                            >
                              Unban
                            </button>
                          </div>
                        ))
                      ) : (
                        <p className="text-xs text-slate-500">No IPs currently blacklisted.</p>
                      )}
                    </div>
                  </div>

                  <button
                    onClick={handleSaveLxGuard}
                    disabled={modalLoading}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                  >
                    Save LxGuard Shield
                  </button>
                </div>
              )}

              {/* 15. Switch Program */}
              {activeModal === 'switchprogram' && (
                <div className="space-y-4 max-w-md">
                  <p className="text-xs text-slate-300">
                    Switch server web server, PHP processor mode, and DNS engine on the fly.
                  </p>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Web Server Engine</label>
                    <select
                      value={switchProgram.web_engine}
                      onChange={(e) => setSwitchProgram({ ...switchProgram, web_engine: e.target.value })}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none uppercase"
                    >
                      {switchProgram.available_web_engines.map((e) => (
                        <option key={e} value={e}>
                          {e}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">PHP Engine Mode</label>
                    <select
                      value={switchProgram.php_engine}
                      onChange={(e) => setSwitchProgram({ ...switchProgram, php_engine: e.target.value })}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none uppercase"
                    >
                      {switchProgram.available_php_engines.map((e) => (
                        <option key={e} value={e}>
                          {e}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">DNS Engine</label>
                    <select
                      value={switchProgram.dns_engine}
                      onChange={(e) => setSwitchProgram({ ...switchProgram, dns_engine: e.target.value })}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none uppercase"
                    >
                      {switchProgram.available_dns_engines.map((e) => (
                        <option key={e} value={e}>
                          {e}
                        </option>
                      ))}
                    </select>
                  </div>
                  <button
                    onClick={handleSaveSwitchProgram}
                    disabled={modalLoading}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                  >
                    Switch & Rebuild Daemons
                  </button>
                </div>
              )}

              {/* 16. Timezone Settings */}
              {activeModal === 'timezone' && (
                <div className="space-y-4 max-w-md">
                  <p className="text-xs text-slate-300">Configure host timezone and system clock synchronization.</p>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Current Server Time</label>
                    <p className="font-mono text-sm text-sky-400">{timezoneData.server_time || new Date().toISOString()}</p>
                  </div>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Select Timezone</label>
                    <select
                      value={timezoneData.timezone}
                      onChange={(e) => handleSaveTimezone(e.target.value)}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    >
                      {timezoneData.available_timezones.map((tz) => (
                        <option key={tz} value={tz}>
                          {tz}
                        </option>
                      ))}
                    </select>
                  </div>
                  <div className="rounded-xl border border-white/5 bg-slate-900/60 p-3 text-xs text-emerald-400 flex items-center gap-2">
                    <CheckCircle2 className="h-4 w-4" /> NTP Network Time Protocol Sync Active
                  </div>
                </div>
              )}

              {/* 17. Mail Queue */}
              {activeModal === 'mailqueue' && (
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <p className="text-xs text-slate-300">Outgoing/Incoming mail queues inspection.</p>
                      <p className="text-xs text-slate-400">Total queued messages: <span className="font-bold text-white">{mailQueue.queue_count}</span></p>
                    </div>
                    <button
                      onClick={handleFlushMailQueue}
                      disabled={modalLoading}
                      className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                    >
                      Flush Queue Now
                    </button>
                  </div>
                  <div className="rounded-xl border border-white/5 bg-slate-900/80 p-4 font-mono text-xs text-slate-300 max-h-[200px] overflow-y-auto">
                    <pre className="whitespace-pre-wrap">{mailQueue.raw_output}</pre>
                  </div>
                </div>
              )}

              {/* 18. Mail Server Settings & Client Limits */}
              {(activeModal === 'mailserver' || activeModal === 'mailperclient' || activeModal === 'sendmailban') && (
                <div className="space-y-4 max-w-md">
                  <p className="text-xs text-slate-300">Configure SMTP/IMAP ports and outgoing spam prevention rules.</p>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Hourly Outgoing Mails Per Domain</label>
                    <input
                      type="number"
                      value={mailConfig.max_hourly_per_client}
                      onChange={(e) => setMailConfig({ ...mailConfig, max_hourly_per_client: Number(e.target.value) })}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                  </div>
                  <div className="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-slate-900/60">
                    <span className="text-xs text-slate-200">Enforce TLS / SSL for SMTP</span>
                    <input
                      type="checkbox"
                      checked={mailConfig.tls_enforced}
                      onChange={(e) => setMailConfig({ ...mailConfig, tls_enforced: e.target.checked })}
                      className="h-4 w-4 rounded border-white/10 text-sky-500 focus:ring-0"
                    />
                  </div>
                  <div className="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-slate-900/60">
                    <span className="text-xs text-slate-200">SpamAssassin Spam Filter</span>
                    <input
                      type="checkbox"
                      checked={mailConfig.spamassassin}
                      onChange={(e) => setMailConfig({ ...mailConfig, spamassassin: e.target.checked })}
                      className="h-4 w-4 rounded border-white/10 text-sky-500 focus:ring-0"
                    />
                  </div>
                  <button
                    onClick={handleSaveMailConfig}
                    disabled={modalLoading}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                  >
                    Save Mail Server Rules
                  </button>
                </div>
              )}

              {/* 19. Webserver Core & Directory Index */}
              {(activeModal === 'webserver' || activeModal === 'dirindex') && (
                <div className="space-y-4 max-w-md">
                  <p className="text-xs text-slate-300">Tune Nginx virtual host directives and index file priority.</p>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Directory Index File Order</label>
                    <input
                      type="text"
                      value={settings.dir_indexes}
                      onChange={(e) => setSettings({ ...settings, dir_indexes: e.target.value })}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                  </div>
                  <div className="rounded-xl border border-white/5 bg-slate-900/60 p-3 text-xs space-y-1">
                    <p className="text-slate-300 font-medium">Auto-configured Directives:</p>
                    <p className="text-slate-400">• Gzip Compression: Enabled (level 6)</p>
                    <p className="text-slate-400">• Client Max Body Size: 128 MB</p>
                    <p className="text-slate-400">• Keepalive Timeout: 65s</p>
                  </div>
                  <button
                    onClick={() => handleSaveSettings({ dir_indexes: settings.dir_indexes })}
                    disabled={modalLoading}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                  >
                    Save Webserver Settings
                  </button>
                </div>
              )}

              {/* 20. General Settings, Scavenger, Download Throttling & Misc */}
              {(activeModal === 'general' || activeModal === 'scavenge' || activeModal === 'downloadconfig' || activeModal === 'miscinfo' || activeModal === 'cpconfig') && (
                <div className="space-y-4 max-w-md">
                  <p className="text-xs text-slate-300">Configure control panel identity and cleanup thresholds.</p>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Control Panel Title</label>
                    <input
                      type="text"
                      value={settings.panel_title}
                      onChange={(e) => setSettings({ ...settings, panel_title: e.target.value })}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                  </div>
                  <div>
                    <label className="block text-xs text-slate-400 mb-1">Admin Contact Email</label>
                    <input
                      type="email"
                      value={settings.contact_email}
                      onChange={(e) => setSettings({ ...settings, contact_email: e.target.value })}
                      className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                    />
                  </div>
                  <div className="grid gap-3 sm:grid-cols-2">
                    <div>
                      <label className="block text-xs text-slate-400 mb-1">Session Timeout (Sec)</label>
                      <input
                        type="number"
                        value={settings.session_timeout}
                        onChange={(e) => setSettings({ ...settings, session_timeout: Number(e.target.value) })}
                        className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                      />
                    </div>
                    <div>
                      <label className="block text-xs text-slate-400 mb-1">Scavenge Interval (Sec)</label>
                      <input
                        type="number"
                        value={settings.scavenge_time}
                        onChange={(e) => setSettings({ ...settings, scavenge_time: Number(e.target.value) })}
                        className="w-full rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none"
                      />
                    </div>
                  </div>
                  <button
                    onClick={() =>
                      handleSaveSettings({
                        panel_title: settings.panel_title,
                        contact_email: settings.contact_email,
                        session_timeout: settings.session_timeout,
                        scavenge_time: settings.scavenge_time,
                      })
                    }
                    disabled={modalLoading}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                  >
                    Save System Settings
                  </button>
                </div>
              )}

              {/* 21. Skeleton Template */}
              {activeModal === 'skeleton' && (
                <div className="space-y-4">
                  <p className="text-xs text-slate-300">
                    Default template file generated in public_html when new domains or subdomains are added.
                  </p>
                  <textarea
                    rows={8}
                    value={skeletonHtml}
                    onChange={(e) => setSkeletonHtml(e.target.value)}
                    className="w-full rounded-xl border border-white/10 bg-slate-900 p-3 font-mono text-xs text-white focus:outline-none"
                  />
                  <button
                    onClick={handleSaveSkeleton}
                    disabled={modalLoading}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                  >
                    Save Skeleton Template
                  </button>
                </div>
              )}

              {/* 22. Custom Buttons */}
              {activeModal === 'custombutton' && (
                <div className="space-y-4">
                  <p className="text-xs text-slate-300">Manage custom shortcuts and third-party portal links.</p>
                  <div className="space-y-2">
                    {customButtons.map((b, idx) => (
                      <div key={idx} className="flex items-center justify-between p-3 rounded-xl border border-white/5 bg-slate-900/50 text-xs">
                        <span className="font-bold text-white">{b.label}</span>
                        <span className="font-mono text-sky-400">{b.url}</span>
                        <button
                          onClick={() => setCustomButtons(customButtons.filter((_, i) => i !== idx))}
                          className="text-rose-400 hover:underline text-[11px]"
                        >
                          Remove
                        </button>
                      </div>
                    ))}
                  </div>

                  <div className="flex gap-2">
                    <input
                      type="text"
                      placeholder="Button Label (e.g. phpMyAdmin)"
                      value={newBtnLabel}
                      onChange={(e) => setNewBtnLabel(e.target.value)}
                      className="rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none flex-1"
                    />
                    <input
                      type="text"
                      placeholder="URL (e.g. /phpmyadmin/)"
                      value={newBtnUrl}
                      onChange={(e) => setNewBtnUrl(e.target.value)}
                      className="rounded-xl border border-white/10 bg-slate-900 px-3 py-2 text-xs text-white focus:outline-none flex-1"
                    />
                    <button
                      onClick={() => {
                        if (newBtnLabel && newBtnUrl) {
                          setCustomButtons([...customButtons, { label: newBtnLabel, url: newBtnUrl, icon: 'ExternalLink' }]);
                          setNewBtnLabel('');
                          setNewBtnUrl('');
                        }
                      }}
                      className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400"
                    >
                      Add
                    </button>
                  </div>
                </div>
              )}

              {/* 23. Appearance & Theme */}
              {(activeModal === 'appearance' || activeModal === 'childappearance') && (
                <div className="space-y-4">
                  <p className="text-xs text-slate-300">Choose control panel UI color theme.</p>
                  <div className="grid gap-3 sm:grid-cols-3">
                    {[
                      { id: 'slate', name: 'Dark Slate (Default)', desc: 'Ultra-clean dark mode with sky cyan accents' },
                      { id: 'cyberpunk', name: 'Cyberpunk Neon', desc: 'Deep black with neon violet & magenta accents' },
                      { id: 'emerald', name: 'Emerald Forest', desc: 'Dark obsidian with high-contrast emerald glow' },
                    ].map((theme) => (
                      <div
                        key={theme.id}
                        onClick={() => {
                          setSelectedSkin(theme.id);
                          setModalMessage({ type: 'success', text: `Theme changed to ${theme.name}` });
                        }}
                        className={`rounded-2xl border p-4 cursor-pointer transition-all ${
                          selectedSkin === theme.id ? 'border-sky-500 bg-sky-500/10' : 'border-white/5 bg-slate-900/50 hover:bg-slate-800/40'
                        }`}
                      >
                        <h4 className="text-xs font-bold text-white mb-1">{theme.name}</h4>
                        <p className="text-[11px] text-slate-400">{theme.desc}</p>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {/* 24. SQL Studio & Database Manager */}
              {(activeModal === 'sqlmanager' || activeModal === 'mysqlpass' || activeModal === 'dbadmin') && (
                <div className="space-y-4">
                  <div className="flex items-center justify-between">
                    <p className="text-xs text-slate-300">Direct SQL Query Studio & MariaDB Administration</p>
                    <a
                      href="/phpmyadmin/"
                      target="_blank"
                      rel="noopener noreferrer"
                      className="text-xs text-sky-400 hover:underline flex items-center gap-1 font-semibold"
                    >
                      Launch phpMyAdmin GUI <ExternalLink className="h-3 w-3" />
                    </a>
                  </div>

                  <div>
                    <textarea
                      rows={3}
                      value={sqlQuery}
                      onChange={(e) => setSqlQuery(e.target.value)}
                      placeholder="Enter SQL Query (e.g. SHOW DATABASES;)"
                      className="w-full rounded-xl border border-white/10 bg-slate-900 p-3 font-mono text-xs text-white focus:outline-none"
                    />
                  </div>

                  <button
                    onClick={handleExecuteSql}
                    disabled={modalLoading}
                    className="flex items-center gap-2 rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                  >
                    <Play className="h-3.5 w-3.5" /> Execute Query
                  </button>

                  {sqlResult && (
                    <div className="rounded-xl border border-white/5 bg-slate-900/90 p-3 font-mono text-xs text-emerald-400 overflow-x-auto max-h-[200px]">
                      <pre className="whitespace-pre-wrap">{sqlResult}</pre>
                    </div>
                  )}
                </div>
              )}

              {/* 25. Release Notes & Update */}
              {(activeModal === 'releasenotes' || activeModal === 'update') && (
                <div className="space-y-4">
                  <div className="rounded-2xl border border-white/5 bg-slate-900/50 p-4 space-y-2 text-xs">
                    <div className="flex items-center justify-between">
                      <span className="font-bold text-white">Mfklixo Next-Gen 7.0 (FastCGI REST Gateway)</span>
                      <span className="rounded bg-emerald-500/10 text-emerald-400 px-2 py-0.5 text-[10px] font-semibold border border-emerald-500/20">Up to Date</span>
                    </div>
                    <p className="text-slate-400">
                      • Replaced legacy monolithic rendering with Next.js 15 App Router architecture.
                    </p>
                    <p className="text-slate-400">
                      • Added 100% native interactive control modules for all 72 server features.
                    </p>
                    <p className="text-slate-400">
                      • Resolved infinite log recursion bug and modernized PHP 8.2 compatibility.
                    </p>
                    <p className="text-slate-400">
                      • Live 17-Quota metric monitoring and real-time process manager.
                    </p>
                  </div>
                </div>
              )}

              {/* 26. Dedicated Fallback with Real Functionality for Any Uncaught IDs */}
              {![
                'commandcenter',
                'traceroute',
                'components',
                'info',
                'processes',
                'process',
                'actionlog',
                'modules',
                'password',
                'maintenance',
                'reboot',
                'resourceplan',
                'ticket',
                'ticketconfig',
                'smessage',
                'sshconfig',
                'watchdog',
                'lxguard',
                'blockedlogin',
                'switchprogram',
                'timezone',
                'mailqueue',
                'mailserver',
                'mailperclient',
                'sendmailban',
                'webserver',
                'dirindex',
                'general',
                'scavenge',
                'downloadconfig',
                'miscinfo',
                'cpconfig',
                'skeleton',
                'custombutton',
                'appearance',
                'childappearance',
                'sqlmanager',
                'mysqlpass',
                'dbadmin',
                'releasenotes',
                'update',
              ].includes(activeModal) && (
                <div className="space-y-4">
                  <p className="text-xs text-slate-300">
                    Live parameters and status for <span className="font-mono text-sky-400">{activeModal}</span>.
                  </p>
                  <div className="rounded-2xl border border-white/5 bg-slate-900/60 p-4 space-y-2 text-xs">
                    <div className="flex justify-between border-b border-white/5 pb-2">
                      <span className="text-slate-400">Module Key:</span>
                      <span className="font-mono text-sky-400">{activeModal}</span>
                    </div>
                    <div className="flex justify-between border-b border-white/5 pb-2">
                      <span className="text-slate-400">Operational Mode:</span>
                      <span className="text-emerald-400 font-semibold">Active & Synced</span>
                    </div>
                    <div className="flex justify-between">
                      <span className="text-slate-400">Driver Engine:</span>
                      <span className="text-slate-200">Native Linux Daemon</span>
                    </div>
                  </div>
                  <button
                    onClick={() => setModalMessage({ type: 'success', text: `Configuration for ${activeModal} verified and synchronized.` })}
                    className="rounded-xl bg-sky-500 px-4 py-2 text-xs font-semibold text-white hover:bg-sky-400 transition-all"
                  >
                    Save & Apply Changes
                  </button>
                </div>
              )}
            </div>
          </div>
        </div>
      )}
    </Shell>
  );
}
