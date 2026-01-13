<?php

namespace Athka\Saas\Livewire\EmailTemplates;

use Athka\Saas\Models\EmailTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Edit extends Component
{
    public EmailTemplate $template;

    public string $name = '';

    public string $subject = '';

    public string $body = '';

    public string $type = 'custom';

    public bool $is_active = true;

    public array $variables = [];

    protected $rules = [
        'name' => 'required|string|max:190',
        'subject' => 'required|string|max:255',
        'body' => 'required|string',
        'type' => 'required|in:subscription_expiry,update_notification,greeting,user_welcome,custom',
        'is_active' => 'boolean',
    ];

    public function mount(int $id): void
    {
        $this->template = EmailTemplate::findOrFail($id);
        $this->name = $this->template->name;
        $this->subject = $this->template->subject;
        $this->body = $this->template->body;
        $this->type = $this->template->type;
        $this->is_active = $this->template->is_active;
        $this->variables = $this->template->variables ?? [];
    }

    public function updatedType(): void
    {
        $this->updateVariablesByType();
    }

    private function updateVariablesByType(): void
    {
        $defaultVariables = [
            'subscription_expiry' => ['company_name', 'expiry_date', 'days_remaining', 'admin_name'],
            'subscription_anniversary' => ['company_name', 'subscription_start_date', 'years_subscribed', 'admin_name', 'renewal_date'],
            'update_notification' => ['company_name', 'update_title', 'update_description', 'update_version', 'admin_name', 'update_date'],
            'greeting' => ['company_name', 'admin_name', 'welcome_message'],
            'user_welcome' => [
                'system_name', 
                'user_name', 
                'login_url', 
                'username', 
                'reset_password_url', 
                'support_email', 
                'support_phone', 
                'support_hours',
                'company_name'
            ],
            'new_year_greeting' => ['company_name', 'admin_name', 'year', 'new_year_date', 'wishes_message'],
            'holiday_greeting' => ['company_name', 'admin_name', 'holiday_name', 'holiday_date', 'wishes_message'],
            'custom' => ['company_name', 'admin_name'],
        ];

        // Only update if variables are empty or if type changed
        if (empty($this->variables) || $this->template->type !== $this->type) {
            $this->variables = $defaultVariables[$this->type] ?? [];
        }
    }

    public function save(): void
    {
        $this->validate();

        try {
            $this->template->update([
                'name' => $this->name,
                'subject' => $this->subject,
                'body' => $this->body,
                'type' => $this->type,
                'is_active' => $this->is_active,
                'variables' => $this->variables,
                'updated_by' => Auth::id(),
            ]);

            session()->flash('status', tr('Template updated successfully'));
            $this->redirect(route('saas.emails.index', ['tab' => 'templates']));
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', tr('Failed to update template. Please try again.'));
        }
    }

    public function render()
    {
        return view('saas::email-templates.edit', [
            'template' => $this->template,
        ])
            ->extends('saas::layouts.saas')
            ->section('content');
    }
}
