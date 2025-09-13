<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Context;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

final class CreateContext extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public ?Context $editingContext = null;

    #[On('open-create-context-modal')]
    public function openCreateModal(): void
    {
        $this->editingContext = null;
        $this->mountAction('createContext');
    }

    #[On('open-edit-context-modal')]
    public function openEditModal(int $contextId): void
    {
        $this->editingContext = Context::where('user_id', auth()->id())
            ->findOrFail($contextId);
        $this->mountAction('editContext');
    }

    public function createContextAction(): Action
    {
        return Action::make('createContext')
            ->modalHeading('Create New Context')
            ->form([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if (filled($state)) {
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
                        modifyRuleUsing: fn ($rule) => $rule->where('user_id', auth()->id())
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
            ->action(function (array $data): void {
                $data['user_id'] = auth()->id();
                Context::create($data);

                Notification::make()
                    ->title('Context created successfully')
                    ->success()
                    ->send();

                $this->redirect(route('dashboard'));
            })
            ->modalButton('Create')
            ->modalCancelActionLabel('Cancel');
    }

    public function editContextAction(): Action
    {
        return Action::make('editContext')
            ->modalHeading('Edit Context')
            ->fillForm(fn (): array => $this->editingContext instanceof Context ? [
                'name' => $this->editingContext->name,
                'slug' => $this->editingContext->slug,
                'description' => $this->editingContext->description,
                'is_active' => $this->editingContext->is_active,
            ] : [])
            ->form([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),

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
                        modifyRuleUsing: fn ($rule) => $rule->where('user_id', auth()->id())
                            ->where('id', '!=', $this->editingContext?->id)
                    ),

                Textarea::make('description')
                    ->label('Description')
                    ->maxLength(500)
                    ->rows(3),

                Toggle::make('is_active')
                    ->label('Active')
                    ->helperText('Inactive contexts will not be available for selection'),
            ])
            ->action(function (array $data): void {
                $this->editingContext->update($data);

                Notification::make()
                    ->title('Context updated successfully')
                    ->success()
                    ->send();

                $this->redirect(route('dashboard'));
            })
            ->modalButton('Update')
            ->modalCancelActionLabel('Cancel');
    }

    public function render(): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
    {
        return view('livewire.create-context');
    }
}
