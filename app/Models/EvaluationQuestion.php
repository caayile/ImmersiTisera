<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['evaluation_section_id', 'question', 'position', 'is_active'])]
class EvaluationQuestion extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function section()
    {
        return $this->belongsTo(EvaluationSection::class, 'evaluation_section_id');
    }

    /**
     * Bank kuesioner bawaan (3 langkah): dipakai seeder dan
     * sebagai fallback bila admin belum mengatur bank.
     *
     * @return array<int, array{key: string, title: string, questions: array<int, string>}>
     */
    public static function defaultBank(): array
    {
        return [
            [
                'key' => 'program',
                'title' => 'Evaluasi terhadap Program Magang Dosen TSU',
                'questions' => [
                    'Proses pendaftaran dan seleksi program berjalan jelas dan mudah diikuti.',
                    'Alur program (pendaftaran, perjanjian, magang aktif, evaluasi) disampaikan dengan jelas.',
                    'Durasi program (± 8 minggu) sudah sesuai untuk mencapai tujuan pembelajaran.',
                    'Informasi dan komunikasi dari pengelola program mudah diakses selama magang.',
                    'Program memberikan pengalaman yang relevan dengan pengembangan kompetensi dosen.',
                ],
            ],
            [
                'key' => 'departemen',
                'title' => 'Evaluasi terhadap Departemen',
                'questions' => [
                    'Departemen memberikan akses data, fasilitas, dan informasi yang memadai.',
                    'Bimbingan dan dukungan dari tim Departemen memadai untuk pelaksanaan magang.',
                    'Ruang lingkup pekerjaan yang diberikan sesuai dengan kesepakatan dalam Perjanjian Magang.',
                    'Lingkungan kerja di Departemen mendukung kolaborasi dan pembelajaran.',
                    'Tugas dan proyek yang diberikan Departemen sesuai dengan keahlian serta tujuan magang.',
                ],
            ],
            [
                'key' => 'mentor',
                'title' => 'Evaluasi terhadap Mentor',
                'questions' => [
                    'Mentor menjelaskan ruang lingkup dan ekspektasi kerja dengan jelas di awal program.',
                    'Mentor memberikan umpan balik yang membangun terhadap logbook dan pekerjaan.',
                    'Mentor responsif dan mudah dihubungi saat saya membutuhkan arahan.',
                    'Mentor memfasilitasi pembelajaran praktis yang relevan dengan tujuan magang.',
                    'Interaksi dengan mentor membantu saya mengembangkan kompetensi profesional.',
                ],
            ],
        ];
    }
}
