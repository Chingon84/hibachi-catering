@props([
    'groups' => [],
    'buttonLabel' => 'New Feedback',
])

@php
    $menuId = 'new-feedback-menu-' . \Illuminate\Support\Str::uuid();
    $buttonId = $menuId . '-button';
@endphp

<div class="relative inline-flex" data-feedback-menu>
  <button
    type="button"
    id="{{ $buttonId }}"
    data-feedback-menu-trigger
    aria-haspopup="menu"
    aria-expanded="false"
    aria-controls="{{ $menuId }}"
    class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-sm transition-all duration-150 hover:border-gray-300 hover:bg-gray-50 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2"
  >
    <svg class="size-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
    </svg>
    <span>{{ $buttonLabel }}</span>
    <svg class="size-4 text-gray-400 transition-transform duration-150" data-feedback-menu-chevron viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
      <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 10.94l3.71-3.71a.75.75 0 1 1 1.06 1.06l-4.24 4.25a.75.75 0 0 1-1.06 0L5.21 8.29a.75.75 0 0 1 .02-1.08Z" clip-rule="evenodd" />
    </svg>
  </button>

  <div
    id="{{ $menuId }}"
    data-feedback-menu-panel
    role="menu"
    aria-labelledby="{{ $buttonId }}"
    tabindex="-1"
    class="pointer-events-none absolute right-0 top-full z-30 mt-2 hidden w-64 origin-top-right rounded-xl border border-gray-200 bg-white py-2 opacity-0 shadow-lg scale-95 transition-all duration-150 ease-out"
  >
    @foreach ($groups as $label => $items)
      <div class="{{ $loop->first ? '' : 'border-t border-gray-100 my-2 pt-2' }}">
        <p class="px-4 pt-2 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-400">
          {{ $label }}
        </p>

        <div>
          @foreach ($items as $item)
            @php
                $icon = $item['icon'] ?? 'square-3-stack-3d';
                $iconClass = $item['icon_class'] ?? 'text-gray-500';
            @endphp

            <a
              href="{{ $item['href'] }}"
              role="menuitem"
              class="flex items-center gap-3 rounded-md px-4 py-1.5 text-sm font-medium text-gray-700 transition duration-150 hover:bg-gray-100 focus:bg-gray-100 focus:outline-none"
            >
              <svg class="h-4 w-4 shrink-0 {{ $iconClass }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                @switch($icon)
                  @case('alert-triangle')
                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 3.86-8.23 14.25a1.5 1.5 0 0 0 1.3 2.25h16.36a1.5 1.5 0 0 0 1.3-2.25L13.75 3.86a1.5 1.5 0 0 0-2.5 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 3h.01" />
                    @break
                  @case('thumb-up')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 10.5V19.5m0-9 4.31-6.47a1.13 1.13 0 0 1 2.06.63V7.5h4.26a1.5 1.5 0 0 1 1.48 1.77l-1.2 7.2a1.5 1.5 0 0 1-1.48 1.23H7.5m0-7.2H5.25A1.5 1.5 0 0 0 3.75 12v6A1.5 1.5 0 0 0 5.25 19.5H7.5" />
                    @break
                  @case('award')
                    <circle cx="12" cy="8.5" r="3.5" stroke-linecap="round" stroke-linejoin="round" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 12.5 7 21l5-2.5L17 21l-1.5-8.5" />
                    @break
                  @case('truck')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M1.5 6.75h11.25v8.25H1.5zM12.75 9h3.63a1.5 1.5 0 0 1 1.2.6l2.17 2.9v2.5h-7" />
                    <circle cx="6" cy="17.25" r="1.75" stroke-linecap="round" stroke-linejoin="round" />
                    <circle cx="17.25" cy="17.25" r="1.75" stroke-linecap="round" stroke-linejoin="round" />
                    @break
                  @case('clock')
                    <circle cx="12" cy="12" r="8.25" stroke-linecap="round" stroke-linejoin="round" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5v5.25l3 1.5" />
                    @break
                  @case('car')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 15.75h13.5l-1.28-5.12A2.25 2.25 0 0 0 15.3 9H8.7a2.25 2.25 0 0 0-2.18 1.63l-1.27 5.12Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75v1.5a.75.75 0 0 0 .75.75h1.5a.75.75 0 0 0 .75-.75v-1.5m9 0v1.5a.75.75 0 0 0 .75.75h1.5a.75.75 0 0 0 .75-.75v-1.5" />
                    <circle cx="7.5" cy="15.75" r=".75" fill="currentColor" stroke="none" />
                    <circle cx="16.5" cy="15.75" r=".75" fill="currentColor" stroke="none" />
                    @break
                  @case('clipboard-document-list')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75h6m-7.5 3h9a1.5 1.5 0 0 1 1.5 1.5v10.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 6 18.75V8.25a1.5 1.5 0 0 1 1.5-1.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 11.25h6m-6 3h4.5" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3h4.5A.75.75 0 0 1 15 3.75v1.5a.75.75 0 0 1-.75.75h-4.5A.75.75 0 0 1 9 5.25v-1.5A.75.75 0 0 1 9.75 3Z" />
                    @break
                  @default
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15m-15 5.25h15m-15 5.25h15" />
                @endswitch
              </svg>

              <span class="min-w-0 flex-1 truncate">{{ $item['label'] }}</span>
            </a>
          @endforeach
        </div>
      </div>
    @endforeach
  </div>
