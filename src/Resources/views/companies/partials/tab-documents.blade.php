<div class="space-y-4 sm:space-y-6">
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">

        <x-ui.file
            :label="tr('CR Document')"
            name="doc_cr"
            wire:model="doc_cr"
            target="doc_cr"
            :file="$doc_cr"
            :existingFile="$this->existingDocument('cr')"
            accept=".pdf,.jpg,.jpeg,.png"
            :hint="tr('PDF/JPG/PNG — max 10MB')"
        />

        <x-ui.file
            :label="tr('VAT Certificate')"
            name="doc_vat"
            wire:model="doc_vat"
            target="doc_vat"
            :file="$doc_vat"
            :existingFile="$this->existingDocument('vat')"
            accept=".pdf,.jpg,.jpeg,.png"
            :hint="tr('PDF/JPG/PNG — max 10MB')"
        />

        <x-ui.file
            :label="tr('Activity License')"
            name="doc_activity_license"
            wire:model="doc_activity_license"
            target="doc_activity_license"
            :file="$doc_activity_license"
            :existingFile="$this->existingDocument('activity_license')"
            accept=".pdf,.jpg,.jpeg,.png"
            :hint="tr('PDF/JPG/PNG — max 10MB')"
        />

        <x-ui.file
            :label="tr('Incorporation Contract')"
            name="doc_incorporation"
            wire:model="doc_incorporation"
            target="doc_incorporation"
            :file="$doc_incorporation"
            :existingFile="$this->existingDocument('incorporation')"
            accept=".pdf,.jpg,.jpeg,.png"
            :hint="tr('PDF/JPG/PNG — max 10MB')"
        />

        <div class="sm:col-span-2">
            <x-ui.file
                :label="tr('Owner ID / Passport')"
                name="doc_owner_id"
                wire:model="doc_owner_id"
                target="doc_owner_id"
                :file="$doc_owner_id"
                :existingFile="$this->existingDocument('owner_id')"
                accept=".pdf,.jpg,.jpeg,.png"
                :hint="tr('PDF/JPG/PNG — max 10MB')"
            />
        </div>

        <div class="sm:col-span-2">
            <x-ui.file
                :label="tr('National Address Document')"
                name="doc_national_address"
                wire:model="doc_national_address"
                target="doc_national_address"
                :file="$doc_national_address"
                :existingFile="$this->existingDocument('national_address')"
                accept=".pdf,.jpg,.jpeg,.png"
                :hint="tr('PDF/JPG/PNG — max 10MB')"
            />
        </div>

    </div>

    <div class="rounded-xl sm:rounded-2xl border bg-gray-50 p-3 sm:p-4 text-[11px] sm:text-[12px] text-gray-600">
        <div class="font-bold text-gray-900 mb-1">{{ tr('Notes') }}</div>
        <ul class="list-disc ms-4 sm:ms-5 space-y-1">
            <li>{{ tr('All documents are optional, but recommended for verification.') }}</li>
            <li>{{ tr('You can upload PDF or images, up to 10MB per file.') }}</li>
        </ul>
    </div>
</div>
