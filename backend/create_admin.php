<?php

use App\Models\User;

$user = User::create([
    'name' => 'Admin User',
    'email' => 'admin@auditflow.com',
    'password' => bcrypt('password')
]);

$user->assignRole('admin');

echo "Admin user created successfully!\n";
echo "Email: admin@auditflow.com\n";
echo "Password: password\n";
