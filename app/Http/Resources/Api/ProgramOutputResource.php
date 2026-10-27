<?php

namespace App\Http\Resources\Api;

use App\Models\ProgramOutput;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProgramOutput
 */
class ProgramOutputResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'program_id' => $this->program_id,
            'participant_id' => $this->participant_id,
            'title' => $this->title,
            'type' => $this->type,
            'description' => $this->description,
            'file_path' => $this->file_path,
            'link' => $this->linkUrl(),
            'hasil_file_path' => $this->hasil_file_path,
            'laporan_link' => $this->laporanLinkUrl(),
            'department_id' => $this->department_id,
            'business_unit_id' => $this->business_unit_id,
            'year' => $this->year,
            'is_main_output' => $this->is_main_output,
            'is_final_report' => $this->is_final_report,
            'mentor_feedback' => $this->mentor_feedback,
            'status' => $this->status,
        ];
    }
}
