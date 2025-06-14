<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OfficeResource\Pages;
use App\Models\Office;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Humaidem\FilamentMapPicker\Fields\OSMMap;

class OfficeResource extends Resource
{
    protected static ?string $model = Office::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Manajemen Lokasi';

    protected static ?string $modelLabel = 'Lokasi Kantor';

    protected static ?string $pluralModelLabel = 'Lokasi Kantor';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(255),
                                // Field pencarian lokasi telah dihapus
                                OSMMap::make('location')
                                    ->label('Lokasi')
                                    ->showMarker()
                                    ->draggable()
                                    ->extraAttributes([
                                        'id' => 'office-location-map',
                                    ])
                                    ->extraControl([
                                        'lat' => 0, 'lng' => 0,
                                        'zoomDelta' => 1,
                                        'zoomSnap' => 0.25,
                                        'wheelPxPerZoomLevel' => 60,
                                    ])

                                    ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set, $record) {
                                        if ($record) {
                                            $latitude = $record->latitude;
                                            $longitude = $record->longitude;

                                            if ($latitude && $longitude) {
                                                $set('location', ['lat' => $latitude, 'lng' => $longitude]);
                                            }
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $set('latitude', $state['lat']);
                                        $set('longitude', $state['lng']);
                                    })
                                    ->tilesUrl('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png'),
                                Forms\Components\Group::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('latitude')
                                            ->label('Garis Lintang')
                                            ->default(0)
                                            ->required()
                                            ->numeric(),
                                        Forms\Components\TextInput::make('longitude')
                                            ->label('Garis Bujur')
                                            ->default(0)
                                            ->required()
                                            ->numeric(),
                                    ])->columns(2),

                            ]),

                    ]),
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('radius')
                                    ->label('Radius')
                                    ->required()
                                    ->numeric(),
                            ]),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latitude')
                    ->label('Garis Lintang')
                    ->sortable(),
                Tables\Columns\TextColumn::make('longitude')
                    ->label('Garis Bujur')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Dihapus Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('radius')
                    ->label('Radius')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Edit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Hapus'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOffices::route('/'),
            'create' => Pages\CreateOffice::route('/create'),
            'edit' => Pages\EditOffice::route('/{record}/edit'),
        ];
    }
}

// diasodasd
