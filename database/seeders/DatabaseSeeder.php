<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Payment;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with demo data.
     */
    public function run(): void
    {
        // 1. Create System Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@edutrack.test'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. Create Teachers
        $teacher1 = User::firstOrCreate(
            ['email' => 'teacher@edutrack.test'],
            [
                'name' => 'Prof. Alan Turing',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'email_verified_at' => now(),
            ]
        );

        $teacher2 = User::firstOrCreate(
            ['email' => 'grace@edutrack.test'],
            [
                'name' => 'Dr. Grace Hopper',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'email_verified_at' => now(),
            ]
        );

        // 3. Create Classes
        $classesData = [
            [
                'teacher_id' => $teacher1->id,
                'name' => 'Advanced Pure Mathematics',
                'grade_level' => 'Grade 12',
                'monthly_fee' => 3500.00,
                'registration_token' => 'math12-token-2026',
                'is_registration_open' => true,
            ],
            [
                'teacher_id' => $teacher1->id,
                'name' => 'Computer Science & Algorithms',
                'grade_level' => 'Grade 11',
                'monthly_fee' => 4000.00,
                'registration_token' => 'cs11-token-2026',
                'is_registration_open' => true,
            ],
            [
                'teacher_id' => $teacher1->id,
                'name' => 'Applied Physics & Mechanics',
                'grade_level' => 'Grade 10',
                'monthly_fee' => 3000.00,
                'registration_token' => 'phy10-token-2026',
                'is_registration_open' => false,
            ],
            [
                'teacher_id' => $teacher2->id,
                'name' => 'Information & Communication Tech',
                'grade_level' => 'Grade 11',
                'monthly_fee' => 3800.00,
                'registration_token' => 'ict11-token-2026',
                'is_registration_open' => true,
            ],
        ];

        $classes = [];
        foreach ($classesData as $c) {
            $classes[] = ClassRoom::firstOrCreate(['registration_token' => $c['registration_token']], $c);
        }

        // 4. Create 20 Demo Students
        $studentsData = [
            ['full_name' => 'Alexander Hayes', 'contact_number' => '+1 555-0101', 'guardian_name' => 'Robert Hayes', 'guardian_phone' => '+1 555-0201'],
            ['full_name' => 'Sophia Vance', 'contact_number' => '+1 555-0102', 'guardian_name' => 'Elena Vance', 'guardian_phone' => '+1 555-0202'],
            ['full_name' => 'Liam Chen', 'contact_number' => '+1 555-0103', 'guardian_name' => 'David Chen', 'guardian_phone' => '+1 555-0203'],
            ['full_name' => 'Olivia Martinez', 'contact_number' => '+1 555-0104', 'guardian_name' => 'Maria Martinez', 'guardian_phone' => '+1 555-0204'],
            ['full_name' => 'Ethan Walker', 'contact_number' => '+1 555-0105', 'guardian_name' => 'George Walker', 'guardian_phone' => '+1 555-0205'],
            ['full_name' => 'Emma Watson-Lee', 'contact_number' => '+1 555-0106', 'guardian_name' => 'Arthur Watson', 'guardian_phone' => '+1 555-0206'],
            ['full_name' => 'Noah Patel', 'contact_number' => '+1 555-0107', 'guardian_name' => 'Rajesh Patel', 'guardian_phone' => '+1 555-0207'],
            ['full_name' => 'Ava Robinson', 'contact_number' => '+1 555-0108', 'guardian_name' => 'Sarah Robinson', 'guardian_phone' => '+1 555-0208'],
            ['full_name' => 'Lucas Miller', 'contact_number' => '+1 555-0109', 'guardian_name' => 'James Miller', 'guardian_phone' => '+1 555-0209'],
            ['full_name' => 'Mia Jenkins', 'contact_number' => '+1 555-0110', 'guardian_name' => 'Clara Jenkins', 'guardian_phone' => '+1 555-0210'],
            ['full_name' => 'Benjamin Brooks', 'contact_number' => '+1 555-0111', 'guardian_name' => 'Paul Brooks', 'guardian_phone' => '+1 555-0211'],
            ['full_name' => 'Charlotte Diaz', 'contact_number' => '+1 555-0112', 'guardian_name' => 'Carlos Diaz', 'guardian_phone' => '+1 555-0212'],
            ['full_name' => 'Henry Cooper', 'contact_number' => '+1 555-0113', 'guardian_name' => 'Thomas Cooper', 'guardian_phone' => '+1 555-0213'],
            ['full_name' => 'Amelia Hughes', 'contact_number' => '+1 555-0114', 'guardian_name' => 'Patricia Hughes', 'guardian_phone' => '+1 555-0214'],
            ['full_name' => 'Daniel Kim', 'contact_number' => '+1 555-0115', 'guardian_name' => 'Joon Kim', 'guardian_phone' => '+1 555-0215'],
            ['full_name' => 'Isabella Gomez', 'contact_number' => '+1 555-0116', 'guardian_name' => 'Lucia Gomez', 'guardian_phone' => '+1 555-0216'],
            ['full_name' => 'Mason Taylor', 'contact_number' => '+1 555-0117', 'guardian_name' => 'William Taylor', 'guardian_phone' => '+1 555-0217'],
            ['full_name' => 'Harper Wright', 'contact_number' => '+1 555-0118', 'guardian_name' => 'Rachel Wright', 'guardian_phone' => '+1 555-0218'],
            ['full_name' => 'Elijah Scott', 'contact_number' => '+1 555-0119', 'guardian_name' => 'Brian Scott', 'guardian_phone' => '+1 555-0219'],
            ['full_name' => 'Evelyn Adams', 'contact_number' => '+1 555-0120', 'guardian_name' => 'Susan Adams', 'guardian_phone' => '+1 555-0220'],
        ];

        $students = [];
        foreach ($studentsData as $idx => $s) {
            $adm = 'ADM-'.str_pad($idx + 1, 5, '0', STR_PAD_LEFT);
            $students[] = Student::firstOrCreate(
                ['admission_number' => $adm],
                array_merge($s, ['admission_number' => $adm])
            );
        }

        // 5. Enroll Students into Classes
        // Enroll students 0-14 in Class 0 (Advanced Pure Mathematics)
        // Enroll students 5-18 in Class 1 (Computer Science & Algorithms)
        // Enroll students 10-19 in Class 2 (Applied Physics)
        // Enroll students 0-10 in Class 3 (ICT - Grace Hopper)
        $enrollmentMap = [
            0 => range(0, 14),
            1 => range(5, 18),
            2 => range(10, 19),
            3 => range(0, 10),
        ];

        foreach ($enrollmentMap as $classIdx => $studentIndices) {
            $class = $classes[$classIdx];
            foreach ($studentIndices as $sIdx) {
                $student = $students[$sIdx];
                $uid = ClassStudent::generateUid($class->id, $student->id);
                // Predetermined QR token for easy testing of first student
                $qrToken = ($classIdx === 0 && $sIdx === 0)
                    ? 'qr-demo-student-001'
                    : (string) Str::uuid();

                ClassStudent::firstOrCreate(
                    [
                        'class_id' => $class->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'student_class_uid' => $uid,
                        'qr_token' => $qrToken,
                        'joined_via' => ($sIdx % 2 === 0) ? 'link' : 'manual',
                        'status' => 'active',
                    ]
                );
            }
        }

        // 6. Seed Attendance Records for past 7 days (including today)
        $statuses = ['present', 'present', 'present', 'present', 'late', 'present'];
        $today = Carbon::today();

        for ($d = 6; $d >= 0; $d--) {
            $date = $today->copy()->subDays($d);
            // Skip Sunday
            if ($date->isSunday()) {
                continue;
            }

            // Record attendance for Class 0
            $class0 = $classes[0];
            $enrolled = ClassStudent::where('class_id', $class0->id)->get();
            foreach ($enrolled as $item) {
                // 90% attendance chance
                if (rand(1, 10) <= 9) {
                    $st = $statuses[array_rand($statuses)];
                    $checkInHour = ($st === 'late') ? '09:20:00' : '08:45:00';
                    Attendance::firstOrCreate(
                        [
                            'class_id' => $class0->id,
                            'student_id' => $item->student_id,
                            'date' => $date->toDateString(),
                        ],
                        [
                            'check_in_time' => $checkInHour,
                            'status' => $st,
                            'marked_by' => $class0->teacher_id,
                        ]
                    );
                }
            }

            // Record attendance for Class 1
            $class1 = $classes[1];
            $enrolled1 = ClassStudent::where('class_id', $class1->id)->get();
            foreach ($enrolled1 as $item) {
                if (rand(1, 10) <= 8) {
                    $st = $statuses[array_rand($statuses)];
                    $checkInHour = ($st === 'late') ? '14:25:00' : '13:55:00';
                    Attendance::firstOrCreate(
                        [
                            'class_id' => $class1->id,
                            'student_id' => $item->student_id,
                            'date' => $date->toDateString(),
                        ],
                        [
                            'check_in_time' => $checkInHour,
                            'status' => $st,
                            'marked_by' => $class1->teacher_id,
                        ]
                    );
                }
            }
        }

        // 7. Seed Payments for past 6 months to populate Chart.js trends!
        $currentMonth = $today->format('Y-m');
        $months = [];
        for ($m = 5; $m >= 0; $m--) {
            $months[] = $today->copy()->subMonths($m)->format('Y-m');
        }

        // Previous months: all or most paid
        foreach ($classes as $class) {
            $enrolled = ClassStudent::where('class_id', $class->id)->get();
            foreach ($months as $idx => $mStr) {
                $isCurrent = ($mStr === $currentMonth);
                $paidDate = Carbon::createFromFormat('Y-m', $mStr)->startOfMonth()->addDays(rand(2, 18))->toDateString();

                foreach ($enrolled as $eIdx => $item) {
                    // For past months, 90% paid; for current month, 60% paid (to test Paid vs Pending nicely)
                    $shouldPay = $isCurrent ? ($eIdx % 3 !== 0) : ($eIdx % 6 !== 0);
                    if ($shouldPay) {
                        Payment::firstOrCreate(
                            [
                                'class_id' => $class->id,
                                'student_id' => $item->student_id,
                                'payment_month' => $mStr,
                            ],
                            [
                                'teacher_id' => $class->teacher_id,
                                'amount' => $class->monthly_fee,
                                'paid_date' => $paidDate,
                                'receipt_no' => 'RCP-'.str_replace('-', '', $mStr).'-'.str_pad($class->id, 2, '0', STR_PAD_LEFT).'-'.str_pad($item->student_id, 4, '0', STR_PAD_LEFT),
                                'payment_method' => 'cash',
                                'notes' => 'Regular monthly cash fee payment',
                            ]
                        );
                    }
                }
            }
        }
    }
}
