<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class FixPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix all permissions and assign admin role to all admin accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting permissions fix...');

        // 1. Run SimplePermissionsSeeder
        $this->call('db:seed', ['--class' => 'Database\\Seeders\\SimplePermissionsSeeder']);

        // 2. Clear Spatie permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 3. Clear application caches
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('view:clear');

        $this->info(' All permissions and admin roles have been fixed successfully!');
        return 0;
    }
}
