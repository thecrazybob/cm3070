<?php

namespace App\Livewire;

use App\Models\Context;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class CreateContext extends Component implements HasSchemas, HasActions
{
    use InteractsWithSchemas;
    use InteractsWithActions;
    
    public bool $showModal = false;
    public ?Context $editingContext = null;
    public string $modalTitle = 'Create New Context';
    public string $submitButtonText = 'Create';
    
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
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                        if (!$this->editingContext && filled($state)) {
                            $set('slug', Str::slug($state));
                        }
                    }),
                    
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->regex('/^[a-z0-9-]+$/')
                    ->helperText('URL-friendly identifier (lowercase letters, numbers, and hyphens only)')
                    ->unique(
                        table: 'contexts',
                        column: 'slug',
                        ignoreRecord: true,
                        modifyRuleUsing: function ($rule) {
                            return $rule->where('user_id', auth()->id());
                        }
                    ),
                    
                Textarea::make('description')
                    ->label('Description')
                    ->maxLength(500)
                    ->rows(3),
                    
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->helperText('Inactive contexts will not be available for selection'),
            ])
            ->statePath('data')
            ->model($this->editingContext ?? Context::class);
    }

    #[On('open-create-context-modal')]
    public function openCreateModal(): void
    {
        $this->editingContext = null;
        $this->modalTitle = 'Create New Context';
        $this->submitButtonText = 'Create';
        
        $this->form->fill([
            'name' => '',
            'slug' => '',
            'description' => '',
            'is_active' => true,
        ]);
        
        $this->showModal = true;
    }

    #[On('open-edit-context-modal')]
    public function openEditModal(int $contextId): void
    {
        $this->editingContext = Context::where('user_id', auth()->id())
            ->findOrFail($contextId);
        
        $this->modalTitle = 'Edit Context';
        $this->submitButtonText = 'Update';
        
        $this->form->fill([
            'name' => $this->editingContext->name,
            'slug' => $this->editingContext->slug,
            'description' => $this->editingContext->description,
            'is_active' => $this->editingContext->is_active,
        ]);
        
        $this->showModal = true;
    }

    public function submit(): void
    {
        try {
            $data = $this->form->getState();
            
            if ($this->editingContext) {
                // Update existing context
                $this->editingContext->update($data);
                session()->flash('success', 'Context updated successfully');
            } else {
                // Create new context
                $data['user_id'] = auth()->id();
                Context::create($data);
                session()->flash('success', 'Context created successfully');
            }
            
            $this->closeModal();
            
            // Refresh the page to show the new/updated context
            $this->redirect(route('dashboard'));
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error saving context: ' . $e->getMessage());
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->editingContext = null;
        $this->form->fill();
    }

    public function render()
    {
        return view('livewire.create-context');
    }
}