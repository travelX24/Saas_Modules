@php
    $documents = $company->documents;
    $docTypes = [
        'cr' => tr('CR Document'),
        'vat' => tr('VAT Certificate'),
        'activity_license' => tr('Activity License'),
        'incorporation' => tr('Incorporation Contract'),
        'owner_id' => tr('Owner ID / Passport'),
        'national_address' => tr('National Address Document'),
    ];
@endphp

<div class="space-y-3">
    @if($documents && $documents->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
            @foreach($documents as $doc)
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">
                        {{ $docTypes[$doc->type] ?? ucfirst($doc->type) }}
                    </label>
                    <div class="bg-gray-50 rounded-xl p-2 border border-gray-200">
                        @php
                            $cleanPath = str_replace('\\', '/', $doc->file_path);
                            $cleanPath = ltrim($cleanPath, '/');
                            $fileUrl = \Illuminate\Support\Facades\Route::has('secure.saas.company-documents.file')
                                ? route('secure.saas.company-documents.file', ['document' => $doc->id])
                                : asset('storage/' . $cleanPath);
                            $isImage = in_array(strtolower(pathinfo($doc->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif']);
                        @endphp
                        
                        @if($isImage)
                            <img
                                x-bind:src="typeof open !== 'undefined' && typeof activeTab !== 'undefined' && open && activeTab === 4 ? @js($fileUrl) : null"
                                alt="{{ $docTypes[$doc->type] ?? $doc->type }}"
                                class="w-full h-28 object-cover rounded-lg mb-1.5"
                                loading="lazy"
                                decoding="async"
                                fetchpriority="low"
                            >
                        @else
                            <div class="w-full h-28 bg-gradient-to-br from-gray-100 to-gray-200 rounded-lg mb-1.5 flex items-center justify-center">
                                <i class="fas fa-file-pdf text-3xl text-[color:var(--error)]"></i>
                            </div>
                        @endif
                        
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-600 truncate">
                                    {{ $doc->original_name ?? basename($doc->file_path) }}
                                </p>
                                @if($doc->size)
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        {{ number_format($doc->size / 1024, 2) }} KB
                                    </p>
                                @endif
                            </div>
                            <a 
                                href="{{ $fileUrl }}" 
                                target="_blank"
                                class="flex-shrink-0 px-2 py-1 bg-[color:var(--accent-orange)] text-white rounded-lg hover:bg-[color:var(--accent-orange-hover)] transition-colors text-xs font-semibold flex items-center gap-1"
                            >
                                <i class="fas fa-external-link-alt text-xs"></i>
                                <span class="hidden sm:inline">{{ tr('View') }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-6">
            <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-gray-100 flex items-center justify-center">
                <i class="fas fa-file text-gray-400 text-lg"></i>
            </div>
            <p class="text-xs sm:text-sm text-gray-500">
                {{ tr('No documents uploaded') }}
            </p>
        </div>
    @endif
</div>
