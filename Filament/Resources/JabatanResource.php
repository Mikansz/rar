<?php

namespace App\Filament\Resources;

use App\Filament\Resources\JabatanResource\Pages;
use App\Filament\Resources\JabatanResource\RelationManagers;
use App\Models\Jabatan;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JabatanResource extends Resource
{
    protected static ?string $model = Jabatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Data Jabatan';

    protected static ?string $modelLabel = 'Jabatan';

    protected static ?string $pluralModelLabel = 'Data Jabatan';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'nama_jabatan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Jabatan')
                    ->schema([
                        Forms\Components\TextInput::make('kode_jabatan')
                            ->label('Kode Jabatan')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated')
                            ->helperText('Kode akan digenerate otomatis dengan format JBT25XXX'),
                        Forms\Components\TextInput::make('nama_jabatan')
                            ->label('Nama Jabatan')
                            ->required()
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Kompensasi')
                    ->schema([
                        Forms\Components\TextInput::make('gaji_pokok')
                            ->label('Gaji Pokok')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->inputMode('decimal'),
                        Forms\Components\TextInput::make('tunjangan_transportasi')
                            ->label('Tunjangan Transportasi')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->inputMode('decimal'),
                        Forms\Components\TextInput::make('tunjangan_makan')
                            ->label('Tunjangan Makan')
                            ->required()
                            ->numeric()
                            ->prefix('Rp')
                            ->inputMode('decimal'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nama_jabatan')
                    ->label('Nama Jabatan')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                Tables\Columns\TextColumn::make('gaji_pokok')
                    ->label('Gaji Pokok')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Average::make()
                            ->money('IDR')
                            ->label('Rata-rata'),
                    ]),
                Tables\Columns\TextColumn::make('tunjangan_transportasi')
                    ->label('Tunj. Transportasi')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tunjangan_makan')
                    ->label('Tunj. Makan')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('gaji_tinggi')
                    ->label('Gaji Tinggi (>= 5 Juta)')
                    ->query(fn (Builder $query): Builder => $query->where('gaji_pokok', '>=', 5000000)),
                Filter::make('gaji_rendah')
                    ->label('Gaji Rendah (< 3 Juta)')
                    ->query(fn (Builder $query): Builder => $query->where('gaji_pokok', '<', 3000000)),
                Filter::make('total_gaji_range')
                    ->form([
                        TextInput::make('min_gaji')
                            ->label('Gaji Minimum')
                            ->numeric()
                            ->prefix('Rp'),
                        TextInput::make('max_gaji')
                            ->label('Gaji Maksimum')
                            ->numeric()
                            ->prefix('Rp'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['min_gaji'],
                                fn (Builder $query, $amount): Builder => $query->whereRaw('(gaji_pokok + tunjangan_transportasi + tunjangan_makan) >= ?', [$amount]),
                            )
                            ->when(
                                $data['max_gaji'],
                                fn (Builder $query, $amount): Builder => $query->whereRaw('(gaji_pokok + tunjangan_transportasi + tunjangan_makan) <= ?', [$amount]),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
                Tables\Actions\EditAction::make()
                    ->label('Edit'),
                Tables\Actions\DeleteAction::make()
                    ->label('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus'),
                ]),
            ])
            ->defaultSort('nama_jabatan');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\KaryawanRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListJabatans::route('/'),
            'create' => Pages\CreateJabatan::route('/create'),
            'view' => Pages\ViewJabatan::route('/{record}'),
            'edit' => Pages\EditJabatan::route('/{record}/edit'),
        ];
    }
}
