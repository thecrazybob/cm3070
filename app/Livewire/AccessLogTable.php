<?php

namespace App\Livewire;

use App\Models\AccessLog;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class AccessLogTable extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(
                AccessLog::query()
                    ->where('user_id', auth()->id())
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('created_at')
                    ->label('Date & Time')
                    ->dateTime('M j, Y H:i:s')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('context_requested')
                    ->label('Context Requested')
                    ->searchable()
                    ->wrap()
                    ->limit(50),
                    
                TextColumn::make('accessor_type')
                    ->label('Accessor Type')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'api' => 'success',
                        'web' => 'info',
                        'admin' => 'warning',
                        default => 'gray',
                    }),
                    
                TextColumn::make('attributes_returned')
                    ->label('Attributes')
                    ->wrap()
                    ->limit(100)
                    ->formatStateUsing(function ($state) {
                        if (!$state) {
                            return 'None';
                        }
                        $attributes = json_decode($state, true);
                        if (is_array($attributes)) {
                            return implode(', ', array_keys($attributes));
                        }
                        return $state;
                    }),
                    
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('IP address copied'),
                    
                TextColumn::make('response_code')
                    ->label('Response')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 300 && $state < 400 => 'warning',
                        $state >= 400 => 'danger',
                        default => 'gray',
                    }),
            ])
            ->filters([
                SelectFilter::make('response_code')
                    ->label('Response Code')
                    ->options([
                        '200' => '200 OK',
                        '201' => '201 Created',
                        '400' => '400 Bad Request',
                        '401' => '401 Unauthorized',
                        '403' => '403 Forbidden',
                        '404' => '404 Not Found',
                        '500' => '500 Server Error',
                    ]),
                    
                SelectFilter::make('accessor_type')
                    ->label('Accessor Type')
                    ->options([
                        'api' => 'API',
                        'web' => 'Web',
                        'admin' => 'Admin',
                    ]),
                    
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label('From'),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators[] = 'From ' . \Carbon\Carbon::parse($data['created_from'])->toFormattedDateString();
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators[] = 'Until ' . \Carbon\Carbon::parse($data['created_until'])->toFormattedDateString();
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                // You can add row actions here if needed
            ])
            ->bulkActions([
                // You can add bulk actions here if needed
            ])
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(10)
            ->striped()
            ->poll('10s'); // Auto-refresh every 10 seconds
    }
    
    public function render(): View
    {
        return view('livewire.access-log-table');
    }
}