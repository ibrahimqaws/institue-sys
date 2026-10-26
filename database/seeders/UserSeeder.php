<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   

public function run(): void
    {
        $accounts = [
            ['admin@institute.com', 'المهندس المسؤول', 'admin'],
            ['teacher@institute.com', 'أحمد المدرس', 'teacher'],
            ['student@institute.com', 'خالد الطالب', 'student'],
        ];

        foreach ($accounts as [$email, $name, $role]) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => $role,
                    'is_active' => true,
                ]
            );
        }
    }
}
