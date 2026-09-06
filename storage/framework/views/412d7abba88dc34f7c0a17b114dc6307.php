<style>
    :root {
        --theme-primary: <?php echo e($theme['theme_primary'] ?? '#4f46e5'); ?>;
        --theme-primary-hover: <?php echo e($theme['theme_primary_hover'] ?? '#4338ca'); ?>;
        --theme-primary-soft: <?php echo e($theme['theme_primary_soft'] ?? '#eef2ff'); ?>;

        --sidebar-bg: <?php echo e($theme['sidebar_background'] ?? '#ffffff'); ?>;
        --sidebar-border: <?php echo e($theme['sidebar_border'] ?? '#e5e7eb'); ?>;
        --sidebar-text: <?php echo e($theme['sidebar_text'] ?? '#374151'); ?>;
        --sidebar-muted: <?php echo e($theme['sidebar_muted'] ?? '#9ca3af'); ?>;

        --sidebar-menu-text: <?php echo e($theme['sidebar_menu_text'] ?? '#374151'); ?>;
        --sidebar-menu-hover-bg: <?php echo e($theme['sidebar_menu_hover_background'] ?? '#f3f4f6'); ?>;
        --sidebar-menu-hover-text: <?php echo e($theme['sidebar_menu_hover_text'] ?? '#111827'); ?>;
        --sidebar-menu-active-bg: <?php echo e($theme['sidebar_menu_active_background'] ?? '#eef2ff'); ?>;
        --sidebar-menu-active-text: <?php echo e($theme['sidebar_menu_active_text'] ?? '#4f46e5'); ?>;
        --sidebar-menu-active-border: <?php echo e($theme['sidebar_menu_active_border'] ?? '#4f46e5'); ?>;

        --sidebar-submenu-text: <?php echo e($theme['sidebar_submenu_text'] ?? '#6b7280'); ?>;
        --sidebar-submenu-hover-bg: <?php echo e($theme['sidebar_submenu_hover_background'] ?? '#f9fafb'); ?>;
        --sidebar-submenu-hover-text: <?php echo e($theme['sidebar_submenu_hover_text'] ?? '#4f46e5'); ?>;
        --sidebar-submenu-active-bg: <?php echo e($theme['sidebar_submenu_active_background'] ?? '#eef2ff'); ?>;
        --sidebar-submenu-active-text: <?php echo e($theme['sidebar_submenu_active_text'] ?? '#4f46e5'); ?>;

        --topbar-bg: <?php echo e($theme['topbar_background'] ?? '#ffffff'); ?>;
        --topbar-border: <?php echo e($theme['topbar_border'] ?? '#e5e7eb'); ?>;
        --page-bg: #f9fafb;
        --surface-bg: #ffffff;
    }

    html, body { font-family: 'Inter', sans-serif; }

    [x-cloak] { display: none !important; }

    .sidebar-scroll::-webkit-scrollbar { width: 6px; }
    .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 9999px; }
    .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #d1d5db; }

    .btn-primary { background: var(--theme-primary); color: #ffffff; }
    .btn-primary:hover { background: var(--theme-primary-hover); }
    .btn-primary:disabled { opacity: .6; cursor: not-allowed; }
    .focus-accent:focus { outline: none; border-color: var(--theme-primary); box-shadow: 0 0 0 3px var(--theme-primary-soft); }

    .sidebar-menu-item { color: var(--sidebar-menu-text); transition: background-color .2s ease, color .2s ease, border-color .2s ease; }
    .sidebar-menu-item:hover { background: var(--sidebar-menu-hover-bg); color: var(--sidebar-menu-hover-text); }
    .sidebar-menu-item.active { background: var(--sidebar-menu-active-bg); color: var(--sidebar-menu-active-text); border-left-color: var(--sidebar-menu-active-border); }
    .sidebar-menu-item.active .sidebar-menu-icon { color: var(--sidebar-menu-active-text); }

    .sidebar-submenu { max-height: 0; overflow: hidden; transition: max-height .25s ease; }
    .sidebar-submenu.open { max-height: 1000px; }
    .sidebar-submenu-item { color: var(--sidebar-submenu-text); display: block; border-radius: .375rem; transition: color .15s ease, background-color .15s ease; }
    .sidebar-submenu-item:hover { background: var(--sidebar-submenu-hover-bg); color: var(--sidebar-submenu-hover-text); }
    .sidebar-submenu-item.active { background: var(--sidebar-submenu-active-bg); color: var(--sidebar-submenu-active-text); font-weight: 600; }
</style>
<?php /**PATH C:\laragon\www\edushopify\resources\views/backend/layouts/partials/shared/_theme.blade.php ENDPATH**/ ?>