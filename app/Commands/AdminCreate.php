<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Creates an administrator or resets an existing one's password.
 *   php spark admin:create admin@example.com "Full Name" [role-slug]
 * The password is typed at a prompt so it never lands in shell history.
 */
class AdminCreate extends BaseCommand
{
    protected $group       = 'Sugarlif';
    protected $name        = 'admin:create';
    protected $description = 'Create an admin user, or reset the password of an existing one.';
    protected $usage       = 'admin:create <email> [name] [role: super-admin|store-manager|content-editor]';

    public function run(array $params)
    {
        $email = strtolower(trim($params[0] ?? ''));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Usage: php spark ' . $this->usage);

            return EXIT_ERROR;
        }
        $role = model('RoleModel')->where('slug', $params[2] ?? 'super-admin')->first();
        if (! $role) {
            CLI::error('Unknown role. Run "php spark db:seed AccessSeeder" first, or use super-admin, store-manager or content-editor.');

            return EXIT_ERROR;
        }

        $password = CLI::prompt('Password (at least 10 characters)');
        if (strlen($password) < 10) {
            CLI::error('The password must be at least 10 characters.');

            return EXIT_ERROR;
        }

        $users = model('AdminUserModel');
        $hash  = password_hash($password, PASSWORD_DEFAULT);
        if ($existing = $users->where('email', $email)->first()) {
            $users->update($existing['id'], ['password_hash' => $hash, 'is_active' => 1, 'must_change_password' => 0]);
            CLI::write("Password reset for {$email}.", 'green');
        } else {
            $users->insert(['role_id' => $role['id'], 'name' => $params[1] ?? 'Administrator', 'email' => $email, 'password_hash' => $hash, 'is_active' => 1, 'must_change_password' => 0]);
            CLI::write("Admin user {$email} created with the role {$role['name']}.", 'green');
        }

        return EXIT_SUCCESS;
    }
}
