<?php

namespace App\Services;

use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ResumePdfService
{
    public function download(User $user): Response
    {
        $user->load(['profile', 'portfolioProjects']);

        $pdf = Pdf::loadView('resume.pdf', [
            'user' => $user,
            'profile' => $user->profile,
            'projects' => $user->portfolioProjects ?? collect(),
        ])->setPaper('a4');

        $filename = str($user->name)->slug().'-resume.pdf';

        return $pdf->download($filename);
    }
}
