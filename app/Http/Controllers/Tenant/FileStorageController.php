<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantFileRequest;
use App\Tenancy\TenantContext;
use App\Tenant\Services\TenantActivityLogService;
use App\Tenant\Services\TenantFileStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FileStorageController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenantContext,
        private readonly TenantFileStorageService $fileStorageService,
        private readonly TenantActivityLogService $activityLogService,
    ) {}

    public function index(): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $files = $this->fileStorageService->listFiles($tenant);

        return response()->json($files);
    }

    public function store(StoreTenantFileRequest $request): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $file = $this->fileStorageService->storeFile(
            tenant: $tenant,
            file: $request->file('file'),
            uploadedBy: $request->user()?->id,
            visibility: (string) $request->string('visibility', 'private'),
            meta: $request->validated('meta') ?? [],
        );

        $this->activityLogService->record(
            tenant: $tenant,
            action: 'tenant.files.uploaded',
            userId: $request->user()?->id,
            subjectType: 'file',
            subjectId: $file->id,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            meta: ['path' => $file->path, 'size' => $file->size],
        );

        return response()->json([
            'message' => 'File uploaded successfully.',
            'data' => $file,
        ], 201);
    }

    public function show(string $file): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $tenantFile = $this->fileStorageService->findFile($tenant, $file);

        return response()->json([
            'data' => $tenantFile,
            'download_url' => route('tenant.files.download', ['file' => $tenantFile->id]),
        ]);
    }

    public function destroy(string $file): JsonResponse
    {
        $tenant = $this->tenantContext->get();
        $tenantFile = $this->fileStorageService->findFile($tenant, $file);

        if (! request()->user()?->isTenantOwner() && request()->user()?->id !== $tenantFile->uploaded_by) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $this->fileStorageService->deleteFile($tenantFile);

        $this->activityLogService->record(
            tenant: $tenant,
            action: 'tenant.files.deleted',
            userId: request()->user()?->id,
            subjectType: 'file',
            subjectId: $tenantFile->id,
            ipAddress: request()->ip(),
            userAgent: request()->userAgent(),
            meta: ['path' => $tenantFile->path],
        );

        return response()->json(['message' => 'File deleted successfully.']);
    }

    public function download(string $file): BinaryFileResponse
    {
        $tenant = $this->tenantContext->get();
        $tenantFile = $this->fileStorageService->findFile($tenant, $file);

        return response()->download(
            Storage::disk($tenantFile->disk)->path($tenantFile->path),
            $tenantFile->original_name
        );
    }
}
