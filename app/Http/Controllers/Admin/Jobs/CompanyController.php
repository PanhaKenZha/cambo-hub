<?php

namespace App\Http\Controllers\Admin\Jobs;

use App\Enums\PermissionAction;
use App\Enums\PermissionArea;
use App\Enums\VerificationStatus;
use App\Http\Controllers\Admin\Cms\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\JobContentSync;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Employers and their verification. Verification documents are stored privately.
 */
class CompanyController extends Controller
{
    use HandlesImageUploads;

    public function __construct(private AuditLogger $auditLogger) {}

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'verification' => ['nullable', Rule::enum(VerificationStatus::class)],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        return view('admin.jobs.companies.index', [
            'companies' => Company::query()
                ->with('owner')
                ->withCount('jobListings')
                ->when($filters['verification'] ?? null, fn ($query, string $status) => $query->where('verification_status', $status))
                ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                    ->where(fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")))
                ->orderByRaw("case when verification_status = 'pending' then 0 else 1 end")
                ->orderBy('name')
                ->paginate(25)
                ->withQueryString(),
            'filters' => $filters,
            'statusCounts' => Company::query()->toBase()->selectRaw('verification_status, count(*) as total')->groupBy('verification_status')->pluck('total', 'verification_status'),
        ]);
    }

    public function create(): View
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Create);

        return view('admin.jobs.companies.form', ['company' => new Company(['is_active' => true, 'verification_status' => VerificationStatus::Unverified])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Create);

        $company = Company::create($this->validatedData($request));
        $this->auditLogger->log('jobs.company.created', $company, newValues: $company->only(['name', 'owner_id']));

        return redirect()->route('admin.jobs.companies.edit', $company)->with('status', 'Company added.');
    }

    public function edit(Company $company): View
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Edit);

        return view('admin.jobs.companies.form', ['company' => $company->load(['owner', 'verifier'])->loadCount('jobListings')]);
    }

    public function update(Request $request, Company $company, JobContentSync $sync): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Edit);

        $oldValues = $company->only(['name', 'owner_id', 'is_active']);
        $company->update($this->validatedData($request, $company));
        $this->auditLogger->log('jobs.company.updated', $company, $oldValues, $company->only(array_keys($oldValues)));

        // Company name and logo are shown on homepage job cards.
        $company->jobListings()->each(fn ($job) => $sync->sync($job));

        return back()->with('status', 'Company saved.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Delete);

        if ($company->jobListings()->exists()) {
            return back()->withErrors(['company' => 'Delete or move this company’s jobs before deleting the company.']);
        }

        $this->auditLogger->log('jobs.company.deleted', $company, $company->only(['name', 'owner_id']));
        $this->deleteImage($company->logo_path);
        if ($company->verification_document_path) {
            Storage::disk('local')->delete($company->verification_document_path);
        }
        $company->delete();

        return redirect()->route('admin.jobs.companies.index')->with('status', 'Company deleted.');
    }

    /**
     * Approve or reject a company's verification.
     */
    public function verify(Request $request, Company $company, JobContentSync $sync): RedirectResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Manage);
        $data = $request->validateWithBag('verification', [
            'decision' => ['required', Rule::in(['verify', 'reject', 'revoke'])],
            'note' => ['nullable', 'required_if:decision,reject', 'string', 'max:1000'],
        ], ['note.required_if' => 'Explain why the company was not verified.']);

        $oldStatus = $company->verification_status;
        $company->update([
            'verification_status' => match ($data['decision']) {
                'verify' => VerificationStatus::Verified,
                'reject' => VerificationStatus::Rejected,
                default => VerificationStatus::Unverified,
            },
            'verification_note' => $data['note'] ?? null,
            'verified_by' => $request->user()->id,
            'verified_at' => $data['decision'] === 'verify' ? now() : null,
        ]);
        $this->auditLogger->log('jobs.company.verification', $company, ['status' => $oldStatus->value], ['status' => $company->verification_status->value, 'note' => $data['note'] ?? null]);
        $company->jobListings()->each(fn ($job) => $sync->sync($job));

        return back()->with('status', match ($data['decision']) {
            'verify' => "{$company->name} is now verified.",
            'reject' => 'Verification rejected.',
            default => 'Verification removed.',
        });
    }

    public function document(Company $company): StreamedResponse
    {
        $this->authorizePermission(PermissionArea::Jobs, PermissionAction::Edit);
        abort_unless($company->verification_document_path && Storage::disk('local')->exists($company->verification_document_path), 404);

        return Storage::disk('local')->download($company->verification_document_path, Str::slug($company->name).'-verification.'.pathinfo($company->verification_document_path, PATHINFO_EXTENSION));
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedData(Request $request, ?Company $company = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description.*' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'url', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],
            'owner_email' => ['nullable', 'email', Rule::exists('users', 'email')],
            'logo' => $this->imageRules(required: false),
            'verification_document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'is_active' => ['boolean'],
        ], ['owner_email.exists' => 'No account uses this email address.'], ['owner_email' => 'owner email']);

        $values = [
            'name' => $data['name'],
            'slug' => $company?->slug ?? Str::slug($data['name']).'-'.Str::lower(Str::random(4)),
            'description' => Company::normalizeTranslations($data['description'] ?? []),
            'website' => $data['website'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'owner_id' => filled($data['owner_email'] ?? null) ? User::where('email', $data['owner_email'])->value('id') : null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('logo')) {
            $values['logo_path'] = $this->storeImage($request->file('logo'), 'companies', $company?->logo_path);
        }

        if ($request->hasFile('verification_document')) {
            if ($company?->verification_document_path) {
                Storage::disk('local')->delete($company->verification_document_path);
            }
            $values['verification_document_path'] = $request->file('verification_document')->store('verification-documents', 'local');

            if ($company === null || $company->verification_status !== VerificationStatus::Verified) {
                $values['verification_status'] = VerificationStatus::Pending;
            }
        }

        return $values;
    }
}
