<div class="subnav">
  <a href="{{ route('admin.inventory.dashboard') }}" class="{{ request()->routeIs('admin.inventory.dashboard') ? 'active' : '' }}">Dashboard</a>
  <a href="{{ route('admin.inventory.items.index') }}" class="{{ request()->routeIs('admin.inventory.items.*') ? 'active' : '' }}">Event Inventory</a>
  <a href="{{ route('admin.inventory.vans.index') }}" class="{{ request()->routeIs('admin.inventory.vans.*') ? 'active' : '' }}">Van Inventory</a>
  <a href="{{ route('admin.inventory.movements.index') }}" class="{{ request()->routeIs('admin.inventory.movements.*') ? 'active' : '' }}">Stock Movements</a>
  <a href="{{ route('admin.inventory.alerts.index') }}" class="{{ request()->routeIs('admin.inventory.alerts.*') ? 'active' : '' }}">Low Stock Alerts</a>
</div>
