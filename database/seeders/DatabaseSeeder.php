<?php

namespace Database\Seeders;

use App\Models\Faculty;
use App\Models\StudyProgram;
use App\Models\User;
use App\Models\UserAccess;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        $roles = [
            'super_admin',
            'admin_fakultas',
            'dekan',
            'wakil_dekan',
            'kabag_tu',
            'keuangan',
            'admin_prodi',
            'ka_prodi',
            'dosen',
            'mahasiswa',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // 2. Master Fakultas
        $faculty = Faculty::firstOrCreate(
            ['code' => 'FISIP'],
            ['name' => 'Fakultas Ilmu Sosial dan Ilmu Politik']
        );

        // 3. Master Program Studi
        $prodis = [
            [
                'name' => 'Ilmu Pemerintahan',
                'code' => 'IP-S1',
                'degree' => 'S1',
                'wa_group_url' => 'https://chat.whatsapp.com/sample-grup-prodi-ip',
            ],
            [
                'name' => 'Hubungan Internasional',
                'code' => 'HI-S1',
                'degree' => 'S1',
                'wa_group_url' => 'https://chat.whatsapp.com/sample-grup-prodi-hi',
            ],
            [
                'name' => 'Magister Ilmu Pemerintahan',
                'code' => 'MIP-S2',
                'degree' => 'S2',
                'wa_group_url' => 'https://chat.whatsapp.com/sample-grup-prodi-mip',
            ],
            [
                'name' => 'Magister Hubungan Internasional',
                'code' => 'MHI-S2',
                'degree' => 'S2',
                'wa_group_url' => 'https://chat.whatsapp.com/sample-grup-prodi-mhi',
            ],
            [
                'name' => 'Doktor Ilmu Pemerintahan',
                'code' => 'DIP-S3',
                'degree' => 'S3',
                'wa_group_url' => 'https://chat.whatsapp.com/sample-grup-prodi-doktor-ip',
            ],
        ];

        $studyProgramModels = [];
        foreach ($prodis as $p) {
            $studyProgramModels[$p['code']] = StudyProgram::firstOrCreate(
                ['code' => $p['code']],
                [
                    'faculty_id' => $faculty->id,
                    'name' => $p['name'],
                    'degree' => $p['degree'],
                    'wa_group_url' => $p['wa_group_url'],
                ]
            );
        }

        // 4. Default Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@pmb.test'],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->syncRoles(['super_admin']);

        // 5. Admin Fakultas
        $adminFakultas = User::firstOrCreate(
            ['email' => 'admin.fakultas@pmb.test'],
            [
                'name' => 'Admin Fakultas FISIP',
                'password' => Hash::make('password'),
            ]
        );
        $adminFakultas->syncRoles(['admin_fakultas']);

        // 6. Keuangan
        $keuangan = User::firstOrCreate(
            ['email' => 'keuangan@pmb.test'],
            [
                'name' => 'Staf Keuangan',
                'password' => Hash::make('password'),
            ]
        );
        $keuangan->syncRoles(['keuangan']);

        // 7. Admin Prodi IP (scoped via user_access)
        $adminProdiIp = User::firstOrCreate(
            ['email' => 'admin.prodi.ip@pmb.test'],
            [
                'name' => 'Admin Prodi IP',
                'password' => Hash::make('password'),
            ]
        );
        $adminProdiIp->syncRoles(['admin_prodi']);
        UserAccess::firstOrCreate([
            'user_id' => $adminProdiIp->id,
            'study_program_id' => $studyProgramModels['IP-S1']->id,
        ]);

        // 8. Ka Prodi IP
        $kaProdiIp = User::firstOrCreate(
            ['email' => 'kaprodi.ip@pmb.test'],
            [
                'name' => 'Dr. Ka Prodi IP',
                'password' => Hash::make('password'),
            ]
        );
        $kaProdiIp->syncRoles(['ka_prodi']);
        UserAccess::firstOrCreate([
            'user_id' => $kaProdiIp->id,
            'study_program_id' => $studyProgramModels['IP-S1']->id,
        ]);

        // 9. Dosen Penguji IP
        $dosenIp = User::firstOrCreate(
            ['email' => 'dosen.ip@pmb.test'],
            [
                'name' => 'Dosen Asesor IP',
                'password' => Hash::make('password'),
            ]
        );
        $dosenIp->syncRoles(['dosen']);
        UserAccess::firstOrCreate([
            'user_id' => $dosenIp->id,
            'study_program_id' => $studyProgramModels['IP-S1']->id,
        ]);

        // 10. Sample Calon Mahasiswa
        $mahasiswa = User::firstOrCreate(
            ['email' => 'mhs@pmb.test'],
            [
                'name' => 'Budi Santoso (Calon Mhs)',
                'password' => Hash::make('password'),
            ]
        );
        $mahasiswa->syncRoles(['mahasiswa']);
    }
}
