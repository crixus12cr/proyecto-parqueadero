// comandos para crear componentes, interfaces y repositorios y clases form

# 1. Crear el componente Livewire principal
php artisan make:livewire Admin/Administracion/RolesIndex

# 2. Crear los Form Requests
php artisan make:request Admin/Administracion/CrearRolRequest
php artisan make:request Admin/Administracion/ActualizarRolRequest

# 3. Crear interfaces y repositorios
php artisan make:interface Repositories/Interfaces/RolRepositoryInterface
php artisan make:class Repositories/Eloquent/RolRepository