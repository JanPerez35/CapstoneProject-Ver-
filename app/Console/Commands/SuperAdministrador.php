<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SuperAdministrador extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:super-administrador
                            {email : El correo electrónico del super administrador}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a super administrator user with the specified email, password, and name.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');

        if (User::where('email', $email)->exists()) {
            $this->error('El usuario ya existe');
            return Command::FAILURE;
        }

        $user = User::create([
            'email' => $email,
            'role' => 'Super Administrador', 
        ]);

        $this->info("Usuario creado correctamente:");
        $this->line("Email: {$user->email}");
        $this->line("Rol: {$user->role}");

        return Command::SUCCESS;
    }
}
