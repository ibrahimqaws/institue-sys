<?php

namespace Database\Seeders;
use App\Models\Classroom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    

public function run(): void
{
    Classroom::create(['name' => 'قاعة 101', 'capacity' => 25, 'type' => 'lecture']);
    Classroom::create(['name' => 'قاعة 102', 'capacity' => 20, 'type' => 'lecture']);
    Classroom::create(['name' => 'مختبر الحاسوب A', 'capacity' => 15, 'type' => 'lab']);
}
}
