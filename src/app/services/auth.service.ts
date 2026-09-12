import { Injectable } from '@angular/core';
import axios, { AxiosInstance } from 'axios';

// Cambia esta URL por la de tu servidor donde subas la carpeta /api-php
// Ejemplo local con XAMPP/WAMP: 'http://localhost/api-php'
// Ejemplo con dispositivo/emulador real, usa la IP de tu PC, no "localhost"
const API_URL = 'http://localhost/api-php';

export interface Usuario {
  id: number;
  username: string;
  nombre: string;
  email: string;
}

export interface LoginResponse {
  success: boolean;
  message: string;
  usuario?: Usuario;
  token?: string;
}

@Injectable({
  providedIn: 'root',
})
export class AuthService {
  private http: AxiosInstance = axios.create({
    baseURL: API_URL,
    headers: { 'Content-Type': 'application/json' },
  });

  private usuarioActual: Usuario | null = null;

  async login(username: string, password: string): Promise<LoginResponse> {
    try {
      const { data } = await this.http.post<LoginResponse>('/login.php', {
        username,
        password,
      });

      if (data.success && data.usuario) {
        this.usuarioActual = data.usuario;
        if (data.token) {
          localStorage.setItem('token', data.token);
        }
        localStorage.setItem('usuario', JSON.stringify(data.usuario));
      }

      return data;
    } catch (error: any) {
      // Error de red, servidor caído, CORS, etc.
      return {
        success: false,
        message:
          error?.response?.data?.message ??
          'No se pudo conectar con el servidor. Intenta de nuevo.',
      };
    }
  }

  logout() {
    this.usuarioActual = null;
    localStorage.removeItem('token');
    localStorage.removeItem('usuario');
  }

  getUsuario(): Usuario | null {
    if (this.usuarioActual) return this.usuarioActual;
    const raw = localStorage.getItem('usuario');
    return raw ? JSON.parse(raw) : null;
  }

  isAuthenticated(): boolean {
    return !!localStorage.getItem('token');
  }
}
