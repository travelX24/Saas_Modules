<?php

namespace Athka\Saas\Livewire\Companies;

use App\Models\User;
use Athka\Saas\Models\SaasCompany;
use Athka\Saas\Models\SaasCompanyDocument;
use Athka\Saas\Models\SaasCompanyOtherinfo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public int $tab = 1;

    // TAB 1: Basic Information
    public string $legal_name_ar = '';

    public ?string $legal_name_en = null;

    public string $company_type = 'company';

    public $logo = null;

    public ?string $main_industry = null;

    public ?string $main_industry_other = null;

    public string $sub_industries_text = '';

    public ?string $bio = null;

    // ✅ الدومين الرئيسي للشركة
    public string $primary_domain = '';

    // Company Admin (will be created and linked)
    public string $company_admin_name = '';

    public string $company_admin_email = '';

    // internal (not shown)
    public string $slug = '';

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

    // TAB 3: Additional Info (Subscription & Settings)
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

    // Store existing documents info for display (empty for create, populated for edit)
    public array $existingDocuments = [];

    public function mount(): void
    {
        // ✅ تعيين تاريخ اليوم تلقائياً لحقل Subscription Start
        if (empty($this->subscription_starts_at)) {
            $this->subscription_starts_at = now()->format('Y-m-d');
        }
    }

    private function isAr(): bool
    {
        return substr((string) app()->getLocale(), 0, 2) === 'ar';
    }

    private function txt(string $ar, string $en): string
    {
        return $this->isAr() ? $ar : $en;
    }

    public function updatedLegalNameAr(): void
    {
        // إذا المستخدم كتب subdomain لا تلمس slug
        if (trim($this->primary_domain) !== '') {
            return;
        }

        $base = $this->legal_name_en ?: $this->legal_name_ar;
        $slug = Str::slug($base) ?: 'company';
        
        if (trim($this->slug) === '') {
            $this->slug = $slug;
        }
        
        if (trim($this->primary_domain) === '') {
            $this->primary_domain = $slug;
        }
    }

    public function updatedLegalNameEn(): void
    {
        if (trim($this->primary_domain) !== '') {
            return;
        }

        $slug = Str::slug($this->legal_name_en ?: $this->legal_name_ar) ?: 'company';
        
        if (trim($this->slug) === '') {
            $this->slug = $slug;
        }
        
        if (trim($this->primary_domain) === '') {
            $this->primary_domain = $slug;
        }
    }

    // ✅ تطبيع الدومين (يشيل https:// و أي /path)
    private function normalizeSubdomain(?string $value): string
    {
        $v = trim(strtolower((string) $value));
        $v = preg_replace('#^https?://#i', '', $v);
        $v = preg_replace('#/.*$#', '', $v);
        $v = explode(':', $v)[0];      // يشيل البورت لو موجود
        $v = trim($v, '.');

        if ($v === '') {
            return '';
        }

        // لو كتب anas.com أو anas.athkahr.com نأخذ أول جزء فقط
        $first = explode('.', $v)[0];

        // نخليها حروف/أرقام/-
        return Str::slug($first);
    }

    public function updatedPrimaryDomain($value): void
    {
        $sub = $this->normalizeSubdomain($value);
        $this->primary_domain = $sub;

        // ✅ أهم نقطة: خلّي slug يساوي subdomain
        $this->slug = $sub;
    }

    // ✅ رسائل Validation مفهومة (بدون اعتماد على ملفات lang)
    private function validationMessages(): array
    {
        return [
            'required' => $this->txt('حقل :attribute مطلوب.', 'The :attribute field is required.'),
            'email' => $this->txt('يرجى إدخال بريد إلكتروني صحيح.', 'Please enter a valid email address.'),
            'unique' => $this->txt('قيمة :attribute مستخدمة مسبقاً.', 'The :attribute has already been taken.'),
            'in' => $this->txt('القيمة المختارة في :attribute غير صحيحة.', 'The selected :attribute is invalid.'),
            'date' => $this->txt('يرجى إدخال تاريخ صحيح في :attribute.', 'The :attribute is not a valid date.'),
            'after_or_equal' => $this->txt('يجب أن يكون :attribute بعد أو يساوي :date.', 'The :attribute must be a date after or equal to :date.'),
            'integer' => $this->txt('يرجى إدخال رقم صحيح في :attribute.', 'The :attribute must be an integer.'),
            'numeric' => $this->txt('يرجى إدخال رقم صحيح في :attribute.', 'The :attribute must be a number.'),
            'min' => $this->txt('قيمة :attribute يجب ألا تقل عن :min.', 'The :attribute must be at least :min.'),
            'max' => $this->txt('قيمة :attribute يجب ألا تزيد عن :max.', 'The :attribute may not be greater than :max.'),
            'image' => $this->txt('يرجى رفع صورة صالحة في :attribute.', 'The :attribute must be an image.'),
            'file' => $this->txt('يرجى رفع ملف صالح في :attribute.', 'Please upload a valid file for :attribute.'),
            'mimes' => $this->txt('يجب أن يكون الملف في :attribute من نوع: :values.', 'The :attribute must be a file of type: :values.'),
        ];
    }

    private function validationAttributes(): array
    {
        // نفس عناوين الحقول بالواجهة (تطلع عربي/إنجليزي حسب tr())
        return [
            'legal_name_ar' => tr('Legal Name (AR)'),
            'legal_name_en' => tr('Legal Name (EN)'),
            'company_type' => tr('Company Type'),
            'logo' => tr('Logo'),
            'main_industry' => tr('Main Industry'),
            'main_industry_other' => tr('Other Industry'),
            'sub_industries_text' => tr('Sub Industries'),
            'bio' => tr('Bio'),

            'company_admin_name' => tr('Company Admin Name'),
            'company_admin_email' => tr('Company Admin Email'),
            'primary_domain' => tr('Company Subdomain'),

            'official_email' => tr('Official Email'),
            'phone_1' => tr('Phone 1'),
            'phone_2' => tr('Phone 2'),
            'country' => tr('Country'),
            'city' => tr('City'),
            'region' => tr('Region'),
            'address_line' => tr('Address'),
            'postal_code' => tr('Postal Code'),
            'lat' => tr('Lat'),
            'lng' => tr('Lng'),

            'license_number' => tr('License Number'),
            'tax_number' => tr('Tax Number'),
            'cr_number' => tr('CR Number'),
            'subscription_starts_at' => tr('Subscription Start'),
            'subscription_ends_at' => tr('Subscription End'),
            'allowed_users' => tr('Allowed Users'),
            'timezone' => tr('Timezone'),
            'default_locale' => tr('Default Locale'),
            'datetime_format' => tr('DateTime Format'),

            'doc_cr' => tr('CR Document'),
            'doc_vat' => tr('VAT Certificate'),
            'doc_activity_license' => tr('Activity License'),
            'doc_incorporation' => tr('Incorporation Contract'),
            'doc_owner_id' => tr('Owner ID / Passport'),
            'doc_national_address' => tr('National Address Document'),
        ];
    }

    // --------- Validation per tab ----------
    private function rulesTab1(): array
    {
        return [
            // ✅ Required
            'legal_name_ar' => ['required', 'string', 'max:190'],
            'company_type' => ['required', 'in:individual,foundation,company'],

            'company_admin_name' => ['required', 'string', 'max:190'],
            // لاحظ: بدون dns لتجنب مشاكل بيئة لوكال
            'company_admin_email' => ['required', 'email', 'max:190', 'unique:users,email'],

            'primary_domain' => [
                'required',
                'string',
                'max:63',
                function ($attr, $value, $fail) {
                    $sub = $this->normalizeSubdomain($value);

                    // subdomain: anas | anas-1 | a1
                    $ok = preg_match('/^[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/', $sub);
                    if (! $ok) {
                        $fail($this->txt('صيغة الساب دومين غير صحيحة (استخدم حروف/أرقام و - فقط).', 'Invalid subdomain format (letters/numbers and hyphen only).'));

                        return;
                    }

                    // كلمات محجوزة (اختياري لكن مفيد)
                    $reserved = ['www', 'admin', 'api', 'saas', 'app'];
                    if (in_array($sub, $reserved, true)) {
                        $fail($this->txt('هذا الساب دومين محجوز.', 'This subdomain is reserved.'));

                        return;
                    }

                    // ✅ يجب أن يكون فريد
                    if (SaasCompany::where('slug', $sub)->exists() || SaasCompany::where('primary_domain', $sub)->exists()) {
                        $fail($this->txt('هذا الساب دومين مستخدم مسبقاً.', 'This subdomain is already used.'));
                    }
                },
            ],

            // Optional
            'legal_name_en' => ['nullable', 'string', 'max:190'],
            'logo' => ['nullable', 'image', 'max:2048'],

            'main_industry' => ['nullable', 'string', 'max:190'],
            'main_industry_other' => ['nullable', 'required_if:main_industry,أخرى', 'string', 'max:190'],
            'sub_industries_text' => ['nullable', 'string', 'max:1000'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ];
    }

    private function rulesTab2(): array
    {
        return [
            'official_email' => ['nullable', 'email', 'max:190'],
            'phone_1' => ['nullable', 'string', 'max:50'],
            'phone_2' => ['nullable', 'string', 'max:50'],

            // ✅ Required (خفيفة)
            'country' => ['required', 'string', 'max:120'],
            'city' => ['required', 'string', 'max:120'],

            'region' => ['nullable', 'string', 'max:120'],
            'address_line' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
        ];
    }

    private function rulesTab3(): array
    {
        return [
            'license_number' => ['nullable', 'string', 'max:190'],
            'tax_number' => ['nullable', 'string', 'max:190'],
            'cr_number' => ['nullable', 'string', 'max:190'],

            // ✅ Required
            'subscription_starts_at' => ['required', 'date'],
            'subscription_ends_at' => ['required', 'date', 'after_or_equal:subscription_starts_at'],

            'allowed_users' => [
                'required',
                'integer',
                'min:1',
                'max:100000',
                function ($attribute, $value, $fail) {
                    // ✅ التحقق من أن allowed_users >= 1 (لأن admin هو المستخدم الأول)
                    if ($value < 1) {
                        $fail(tr('Allowed users must be at least 1 (for the company admin).'));
                    }
                },
            ],
            'timezone' => ['nullable', 'string', 'max:100'],
            'default_locale' => ['nullable', 'in:ar,en'],
            'datetime_format' => ['nullable', 'string', 'max:50'],
        ];
    }

    private function rulesTab4(): array
    {
        $file = ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240']; // 10MB

        return [
            'doc_cr' => $file,
            'doc_vat' => $file,
            'doc_activity_license' => $file,
            'doc_incorporation' => $file,
            'doc_owner_id' => $file,
            'doc_national_address' => $file,
        ];
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

    // ✅ منع القفز: لو رايح للأمام نتحقق من كل التبويبات السابقة
    public function goToTab(int $target): void
    {
        $target = max(1, min(4, $target));

        // إذا كان الانتقال للخلف، لا حاجة للتحقق
        if ($target < $this->tab) {
            $this->tab = $target;

            return;
        }

        // إذا كان الانتقال للأمام، نتحقق من التبويبات السابقة
        if ($target > $this->tab) {
            for ($t = $this->tab; $t < $target; $t++) {
                $this->validate(
                    $this->rulesForTab($t),
                    $this->validationMessages(),
                    $this->validationAttributes()
                );
            }
        }

        $this->tab = $target;
    }

    public function nextTab(): void
    {
        $this->validate(
            $this->rulesForTab($this->tab),
            $this->validationMessages(),
            $this->validationAttributes()
        );

        $this->tab = min(4, $this->tab + 1);
    }

    public function prevTab(): void
    {
        // الانتقال للخلف لا يحتاج validation
        $this->tab = max(1, $this->tab - 1);
    }

    // --------- Store ----------
    public function store()
    {
        try {
            $this->validate(array_merge(
                $this->rulesTab1(),
                $this->rulesTab2(),
                $this->rulesTab3(),
                $this->rulesTab4(),
            ), $this->validationMessages(), $this->validationAttributes());

            $sub = collect(explode(',', (string) $this->sub_industries_text))
                ->map(fn ($v) => trim($v))
                ->filter()
                ->values()
                ->all();

            $requestedSub = $this->normalizeSubdomain($this->primary_domain);

            // بما أن validation يضمن التفرد، نستخدمه مباشرة
            $slug = $requestedSub !== '' ? $requestedSub : $this->makeUniqueSlug(
                Str::slug($this->legal_name_en ?: $this->legal_name_ar) ?: 'company'
            );

            [$companyId, $adminId] = DB::transaction(function () use ($sub, $slug) {
                $company = SaasCompany::create([
                    'legal_name_ar' => $this->legal_name_ar,
                    'legal_name_en' => $this->legal_name_en,
                    'slug' => $slug,

                    // نخزنها نفس slug (لأنها فعلياً subdomain عندك الآن)
                    'primary_domain' => $slug,

                    'company_type' => $this->company_type,
                    'main_industry' => $this->main_industry === 'أخرى' ? $this->main_industry_other : $this->main_industry,
                    'sub_industries' => $sub ?: null,
                    'bio' => $this->bio,

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

                if ($this->logo) {
                    // ✅ حذف جميع الصور القديمة من مجلد logo
                    $logoDir = "saas/companies/{$company->id}/logo";
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($logoDir)) {
                        $oldFiles = \Illuminate\Support\Facades\Storage::disk('public')->files($logoDir);
                        foreach ($oldFiles as $oldFile) {
                            \Illuminate\Support\Facades\Storage::disk('public')->delete($oldFile);
                        }
                    }
                    
                    // ✅ حفظ الصورة الجديدة
                    $path = $this->logo->store($logoDir, 'public');
                    $company->update(['logo_path' => $path]);
                }

                $adminData = [
                    'name' => $this->company_admin_name,
                    'email' => $this->company_admin_email,
                    'password' => Hash::make(Str::random(48)),
                ];

                if (Schema::hasColumn('users', 'saas_company_id')) {
                    $adminData['saas_company_id'] = $company->id;
                }
                if (Schema::hasColumn('users', 'must_change_password')) {
                    $adminData['must_change_password'] = true;
                }
                if (Schema::hasColumn('users', 'email_verified_at')) {
                    $adminData['email_verified_at'] = null;
                }

                // ✅ التحقق من عدد المستخدمين المسموحين قبل إنشاء admin
                // (admin هو المستخدم الأول، لذا يجب أن يكون allowed_users >= 1)
                if ($this->allowed_users < 1) {
                    throw new \Exception(tr('Allowed users must be at least 1 (for the company admin).'));
                }

                $admin = User::create($adminData);

                try {
                    if (method_exists($admin, 'assignRole')) {
                        $admin->assignRole('company-admin');
                    }
                } catch (\Throwable $e) {
                    // ignore
                }

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

                $this->saveDoc($company->id, 'cr', $this->doc_cr);
                $this->saveDoc($company->id, 'vat', $this->doc_vat);
                $this->saveDoc($company->id, 'activity_license', $this->doc_activity_license);
                $this->saveDoc($company->id, 'incorporation', $this->doc_incorporation);
                $this->saveDoc($company->id, 'owner_id', $this->doc_owner_id);
                $this->saveDoc($company->id, 'national_address', $this->doc_national_address);

                return [$company->id, $admin->id];
            });

            try {
                $admin = User::findOrFail($adminId);

                $token = Password::broker()->createToken($admin);

                $relative = URL::temporarySignedRoute(
                    'saas.company-admin.password.create',
                    now()->addHours(24),
                    ['email' => $admin->email, 'token' => $token],
                    false // ✅ relative signature
                );

                $url = request()->getSchemeAndHttpHost().$relative; // ✅ نخلي الرابط مطلق للإيميل

                $locale = app()->getLocale(); // ✅ لغة الواجهة الحالية (en/ar)

                \Illuminate\Support\Facades\Notification::sendNow(
                    $admin,
                    (new \App\Notifications\CompanyAdminSetPasswordNotification(
                        $url,
                        $this->legal_name_ar
                    ))->locale($locale)
                );

            } catch (\Throwable $e) {
                report($e);
                session()->flash('warning', tr('Company created but invitation email could not be sent.'));
            }

            // ✅ استخدام 'status' بدلاً من 'success' لعرض الرسالة عبر flash-toast
            return redirect()->route('saas.companies.index')
                ->with('status', tr('Company created successfully'))
                ->with('company_admin_email', $this->company_admin_email);

        } catch (\Illuminate\Validation\ValidationException $e) {
            // إعادة رمي ValidationException للتعامل معها تلقائياً من Livewire
            throw $e;
        } catch (\Throwable $e) {
            report($e);
            
            // ✅ إظهار رسالة خطأ عبر flash-toast
            session()->flash('error', tr('Failed to create company. Please try again.'));
            
            // إعادة تحميل الصفحة لإظهار رسالة الخطأ
            return redirect()->route('saas.companies.create');
        }
    }

    private function makeUniqueSlug(string $base): string
    {
        $base = Str::slug($base) ?: 'company';
        $slug = $base;
        $i = 1;

        while (SaasCompany::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
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

    // ✅ Computed property لجلب الصناعات مترجمة
    public function getIndustriesProperty(): array
    {
        $locale = app()->getLocale();
        $industriesConfig = config('industries.main_industries', []);

        return collect($industriesConfig)->map(function ($english, $arabic) use ($locale) {
            return [
                'value' => $arabic, // نخزن العربي كـ value (كما هو عندك)
                'label' => $locale === 'en' ? $english : $arabic,
            ];
        })->values()->toArray();
    }

    public function existingDocument(string $type): ?array
    {
        // In create mode, there are no existing documents, so always return null
        return $this->existingDocuments[$type] ?? null;
    }

    public function render()
    {
        return view('saas::companies.create')
            ->extends('saas::layouts.saas')
            ->section('content');
    }
}
