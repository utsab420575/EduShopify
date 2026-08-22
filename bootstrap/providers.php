<?php

return [
    App\Providers\AppServiceProvider::class,
    // Filament admin panel retired — the Admin dashboard is now the
    // Controller+Blade implementation at routes/backend/admin.php, per
    // docs/ai/workflows/admin_dashboard_workflow.md ("no Filament").
    // App\Providers\Filament\AdminPanelProvider::class,
];
