# Build Your First Ionic App: Photo Gallery (Ionic Angular and Capacitor)
# Aplicación Ionic + Angular

**Objetivo de la aplicación**
Aplicación móvil con sistema de autenticación conectado a una base de datos propia, diseñada para gestionar perfiles de usuario.

**Vistas Incluidas**
* **Login:** Pantalla principal de acceso con validación de credenciales.
* **Tab 1:** Interfaz principal tras el inicio de sesión.
* **Tab 2:** Galería o sección secundaria.
* **Tab 3:** Sección de configuración o vista adicional.

**Modelo Inicial de Datos**
El proyecto incluye el modelo relacional exportado en `database.sql`, el cual es consumido por los scripts `db.php`, `login.php` y `usuarios.php` mediante el backend.

## Evidencia de IA y Resolución de Errores

| Prompt Utilizado | Código Generado por IA | Decisión y Explicación del Error |
| :--- | :--- | :--- |
| *"Requiero que para mi aplicacion ionic angular ngmodules, me migres la vista de tab1page hacia la de 'login', [...] agregalo a mi aap routes; requiero tambien una API de php que conecte el login mediante AXIOS y asu vez dame el SQL de las tablas"* | Componente `login.page.ts`, actualización de `app.routes.ts`, scripts PHP y archivo `database.sql`. | **Modificado:** La IA proporcionó la estructura base. Adapté el código para usar los módulos HTTP nativos de Angular en lugar de Axios y configuré los datos de conexión local en el PHP. |
| *"Tengo un proyecto de ionic angular template my first app, requiero convertir el html,css y js a versiones compatibles siendo: [Código HAML, SCSS y animaciones jQuery]. Estos irían en el componente de Tab1Page"* | Traducción del código HAML a etiquetas de Ionic, SCSS y lógica TypeScript con variables de estado. | **Modificado con corrección de errores:** La IA generó el código, pero **la terminal marcó un error de compilación** porque la vista HTML utilizaba directivas como `[(ngModel)]` y `[ngClass]` sin que la IA importara los módulos necesarios en el componente Standalone. **Solución:** Arreglé el código importando manualmente `FormsModule` y `CommonModule` desde `@angular/forms` y `@angular/common` en el archivo TypeScript para que el proyecto pudiera desplegarse correctamente. |