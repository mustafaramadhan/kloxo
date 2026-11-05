/**
 * Mfklixo REST API Client
 */

const API_BASE = '/api/v1';

export interface SystemStatus {
  hostname: string;
  os: string;
  uptime_seconds: number;
  load_average: { '1m': number; '5m': number; '15m': number };
  cpu: { cores: number; percent: number };
  memory: { total_bytes: number; used_bytes: number; free_bytes: number; percent: number };
  disk: { total_bytes: number; used_bytes: number; free_bytes: number; percent: number };
}

export interface ServiceItem {
  service: string;
  title: string;
  active: boolean;
  state: string;
}

export interface DomainItem {
  domain: string;
  owner: string;
  status: boolean;
  web_server: string;
  dns_server: string;
  mail_server: string;
}

export interface DatabaseItem {
  name: string;
  username: string;
  owner: string;
  usage_mb: number;
}

export interface IpItem {
  interface: string;
  ip: string;
  netmask: string;
  status: boolean;
}

export interface MailAccountItem {
  email: string;
  owner: string;
  status: boolean;
  used_mb: number;
  quota_mb: number | string;
}

export interface FtpUserItem {
  username: string;
  directory: string;
  owner: string;
  status: boolean;
  disk_usage_mb: number;
}

export interface ClientItem {
  username: string;
  type: string;
  email: string;
  status: boolean;
  domains_used: number;
  domains_quota: string | number;
  disk_used_mb: number;
}

export interface CronJobItem {
  id: string;
  owner: string;
  command: string;
  type: string;
  schedule: string;
}

export interface FileItem {
  name: string;
  path: string;
  is_directory: boolean;
  size_bytes: number;
  permissions: string;
  modified: string;
}

export interface FileListResponse {
  success: boolean;
  current_path: string;
  parent_path: string | null;
  items: FileItem[];
}

export interface PhpInfoData {
  version: string;
  sapi: string;
  memory_limit: string;
  max_execution_time: string;
  upload_max_filesize: string;
  post_max_size: string;
  opcache_enabled: boolean;
  extensions: string[];
}

export interface LogResponse {
  success: boolean;
  service: string;
  file: string;
  lines: string[];
}

export interface DnsZoneItem {
  domain: string;
  soanameserver: string;
  ttl: number;
  zone_type: string;
}

export interface DnsRecordItem {
  id: string;
  type: string;
  hostname: string;
  value: string;
  priority?: number;
  ttl: number;
}

export interface SslCertItem {
  id: string;
  certname: string;
  domain: string;
  upload_status: string;
  has_crt: boolean;
  has_key: boolean;
}

export interface FirewallRuleItem {
  id: string;
  port: number;
  action: 'allow' | 'deny';
  protocol: string;
  status: boolean;
  description: string;
}

export interface BlockedIpItem {
  id: string;
  ip: string;
}

export interface BackupItem {
  filename: string;
  path: string;
  size_bytes: number;
  size_formatted: string;
  created_at: string;
}

export interface PhpConfigDirectives {
  memory_limit: string;
  upload_max_filesize: string;
  post_max_size: string;
  max_execution_time: string;
  max_input_time: string;
  display_errors: string;
}

export interface PhpConfigResponse {
  success: boolean;
  php_version: string;
  directives: PhpConfigDirectives;
  extensions: string[];
}

export interface SubdomainItem {
  subdomain: string;
  parent_domain: string;
  docroot: string;
  status: boolean;
}

export interface MailForwarderItem {
  id: string;
  source: string;
  destination: string;
}

export interface SqlQueryResult {
  success: boolean;
  type: 'select' | 'mutation';
  columns?: string[];
  rows?: Record<string, any>[];
  total_rows?: number;
  affected_rows?: number;
  duration_ms: number;
  message?: string;
}

export interface ServerInfo {
  home_dir: string;
  contact_email: string;
  username: string;
  default_domain: string;
  last_login_ip: string;
  current_login_ip: string;
  last_login_time: string;
  skin: string;
  maintenance?: boolean;
}

export interface QuotaItem {
  key: string;
  title: string;
  used: number;
  max: number | string;
  unit: string;
  category: string;
}

export interface QuotasResponse {
  success: boolean;
  server_info: ServerInfo;
  quotas: QuotaItem[];
}

export interface ComponentItem {
  name: string;
  version: string;
  status: string;
  type: string;
}

export interface ProcessItem {
  pid: number;
  user: string;
  cpu: number;
  mem: number;
  stat: string;
  time: string;
  command: string;
}

export interface CommandCenterResponse {
  success: boolean;
  tool: string;
  target: string;
  output: string;
}

export interface ActionLogItem {
  log: string;
  timestamp: string;
}

export interface ModuleItem {
  name: string;
  version: string;
  active: boolean;
}

export function getStoredToken(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem('mfklixo_token');
}

export function setStoredToken(token: string | null) {
  if (typeof window === 'undefined') return;
  if (token) {
    localStorage.setItem('mfklixo_token', token);
    document.cookie = `kloxo_token=${encodeURIComponent(token)}; path=/; max-age=2592000; SameSite=Lax`;
  } else {
    localStorage.removeItem('mfklixo_token');
    document.cookie = 'kloxo_token=; path=/; max-age=0; expires=Thu, 01 Jan 1970 00:00:01 GMT';
  }
}

export async function apiFetch<T>(endpoint: string, options: RequestInit = {}): Promise<T> {
  const token = getStoredToken();
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    ...(options.headers as Record<string, string> || {}),
  };

  if (token) {
    headers['Authorization'] = `Bearer ${token}`;
  }

  const res = await fetch(`${API_BASE}${endpoint}`, {
    ...options,
    headers,
  });

  if (res.status === 401) {
    setStoredToken(null);
    if (typeof window !== 'undefined' && !window.location.pathname.startsWith('/login')) {
      window.location.href = '/login';
    }
    throw new Error('Unauthorized');
  }

  const data = await res.json();
  if (!res.ok) {
    throw new Error(data.message || data.error || 'API Request failed');
  }

  return data;
}

export function formatBytes(bytes: number, decimals = 1): string {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const dm = decimals < 0 ? 0 : decimals;
  const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

export function formatUptime(seconds: number): string {
  const days = Math.floor(seconds / 86400);
  const hours = Math.floor((seconds % 86400) / 3600);
  const minutes = Math.floor((seconds % 3600) / 60);
  if (days > 0) return `${days}d ${hours}h`;
  if (hours > 0) return `${hours}h ${minutes}m`;
  return `${minutes}m`;
}
