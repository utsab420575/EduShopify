<?php

namespace App\Livewire\Auth;

use App\Mail\Subscription\SubscriptionActivatedMail;
use App\Models\AccountCapability;
use App\Models\BusinessHour;
use App\Models\CapabilityApplicationHistory;
use App\Models\City;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\Exhibition;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SubscriptionPlan;
use App\Models\SupplierDocument;
use App\Models\SupplierGallery;
use App\Models\SupplierProfile;
use App\Models\SupplierType;
use App\Models\SupplierVideo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class SupplierApplication extends Component
{
    use WithFileUploads;

    protected array $messages = [
        'gallery_files.max' => 'The gallery files field must not have more than 10 items. Please select again not more than 10.',
    ];

    // Current section (1–9)
    public int $step = 1;
    public int $totalSteps = 9;

    /* ── Step 9: Payment ── */
    public ?int    $selectedPlanId            = null;
    public string  $billingCycle              = 'monthly'; // 'monthly' | 'yearly'
    public ?string $paymentIntentClientSecret = null;
    public ?string $paymentError              = null;
    public bool    $paymentProcessing         = false;
    public bool    $profileSaved              = false;  // true once step-8 data is persisted

    /* ── Section 1: Company Info ── */
    public string $company_name    = '';
    public string $company_type    = '';
    public string $custom_company_type = '';
    public ?int   $country_id      = null;
    public ?int   $city_id         = null;
    public string $address         = '';
    public string $website         = '';
    public string $founded_year    = '';
    public string $employees       = '';

    /* ── Section 2: Branding ── */
    public $logo;
    public $banner;
    public $profile_photo;
    public array $gallery_files = [];   // multiple images
    public array $video_urls    = [];   // YouTube URLs, max 5

    /* ── Section 3: Supplier Types ── */
    public array $supplier_type_ids = [];  // max 5

    /* ── Section 4: Exhibitions (optional) ── */
    public array $exhibition_ids = [];

    /* ── Section 5: Contact ── */
    public string $contact_person = '';
    public string $contact_phone  = '';
    public string $contact_email  = '';
    public string $whatsapp       = '';
    public string $support_email  = '';

    /* ── Section 6: Social ── */
    public string $linkedin   = '';
    public string $facebook   = '';
    public string $instagram  = '';
    public string $youtube    = '';
    public string $x          = '';

    /* ── Section 7: Documents ── */
    public array $documents = [];  // keyed by document_type_id → uploaded file

    /* ── Section 8: Business Hours ── */
    public array $business_hours = [];
    public string $default_open_time = '09:00';
    public string $default_close_time = '17:00';

    // For dependent select
    public array $cities = [];

    /* ─────────────────── Lifecycle ─────────────────── */

    public function hydrate(): void
    {
        $this->cleanupMissingTempFiles();
    }

    protected function cleanupMissingTempFiles(): void
    {
        if ($this->logo && $this->isTempFileAndMissing($this->logo)) {
            $this->logo = null;
        }

        if ($this->banner && $this->isTempFileAndMissing($this->banner)) {
            $this->banner = null;
        }

        if ($this->profile_photo && $this->isTempFileAndMissing($this->profile_photo)) {
            $this->profile_photo = null;
        }

        if (is_array($this->gallery_files)) {
            $this->gallery_files = array_filter($this->gallery_files, function ($file) {
                return ! $this->isTempFileAndMissing($file);
            });
        }

        if (is_array($this->documents)) {
            foreach ($this->documents as $key => $file) {
                if ($file && $this->isTempFileAndMissing($file)) {
                    $this->documents[$key] = null;
                }
            }
        }
    }

    protected function isTempFileAndMissing($file): bool
    {
        if (is_object($file) && $file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
            try {
                if (! $file->exists()) {
                    return true;
                }
            } catch (\Throwable $e) {
                return true;
            }
        }
        return false;
    }

    public function mount(): void
    {
        // Pre-fill business hours with Mon–Fri open, Sat–Sun closed
        $this->business_hours = collect(range(0, 6))->map(fn($d) => [
            'day'        => $d,
            'day_name'   => BusinessHour::dayName($d),
            'is_open'    => $d >= 1 && $d <= 5,
            'open_time'  => '09:00',
            'close_time' => '17:00',
        ])->toArray();

        // Profiles belong to the account, and application state lives on the
        // supplier capability — not on the profile.
        $account    = Auth::user()->account;
        $profile    = $account?->supplierProfile;
        $capability = $account?->capabilityStatus('supplier');

        // Once submitted, the form is closed until Admin asks for a revision.
        if ($capability !== null && ! in_array($capability, ['draft', 'revision_required'], true)) {
            $this->redirect(route('supplier.pending'), navigate: false);
            return;
        }

        if ($profile) {
            $this->company_name = $profile->legal_name ?? $profile->display_name ?? '';
            $this->company_type = $profile->company_type ?? '';
            if ($this->company_type && !in_array($this->company_type, ['LLC', 'JSC', 'Sole Trader', 'Partnership', 'Corporation'])) {
                $this->custom_company_type = $this->company_type;
                $this->company_type = 'Other';
            }
            $this->country_id   = $profile->country_id;
            $this->city_id      = $profile->city_id;
            if ($this->country_id) {
                $this->cities = City::where('country_id', $this->country_id)->orderBy('name')->get(['id','name'])->toArray();
            }
            $this->address      = $profile->address ?? '';
            $this->website      = $profile->website ?? '';
            $this->founded_year = (string) ($profile->founded_year ?? '');
            $this->employees    = (string) ($profile->employees ?? '');
            $this->supplier_type_ids = $account->supplierTypes->pluck('id')->toArray();
            $this->exhibition_ids    = $account->exhibitions->pluck('id')->toArray();
            $this->contact_person = $profile->contact_person ?? '';
            $this->contact_phone  = $profile->contact_phone ?? '';
            $this->contact_email  = $profile->contact_email ?? '';
            $this->whatsapp       = $profile->whatsapp ?? '';
            $this->support_email  = $profile->support_email ?? '';
            $socials              = $profile->socials ?? [];
            $this->linkedin       = $socials['linkedin']  ?? '';
            $this->facebook       = $socials['facebook']  ?? '';
            $this->instagram      = $socials['instagram'] ?? '';
            $this->youtube        = $socials['youtube']   ?? '';
            $this->x              = $socials['x']         ?? $socials['twitter'] ?? '';
        }
    }

    /* ─────────────────── Country → City ─────────────────── */

    public function updatedCountryId(?int $value): void
    {
        $this->cities  = $value
            ? City::where('country_id', $value)->orderBy('name')->get(['id','name'])->toArray()
            : [];
        $this->city_id = null;
    }

    public function updatedWebsite(?string $value): void
    {
        if ($value) {
            $value = trim($value);
            if (! preg_match('/^https?:\/\//i', $value)) {
                $value = 'https://' . $value;
            } else {
                $value = preg_replace('/^http:\/\//i', 'https://', $value);
            }
            $this->website = $value;
        }
    }

    /* ─────────────────── Supplier Types (max 5) ─────────────────── */

    public function toggleSupplierType(int $id): void
    {
        if (in_array($id, $this->supplier_type_ids)) {
            $this->supplier_type_ids = array_values(array_diff($this->supplier_type_ids, [$id]));
        } elseif (count($this->supplier_type_ids) < 5) {
            $this->supplier_type_ids[] = $id;
        } else {
            $this->addError('supplier_types', 'You can select a maximum of 5 supplier types.');
        }
    }

    /* ─────────────────── Videos ─────────────────── */

    public function addVideoUrl(): void
    {
        if (count($this->video_urls) < 5) {
            $this->video_urls[] = '';
        }
    }

    public function removeVideoUrl(int $index): void
    {
        array_splice($this->video_urls, $index, 1);
        $this->video_urls = array_values($this->video_urls);
    }

    /* ─────────────────── Validation per step ─────────────────── */

    protected function rulesForStep(int $step): array
    {
        return match ($step) {
            1 => [
                'company_name' => 'required|string|max:200',
                'company_type' => 'nullable|string|max:100',
                'custom_company_type' => 'required_if:company_type,Other|nullable|string|max:100',
                'country_id'   => 'nullable|exists:countries,id',
                'city_id'      => 'nullable|exists:cities,id',
                'address'      => 'nullable|string|max:500',
                'website'      => [
                    'nullable',
                    'max:255',
                    function ($attribute, $value, $fail) {
                        $host = parse_url($value, PHP_URL_HOST);
                        if (!$host) {
                            $fail('The website must be a valid URL.');
                            return;
                        }
                        if (strpos($host, '.') === false) {
                            $fail('The website must be a valid domain (e.g., google.com).');
                            return;
                        }
                        if (str_starts_with(strtolower($host), 'www.')) {
                            if (substr_count($host, '.') < 2) {
                                $fail('The website must be a valid domain (e.g., www.google.com).');
                                return;
                            }
                        }
                        if (!preg_match('/^[a-z0-9-]+(\.[a-z0-9-]+)+$/i', $host)) {
                            $fail('The website must contain only valid domain characters.');
                        }
                    }
                ],
                'founded_year' => 'nullable|digits:4|integer|min:1800|max:' . date('Y'),
                'employees'    => 'nullable|integer|min:1',
            ],
            2 => [
                'logo'           => 'nullable|image|max:5120',
                'banner'         => 'nullable|image|max:5120',
                'profile_photo'  => 'nullable|image|max:5120',
                'gallery_files'  => 'nullable|array|max:10',
                'gallery_files.*'=> 'image|max:5120',
                'video_urls'     => 'nullable|array|max:5',
                'video_urls.*'   => 'nullable|url',
            ],
            3 => [
                'supplier_type_ids'   => 'required|array|min:1|max:5',
                'supplier_type_ids.*' => 'exists:supplier_types,id',
            ],
            4 => [
                'exhibition_ids'   => 'nullable|array',
                'exhibition_ids.*' => 'exists:exhibitions,id',
            ],
            5 => [
                'contact_person' => 'required|string|max:150',
                'contact_phone'  => 'required|string|max:30',
                'contact_email'  => 'required|email|max:150',
                'whatsapp'       => 'nullable|string|max:30',
                'support_email'  => 'nullable|email|max:150',
            ],
            6 => [
                'linkedin'  => 'nullable|url|max:255',
                'facebook'  => 'nullable|url|max:255',
                'instagram' => 'nullable|url|max:255',
                'youtube'   => 'nullable|url|max:255',
                'x'         => 'nullable|url|max:255',
            ],
            7 => $this->documentRules(),
            8 => [
                'business_hours'                => 'required|array|size:7',
                'business_hours.*.is_open'      => 'boolean',
                'business_hours.*.open_time'    => 'nullable|date_format:H:i',
                'business_hours.*.close_time'   => 'nullable|date_format:H:i',
            ],
            default => [],
        };
    }

    protected function documentRules(): array
    {
        $rules = [];
        $docTypes = DocumentType::where('is_active', true)
            ->whereHas('capabilityEnables', function ($q) {
                $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'));
            })
            ->get();

        foreach ($docTypes as $dt) {
            $key = "documents.{$dt->id}";
            if ($dt->is_required) {
                // Required only if no existing document for this type
                $existing = Auth::user()->supplierDocuments()
                    ->where('document_type_id', $dt->id)
                    ->exists();
                $rules[$key] = $existing ? 'nullable|file|max:' . $dt->max_size_kb : 'required|file|max:' . $dt->max_size_kb;
            } else {
                $rules[$key] = 'nullable|file|max:' . ($dt->max_size_kb ?? 5120);
            }
        }

        return $rules;
    }

    public function applyDefaultHours(): void
    {
        foreach ($this->business_hours as $index => $bh) {
            if ($bh['is_open']) {
                $this->business_hours[$index]['open_time'] = $this->default_open_time;
                $this->business_hours[$index]['close_time'] = $this->default_close_time;
            }
        }
    }

    /* ─────────────────── Navigation ─────────────────── */

    public function nextStep(): void
    {
        $this->validate($this->rulesForStep($this->step));

        // Step 8 → save profile as draft before advancing to payment step
        if ($this->step === 8) {
            $this->saveProfile();
            $this->profileSaved = true;
        }

        if ($this->step < $this->totalSteps) {
            $this->step++;
        }
    }

    public function prevStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    /* ─────────────────── Save Profile (Steps 1–8 data) ─────────────────── */

    /**
     * Persist all profile data. Capability status is untouched here; submitCapabilityForReview() moves it.
     * Called when advancing from step 8 to step 9.
     */
    protected function saveProfile(): void
    {
        $user    = Auth::user();
        $account = $user->account;

        if (! $account) {
            throw new \RuntimeException('No account context could be resolved for this user.');
        }

        DB::transaction(function () use ($user, $account) {

            /* -- Section 1 & 2: Profile (owned by the ACCOUNT) -- */
            $profile    = $account->supplierProfile;
            $logoPath   = $this->logo          ? $this->cropAndSaveImage($this->logo,          'suppliers/logos',    512,  512)  : ($profile?->logo          ?? null);
            $bannerPath = $this->banner         ? $this->cropAndSaveImage($this->banner,        'suppliers/banners',  1200, 400)  : ($profile?->banner         ?? null);
            $photoPath  = $this->profile_photo  ? $this->cropAndSaveImage($this->profile_photo, 'suppliers/photos',   500,  500)  : ($profile?->profile_photo  ?? null);

            $finalCompanyType = $this->company_type;
            if ($this->company_type === 'Other') {
                $finalCompanyType = $this->custom_company_type;
            }

            SupplierProfile::updateOrCreate(
                ['account_id' => $account->id],
                [
                    'slug'           => $this->generateSlug($account),
                    'display_name'   => $this->company_name ?: $account->display_name,
                    'legal_name'     => $this->company_name ?: null,
                    'company_type'   => $finalCompanyType ?: null,
                    'country_id'     => $this->country_id,
                    'city_id'        => $this->city_id,
                    'address'        => $this->address ?: null,
                    'website'        => $this->website ?: null,
                    'founded_year'   => $this->founded_year ?: null,
                    'employees'      => $this->employees ?: null,
                    'logo'           => $logoPath,
                    'banner'         => $bannerPath,
                    'profile_photo'  => $photoPath,
                    'contact_person' => $this->contact_person,
                    'contact_phone'  => $this->contact_phone,
                    'contact_email'  => $this->contact_email,
                    'whatsapp'       => $this->whatsapp ?: null,
                    'support_email'  => $this->support_email ?: null,
                    'socials'        => array_filter([
                        'linkedin'  => $this->linkedin  ?: null,
                        'facebook'  => $this->facebook  ?: null,
                        'instagram' => $this->instagram ?: null,
                        'youtube'   => $this->youtube   ?: null,
                        'x'         => $this->x         ?: null,
                    ]),
                ]
            );

            /* -- Gallery -- */
            foreach ($this->gallery_files as $file) {
                $path = $file->store('suppliers/gallery', 'public');
                SupplierGallery::create([
                    'supplier_account_id' => $account->id,
                    'image_path'          => $path,
                    'created_by_user_id'  => $user->id,
                ]);
            }

            /* -- Videos -- */
            foreach ($this->video_urls as $url) {
                $url = trim($url);
                if (! $url) continue;
                $id = $this->extractYouTubeId($url);
                if ($id) {
                    SupplierVideo::create([
                        'supplier_account_id' => $account->id,
                        'provider'            => 'youtube',
                        'video_id'            => $id,
                        'video_url'           => $url,
                        'created_by_user_id'  => $user->id,
                    ]);
                }
            }

            /* -- Supplier Types (max 5) — pivots key on supplier_account_id -- */
            $account->supplierTypes()->sync($this->supplier_type_ids);

            /* -- Exhibitions -- */
            $account->exhibitions()->sync($this->exhibition_ids);

            /* -- Documents -- */
            foreach ($this->documents as $docTypeId => $file) {
                if (! $file) continue;
                $path = $file->store("suppliers/documents/{$account->id}", 'public');
                SupplierDocument::updateOrCreate(
                    ['supplier_account_id' => $account->id, 'document_type_id' => $docTypeId],
                    [
                        'file_path'           => $path,
                        'original_name'       => $file->getClientOriginalName(),
                        'mime_type'           => $file->getMimeType(),
                        'file_size_kb'        => (int) ceil($file->getSize() / 1024),
                        'status'              => 'pending',
                        'uploaded_by_user_id' => $user->id,
                        'is_current'          => true,
                    ]
                );
            }

            /* -- Business Hours (account-level, no specific location) -- */
            foreach ($this->business_hours as $bh) {
                BusinessHour::updateOrCreate(
                    [
                        'supplier_account_id' => $account->id,
                        'account_location_id' => null,
                        'day_of_week'         => $bh['day'],
                    ],
                    [
                        'is_open'    => (bool) $bh['is_open'],
                        'open_time'  => $bh['is_open'] ? $bh['open_time']  : null,
                        'close_time' => $bh['is_open'] ? $bh['close_time'] : null,
                    ]
                );
            }
        });
    }

    /**
     * Move the Supplier capability into review (spec 9.1) and append the
     * immutable application-history row (spec 9.2).
     */
    protected function submitCapabilityForReview(): void
    {
        $user    = Auth::user();
        $account = $user->account;

        $capability = AccountCapability::firstOrNew([
            'account_id' => $account->id,
            'capability' => 'supplier',
        ]);

        $attempt = ((int) $capability->application_attempts) + 1;

        $capability->fill([
            'status'               => 'pending',
            'application_attempts' => $attempt,
            'applied_by_user_id'   => $user->id,
            'applied_at'           => now(),
            'revision_reason'      => null,
            'rejection_reason'     => null,
        ])->save();

        CapabilityApplicationHistory::updateOrCreate(
            ['account_capability_id' => $capability->id, 'attempt_no' => $attempt],
            [
                'submitted_snapshot' => [
                    'company_name'  => $this->company_name,
                    'company_type'  => $this->company_type,
                    'country_id'    => $this->country_id,
                    'city_id'       => $this->city_id,
                    'contact_email' => $this->contact_email,
                    'contact_phone' => $this->contact_phone,
                    'submitted_at'  => now()->toIso8601String(),
                ],
                'status' => 'submitted',
            ]
        );
    }

    /* ─────────────────── Step 9: Plan Selection & Payment ─────────────────── */

    public function selectPlan(int $planId): void
    {
        $this->selectedPlanId            = $planId;
        $this->paymentIntentClientSecret = null;
        $this->paymentError              = null;
    }

    public function setBillingCycle(string $cycle): void
    {
        $this->billingCycle              = $cycle;
        $this->selectedPlanId            = null;
        $this->paymentIntentClientSecret = null;
        $this->paymentError              = null;
    }

    /**
     * Creates a Stripe PaymentIntent for the selected paid plan and
     * dispatches the client_secret to the browser so Stripe.js can
     * mount the card element.
     */
    public function createPaymentIntent(): void
    {
        if (! $this->selectedPlanId) {
            $this->paymentError = 'Please select a plan first.';
            return;
        }

        $plan = SubscriptionPlan::find($this->selectedPlanId);

        if (! $plan || ! $plan->is_active) {
            $this->paymentError = 'This plan is no longer available.';
            return;
        }

        // Free plan — no Stripe intent needed
        if ($plan->isFree()) {
            return;
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $user = Auth::user();

            // Amount in smallest currency unit (cents)
            $amountCents = (int) round((float) $plan->price * 100);

            $intent = PaymentIntent::create([
                'amount'               => $amountCents,
                'currency'             => strtolower($plan->effectiveCurrencyCode()),
                'payment_method_types' => ['card'],
                'receipt_email'        => $user->email,
                'metadata'             => [
                    'supplier_account_id' => $user->account?->id,
                    'selected_by_user_id' => $user->id,
                    'plan_id'             => $plan->id,
                    'billing_type'        => $plan->billing_type,
                ],
            ]);

            $this->paymentIntentClientSecret = $intent->client_secret;

            // Dispatch to JS so Stripe.js can mount the card element
            $this->dispatch('stripeIntentReady', secret: $intent->client_secret);

        } catch (\Throwable $e) {
            Log::error('Registration PaymentIntent failed: ' . $e->getMessage());
            $this->paymentError = 'Unable to initialize payment. Please try again.';
        }
    }

    /**
     * Called from JS after stripe.confirmCardPayment() resolves successfully.
     * Verifies the PaymentIntent server-side, activates subscription, sets
     * the Supplier capability to pending, then redirects to the pending page.
     */
    public function confirmPaymentSuccess(string $paymentIntentId): void
    {
        if (! $this->selectedPlanId) {
            $this->paymentError = 'No plan selected.';
            return;
        }

        $plan = SubscriptionPlan::find($this->selectedPlanId);
        $user = Auth::user();

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $intent = PaymentIntent::retrieve($paymentIntentId);

            if ($intent->status !== 'succeeded') {
                $this->paymentError = 'Payment was not completed. Please try again.';
                $this->paymentProcessing = false;
                return;
            }

            $account = $user->account;

            DB::transaction(function () use ($user, $account, $plan, $paymentIntentId) {
                $sub = Subscription::create([
                    'supplier_account_id' => $account->id,
                    'plan_id'             => $plan->id,
                    'selected_by_user_id' => $user->id,
                    'provider'            => 'stripe',
                    'status'              => 'pending',
                ]);

                $sub->activate();

                SubscriptionPayment::create([
                    'subscription_id'     => $sub->id,
                    'supplier_account_id' => $account->id,
                    'provider'            => 'stripe',
                    'provider_payment_id' => $paymentIntentId,
                    'amount'              => $plan->price,
                    'currency_code'       => $plan->effectiveCurrencyCode(),
                    'status'              => 'paid',
                    'paid_at'             => now(),
                ]);

                // Submit the capability for Admin review.
                $this->submitCapabilityForReview();
            });

            try {
                $sub = Subscription::forSupplierAccount($account->id)->latest()->first();
                Mail::to($user->email)->send(new SubscriptionActivatedMail($sub));
            } catch (\Exception $e) {
                Log::warning('SubscriptionActivatedMail failed: ' . $e->getMessage());
            }

        } catch (\Throwable $e) {
            Log::error('confirmPaymentSuccess failed: ' . $e->getMessage());
            $this->paymentError = 'Payment verification failed. Please contact support.';
            $this->paymentProcessing = false;
            return;
        }

        session()->flash('success', 'Payment successful! Your application is now under review.');
        $this->redirect(route('supplier.pending'), navigate: false);
    }

    /**
     * Handles free plan selection at registration step 9.
     * Activates a free subscription and submits the profile for review.
     */
    public function activateFreePlan(): void
    {
        if (! $this->selectedPlanId) {
            $this->paymentError = 'Please select a plan first.';
            return;
        }

        $plan = SubscriptionPlan::find($this->selectedPlanId);
        $user = Auth::user();

        if (! $plan || ! $plan->isFree()) {
            $this->paymentError = 'Invalid plan selected.';
            return;
        }

        $account = $user->account;

        // A free plan may be taken once, and only while no subscription is live.
        if (! $account || $account->hasActiveSubscription()) {
            $this->paymentError = 'You already have an active subscription.';
            return;
        }

        DB::transaction(function () use ($user, $account, $plan) {
            $sub = Subscription::create([
                'supplier_account_id' => $account->id,
                'plan_id'             => $plan->id,
                'selected_by_user_id' => $user->id,
                'provider'            => 'free',
                'status'              => 'pending',
            ]);

            $sub->activate();

            SubscriptionPayment::create([
                'subscription_id'     => $sub->id,
                'supplier_account_id' => $account->id,
                'provider'            => 'free',
                'amount'              => 0,
                'currency_code'       => $plan->effectiveCurrencyCode(),
                'status'              => 'paid',
                'paid_at'             => now(),
            ]);

            $this->submitCapabilityForReview();
        });

        try {
            $sub = Subscription::forSupplierAccount($account->id)->latest()->first();
            Mail::to($user->email)->send(new SubscriptionActivatedMail($sub));
        } catch (\Exception $e) {
            Log::warning('SubscriptionActivatedMail failed: ' . $e->getMessage());
        }

        session()->flash('success', 'Application submitted! We\'ll review it and get back to you soon.');
        $this->redirect(route('supplier.pending'), navigate: false);
    }

    /* ─────────────────── Helpers ─────────────────── */

    protected function cropAndSaveImage($file, string $directory, int $width, int $height): string
    {
        $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
        $img = $manager->read($file->getRealPath());
        $img->cover($width, $height);

        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext === 'png') {
            $encoded = $img->toPng();
        } elseif ($ext === 'webp') {
            $encoded = $img->toWebp();
        } else {
            $encoded = $img->toJpeg(85);
            $ext = 'jpg';
        }

        $path = $directory . '/' . \Illuminate\Support\Str::random(40) . '.' . $ext;
        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $encoded->toString());

        return $path;
    }

    public function safeTemporaryUrl($file): ?string
    {
        if (! $file) {
            return null;
        }

        try {
            if (is_object($file) && method_exists($file, 'temporaryUrl')) {
                return $file->temporaryUrl();
            }
        } catch (\Throwable $e) {
            // Silently ignore temporary upload file errors
        }

        return null;
    }

    protected function generateSlug(mixed $account): string
    {
        $base = Str::slug($this->company_name ?: $account->display_name);
        $slug = $base;
        $i    = 2;

        while (SupplierProfile::where('slug', $slug)->where('account_id', '!=', $account->id)->exists()) {
            $slug = "$base-$i";
            $i++;
        }

        return $slug;
    }

    protected function extractYouTubeId(string $url): ?string
    {
        preg_match(
            '/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/',
            $url,
            $matches
        );

        return $matches[1] ?? null;
    }

    /* ─────────────────── Render ─────────────────── */

    public function render()
    {
        $allPlans     = SubscriptionPlan::active()->get();
        $monthlyPlans = $allPlans->where('billing_type', 'monthly')->values();
        $yearlyPlans  = $allPlans->where('billing_type', 'yearly')->values();
        $freePlans    = $allPlans->where('billing_type', 'free')->values();

        // At application time the capability is not yet approved, so eligibility
        // here only means "has not already consumed a free plan".
        $account        = Auth::user()->account;
        $isEligibleFree = $account === null || ! $account->subscriptions()
            ->whereHas('plan', fn ($q) => $q->where('is_free', true))
            ->exists();

        return view('livewire.auth.supplier-application', [
            'supplierTypes' => SupplierType::where('is_active', true)->orderBy('sort_order')->get(),
            'exhibitions'   => Exhibition::where('is_active', true)->orderBy('sort_order')->get(),
            'documentTypes' => DocumentType::where('is_active', true)
                ->whereHas('capabilityEnables', function ($q) {
                    $q->whereHas('capabilityType', fn($c) => $c->where('code', 'supplier'));
                })
                ->orderBy('sort_order')
                ->get(),
            'countries'     => Country::where('is_active', true)->orderBy('name')->get(['id','name','flag']),
            'monthlyPlans'  => $monthlyPlans,
            'yearlyPlans'   => $yearlyPlans,
            'freePlans'     => $freePlans,
            'isEligibleFree'=> $isEligibleFree,
        ])->layout('components.layouts.auth', [
            'title' => 'Supplier Application',
            'maxWidth' => 'max-w-3xl'
        ]);
    }
}
