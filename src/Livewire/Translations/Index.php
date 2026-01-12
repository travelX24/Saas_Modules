<?php

namespace Athka\Saas\Livewire\Translations;

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

    public int $perPage = 20;

    public array $editing = [];

    public $importFile = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 20],
    ];

    public function updatingSearch(): void
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

        $data = $this->editing[$id];
        $text = json_encode([
            'en' => $data['en'] ?? '',
            'ar' => $data['ar'] ?? '',
        ], JSON_UNESCAPED_UNICODE);

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
            ->orderBy('key')
            ->get();

        $data = $translations->map(function ($translation) {
            $text = json_decode($translation->text, true) ?? [];

            return [
                'key' => $translation->key,
                'en' => $text['en'] ?? $translation->key,
                'ar' => $text['ar'] ?? '',
            ];
        })->toArray();

        $fileName = 'translations_'.now()->format('Y-m-d_H-i-s').'.json';

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

                $text = json_encode([
                    'en' => $item['en'] ?? $item['key'] ?? '',
                    'ar' => $item['ar'] ?? '',
                ], JSON_UNESCAPED_UNICODE);

                $exists = DB::table('language_lines')
                    ->where('key', $item['key'])
                    ->exists();

                if ($exists) {
                    DB::table('language_lines')
                        ->where('key', $item['key'])
                        ->update([
                            'text' => $text,
                            'updated_at' => now(),
                        ]);
                    $updated++;
                } else {
                    DB::table('language_lines')->insert([
                        'group' => 'ui',
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

    public function render()
    {
        $query = DB::table('language_lines');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('key', 'like', '%'.$this->search.'%')
                    ->orWhere('text', 'like', '%'.$this->search.'%');
            });
        }

        $translations = $query->orderBy('key')
            ->paginate($this->perPage);

        $translations->getCollection()->transform(function ($item) {
            $text = json_decode($item->text, true) ?? [];

            return (object) [
                'id' => $item->id,
                'key' => $item->key,
                'en' => $text['en'] ?? $item->key,
                'ar' => $text['ar'] ?? '',
            ];
        });

        return view('saas::translations.index', [
            'translations' => $translations,
        ])
            ->extends('saas::layouts.saas')
            ->section('content');
    }
}
