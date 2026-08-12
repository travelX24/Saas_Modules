<?php

namespace Athka\Saas\Livewire\Translations;

use HananProgram\L10n\Services\AutoTranslator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $group = 'all';

    public int $perPage = 20;

    public array $editing = [];

    public $importFile = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'group' => ['except' => 'all'],
        'perPage' => ['except' => 20],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingGroup(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function startEdit(int $id): void
    {
        $translation = DB::table('language_lines')
            ->where('id', $id)
            ->first();

        if ($translation) {
            $text = json_decode($translation->text, true) ?? [];
            $this->editing[$id] = [
                'en' => $text['en'] ?? $translation->key,
                'ar' => $text['ar'] ?? '',
            ];
        }
    }

    public function cancelEdit(int $id): void
    {
        unset($this->editing[$id]);
    }

    public function saveTranslation(int $id): void
    {
        if (! isset($this->editing[$id])) {
            return;
        }

        $translation = DB::table('language_lines')
            ->where('id', $id)
            ->first();

        if (! $translation) {
            unset($this->editing[$id]);

            return;
        }

        $data = $this->editing[$id];
        $normalized = $this->normalizeTranslationText([
            'en' => $data['en'] ?? '',
            'ar' => $data['ar'] ?? '',
        ], (string) $translation->key);

        $text = json_encode($normalized, JSON_UNESCAPED_UNICODE);

        DB::table('language_lines')
            ->where('id', $id)
            ->update([
                'text' => $text,
                'updated_at' => now(),
            ]);

        unset($this->editing[$id]);

        session()->flash('status', tr('Translation updated successfully'));
    }

    public function exportTranslations(): StreamedResponse
    {
        $translations = DB::table('language_lines')
            ->when($this->group !== 'all', fn ($query) => $query->where('group', $this->group))
            ->orderBy('key')
            ->get();

        $data = $translations->map(function ($translation) {
            $text = json_decode($translation->text, true) ?? [];

            return [
                'group' => $translation->group,
                'key' => $translation->key,
                'en' => $text['en'] ?? $translation->key,
                'ar' => $text['ar'] ?? '',
            ];
        })->toArray();

        $groupSuffix = $this->group !== 'all' ? '_'.$this->group : '';
        $fileName = 'translations'.$groupSuffix.'_'.now()->format('Y-m-d_H-i-s').'.json';

        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function updatedImportFile(): void
    {
        if (! $this->importFile) {
            return;
        }

        $this->validate([
            'importFile' => ['required', 'file', 'mimetypes:application/json,text/json,text/plain', 'max:10240'],
        ]);

        $this->importTranslations();
    }

    public function importTranslations(): void
    {
        if (! $this->importFile) {
            return;
        }

        try {
            $filePath = $this->importFile->getRealPath();

            if (! file_exists($filePath)) {
                session()->flash('error', tr('File not found'));
                $this->reset('importFile');

                return;
            }

            $content = file_get_contents($filePath);

            if ($content === false) {
                session()->flash('error', tr('Failed to read file'));
                $this->reset('importFile');

                return;
            }

            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                session()->flash('error', tr('Invalid JSON file format').': '.json_last_error_msg());
                $this->reset('importFile');

                return;
            }

            if (! is_array($data)) {
                session()->flash('error', tr('Invalid JSON file format').': '.tr('Data must be an array'));
                $this->reset('importFile');

                return;
            }

            $imported = 0;
            $updated = 0;
            $skipped = 0;

            DB::beginTransaction();

            foreach ($data as $item) {
                if (! is_array($item)) {
                    $skipped++;

                    continue;
                }

                if (! isset($item['key']) || ! is_string($item['key']) || empty($item['key'])) {
                    $skipped++;

                    continue;
                }

                $group = $this->normalizeImportGroup($item['group'] ?? null);

                $normalized = $this->normalizeTranslationText([
                    'en' => $item['en'] ?? $item['key'] ?? '',
                    'ar' => $item['ar'] ?? '',
                ], (string) $item['key']);

                $text = json_encode($normalized, JSON_UNESCAPED_UNICODE);

                $exists = DB::table('language_lines')
                    ->where('group', $group)
                    ->where('key', $item['key'])
                    ->exists();

                if ($exists) {
                    DB::table('language_lines')
                        ->where('group', $group)
                        ->where('key', $item['key'])
                        ->update([
                            'text' => $text,
                            'updated_at' => now(),
                        ]);
                    $updated++;
                } else {
                    DB::table('language_lines')->insert([
                        'group' => $group,
                        'key' => $item['key'],
                        'text' => $text,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $imported++;
                }
            }

            DB::commit();

            $this->reset('importFile');
            $this->resetPage();

            $message = tr('Translations imported successfully').': '.tr('Imported').' '.$imported;
            if ($updated > 0) {
                $message .= ', '.tr('Updated').' '.$updated;
            }
            if ($skipped > 0) {
                $message .= ', '.tr('Skipped').' '.$skipped;
            }

            session()->flash('status', $message);
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', tr('Database error').': '.$e->getMessage());
            $this->reset('importFile');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            session()->flash('error', tr('Failed to import translations').': '.$e->getMessage());
            $this->reset('importFile');
        }
    }

    public function cleanEnglishTranslations(): void
    {
        $query = DB::table('language_lines');

        if ($this->group !== 'all') {
            $query->where('group', $this->group);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('key', 'like', '%'.$this->search.'%')
                    ->orWhere('text->en', 'like', '%'.$this->search.'%')
                    ->orWhere('text->ar', 'like', '%'.$this->search.'%');
            });
        }

        $fixed = 0;
        $checked = 0;

        $query->orderBy('id')->chunkById(100, function ($translations) use (&$fixed, &$checked) {
            foreach ($translations as $translation) {
                $checked++;
                $text = json_decode($translation->text, true) ?? [];
                $normalized = $this->normalizeTranslationText($text, (string) $translation->key);

                if (($normalized['en'] ?? '') === ($text['en'] ?? '') && ($normalized['ar'] ?? '') === ($text['ar'] ?? '')) {
                    continue;
                }

                DB::table('language_lines')
                    ->where('id', $translation->id)
                    ->update([
                        'text' => json_encode($normalized, JSON_UNESCAPED_UNICODE),
                        'updated_at' => now(),
                    ]);

                $fixed++;
            }
        });

        $this->resetPage();

        session()->flash('status', tr('English translations cleaned successfully').': '.$fixed.' / '.$checked);
    }

    public function render()
    {
        $query = DB::table('language_lines');

        if ($this->group !== 'all') {
            $query->where('group', $this->group);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('key', 'like', '%'.$this->search.'%')
                    ->orWhere('text->en', 'like', '%'.$this->search.'%')
                    ->orWhere('text->ar', 'like', '%'.$this->search.'%');
            });
        }

        $translations = $query->orderBy('key')
            ->paginate($this->perPage);

        $translations->getCollection()->transform(function ($item) {
            $text = json_decode($item->text, true) ?? [];

            return (object) [
                'id' => $item->id,
                'group' => $item->group,
                'key' => $item->key,
                'en' => $text['en'] ?? $item->key,
                'ar' => $text['ar'] ?? '',
            ];
        });

        $groups = DB::table('language_lines')
            ->select('group')
            ->distinct()
            ->orderBy('group')
            ->pluck('group')
            ->filter()
            ->values();

        return view('saas::translations.index', [
            'translations' => $translations,
            'groups' => $groups,
        ])
            ->extends('saas::layouts.saas')
            ->section('content');
    }

    private function normalizeImportGroup(?string $group): string
    {
        $group = trim((string) $group);

        if ($group !== '' && $group !== 'all') {
            return $group;
        }

        return $this->group !== 'all' ? $this->group : 'ui';
    }

    private function normalizeTranslationText(array $text, string $key): array
    {
        $key = $this->cleanUtf8($key);
        $en = trim($this->cleanUtf8((string) ($text['en'] ?? '')));
        $ar = trim($this->cleanUtf8((string) ($text['ar'] ?? '')));

        if ($this->containsArabic($en)) {
            $ar = $ar !== '' ? $ar : $en;
            $en = $this->translateArabicToEnglish($en, $key);
        }

        if ($en === '') {
            $en = $this->englishTextFromKey($key);
        }

        return [
            'en' => $en,
            'ar' => $ar,
        ];
    }

    private function translateArabicToEnglish(string $text, string $key): string
    {
        if (config('l10n.auto_translate')) {
            $translated = AutoTranslator::translate($text, 'en', 'ar');

            if ($translated && ! $this->containsArabic($translated)) {
                return trim($translated);
            }
        }

        return $this->englishTextFromKey($key);
    }

    private function englishTextFromKey(string $key): string
    {
        $text = trim(Str::headline(str_replace(['.', '_', '-'], ' ', $key)));

        if ($text === '' || $this->containsArabic($text)) {
            return 'Translation '.substr(sha1($key), 0, 8);
        }

        return $text;
    }

    private function containsArabic(string $text): bool
    {
        $text = $this->cleanUtf8($text);

        return preg_match('/\p{Arabic}/u', $text) === 1;
    }

    private function cleanUtf8(string $value): string
    {
        if ($value === '') {
            return '';
        }

        if (function_exists('mb_check_encoding') && mb_check_encoding($value, 'UTF-8')) {
            return $value;
        }

        foreach (['Windows-1256', 'ISO-8859-6', 'Windows-1252', 'ISO-8859-1'] as $encoding) {
            if (! function_exists('mb_convert_encoding')) {
                break;
            }

            $converted = @mb_convert_encoding($value, 'UTF-8', $encoding);

            if (is_string($converted) && $converted !== '' && mb_check_encoding($converted, 'UTF-8')) {
                return $converted;
            }
        }

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $value);

            if (is_string($converted)) {
                return $converted;
            }
        }

        return preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $value) ?? '';
    }
}
