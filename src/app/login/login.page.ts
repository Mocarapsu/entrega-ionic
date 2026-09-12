import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { IonContent } from '@ionic/angular';
import { AuthService } from '../services/auth.service';

@Component({
  selector: 'app-login',
  templateUrl: './login.page.html',
  styleUrls: ['./login.page.scss'],
  standalone: true,
  imports: [IonContent, CommonModule, FormsModule],
})
export class LoginPage {
  username = '';
  password = '';

  userFocus = false;
  passFocus = false;

  isTest = false;
  isTestTwo = false;

  isAuthenticating = false;
  isAuthenticatingVisible = false;

  hideForm = false;
  isSuccess = false;

  errorMessage = '';

  constructor(private authService: AuthService, private router: Router) {}

  async doLogin() {
    this.errorMessage = '';

    if (!this.username || !this.password) {
      this.errorMessage = 'Ingresa tu usuario y contraseña.';
      return;
    }

    this.isTest = true;

    setTimeout(() => {
      this.isTestTwo = true;
    }, 300);

    setTimeout(() => {
      this.isAuthenticating = true;
      setTimeout(() => (this.isAuthenticatingVisible = true), 50);
    }, 500);

    // Llamada real a la API de PHP vía Axios
    const respuesta = await this.authService.login(this.username, this.password);

    setTimeout(() => {
      this.isAuthenticatingVisible = false;
      this.isTestTwo = false;
    }, 2500);

    setTimeout(() => {
      this.isTest = false;
      this.isAuthenticating = false;

      if (respuesta.success) {
        this.hideForm = true;
      } else {
        this.errorMessage = respuesta.message || 'Usuario o contraseña incorrectos.';
      }
    }, 2800);

    if (respuesta.success) {
      setTimeout(() => {
        this.isSuccess = true;
      }, 3200);

      setTimeout(() => {
        this.router.navigateByUrl('/tabs/tab1');
      }, 4000);
    }
  }
}
