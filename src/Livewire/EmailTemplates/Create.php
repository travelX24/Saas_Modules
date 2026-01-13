<?php

namespace Athka\Saas\Livewire\EmailTemplates;

use Athka\Saas\Models\EmailTemplate;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Create extends Component
{
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
        'type' => 'required|in:subscription_expiry,subscription_anniversary,update_notification,greeting,user_welcome,new_year_greeting,holiday_greeting,custom',
        'is_active' => 'boolean',
    ];

    public function mount(): void
    {
        // Set default variables based on type
        $this->updateVariablesByType();
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

        $this->variables = $defaultVariables[$this->type] ?? [];
    }

    public function save(): void
    {
        $this->validate();

        try {
            EmailTemplate::create([
                'name' => $this->name,
                'subject' => $this->subject,
                'body' => $this->body,
                'type' => $this->type,
                'is_active' => $this->is_active,
                'variables' => $this->variables,
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ]);

            session()->flash('status', tr('Template created successfully'));
            $this->redirect(route('saas.emails.index', ['tab' => 'templates']));
        } catch (\Throwable $e) {
            report($e);
            session()->flash('error', tr('Failed to create template. Please try again.'));
        }
    }

    public function render()
    {
        return view('saas::email-templates.create')
            ->extends('saas::layouts.saas')
            ->section('content');
    }
}
