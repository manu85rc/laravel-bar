# Ocultar enlaces del menú admin según permisos

**Archivo:** `resources/views/admin/partials/sidebar.blade.php`

## Cambios a realizar

### 1. Agregar variable helper `$isAdmin` en el bloque PHP inicial

```diff
     $pedidosActivos = \App\Models\Pedido::whereNotIn('estado', ['pagado', 'cancelado'])->count();
+    $isAdmin = $user->isAdmin() || $user->isSuperAdmin();
 @endphp
```

### 2. Ocultar Dashboard (solo admin/super_admin)

```diff
+        @if ($isAdmin)
         <a href="{{ route('admin.dashboard') }}" class="nav-item{{ request()->routeIs('admin.dashboard') ? ' active' : '' }}">
             <i class="fa-solid fa-gauge-high"></i> Dashboard
         </a>
+        @endif
```

### 3. Ocultar Mesas (solo admin/super_admin)

```diff
+        @if ($isAdmin)
         <a href="{{ route('admin.mesas.index') }}" class="nav-item{{ request()->routeIs('admin.mesas.*') ? ' active' : '' }}">
             <i class="fa-solid fa-table-cells"></i> Mesas
         </a>
+        @endif
```

### 4. Ocultar sección "Barra" completa (solo admin/super_admin)

```diff
+    @if ($isAdmin)
     <div class="nav-section">
         <div class="nav-label">Barra</div>
         <a href="{{ route('admin.menu.index') }}" ...>
         <a href="{{ route('admin.productos.index') }}" ...>
         <a href="{{ route('admin.categorias.index') }}" ...>
     </div>
+    @endif
```

### 5. Ocultar Usuarios (solo admin/super_admin)

```diff
+    @if ($isAdmin)
     <div class="nav-section">
         <div class="nav-label">Personal</div>
         <a href="{{ route('admin.users.index') }}" ...>
     </div>
+    @endif
```

### 6. Ocultar Branding (solo admin/super_admin)

```diff
+    @if ($isAdmin)
     <div class="nav-section">
         <div class="nav-label">Config</div>
         <a href="{{ route('admin.branding.edit') }}" ...>
     </div>
+    @endif
```

## Resultado esperado

| Rol | Ve en el sidebar |
|---|---|
| **admin / super_admin** | Todo (Dashboard, Reservas, Pedidos, Mesas, Menú, Productos, Categorías, Usuarios, Branding) |
| **camarero** | Solo Reservas y Pedidos |
| **user** | Solo Reservas y Pedidos |
