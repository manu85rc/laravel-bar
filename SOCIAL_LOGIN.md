# Login social — Google y Facebook

## Qué quedó implementado

| Pieza | Archivo |
|---|---|
| Provider (Google, Facebook) | `laravel/socialite` v5.31 |
| Credenciales | `config/services.php` → `google`, `facebook` |
| Tabla | `social_accounts` (unique `provider`+`provider_id`, FK cascade) |
| Rutas | `GET /auth/{provider}/redirect`, `GET|POST /auth/{provider}/callback` |
| Controlador | `app/Http/Controllers/Auth/SocialiteController.php` |
| Vistas | `auth/login.blade.php`, `welcome.blade.php` (modal), `profile/edit.blade.php` |
| Tests | `tests/Feature/SocialAuthTest.php` (15 casos) |

**Decisiones de seguridad aplicadas**

- Una cuenta creada por login social **siempre** nace con rol `user` (cliente). Nunca admin ni staff.
- Si el correo del proveedor ya pertenece a un `super_admin`/`admin`/`staff`, **no** se auto-vincula: se rechaza con un mensaje que obliga a entrar con contraseña y vincular desde `/profile`. Evita toma de cuentas privilegiadas.
- Un usuario con contraseña puede vincular Google/Facebook desde su perfil; no puede vincular una cuenta ya unida a otro usuario ni desvincular su última forma de acceso.
- `state` de OAuth verificado por Socialite; `SESSION_SECURE_COOKIE=false` en `.env` porque no hay TLS en local (sin eso el callback falla siempre).

---

## 1. Google

1. Ve a <https://console.cloud.google.com> → crea o elige un proyecto.
2. **APIs & Services → OAuth consent screen**.
   - User type: **External**.
   - App name: el que quieras (p. ej. *The Still*).
   - Scopes: deja el de por defecto.
   - **Test users → + ADD USERS → añade tu propio correo de Gmail.** Es obligatorio: una app en estado *Testing* solo deja entrar a los testers, y sin *Publish* Google avisa "App is not verified" al primer login.
3. **APIs & Services → Credentials → Create credentials → OAuth client ID**.
   - Application type: **Web application**.
   - Name: `The Still local`.
- **Authorized redirect URIs** → **+ ADD URI** (una por cada host por el que entres):
      ```
      https://bar.casualred.com/auth/google/callback
      http://localhost:8000/auth/google/callback
      ```
      Añade también `http://127.0.0.1:8000/auth/google/callback` si alguna vez entras por esa vía.
4. Copia **Client ID** y **Client secret** a `.env`:
   ```dotenv
   GOOGLE_CLIENT_ID=xxxxx.apps.googleusercontent.com
   GOOGLE_CLIENT_SECRET=GOCSPX-xxxxx
   GOOGLE_REDIRECT_URI=
   ```
   Deja `GOOGLE_REDIRECT_URI` **vacío**: producción y desarrollo comparten el mismo `.env` y el mismo cliente OAuth, así que la URI se calcula sola con `route()` y se adapta al host (HTTPS en producción, HTTP en local).
5. `docker exec laravel_bar_app php artisan config:clear` — solo si tensías la config cacheada (`bootstrap/cache/config.php`).

> Si necesitas fijar la URI a mano, `GOOGLE_REDIRECT_URI` tiene prioridad sobre el cálculo automático. Ojo: una sola URL fija rompe el login del otro entorno.

> Google **no acepta IPs privadas** (`http://192.168.x.x:8000/...`) en las redirect URIs de clientes Web. Para probar el login de Google desde el móvil en la LAN, entra por `https://bar.casualred.com` o por un túnel.

---

## 2. Facebook

1. Ve a <https://developers.facebook.com> → **Create app** → elige el caso de uso *Other* → app tipo **Consumer**.
2. En el panel, busca **Facebook Login → Set Up** (o *Add product*).
3. **Facebook Login → Settings**:
- **Valid OAuth Redirect URIs**:
      ```
      https://bar.casualred.com/auth/facebook/callback
      http://localhost:8000/auth/facebook/callback
      ```
   - **Domains**: `bar.casualred.com`, `localhost`
4. Copia **App ID** y **App Secret** (menú *App Settings → Basic*) a `.env`:
   ```dotenv
   FACEBOOK_CLIENT_ID=1234567890123456
   FACEBOOK_CLIENT_SECRET=abcdef0123456789abcdef0123456789
   ```
5. `docker exec laravel_bar_app php artisan config:clear`

Facebook exige que la app esté **Live** o que tu cuenta esté en *App Roles* para que el login funcione. En modo *Development* solo funcionan los testers (rol *Administrator*/*Developer* de la app).

---

## 3. Probar

1. Abre <http://localhost:8000/login> o <https://bar.casualred.com/login>.
2. Los botones de Google/Facebook aparecen **solo** si el proveedor tiene `client_id` + `client_secret`; si no, el bloque "o continúa con" desaparece entero.
3. Al primer login se crea el usuario con rol `user` y caes en `home`. Si venías del modal de reservas, cae en `home` con el modal de reserva abierto.
4. Staff/admin siguen entrando con email + contraseña en `/login`.
5. Para depurar sin navegador:
   ```bash
   curl -sI http://localhost:8000/auth/google/redirect | grep -i '^location:'
   ```
   Comprueba que `redirect_uri` apunta al `/auth/google/callback` del host que estás usando. Si Google devuelve `redirect_uri_mismatch`, es que esa URI no está registrada en la consola.

---

## 4. Cuando llegue Apple

Hace falta, además:

- Membresía de **Apple Developer** ($99/año) y un Service ID.
- HTTPS obligatorio en la URL de retorno (no funciona en `http://localhost`).
- `socialiteproviders/apple:^6.1` (genera el `client_secret` JWT desde tu `.p8`).
- El callback de Apple llega por **POST** (`form_post`) — la ruta ya acepta `GET|POST`, así que esa parte está lista.
- Apple a veces no devuelve el email: la arquitectura ya lo contempla con un correo placeholder y usando el `sub` como identidad primaria.

Lo único pendiente entonces es el provider, el listener en `AppServiceProvider::boot()` y el botón.