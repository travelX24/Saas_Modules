@props([
    'company' => null,
])

@php
    if (! $company) {
        return;
    }

    // counts (يفضل تكون موجودة من withCount في Query)
    $branchesCount = (int) ($company->branches_count ?? 0);
    $activeBranchesCount = $company->active_branches_count ?? null;

    // list (يفضل تكون محمّلة eager load)
    $branches = $company->relationLoaded('branches')
        ? $company->branches
        : collect();
@endphp

<div class="mt-8">
    <div class="flex items-center justify-between mb-3 gap-3">
        <h4 class="text-sm font-bold text-gray-900 flex items-center gap-2">
            <span class="h-8 w-8 rounded-xl bg-[color:var(--brand-via)]/10 flex items-center justify-center text-[color:var(--brand-via)]">
                <i class="fas fa-code-branch"></i>
            </span>
            {{ tr('Branches') }}
        </h4>

        <div class="flex items-center gap-2 text-xs flex-shrink-0">
            <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-700 font-semibold">
                {{ tr('Total') }}: {{ $branchesCount }}
            </span>

            @if(! is_null($activeBranchesCount))
                <span class="px-3 py-1 rounded-full bg-green-50 text-green-700 font-semibold">
                    {{ tr('Active') }}: {{ (int) $activeBranchesCount }}
                </span>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        @if($branchesCount === 0)
            <div class="p-5 text-sm text-gray-500 flex items-center gap-2">
                <i class="fas fa-info-circle text-gray-400"></i>
                <span>{{ tr('No branches found for this company.') }}</span>
            </div>
        @else
            @if($branches->isEmpty())
                {{-- إذا counts موجودة لكن القائمة غير محمّلة --}}
                <div class="p-5 text-sm text-gray-500 flex items-center gap-2">
                    <i class="fas fa-layer-group text-gray-400"></i>
                    <span>{{ tr('Branches summary is available, but branch list is not loaded.') }}</span>
                </div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($branches->take(10) as $branch)
                        <div class="p-4 flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 truncate">
                                    {{ $branch->name }}
                                    @if(! empty($branch->code))
                                        <span class="text-xs text-gray-500 font-medium">({{ $branch->code }})</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">#{{ $branch->id }}</div>
                            </div>

                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if((bool) $branch->is_active)
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-green-50 text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500 me-1.5"></span>
                                        {{ tr('Active') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-400 me-1.5"></span>
                                        {{ tr('Inactive') }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                @if($branchesCount > 10)
                    <div class="p-4 bg-gray-50 text-xs text-gray-600 flex items-center justify-between">
                        <span>{{ tr('Showing first') }} 10 {{ tr('branches') }}.</span>
                        <span class="font-semibold text-[color:var(--brand-via)]">
                            +{{ $branchesCount - 10 }} {{ tr('more') }}
                        </span>
                    </div>
                @endif
            @endif
        @endif
    </div>
</div>
