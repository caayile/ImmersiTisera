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
     * Bank kuesioner bawaan (3 langkah ala gambar): dipakai seeder dan
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
                ],
            ],
            [
                'key' => 'unit',
                'title' => 'Evaluasi terhadap Unit Bisnis',
                'questions' => [
                    'Unit Bisnis memberikan akses data, fasilitas, dan informasi yang memadai.',
                    'Bimbingan dan dukungan dari tim Unit Bisnis memadai untuk pelaksanaan magang.',
                    'Ruang lingkup pekerjaan yang diberikan sesuai dengan kesepakatan dalam Perjanjian Magang.',
                ],
            ],
            [
                'key' => 'mentor',
                'title' => 'Evaluasi terhadap Mentor',
                'questions' => [
                    'Mentor menjelaskan ruang lingkup dan ekspektasi kerja dengan jelas di awal program.',
                    'Mentor memberikan umpan balik yang membangun terhadap logbook dan pekerjaan.',
                    'Mentor responsif dan mudah dihubungi saat saya membutuhkan arahan.',
                ],
            ],
        ];
    }
}
