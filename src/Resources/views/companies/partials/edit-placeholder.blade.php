<div class="w-full">
    <div class="px-6 py-5 bg-gradient-to-br from-gray-50 via-gray-50/80 to-gray-50/60 border-b border-gray-200/50 mb-4">
        <div class="flex justify-center gap-6">
            @for ($i = 0; $i < 4; $i++)
                <div class="flex flex-col items-center gap-2">
                    <div class="h-12 w-12 rounded-2xl bg-gray-200 animate-pulse"></div>
                    <div class="h-3 w-20 rounded bg-gray-200 animate-pulse"></div>
                </div>
            @endfor
        </div>
    </div>

    <div class="p-6 space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @for ($i = 0; $i < 6; $i++)
                <div class="space-y-2">
                    <div class="h-3 w-24 rounded bg-gray-200 animate-pulse"></div>
                    <div class="h-12 rounded-2xl bg-gray-100 border border-gray-200 animate-pulse"></div>
                </div>
            @endfor
        </div>

        <div class="flex items-center justify-center gap-3 rounded-2xl border border-gray-100 bg-white p-4 text-sm font-semibold text-gray-500 shadow-sm">
            <i class="fas fa-circle-notch fa-spin text-[color:var(--accent-orange)]"></i>
            <span>{{ tr('Loading...') }}</span>
        </div>
    </div>
</div>
