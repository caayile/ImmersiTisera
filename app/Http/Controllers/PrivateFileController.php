<?php

namespace App\Http\Controllers;

use App\Models\MentorSession;
use App\Models\Program;
use App\Models\ProgramOutput;
use App\Models\Timeline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PrivateFileController extends Controller
{
    public function output(Request $request, ProgramOutput $output, string $artifact): StreamedResponse
    {
        $this->authorizeProgramAccess($request, $output->program);

        $path = match ($artifact) {
            'report' => $output->file_path,
            'result' => $output->hasil_file_path,
            default => null,
        };

        return $this->download($path);
    }

    public function timeline(Request $request, Timeline $timeline): StreamedResponse
    {
        $this->authorizeProgramAccess($request, $timeline->program);

        return $this->download($timeline->attachment_path);
    }

    public function mentoring(Request $request, MentorSession $session): StreamedResponse
    {
        $this->authorizeProgramAccess($request, $session->program);

        return $this->download($session->attachment_path);
    }

    private function authorizeProgramAccess(Request $request, ?Program $program): void
    {
        abort_unless($program, 404);

        $user = $request->user();
        $isProgramMember = $program->participant?->user_id === $user->id
            || $program->mentor?->user_id === $user->id;

        abort_unless($user->isAdmin() || $isProgramMember, 403);
    }

    private function download(?string $path): StreamedResponse
    {
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, basename($path), [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
