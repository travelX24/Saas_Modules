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
        // Base variables that should be available in all template types
        $baseVariables = ['system_name', 'support_email', 'support_phone', 'support_hours'];
        
        $defaultVariables = [
            'subscription_expiry' => array_merge($baseVariables, ['company_name', 'expiry_date', 'days_remaining', 'admin_name']),
            'subscription_anniversary' => array_merge($baseVariables, ['company_name', 'subscription_start_date', 'years_subscribed', 'admin_name', 'renewal_date']),
            'update_notification' => array_merge($baseVariables, ['company_name', 'update_title', 'update_description', 'update_version', 'admin_name', 'update_date']),
            'greeting' => array_merge($baseVariables, ['company_name', 'admin_name', 'welcome_message']),
            'user_welcome' => array_merge($baseVariables, [
                'user_name', 
                'login_url', 
                'username', 
                'reset_password_url',
                'company_name'
            ]),
            'new_year_greeting' => array_merge($baseVariables, ['company_name', 'admin_name', 'year', 'new_year_date', 'wishes_message']),
            'holiday_greeting' => array_merge($baseVariables, ['company_name', 'admin_name', 'holiday_name', 'holiday_date', 'wishes_message']),
            'custom' => array_merge($baseVariables, ['company_name', 'admin_name']),
        ];

        // Only update if variables are empty or if type changed
        if (empty($this->variables) || $this->template->type !== $this->type) {
            $this->variables = $defaultVariables[$this->type] ?? $baseVariables;
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

    public function getVariableDescriptions(): array
    {
        $descriptions = [
            'company_name' => tr('The name of the company'),
            'expiry_date' => tr('The subscription expiry date (format: Y-m-d)'),
            'days_remaining' => tr('Number of days remaining until subscription expires'),
            'admin_name' => tr('The name of the company administrator'),
            'subscription_start_date' => tr('The date when the subscription started'),
            'years_subscribed' => tr('Number of years the company has been subscribed'),
            'renewal_date' => tr('The subscription renewal date'),
            'update_title' => tr('Title of the system update'),
            'update_description' => tr('Description of the system update'),
            'update_version' => tr('Version number of the update'),
            'update_date' => tr('Date when the update was released'),
            'welcome_message' => tr('Welcome message for the company'),
            'system_name' => tr('Name of the system/platform'),
            'user_name' => tr('Name of the new user'),
            'login_url' => tr('URL for user login page'),
            'username' => tr('Username for the new user account'),
            'reset_password_url' => tr('URL to reset password'),
            'support_email' => tr('Support email address'),
            'support_phone' => tr('Support phone number'),
            'support_hours' => tr('Support working hours'),
            'year' => tr('The new year number'),
            'new_year_date' => tr('Date of the new year'),
            'wishes_message' => tr('New year wishes message'),
            'holiday_name' => tr('Name of the holiday'),
            'holiday_date' => tr('Date of the holiday'),
        ];

        return $descriptions;
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
