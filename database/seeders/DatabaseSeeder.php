<?php

namespace Database\Seeders;

use App\Models\Application;
use App\Models\BusinessUnit;
use App\Models\CollaborationPipeline;
use App\Models\Department;
use App\Models\Evaluation;
use App\Models\Logbook;
use App\Models\Mentor;
use App\Models\MentorSession;
use App\Models\Participant;
use App\Models\Program;
use App\Models\ProgramOutput;
use App\Models\User;
use App\Notifications\ImersiAlert;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $tspm = Department::create([
            'name' => 'TSPM',
            'slug' => 'tspm',
            'subtitle' => 'Tiga Serangkai Pustaka Mandiri',
            'description' => 'Tiga Serangkai Pustaka Mandiri — penerbitan, distribusi, dan operasional bisnis buku sekolah serta digital.',
            'function' => 'Publishing, sales, operations, and corporate services',
            'area' => 'Publishing & Corporate',
            'image_path' => 'images/hero/campus.jpg',
            'status' => 'active',
        ]);

        $tsic = Department::create([
            'name' => 'TSIC',
            'slug' => 'tsic',
            'subtitle' => 'Tiga Serangkai Innovation Center',
            'description' => 'Tiga Serangkai Innovation Center — pengembangan orang, pusat unggulan, dan ekosistem pembelajaran.',
            'function' => 'Learning, talent, and innovation',
            'area' => 'Innovation & Learning',
            'image_path' => 'images/hero/campus.jpg',
            'status' => 'active',
        ]);

        $tspmUnits = [
            ['School Book Publishing', 'Editorial', ['Pendidikan', 'Bahasa', 'Informatika']],
            ['E-Publishing', 'Digital content', ['Informatika', 'Desain Komunikasi Visual']],
            ['Production', 'Print production', ['Teknik Industri', 'Manajemen']],
            ['School Book Sales', 'Sales', ['Manajemen', 'Pendidikan']],
            ['Digital Business', 'Product & analytics', ['Informatika', 'Sistem Informasi']],
            ['General Trading', 'Trading', ['Manajemen']],
            ['Marketing', 'Brand & campaign', ['Manajemen', 'Ilmu Komunikasi']],
            ['Procurement', 'Sourcing', ['Manajemen', 'Akuntansi']],
            ['SCM', 'Supply chain', ['Teknik Industri', 'Manajemen']],
            ['IQA', 'Quality assurance', ['Pendidikan', 'Manajemen']],
            ['Finance', 'Finance', ['Akuntansi', 'Manajemen']],
            ['IT', 'Information technology', ['Informatika', 'Sistem Informasi']],
            ['TAX', 'Taxation', ['Akuntansi']],
            ['HR & GA', 'People & GA', ['Manajemen', 'Psikologi']],
            ['HSE', 'Health & safety', ['Teknik Industri']],
        ];

        $tsicUnits = [
            ['Center Of Excellence', 'Excellence programs', ['Pendidikan', 'Manajemen']],
            ['People Development Center', 'Talent development', ['Manajemen', 'Psikologi']],
            ['MTIS Planning and Development', 'Planning', ['Pendidikan', 'Manajemen']],
            ['MTIS Perpuskita dan Tisera', 'Library & community', ['Pendidikan', 'Ilmu Perpustakaan']],
        ];

        $units = collect();
        foreach ($tspmUnits as [$name, $function, $programs]) {
            $units[$name] = $this->unit($tspm, $name, $function, $programs);
        }
        foreach ($tsicUnits as [$name, $function, $programs]) {
            $units[$name] = $this->unit($tsic, $name, $function, $programs);
        }

        $admin = User::create([
            'name' => 'Sari Wulandari',
            'email' => 'admin@imersi.id',
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
            'verification_status' => 'verified',
            'phone' => '081200000001',
        ]);

        $mentorUser = User::create([
            'name' => 'Budi Santoso',
            'email' => 'mentor@imersi.id',
            'password' => 'password',
            'role' => 'mentor',
            'status' => 'active',
            'verification_status' => 'verified',
            'phone' => '081200000005',
        ]);
        $mentor = Mentor::create([
            'user_id' => $mentorUser->id,
            'department_id' => $tspm->id,
            'business_unit_id' => $units['Digital Business']->id,
            'position' => 'Head of Digital Business',
            'expertise' => ['Product Analytics', 'Digital Publishing', 'Machine Learning'],
            'availability' => 'Selasa & Kamis, 09.00-12.00',
        ]);

        $mentorItUser = User::create([
            'name' => 'Dewi Lestari',
            'email' => 'mentor-it@imersi.id',
            'password' => 'password',
            'role' => 'mentor',
            'status' => 'active',
            'verification_status' => 'verified',
        ]);
        Mentor::create([
            'user_id' => $mentorItUser->id,
            'department_id' => $tspm->id,
            'business_unit_id' => $units['IT']->id,
            'position' => 'IT Manager',
            'expertise' => ['Infrastructure', 'Software Engineering'],
        ]);

        $mentorCoeUser = User::create([
            'name' => 'Agus Wijaya',
            'email' => 'mentor-coe@imersi.id',
            'password' => 'password',
            'role' => 'mentor',
            'status' => 'active',
            'verification_status' => 'verified',
        ]);
        $mentorCoe = Mentor::create([
            'user_id' => $mentorCoeUser->id,
            'department_id' => $tsic->id,
            'business_unit_id' => $units['Center Of Excellence']->id,
            'position' => 'Head of Center of Excellence',
            'expertise' => ['Curriculum', 'Industry-Based Learning'],
        ]);

        $andi = $this->participant('Dr. Andi Pratama', 'dosen@imersi.id', 'Fakultas Teknik', 'Informatika', ['AI', 'Machine Learning'], ['Predictive Analytics']);
        $maya = $this->participant('Dr. Maya Kusuma', 'dosen2@imersi.id', 'Fakultas Teknik', 'Sistem Informasi', ['Cloud Computing', 'Software Engineering'], ['Digital Product']);
        $raka = $this->participant('Dr. Raka Aditya', 'raka@imersi.id', 'Fakultas Humaniora', 'Manajemen', ['Marketing Strategy'], ['Campaign']);
        $sinta = $this->participant('Dr. Sinta Lestari', 'sinta@imersi.id', 'Sekolah Vokasi', 'Desain Produksi Tekstil', ['Uniform Design'], ['Textile Production']);
        $bima = $this->participant('Dr. Bima Nugraha', 'bima@imersi.id', 'Fakultas Humaniora', 'PGSD', ['Curriculum Design'], ['Industry-Based Learning']);

        Application::create([
            'participant_id' => $maya->id,
            'department_id' => $tspm->id,
            'business_unit_id' => $units['Digital Business']->id,
            'mentor_id' => $mentor->id,
            'motivation' => 'Ingin mengamati workflow produk digital untuk case study kurikulum.',
            'preferred_period' => '8 weeks / 60 days',
            'match_score' => 74,
            'relevance_warning' => false,
            'status' => 'submitted',
        ]);

        $active = $this->program($andi, $mentor, $tspm, $units['Digital Business'], 'active', now()->subDays(12), now()->addDays(48), 48);
        $active->seedTimeline();
        $active->agreement->update([
            'objective' => 'Mendalami predictive analytics di Digital Business untuk insight industri dan materi kuliah.',
            'problem_statement' => 'Bagaimana meningkatkan engagement pengguna terhadap konten edukasi digital?',
            'activities' => 'Observasi workflow, riset data engagement, iterasi rekomendasi bersama mentor.',
            'main_output' => 'Research insight dan rekomendasi model prediksi engagement.',
            'participant_benefit' => 'Industry insight, teaching case, jejaring profesional.',
            'business_benefit' => 'External perspective, research support, usulan perbaikan proses.',
            'success_indicators' => ['Problem statement disepakati', 'Insight dipakai tim', 'Ada usulan kolaborasi'],
            'collaboration_potential' => 'Joint research dan guest lecture.',
            'status' => 'agreed',
            'participant_approved_at' => now()->subDays(13),
            'mentor_approved_at' => now()->subDays(12),
        ]);

        Logbook::create([
            'program_id' => $active->id,
            'participant_id' => $andi->id,
            'date' => now()->subDays(11),
            'activity' => 'Orientation Digital Business',
            'what_i_did' => 'Mengikuti orientation dan pengenalan ritual weekly product review.',
            'what_i_learned' => 'Keputusan produk lebih sering berdasarkan ritme sprint.',
            'what_i_found' => 'Data produksi lebih kotor dibanding dataset kuliah.',
            'value' => 'Konteks bisnis awal untuk industry insight.',
            'next_action' => 'Pemetaan stakeholder dan sumber data.',
            'status' => 'approved',
            'mentor_feedback' => 'Arah sudah tepat.',
        ]);
        Logbook::create([
            'program_id' => $active->id,
            'participant_id' => $andi->id,
            'date' => now()->subDays(3),
            'activity' => 'Observasi prediksi churn',
            'what_i_did' => 'Mengikuti workflow prediksi churn bersama tim produk.',
            'what_i_learned' => 'Feature store lebih krusial daripada pemilihan algoritma.',
            'what_i_found' => 'Model existing jarang dipakai karena tidak tertanam di dashboard.',
            'value' => 'Tiga insight teori vs praktik.',
            'next_action' => 'Validasi KPI engagement dengan mentor.',
            'status' => 'submitted',
        ]);

        MentorSession::create([
            'program_id' => $active->id,
            'mentor_id' => $mentor->id,
            'participant_id' => $andi->id,
            'week' => 1,
            'session_date' => now()->subDays(8),
            'findings' => 'Gap antara riset kampus dan ritme keputusan produk.',
            'current_work' => 'Pemetaan stakeholder dan sumber data engagement.',
            'next_action' => 'Wawancara practitioner dan rumuskan industry insight.',
            'feedback' => 'Jangan buru-buru usulkan aplikasi.',
            'checkpoint_status' => 'on_track',
        ]);

        ProgramOutput::create([
            'program_id' => $active->id,
            'participant_id' => $andi->id,
            'title' => 'Industry Insight: Teori vs Praktik Predictive Analytics',
            'type' => 'Insight',
            'description' => 'Tiga temuan awal perbedaan pengambilan keputusan di kampus dan industri.',
            'is_main_output' => true,
            'status' => 'submitted',
        ]);

        $revision = $this->program($raka, $mentor, $tspm, $units['Marketing'], 'revision', null, null, 10);
        $revision->agreement->update([
            'objective' => 'Memahami campaign school book sales.',
            'problem_statement' => 'Campaign masih belum terukur di level sekolah.',
            'activities' => 'Observasi campaign dan penyusunan rekomendasi.',
            'main_output' => 'Campaign recommendation',
            'participant_benefit' => 'Praktik pemasaran industri.',
            'business_benefit' => 'External campaign perspective.',
            'success_indicators' => ['Problem statement jelas', 'Rekomendasi actionable'],
            'status' => 'revision',
            'revision_note' => 'Perjelas success indicator dan output utama.',
            'participant_approved_at' => now()->subDay(),
        ]);

        $agreed = $this->program($sinta, $mentor, $tspm, $units['Finance'], 'agreed', now(), now()->addDays(60), 5);
        $agreed->agreement->update([
            'objective' => 'Memahami proses finance operasional penerbitan.',
            'problem_statement' => 'Rekonsiliasi masih memakan waktu manual.',
            'activities' => 'Observasi proses dan mapping alur kerja.',
            'main_output' => 'Process mapping',
            'participant_benefit' => 'Praktik akuntansi industri.',
            'business_benefit' => 'Usulan efisiensi proses.',
            'success_indicators' => ['Process map disepakati', 'Ada 1 usulan perbaikan'],
            'status' => 'agreed',
            'participant_approved_at' => now()->subDays(2),
            'mentor_approved_at' => now()->subDay(),
        ]);

        $done = $this->program($bima, $mentorCoe, $tsic, $units['Center Of Excellence'], 'completed', now()->subDays(70), now()->subDays(10), 100);
        $done->seedTimeline();
        $done->agreement->update([
            'objective' => 'Mengembangkan model industry-based learning untuk TSU.',
            'problem_statement' => 'Kurikulum belum memiliki case study autentik dari COE.',
            'activities' => 'Observasi program unggulan dan penyusunan modul.',
            'main_output' => 'Curriculum development brief',
            'participant_benefit' => 'Curriculum improvement.',
            'business_benefit' => 'Academic connection.',
            'success_indicators' => ['Modul draf selesai', 'Presentasi diterima', 'Rencana guest lecture'],
            'status' => 'agreed',
            'participant_approved_at' => now()->subDays(70),
            'mentor_approved_at' => now()->subDays(69),
        ]);
        ProgramOutput::create([
            'program_id' => $done->id,
            'participant_id' => $bima->id,
            'title' => 'Curriculum Brief: Industry-Based Learning',
            'type' => 'Recommendation',
            'is_main_output' => true,
            'status' => 'approved',
        ]);
        ProgramOutput::create([
            'program_id' => $done->id,
            'participant_id' => $bima->id,
            'title' => 'Final Report Center of Excellence',
            'type' => 'Research Report',
            'is_final_report' => true,
            'status' => 'approved',
        ]);
        Evaluation::create([
            'program_id' => $done->id,
            'evaluator_id' => $bima->user_id,
            'industry_understanding' => 5,
            'relationship' => 5,
            'output' => 4,
            'mutual_benefit' => 5,
            'collaboration_potential' => 5,
            'comments' => 'Program sangat relevan untuk pembaruan kurikulum.',
        ]);
        Evaluation::create([
            'program_id' => $done->id,
            'evaluator_id' => $mentorUser->id,
            'industry_understanding' => 4,
            'relationship' => 5,
            'output' => 5,
            'mutual_benefit' => 4,
            'collaboration_potential' => 5,
            'comments' => 'Potensi guest lecture dan student project tinggi.',
        ]);
        CollaborationPipeline::create([
            'program_id' => $done->id,
            'level' => 2,
            'collaboration_type' => 'Guest Lecture',
            'description' => 'Rencana guest lecture semester depan dari Center of Excellence.',
            'next_action' => 'Menyusun silabus bersama.',
            'responsible_person' => 'Agus Wijaya',
            'target_date' => now()->addMonth(),
            'notes' => 'Sudah ada komitmen awal.',
        ]);

        $andi->user->notify(new ImersiAlert('Program ACTIVE', 'Lanjutkan logbook minggu ini.', '/participant/logbooks'));
        $admin->notify(new ImersiAlert('Matching menunggu review', 'Ada pengajuan baru dari Dr. Maya Kusuma.', '/admin/matching'));
        $mentorUser->notify(new ImersiAlert('Logbook pending review', 'Ada logbook dari Dr. Andi Pratama.', '/mentor/logbooks'));

        $this->call(NewsSeeder::class);
        $this->call(DepartmentK33WjlSeeder::class);
        $this->call(DepartmentAhAlFirdausPuspaSeeder::class);
        $this->call(DepartmentHeroSeeder::class);
    }

    private function unit(Department $department, string $name, string $function, array $programs): BusinessUnit
    {
        return BusinessUnit::create([
            'department_id' => $department->id,
            'name' => $name,
            'description' => "Unit $name pada {$department->name} untuk immersion dosen TSU.",
            'function' => $function,
            'image_path' => $department->image_path,
            'work_done' => "Operasional harian $name, kolaborasi lintas tim, dan improvement berkelanjutan.",
            'example_activities' => 'Observasi, penugasan, riset terapan, dan diskusi mentoring 30 menit.',
            'requirements' => 'Kompetensi relevan dengan fungsi unit dan komitmen 8 minggu.',
            'relevant_programs' => $programs,
            'period' => '8 weeks / ±60 days',
            'status' => 'open',
        ]);
    }

    private function participant(string $name, string $email, string $faculty, string $prodi, array $expertise, array $competency): Participant
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'role' => 'participant',
            'status' => 'active',
            'verification_status' => 'verified',
        ]);

        return Participant::create([
            'user_id' => $user->id,
            'nidn' => '00'.substr(md5($email), 0, 8),
            'faculty' => $faculty,
            'study_program' => $prodi,
            'expertise' => $expertise,
            'competency' => $competency,
            'experience' => "Dosen $prodi dengan pengalaman riset dan pengajaran terapan.",
            'motivation' => 'Memahami dunia industri secara langsung dan membawa insight ke kampus.',
        ]);
    }

    private function program(Participant $participant, Mentor $mentor, Department $department, BusinessUnit $unit, string $status, $start, $end, int $progress): Program
    {
        $application = Application::create([
            'participant_id' => $participant->id,
            'department_id' => $department->id,
            'business_unit_id' => $unit->id,
            'mentor_id' => $mentor->id,
            'motivation' => 'Mengikuti Industry Immersion pada unit '.$unit->name.'.',
            'preferred_period' => '8 weeks / 60 days',
            'match_score' => 88,
            'status' => 'approved',
        ]);

        $program = Program::create([
            'application_id' => $application->id,
            'participant_id' => $participant->id,
            'mentor_id' => $mentor->id,
            'department_id' => $department->id,
            'business_unit_id' => $unit->id,
            'start_date' => $start,
            'end_date' => $end,
            'progress' => $progress,
            'current_week' => $status === 'active' ? 2 : ($status === 'completed' ? 8 : 1),
            'status' => $status,
        ]);
        $program->agreement()->create(['status' => $status === 'active' ? 'agreed' : 'draft']);

        return $program->load('agreement');
    }
}
