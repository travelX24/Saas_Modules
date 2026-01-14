<?php

namespace Athka\Saas\Livewire\Companies;

use Athka\Saas\Models\SaasCompany;
use Athka\Saas\Models\SaasCompanyDocument;
use Athka\Saas\Models\SaasCompanyOtherinfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public int $companyId;

    public int $tab = 1;

    public bool $isEditMode = true;

    // TAB 1: Basic Information
    public string $legal_name_ar = '';

    public ?string $legal_name_en = null;

    public string $company_type = 'company';

    public $logo = null;

    public ?string $logoPath = null;

    public ?string $main_industry = null;

    public ?string $main_industry_other = null;

    public string $sub_industries_text = '';

    public ?string $bio = null;

    public string $primary_domain = '';

    // TAB 2: Address & Contact
    public ?string $official_email = null;

    public ?string $phone_1 = null;

    public ?string $phone_2 = null;

    public ?string $country = null;

    public ?string $city = null;

    public ?string $region = null;

    public ?string $address_line = null;

    public ?string $postal_code = null;

    public ?string $lat = null;

    public ?string $lng = null;

    // TAB 3: Additional Info
    public ?string $license_number = null;

    public ?string $tax_number = null;

    public ?string $cr_number = null;

    public ?string $subscription_starts_at = null;

    public ?string $subscription_ends_at = null;

    public int $allowed_users = 1;

    public string $timezone = 'Asia/Aden';

    public string $default_locale = 'ar';

    public string $datetime_format = 'Y-m-d H:i';

    // TAB 4: Documents
    public $doc_cr = null;

    public $doc_vat = null;

    public $doc_activity_license = null;

    public $doc_incorporation = null;

    public $doc_owner_id = null;

    public $doc_national_address = null;

    // Store existing documents info for display
    public array $existingDocuments = [];

    public function mount(int $companyId): void
    {
        $this->companyId = $companyId;
        $company = SaasCompany::with(['settings', 'documents'])->findOrFail($companyId);

        // Load company data
        $this->legal_name_ar = $company->legal_name_ar;
        $this->legal_name_en = $company->legal_name_en;
        $this->company_type = $company->company_type;
        $this->logoPath = $company->logo_path;
        
        // Load main_industry - check if it's in the list or custom
        $industriesConfig = config('industries.main_industries', []);
        $savedIndustry = $company->main_industry;
        
        if ($savedIndustry && array_key_exists($savedIndustry, $industriesConfig)) {
            // Industry is in the list
            $this->main_industry = $savedIndustry;
        } elseif ($savedIndustry) {
            // Custom industry - set as "أخرى" and put value in other
            $this->main_industry = 'أخرى';
            $this->main_industry_other = $savedIndustry;
        }
        
        $this->sub_industries_text = $company->sub_industries ? implode(', ', $company->sub_industries) : '';
        $this->bio = $company->bio;
        $this->primary_domain = $company->primary_domain;

        // Address
        $this->official_email = $company->official_email;
        $this->phone_1 = $company->phone_1;
        $this->phone_2 = $company->phone_2;
        $this->country = $company->country;
        $this->city = $company->city;
        $this->region = $company->region;
        $this->address_line = $company->address_line;
        $this->postal_code = $company->postal_code;
        $this->lat = $company->lat;
        $this->lng = $company->lng;

        // Settings
        if ($company->settings) {
            $this->license_number = $company->settings->license_number;
            $this->tax_number = $company->settings->tax_number;
            $this->cr_number = $company->settings->cr_number;
            $this->subscription_starts_at = $company->settings->subscription_starts_at?->format('Y-m-d');
            $this->subscription_ends_at = $company->settings->subscription_ends_at?->format('Y-m-d');
            $this->allowed_users = $company->settings->allowed_users;
            $this->timezone = $company->settings->timezone;
            $this->default_locale = $company->settings->default_locale;
            $this->datetime_format = $company->settings->datetime_format;
        }

        // Load existing documents info
        $this->existingDocuments = [];
        if ($company->documents) {
            foreach ($company->documents as $doc) {
                $cleanPath = str_replace('\\', '/', $doc->file_path);
                $cleanPath = ltrim($cleanPath, '/');
                
                $this->existingDocuments[$doc->type] = [
                    'file_path' => $doc->file_path,
                    'original_name' => $doc->original_name ?: basename($doc->file_path),
                    'url' => asset('storage/'.$cleanPath),
                ];
            }
        }
    }

    public function goToTab(int $target): void
    {
        $target = max(1, min(4, $target));
        $this->tab = $target;
    }

    public function update(): void
    {
        $this->validate($this->rulesForTab($this->tab));

        try {
            DB::transaction(function () {
                $company = SaasCompany::findOrFail($this->companyId);

                $sub = $this->sub_industries_text
                    ? array_map('trim', explode(',', $this->sub_industries_text))
                    : null;

                // Update company
                $company->update([
                    'legal_name_ar' => $this->legal_name_ar,
                    'legal_name_en' => $this->legal_name_en,
                    'company_type' => $this->company_type,
                    'main_industry' => $this->main_industry === 'أخرى' ? $this->main_industry_other : $this->main_industry,
                    'sub_industries' => $sub ?: null,
                    'bio' => $this->bio,
                    'primary_domain' => $this->primary_domain,
                    'official_email' => $this->official_email,
                    'phone_1' => $this->phone_1,
                    'phone_2' => $this->phone_2,
                    'country' => $this->country,
                    'city' => $this->city,
                    'region' => $this->region,
                    'address_line' => $this->address_line,
                    'postal_code' => $this->postal_code,
                    'lat' => $this->lat,
                    'lng' => $this->lng,
                ]);

                // Update logo if provided
                if ($this->logo) {
                    $logoDir = "saas/companies/{$company->id}/logo";
                    if (Storage::disk('public')->exists($logoDir)) {
                        $oldFiles = Storage::disk('public')->files($logoDir);
                        foreach ($oldFiles as $oldFile) {
                            Storage::disk('public')->delete($oldFile);
                        }
                    }
                    $path = $this->logo->store($logoDir, 'public');
                    $company->update(['logo_path' => $path]);
                }

                // Update settings
                if ($company->settings) {
                    $company->settings->update([
                        'license_number' => $this->license_number,
                        'tax_number' => $this->tax_number,
                        'cr_number' => $this->cr_number,
                        'subscription_starts_at' => $this->subscription_starts_at,
                        'subscription_ends_at' => $this->subscription_ends_at,
                        'allowed_users' => $this->allowed_users,
                        'timezone' => $this->timezone ?: 'Asia/Aden',
                        'default_locale' => $this->default_locale ?: 'ar',
                        'datetime_format' => $this->datetime_format ?: 'Y-m-d H:i',
                    ]);
                } else {
                    SaasCompanyOtherinfo::create([
                        'company_id' => $company->id,
                        'license_number' => $this->license_number,
                        'tax_number' => $this->tax_number,
                        'cr_number' => $this->cr_number,
                        'subscription_starts_at' => $this->subscription_starts_at,
                        'subscription_ends_at' => $this->subscription_ends_at,
                        'allowed_users' => $this->allowed_users,
                        'timezone' => $this->timezone ?: 'Asia/Aden',
                        'default_locale' => $this->default_locale ?: 'ar',
                        'datetime_format' => $this->datetime_format ?: 'Y-m-d H:i',
                    ]);
                }

                // Update documents
                $this->saveDoc($company->id, 'cr', $this->doc_cr);
                $this->saveDoc($company->id, 'vat', $this->doc_vat);
                $this->saveDoc($company->id, 'activity_license', $this->doc_activity_license);
                $this->saveDoc($company->id, 'incorporation', $this->doc_incorporation);
                $this->saveDoc($company->id, 'owner_id', $this->doc_owner_id);
                $this->saveDoc($company->id, 'national_address', $this->doc_national_address);
            });

            // مسح Cache الفلاتر لإظهار المدينة الجديدة في قائمة المدن (لجميع اللغات)
            foreach (['ar', 'en'] as $lang) {
                Cache::forget("companies:filters:industries:{$lang}");
                Cache::forget("companies:filters:locations:{$lang}");
            }

            session()->flash('status', tr('Company updated successfully'));
            $this->dispatch('company-updated');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', tr('Failed to update company. Please try again.'));
        }
    }

    private function rulesForTab(int $tab): array
    {
        return match ($tab) {
            1 => $this->rulesTab1(),
            2 => $this->rulesTab2(),
            3 => $this->rulesTab3(),
            4 => $this->rulesTab4(),
            default => [],
        };
    }

    private function rulesTab1(): array
    {
        return [
            'legal_name_ar' => ['required', 'string', 'max:190'],
            'legal_name_en' => ['nullable', 'string', 'max:190'],
            'company_type' => ['required', 'in:individual,foundation,company'],
            'primary_domain' => ['required', 'string', 'max:190'],
        ];
    }

    private function rulesTab2(): array
    {
        return [
            'official_email' => ['nullable', 'email', 'max:190'],
            'phone_1' => ['nullable', 'string', 'max:50'],
            'phone_2' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
        ];
    }

    private function rulesTab3(): array
    {
        return [
            'license_number' => ['nullable', 'string', 'max:190'],
            'tax_number' => ['nullable', 'string', 'max:190'],
            'cr_number' => ['nullable', 'string', 'max:190'],
            'subscription_starts_at' => ['nullable', 'date'],
            'subscription_ends_at' => ['nullable', 'date', 'after_or_equal:subscription_starts_at'],
            'allowed_users' => ['required', 'integer', 'min:1', 'max:100000'],
            'timezone' => ['nullable', 'string', 'max:100'],
            'default_locale' => ['nullable', 'in:ar,en'],
            'datetime_format' => ['nullable', 'string', 'max:50'],
        ];
    }

    private function rulesTab4(): array
    {
        $file = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'];

        return [
            'doc_cr' => $file,
            'doc_vat' => $file,
            'doc_activity_license' => $file,
            'doc_incorporation' => $file,
            'doc_owner_id' => $file,
            'doc_national_address' => $file,
        ];
    }

    private function saveDoc(int $companyId, string $type, $file): void
    {
        if (! $file) {
            return;
        }

        $path = $file->store("saas/companies/{$companyId}/documents", 'public');

        SaasCompanyDocument::updateOrCreate(
            ['company_id' => $companyId, 'type' => $type],
            [
                'file_path' => $path,
                'original_name' => method_exists($file, 'getClientOriginalName') ? $file->getClientOriginalName() : null,
                'mime' => method_exists($file, 'getMimeType') ? $file->getMimeType() : null,
                'size' => method_exists($file, 'getSize') ? $file->getSize() : null,
                'uploaded_by' => Auth::id(),
            ]
        );
    }

    public function getIndustriesProperty(): array
    {
        $locale = app()->getLocale();
        $industriesConfig = config('industries.main_industries', []);

        return collect($industriesConfig)->map(function ($english, $arabic) use ($locale) {
            return [
                'value' => $arabic,
                'label' => $locale === 'en' ? $english : $arabic,
            ];
        })->values()->toArray();
    }


    public function getLogoUrlProperty(): ?string
    {
        if ($this->logoPath) {
            $cleanPath = str_replace('\\', '/', $this->logoPath);
            $cleanPath = ltrim($cleanPath, '/');

            return asset('storage/'.$cleanPath);
        }

        return null;
    }

    public function existingDocument(string $type): ?array
    {
        return $this->existingDocuments[$type] ?? null;
    }

    public function render()
    {
        $company = SaasCompany::with(['settings', 'documents'])->findOrFail($this->companyId);

        return view('saas::companies.edit', [
            'company' => $company,
        ]);
    }
}
