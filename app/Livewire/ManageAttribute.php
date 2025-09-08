<?php

namespace App\Livewire;

use App\Models\Context;
use App\Models\ContextProfileValue;
use App\Models\ProfileAttribute;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class ManageAttribute extends Component implements HasSchemas, HasActions
{
    use InteractsWithSchemas;
    use InteractsWithActions;
    
    public bool $showModal = false;
    public ?ContextProfileValue $editingAttribute = null;
    public ?Context $context = null;
    public string $modalTitle = 'Add New Attribute';
    public string $submitButtonText = 'Add Attribute';
    
    // Form data property
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('key_name')
                    ->label('Key Name')
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[a-z0-9_]+$/')
                    ->helperText('Lowercase letters, numbers, and underscores only (e.g., full_name, email, phone_number)')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        if (!$this->editingAttribute && filled($state)) {
                            // Auto-generate display name from key name
                            $displayName = Str::title(str_replace('_', ' ', $state));
                            $set('display_name', $displayName);
                        }
                    })
                    ->disabled(fn () => $this->editingAttribute !== null),
                    
                TextInput::make('display_name')
                    ->label('Display Name')
                    ->required()
                    ->maxLength(255)
                    ->helperText('Human-readable name (e.g., Full Name, Email Address, Phone Number)')
                    ->disabled(fn () => $this->editingAttribute !== null),
                    
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
                    ])
                    ->default('string')
                    ->helperText('Select the type of data this attribute will store')
                    ->disabled(fn () => $this->editingAttribute !== null)
                    ->live(),
                    
                Textarea::make('value')
                    ->label('Value')
                    ->required()
                    ->maxLength(1000)
                    ->rows(2)
                    ->helperText(function (Get $get) {
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
            ->statePath('data')
            ->model($this->editingAttribute ?? ContextProfileValue::class);
    }

    #[On('open-add-attribute-modal')]
    public function openAddModal(int $contextId): void
    {
        $this->context = Context::where('user_id', auth()->id())
            ->findOrFail($contextId);
        
        $this->editingAttribute = null;
        $this->modalTitle = 'Add New Attribute';
        $this->submitButtonText = 'Add Attribute';
        
        $this->form->fill([
            'key_name' => '',
            'display_name' => '',
            'value' => '',
            'data_type' => 'string',
            'visibility' => 'private',
        ]);
        
        $this->showModal = true;
    }

    #[On('open-edit-attribute-modal')]
    public function openEditModal(int $attributeId): void
    {
        $this->editingAttribute = ContextProfileValue::where('user_id', auth()->id())
            ->with(['attribute', 'context'])
            ->findOrFail($attributeId);
        
        $this->context = $this->editingAttribute->context;
        
        $this->modalTitle = 'Edit Attribute';
        $this->submitButtonText = 'Update';
        
        $this->form->fill([
            'key_name' => $this->editingAttribute->attribute->key_name,
            'display_name' => $this->editingAttribute->attribute->display_name,
            'data_type' => $this->editingAttribute->attribute->data_type,
            'value' => $this->editingAttribute->value,
            'visibility' => $this->editingAttribute->visibility,
        ]);
        
        $this->showModal = true;
    }

    public function submit(): void
    {
        try {
            $data = $this->form->getState();
            
            if ($this->editingAttribute) {
                // Update existing attribute value and visibility
                $this->editingAttribute->update([
                    'value' => $data['value'],
                    'visibility' => $data['visibility'],
                ]);
                
                session()->flash('success', 'Attribute updated successfully');
            } else {
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
                
                // Create ContextProfileValue
                ContextProfileValue::create([
                    'user_id' => auth()->id(),
                    'context_id' => $this->context->id,
                    'attribute_id' => $profileAttribute->id,
                    'value' => $data['value'],
                    'visibility' => $data['visibility'],
                ]);
                
                session()->flash('success', 'Attribute added successfully');
            }
            
            $this->closeModal();
            
            // Refresh the page to show the new/updated attribute
            $this->redirect(route('dashboard'));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving attribute: ' . $e->getMessage());
        }
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

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingAttribute = null;
        $this->context = null;
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.manage-attribute');
    }
}