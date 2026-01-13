<?php

namespace Athka\Saas\Livewire\EmailTemplates;

use Athka\Saas\Models\EmailTemplate;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $typeFilter = 'all';

    public string $statusFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => 'all'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function deleteTemplate(int $templateId): void
    {
        try {
            $template = EmailTemplate::findOrFail($templateId);
            $template->delete();

            session()->flash('status', tr('Template deleted successfully'));
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', tr('Failed to delete template. Please try again.'));
        }
    }

    public function toggleStatus(int $templateId): void
    {
        try {
            $template = EmailTemplate::findOrFail($templateId);
            $template->is_active = !$template->is_active;
            $template->save();

            $status = $template->is_active ? tr('Template activated successfully') : tr('Template deactivated successfully');
            session()->flash('status', $status);
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', tr('Failed to update template status. Please try again.'));
        }
    }

    public function render()
    {
        $query = EmailTemplate::query()
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('subject', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->typeFilter !== 'all', function ($q) {
                $q->where('type', $this->typeFilter);
            })
            ->when($this->statusFilter !== 'all', function ($q) {
                if ($this->statusFilter === 'active') {
                    $q->where('is_active', true);
                } else {
                    $q->where('is_active', false);
                }
            })
            ->latest();

        $templates = $query->paginate(12);

        $types = [
            ['value' => 'all', 'label' => tr('All Types')],
            ['value' => 'subscription_expiry', 'label' => tr('Subscription Expiry')],
            ['value' => 'subscription_anniversary', 'label' => tr('Subscription Anniversary')],
            ['value' => 'update_notification', 'label' => tr('Update Notification')],
            ['value' => 'greeting', 'label' => tr('Greeting')],
            ['value' => 'user_welcome', 'label' => tr('User Welcome')],
            ['value' => 'new_year_greeting', 'label' => tr('New Year Greeting')],
            ['value' => 'holiday_greeting', 'label' => tr('Holiday Greeting')],
            ['value' => 'custom', 'label' => tr('Custom')],
        ];

        return view('saas::email-templates.index', [
            'templates' => $templates,
            'types' => $types,
        ])
            ->extends('saas::layouts.saas')
            ->section('content');
    }
}
