<?php

namespace Database\Seeders;
use App\Models\Course;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  
public function run(): void
{
    Course::create([
        'title' => 'دبلومة تطوير الويب باستخدام Laravel & Vue.js',
        'description' => 'كورس متكامل لبناء تطبيقات الويب الحديثة من الصفر وحتى الاحتراف.',
        'price' => 250.00,
        'duration_hours' => 80
    ]);

    Course::create([
        'title' => 'أساسيات البرمجة بلغة Python والذكاء الاصطناعي',
        'description' => 'تعلم لغة بايثون وكيفية التعامل مع مكتبات تحليل البيانات ونماذج الـ AI.',
        'price' => 300.00,
        'duration_hours' => 90
    ]);
}
}
