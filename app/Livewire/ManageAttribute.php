<?php

namespace App\Livewire;

use App\Models\Context;
use App\Models\ContextProfileValue;
use App\Models\ProfileAttribute;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class ManageAttribute extends Component implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;
    
    public ?ContextProfileValue $editingAttribute = null;
    public ?Context $context = null;

    #[On('open-add-attribute-modal')]
    public function openAddModal(int $contextId): void
    {
        $this->context = Context::where('user_id', auth()->id())
            ->findOrFail($contextId);
        
        $this->editingAttribute = null;
        $this->mountAction('addAttribute');
    }

    #[On('open-edit-attribute-modal')]
    public function openEditModal(int $attributeId): void
    {
        $this->editingAttribute = ContextProfileValue::where('user_id', auth()->id())
            ->with(['attribute', 'context'])
            ->findOrFail($attributeId);
        
        $this->context = $this->editingAttribute->context;
        $this->mountAction('editAttribute');
    }

    public function addAttributeAction(): Action
    {
        return Action::make('addAttribute')
            ->modalHeading('Add New Attribute')
            ->form([
                TextInput::make('key_name')
                    ->label('Key Name')
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[a-z0-9_]+$/')
                    ->helperText('Lowercase letters, numbers, and underscores only (e.g., full_name, email, phone_number)')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (filled($state)) {
                            // Auto-generate display name from key name
                            $displayName = Str::title(str_replace('_', ' ', $state));
                            $set('display_name', $displayName);
                        }
                    }),
                    
                TextInput::make('display_name')
                    ->label('Display Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Human-readable name (e.g., Full Name, Email Address, Phone Number)'),
                    
                Select::make('data_type')
                    ->label('Data Type')
                    ->required()
                    ->options([
                        'string' => 'String',
                        'email' => 'Email',
                        'url' => 'URL',
                        'phone' => 'Phone',
                        'number' => 'Number',
                        'date' => 'Date',
                        'text' => 'Text',
                    ])
                    ->default('string')
                    ->helperText('Select the type of data this attribute will store')
                    ->reactive(),
                    
                Textarea::make('value')
                    ->label('Value')
                    ->required()
                    ->maxLength(1000)
                    ->rows(2)
                    ->helperText(function (callable $get) {
                        $type = $get('data_type') ?? 'string';
                        return match($type) {
                            'email' => 'Enter a valid email address',
                            'url' => 'Enter a valid URL (e.g., https://example.com)',
                            'phone' => 'Enter a phone number',
                            'number' => 'Enter a numeric value',
                            'date' => 'Enter a date (e.g., 2024-01-01)',
                            default => 'Enter the attribute value',
                        };
                    }),
                    
                Select::make('visibility')
                    ->label('Visibility')
                    ->required()
                    ->options([
                        'private' => 'Private (only owner can see)',
                        'protected' => 'Protected (authenticated users)',
                        'public' => 'Public (everyone can see)',
                    ])
                    ->default('private')
                    ->helperText('Control who can view this attribute'),
            ])
            ->action(function (array $data): void {
                // Find or create ProfileAttribute
                $profileAttribute = ProfileAttribute::firstOrCreate(
                    [
                        'key_name' => $data['key_name'],
                    ],
                    [
                        'display_name' => $data['display_name'],
                        'data_type' => $data['data_type'],
                        'description' => null,
                        'is_system' => false,
                        'validation_rules' => $this->getValidationRules($data['data_type']),
                    ]
                );
                
                // Check if this attribute already exists for this context
                $existingValue = ContextProfileValue::where('context_id', $this->context->id)
                    ->where('attribute_id', $profileAttribute->id)
                    ->first();
                
                if ($existingValue) {
                    Notification::make()
                        ->title('Attribute already exists')
                        ->body('This attribute already exists for this context. Please edit it instead.')
                        ->danger()
                        ->send();
                    return;
                }
                
                // Create ContextProfileValue
                ContextProfileValue::create([
                    'user_id' => auth()->id(),
                    'context_id' => $this->context->id,
                    'attribute_id' => $profileAttribute->id,
                    'value' => $data['value'],
                    'visibility' => $data['visibility'],
                ]);
                
                Notification::make()
                    ->title('Attribute added successfully')
                    ->success()
                    ->send();
                
                // Dispatch event to refresh context data without closing modal
                $this->dispatch('attribute-added', contextId: $this->context->id);
            })
            ->modalButton('Add Attribute')
            ->modalCancelActionLabel('Cancel');
    }

    public function editAttributeAction(): Action
    {
        return Action::make('editAttribute')
            ->modalHeading('Edit Attribute')
            ->fillForm(fn () => $this->editingAttribute ? [
                'key_name' => $this->editingAttribute->attribute->key_name,
                'display_name' => $this->editingAttribute->attribute->display_name,
                'data_type' => $this->editingAttribute->attribute->data_type,
                'value' => $this->editingAttribute->value,
                'visibility' => $this->editingAttribute->visibility,
            ] : [])
            ->form([
                TextInput::make('key_name')
                    ->label('Key Name')
                    ->disabled()
                    ->helperText('Key name cannot be changed'),
                    
                TextInput::make('display_name')
                    ->label('Display Name')
                    ->disabled()
                    ->helperText('Display name cannot be changed'),
                    
                Select::make('data_type')
                    ->label('Data Type')
                    ->disabled()
                    ->options([
                        'string' => 'String',
                        'email' => 'Email',
                        'url' => 'URL',
                        'phone' => 'Phone',
                        'number' => 'Number',
                        'date' => 'Date',
                        'text' => 'Text',
                    ]),
                    
                Textarea::make('value')
                    ->label('Value')
                    ->required()
                    ->maxLength(1000)
                    ->rows(2),
                    
                Select::make('visibility')
                    ->label('Visibility')
                    ->required()
                    ->options([
                        'private' => 'Private (only owner can see)',
                        'protected' => 'Protected (authenticated users)',
                        'public' => 'Public (everyone can see)',
                    ])
                    ->helperText('Control who can view this attribute'),
            ])
            ->action(function (array $data): void {
                $this->editingAttribute->update([
                    'value' => $data['value'],
                    'visibility' => $data['visibility'],
                ]);
                
                Notification::make()
                    ->title('Attribute updated successfully')
                    ->success()
                    ->send();
                
                // Dispatch event to refresh context data
                $this->dispatch('attribute-updated', contextId: $this->context->id);
            })
            ->modalButton('Update')
            ->modalCancelActionLabel('Cancel');
    }

    protected function getValidationRules(string $dataType): array
    {
        return match($dataType) {
            'email' => ['email'],
            'url' => ['url'],
            'phone' => ['regex:/^[+]?[(]?[0-9]{1,4}[)]?[-\s\.]?[(]?[0-9]{1,4}[)]?[-\s\.]?[0-9]{1,9}$/'],
            'number' => ['numeric'],
            'date' => ['date'],
            default => ['string', 'max:1000'],
        };
    }

    public function render()
    {
        return view('livewire.manage-attribute');
    }
}