<?php

namespace App\Http\Controllers\Web\Tenant;

use App\Central\Models\Tenant;
use App\Central\Models\TenantFile;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * Resolve the current tenant ID from TenantContext, falling back to the
     * authenticated user's tenant_id.
     */
    private function resolveTenantId(): ?string
    {
        return $this->tenantContext->getOrNull()?->id
            ?? Auth::user()?->tenant_id;
    }

    private function requireTenantId(): string
    {
        $tenantId = $this->resolveTenantId();

        if ($tenantId === null) {
            abort(403, 'No tenant context available. Please select a tenant first.');
        }

        return $tenantId;
    }

    public function index(Request $request): Response
    {
        $tenantId = $this->resolveTenantId();
        /** @var User|null $user */
        $user = Auth::user();

        $tenantOptions = $user?->isSuperAdmin()
            ? Tenant::on('central')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Tenant $tenant) => ['id' => $tenant->id, 'name' => $tenant->name])
                ->values()
                ->all()
            : [];

        $filesQuery = TenantFile::on('central')
            ->with('uploader:id,name')
            ->orderByDesc('created_at');

        if ($tenantId !== null) {
            $filesQuery->where('tenant_id', $tenantId);
        } else {
            $filesQuery->whereRaw('1 = 0');
        }

        $files = $filesQuery
            ->paginate(20)
            ->withQueryString()
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
        $usageBytes = $tenantId === null
            ? 0
            : TenantFile::on('central')->where('tenant_id', $tenantId)->sum('size');

        $limitGb = 5; // Default; override from plan features when available
        $limitBytes = (int) ($limitGb * 1073741824);

        return Inertia::render('Tenant/Files/Index', [
            'files' => $files,
            'usageBytes' => (int) $usageBytes,
            'limitBytes' => $limitBytes,
            'canUpload' => $tenantId !== null || $user?->isSuperAdmin(),
            'selectedTenantId' => $tenantId,
            'tenantOptions' => $tenantOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $tenantId = $this->resolveTenantId();

        if ($tenantId === null) {
            return back()->with('error', 'Please select a tenant context before uploading files.');
        }

        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $uploaded = $request->file('file');
        $path = $uploaded->store("tenants/{$tenantId}/files", 'local');

        TenantFile::create([
            'tenant_id' => $tenantId,
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
        $tenantId = $this->requireTenantId();

        $tenantFile = TenantFile::on('central')
            ->where('tenant_id', $tenantId)
            ->where('id', $file)
            ->firstOrFail();

        Storage::disk($tenantFile->disk)->delete($tenantFile->path);
        $tenantFile->delete();

        return back()->with('success', 'File deleted successfully.');
    }

    public function download(string $file): StreamedResponse
    {
        $tenantId = $this->requireTenantId();

        $tenantFile = TenantFile::on('central')
            ->where('tenant_id', $tenantId)
            ->where('id', $file)
            ->firstOrFail();

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($tenantFile->disk);

        return $disk->download($tenantFile->path, $tenantFile->original_name);
    }

    public function preview(string $file): BinaryFileResponse
    {
        $tenantId = $this->requireTenantId();

        $tenantFile = TenantFile::on('central')
            ->where('tenant_id', $tenantId)
            ->where('id', $file)
            ->firstOrFail();

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk($tenantFile->disk);

        abort_unless($disk->exists($tenantFile->path), 404);

        return response()->file($disk->path($tenantFile->path), [
            'Content-Type' => $tenantFile->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addcslashes($tenantFile->original_name, '"\\').'"',
        ]);
    }
}
