# Credenciales de Acceso - BarberSoft

## Panel de Administración Filament

**URL:** `http://127.0.0.1:8000/admin`  
**Email:** `admin@barbersoft.com`  
**Contraseña:** `admin123`

### ¿Por qué Filament?

Filament es un panel de administración moderno para Laravel que incluye:
- ✅ Login incorporado (Laravel/Filament auth)
- ✅ CRUD completo para tus modelos
- ✅ UI moderna y responsive
- ✅ Widgets y Dashboard
- ✅ Exports, imports, filtros avanzados
---

## Cambiar Contraseña

Para cambiar la contraseña del administrador, ejecuta en la terminal:

```bash
php artisan tinker
```

Luego escribe:

```php
$user = App\Models\User::where('email', 'admin@barbersoft.com')->first();
$user->password = bcrypt('tu_nueva_contraseña');
$user->save();
```

---

## Sistema de Autenticación

Este proyecto usa **Filament Panel** como sistema de administración completo:

- **Backend:** Laravel 10 con Filament 3
- **Login:** Filament Authentication (en la raíz `/`)
- **Panel:** Filament Admin Panel

### Rutas principales:

- `/` - Dashboard de Filament
- `/login` - Login de Filament
- `/barberos` - Gestión de barberos
- `/citas` - Gestión de citas
- `/servicios` - Gestión de servicios

### Notas de Seguridad:

1. **NUNCA** commitear este archivo con credenciales reales en producción
2. Cambiar la contraseña por defecto antes de desplegar
3. Considera usar autenticación de dos factores en producción

---

## Estructura del Proyecto

- **Filament Resources:** `app/Filament/Admin/Resources/`
- **Modelos:** `app/Models/`
- **Base de datos:** Configurada en `.env` (MySQL)
- **Seeders:** `database/seeders/DatabaseSeeder.php`
