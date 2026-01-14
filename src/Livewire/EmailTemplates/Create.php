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

    public string $type = '';

    public bool $is_active = true;

    public array $variables = [];

    protected $rules = [
        'name' => 'required|string|max:190',
        'subject' => 'required|string|max:255',
        'body' => 'required|string',
        'type' => 'required|in:subscription_expiry,subscription_anniversary,update_notification,greeting,user_welcome,new_year_greeting,holiday_greeting,custom',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'Template name is required',
        'name.max' => 'Template name must not exceed 190 characters',
        'subject.required' => 'Email subject is required',
        'subject.max' => 'Email subject must not exceed 255 characters',
        'body.required' => 'Email body is required',
        'type.required' => 'Template type is required',
        'type.in' => 'Invalid template type selected',
    ];

    public function mount(): void
    {
        // Don't set default type - let user choose
        // Only set variables if type is already selected
        if (!empty($this->type)) {
            $this->updateVariablesByType();
        }
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

    public function save()
    {
        \Log::info('Create::save() method called', [
            'name' => $this->name,
            'type' => $this->type,
            'type_empty' => empty($this->type),
            'subject' => $this->subject,
            'body_length' => strlen($this->body ?? ''),
        ]);

        try {
            // Ensure variables are set before validation
            if (!empty($this->type) && empty($this->variables)) {
                $this->updateVariablesByType();
            }
            
            $this->validate();

            \Log::info('Validation passed, creating template');

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

            \Log::info('Template created successfully');
            session()->flash('success', tr('Template created successfully'));
            return redirect()->route('saas.emails.index', ['tab' => 'templates']);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation failed', ['errors' => $e->errors()]);
            // Re-throw validation exceptions to show them in the form
            throw $e;
        } catch (\Throwable $e) {
            \Log::error('Error creating template', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            report($e);
            session()->flash('error', tr('Failed to create template: ') . $e->getMessage());
        }
    }

    public function render()
    {
        return view('saas::email-templates.create')
            ->extends('saas::layouts.saas')
            ->section('content');
    }
}
