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

export function getStoredToken(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem('mfklixo_token');
}

export function setStoredToken(token: string | null) {
  if (typeof window === 'undefined') return;
  if (token) {
    localStorage.setItem('mfklixo_token', token);
  } else {
    localStorage.removeItem('mfklixo_token');
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