</div>

@once
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      document.querySelectorAll('[data-feedback-menu]').forEach((menu) => {
        if (menu.dataset.feedbackMenuReady === 'true') {
          return;
        }

        menu.dataset.feedbackMenuReady = 'true';

        const trigger = menu.querySelector('[data-feedback-menu-trigger]');
        const panel = menu.querySelector('[data-feedback-menu-panel]');
        const chevron = menu.querySelector('[data-feedback-menu-chevron]');
        let hideTimer = null;

        const items = () => Array.from(panel.querySelectorAll('[role="menuitem"]'));
        const isOpen = () => trigger.getAttribute('aria-expanded') === 'true';

        const openMenu = (focusTarget = false) => {
          if (isOpen()) {
            if (focusTarget) {
              items()[0]?.focus();
            }

            return;
          }

          window.clearTimeout(hideTimer);
          panel.classList.remove('hidden', 'pointer-events-none', 'opacity-0', 'scale-95');
          panel.classList.add('opacity-100', 'scale-100');
          chevron?.classList.add('rotate-180');
          trigger.setAttribute('aria-expanded', 'true');

          if (focusTarget) {
            window.requestAnimationFrame(() => items()[0]?.focus());
          }
        };

        const closeMenu = (returnFocus = false) => {
          if (!isOpen()) {
            return;
          }

          panel.classList.remove('opacity-100', 'scale-100');
          panel.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
          chevron?.classList.remove('rotate-180');
          trigger.setAttribute('aria-expanded', 'false');

          hideTimer = window.setTimeout(() => {
            if (!isOpen()) {
              panel.classList.add('hidden');
            }
          }, 150);

          if (returnFocus) {
            trigger.focus();
          }
        };

        const focusItem = (index) => {
          const menuItems = items();
          if (!menuItems.length) {
            return;
          }

          const safeIndex = (index + menuItems.length) % menuItems.length;
          menuItems[safeIndex].focus();
        };

        trigger.addEventListener('click', () => {
          if (isOpen()) {
            closeMenu();
            return;
          }

          openMenu();
        });

        trigger.addEventListener('keydown', (event) => {
          if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openMenu(true);
          }

          if (event.key === 'ArrowUp') {
            event.preventDefault();
            openMenu();
            const menuItems = items();
            menuItems[menuItems.length - 1]?.focus();
          }

          if (event.key === 'Escape') {
            closeMenu();
          }
        });

        panel.addEventListener('keydown', (event) => {
          const menuItems = items();
          const currentIndex = menuItems.indexOf(document.activeElement);

          if (event.key === 'Escape') {
            event.preventDefault();
            closeMenu(true);
          }

          if (event.key === 'ArrowDown') {
            event.preventDefault();
            focusItem(currentIndex + 1);
          }

          if (event.key === 'ArrowUp') {
            event.preventDefault();
            focusItem(currentIndex - 1);
          }

          if (event.key === 'Home') {
            event.preventDefault();
            focusItem(0);
          }

          if (event.key === 'End') {
            event.preventDefault();
            focusItem(menuItems.length - 1);
          }

          if (event.key === 'Tab') {
            closeMenu();
          }
        });

        panel.addEventListener('click', () => closeMenu());

        document.addEventListener('click', (event) => {
          if (!menu.contains(event.target)) {
            closeMenu();
          }
        });
      });
    });
  </script>
@endonce
