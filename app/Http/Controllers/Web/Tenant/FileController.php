<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Central\Models\TenantFile;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        $files = TenantFile::on('central')
            ->where('tenant_id', $user->tenant_id)
            ->with('uploader:id,name')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->through(fn (TenantFile $f) => [
                'id' => $f->id,
                'original_name' => $f->original_name,
                'mime_type' => $f->mime_type,
                'size' => $f->size,
                'path' => $f->path,
                'visibility' => $f->visibility,
                'meta' => $f->meta,
                'created_at' => $f->created_at?->toISOString(),
                'uploader' => $f->uploader ? ['id' => $f->uploader->id, 'name' => $f->uploader->name] : null,
            ]);

        // Total storage used in bytes
        $usageBytes = TenantFile::on('central')
            ->where('tenant_id', $user->tenant_id)
            ->sum('size');

        $limitGb = 5; // Default; override from plan features when available
        $limitBytes = (int) ($limitGb * 1073741824);

        return Inertia::render('Tenant/Files/Index', [
            'files' => $files,
            'usageBytes' => (int) $usageBytes,
            'limitBytes' => $limitBytes,
            'canUpload' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store("tenants/{$user->tenant_id}/files", 'local');

        TenantFile::create([
            'tenant_id' => $user->tenant_id,
            'uploaded_by' => $user->id,
            'disk' => 'local',
            'path' => $path,
            'original_name' => $uploaded->getClientOriginalName(),
            'mime_type' => $uploaded->getClientMimeType(),
            'size' => $uploaded->getSize(),
            'visibility' => 'private',
            'meta' => [],
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    public function destroy(string $file): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $tenantFile = TenantFile::on('central')
            ->where('tenant_id', $user->tenant_id)
            ->where('id', $file)
            ->firstOrFail();

        Storage::disk($tenantFile->disk)->delete($tenantFile->path);
        $tenantFile->delete();

        return back()->with('success', 'File deleted successfully.');
    }

    public function download(string $file): StreamedResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $tenantFile = TenantFile::on('central')
            ->where('tenant_id', $user->tenant_id)
            ->where('id', $file)
            ->firstOrFail();

        return Storage::disk($tenantFile->disk)
            ->download($tenantFile->path, $tenantFile->original_name);
    }
}
