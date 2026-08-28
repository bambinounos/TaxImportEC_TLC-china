<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\AntiBotService;
use Illuminate\Console\Command;

class AuditBotUsersCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:audit-bots 
                            {--action=list : Action to perform: list, deactivate, delete}
                            {--domain= : Filter suspicious users by specific email domain}
                            {--all-empty : Target all regular users with zero calculations}
                            {--force : Force execution without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit, inspect, deactivate or clean suspicious bot user registrations';

    /**
     * Execute the console command.
     */
    public function handle(AntiBotService $antiBotService): int
    {
        $action = $this->option('action') ?: 'list';
        $domainFilter = $this->option('domain');
        $allEmpty = $this->option('all-empty');
        $force = $this->option('force');

        $this->info("Iniciando auditoría de usuarios y posibles cuentas bot...");

        // Only target users with role 'user' and 0 calculations
        $query = User::withCount('calculations')
            ->where('role', 'user')
            ->having('calculations_count', '=', 0);

        if ($domainFilter) {
            $query->where('email', 'like', "%@{$domainFilter}");
        }

        $candidates = $query->get();

        if ($candidates->isEmpty()) {
            $this->info("No se encontraron usuarios candidatos con 0 cálculos.");
            return Command::SUCCESS;
        }

        // Filter suspicious users
        $suspiciousUsers = $candidates->filter(function (User $user) use ($antiBotService, $allEmpty, $domainFilter) {
            if ($allEmpty || $domainFilter) {
                return true;
            }

            // Check if domain is in disposable list or fails domain checks
            if (!$antiBotService->validateEmailDomain($user->email)) {
                return true;
            }

            return false;
        });

        if ($suspiciousUsers->isEmpty()) {
            $this->info("No se detectaron usuarios con dominios de correo sospechosos o temporales.");
            $this->comment("Tip: Usa la bandera '--all-empty' para listar todos los usuarios con 0 cálculos.");
            return Command::SUCCESS;
        }

        $this->warn("Se detectaron {$suspiciousUsers->count()} cuentas sospechosas (0 cálculos, posibles bots):");

        $tableRows = $suspiciousUsers->map(function (User $user) {
            return [
                'ID' => $user->id,
                'Nombre' => $user->name,
                'Email' => $user->email,
                'Estado' => $user->is_active ? 'Activo' : 'Inactivo',
                'Fecha Registro' => $user->created_at?->format('Y-m-d H:i:s') ?? 'N/A',
                'Cálculos' => $user->calculations_count,
            ];
        });

        $this->table(['ID', 'Nombre', 'Email', 'Estado', 'Fecha Registro', 'Cálculos'], $tableRows);

        if ($action === 'list') {
            $this->info("\nPara desactivar estas cuentas ejecuta: php artisan users:audit-bots --action=deactivate");
            $this->info("Para eliminarlas permanentemente ejecuta: php artisan users:audit-bots --action=delete");
            return Command::SUCCESS;
        }

        if ($action === 'deactivate') {
            if (!$force && !$this->confirm("¿Deseas DESACTIVAR estas {$suspiciousUsers->count()} cuentas sospechosas?")) {
                $this->info("Operación cancelada.");
                return Command::SUCCESS;
            }

            $ids = $suspiciousUsers->pluck('id')->toArray();
            User::whereIn('id', $ids)->update(['is_active' => false]);

            $this->info("✓ {$suspiciousUsers->count()} cuentas fueron desactivadas correctamente.");
            return Command::SUCCESS;
        }

        if ($action === 'delete') {
            if (!$force && !$this->confirm("¿Deseas ELIMINAR PERMANENTEMENTE estas {$suspiciousUsers->count()} cuentas? Esta acción no se puede deshacer.")) {
                $this->info("Operación cancelada.");
                return Command::SUCCESS;
            }

            $count = 0;
            foreach ($suspiciousUsers as $user) {
                // Extra safety check: never delete admin or users with calculations
                if ($user->role === 'user' && $user->calculations_count === 0) {
                    $user->delete();
                    $count++;
                }
            }

            $this->info("✓ {$count} cuentas sospechosas fueron eliminadas.");
            return Command::SUCCESS;
        }

        $this->error("Acción desconocida: '{$action}'. Las opciones válidas son: list, deactivate, delete.");
        return Command::INVALID;
    }
}
