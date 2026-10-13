<?php

namespace App\Services;

use App\Models\Incident;
use App\Models\IncidentAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class IncidentAttachmentService
{
    public function validationRules(): array
    {
        return [
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx',
        ];
    }

    /**
     * @param iterable<UploadedFile> $files
     */
    public function store(iterable $files, Incident $incident, ?int $uploaderId): int
    {
        $storedPaths = [];

        try {
            return DB::transaction(function () use ($files, $incident, $uploaderId, &$storedPaths): int {
                $count = 0;

                foreach ($files as $file) {
                    if (! $file->isValid()) {
                        throw ValidationException::withMessages([
                            'attachments' => 'One or more attachments could not be uploaded. Please try again.',
                        ]);
                    }

                    $path = $file->store('incident-attachments/' . $incident->id, 'local');

                    if (! $path) {
                        throw ValidationException::withMessages([
                            'attachments' => 'Attachments could not be saved. Please try again.',
                        ]);
                    }

                    $storedPaths[] = $path;

                    IncidentAttachment::create([
                        'incident_id' => $incident->id,
                        'uploaded_by' => $uploaderId,
                        'disk' => 'local',
                        'path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize() ?: 0,
                    ]);

                    $count++;
                }

                return $count;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);

            if ($exception instanceof ValidationException) {
                throw $exception;
            }

            throw ValidationException::withMessages([
                'attachments' => 'Attachments could not be saved. Please try again.',
            ]);
        }
    }
}
