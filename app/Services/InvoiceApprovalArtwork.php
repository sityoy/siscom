<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class InvoiceApprovalArtwork
{
    private const PATH = 'company/invoice-approval.json';

    public function exists(): bool
    {
        return Storage::disk('local')->exists(self::PATH);
    }

    public function save(UploadedFile $signature, UploadedFile $stamp): void
    {
        $images = [
            'signature' => $this->pngDataUri($signature),
            'stamp' => $this->pngDataUri($stamp),
        ];

        $path = Storage::disk('local')->path(self::PATH);
        File::ensureDirectoryExists(dirname($path));
        File::replace($path, json_encode($images, JSON_THROW_ON_ERROR), 0640);
    }

    public function images(): array
    {
        $contents = Storage::disk('local')->get(self::PATH);

        return $contents ? json_decode($contents, true, flags: JSON_THROW_ON_ERROR) : [];
    }

    private function pngDataUri(UploadedFile $image): string
    {
        return 'data:image/png;base64,'.base64_encode($image->getContent());
    }
}
