<?php

namespace App\Services;

use Spatie\Browsershot\Browsershot;

class CertificatePdfRenderer
{
    public function render(string $html): string
    {
        $browser = Browsershot::html($html)
            ->setNodeBinary(config('services.browsershot.node_binary', 'node'))
            ->setNodeModulePath(base_path('node_modules'))
            ->format('A4')
            ->landscape()
            ->margins(0, 0, 0, 0)
            ->showBackground();

        $chromePath = $this->chromePath();

        if (filled($chromePath)) {
            $browser->setChromePath($chromePath);
        }

        return $browser->pdf();
    }

    private function chromePath(): ?string
    {
        $configuredPath = config('services.browsershot.chrome_path');

        if (filled($configuredPath)) {
            return $configuredPath;
        }

        if (PHP_OS_FAMILY !== 'Windows') {
            return null;
        }

        $programFiles = getenv('ProgramFiles') ?: 'C:\\Program Files';
        $programFilesX86 = getenv('ProgramFiles(x86)') ?: 'C:\\Program Files (x86)';
        $candidates = [
            $programFilesX86.'\\Microsoft\\Edge\\Application\\msedge.exe',
            $programFiles.'\\Microsoft\\Edge\\Application\\msedge.exe',
            $programFiles.'\\Google\\Chrome\\Application\\chrome.exe',
            $programFilesX86.'\\Google\\Chrome\\Application\\chrome.exe',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
