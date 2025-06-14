<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Models\Karyawan;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Manajemen Karyawan';

    protected static ?string $navigationLabel = 'Data Karyawan';

    protected static ?string $modelLabel = 'Karyawan';

    protected static ?string $pluralModelLabel = 'Data Karyawan';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'user.name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Akun')
                    ->schema([
                        Forms\Components\Toggle::make('create_new_user')
                            ->label('Buat Akun Baru')
                            ->default(true)
                            ->reactive()
                            ->helperText('Aktifkan untuk membuat akun pengguna baru')
                            ->hiddenOn('edit'),

                        Forms\Components\Select::make('user_id')
                            ->label(fn (?string $context) => $context === 'edit' ? 'Pengguna' : 'Pilih Pengguna Existing')
                            ->options(function (?string $context, ?Model $record) {
                                $query = User::query();

                                // Exclude users who are already karyawan
                                $query->whereNotIn('id', function ($subQuery) use ($record) {
                                    $subQuery->select('user_id')
                                        ->from('karyawan')
                                        ->whereNotNull('user_id');

                                    // If editing, exclude current record
                                    if ($record && $record->exists) {
                                        $subQuery->where('id', '!=', $record->id);
                                    }
                                });

                                return $query->pluck('name', 'id');
                            })
                            ->searchable()
                            ->visible(fn (callable $get, ?string $context) => $context === 'edit' || ! $get('create_new_user'))
                            ->required(fn (callable $get, ?string $context) => $context === 'edit' || ! $get('create_new_user')),

                        Forms\Components\TextInput::make('user_name')
                            ->label('Nama Lengkap')
                            ->required(fn (callable $get) => $get('create_new_user'))
                            ->visible(fn (callable $get) => $get('create_new_user'))
                            ->hiddenOn('edit')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('user_email')
                            ->label('Email')
                            ->email()
                            ->required(fn (callable $get) => $get('create_new_user'))
                            ->visible(fn (callable $get) => $get('create_new_user'))
                            ->hiddenOn('edit')
                            ->unique('users', 'email', ignoreRecord: true)
                            ->maxLength(255)
                            ->default(function () {
                                $randomNumber = mt_rand(100, 999);

                                return "karyawan{$randomNumber}@rhi.com";
                            }),

                        Forms\Components\TextInput::make('user_password')
                            ->label('Password')
                            ->password()
                            ->required(fn (callable $get) => $get('create_new_user'))
                            ->visible(fn (callable $get) => $get('create_new_user'))
                            ->hiddenOn('edit')
                            ->minLength(8)
                            ->helperText('Minimal 8 karakter'),
                        Forms\Components\TextInput::make('kode_karyawan')
                            ->label('Kode Karyawan')
                            ->disabled()
                            ->maxLength(15)
                            ->helperText('Kode karyawan akan digenerate otomatis (maksimal 15 karakter)')
                            ->dehydrated(),
                        Forms\Components\TextInput::make('nip')
                            ->label('NIP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(12)
                            ->helperText('Maksimal 12 karakter'),
                    ])->columns(2),

                Forms\Components\Section::make('Data Pribadi')
                    ->schema([
                        Forms\Components\TextInput::make('tempat_lahir')
                            ->label('Tempat Lahir')
                            ->required()
                            ->maxLength(50)
                            ->helperText('Maksimal 50 karakter'),
                        Forms\Components\DatePicker::make('tanggal_lahir')
                            ->label('Tanggal Lahir')
                            ->required(),
                        Forms\Components\TextInput::make('no_ktp')
                            ->label('No KTP')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(16)
                            ->helperText('Maksimal 16 karakter'),
                        Forms\Components\Select::make('jenis_kelamin')
                            ->label('Jenis Kelamin')
                            ->options([
                                'L' => 'Laki-laki',
                                'P' => 'Perempuan',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\Select::make('agama')
                            ->label('Agama')
                            ->options([
                                'Islam' => 'Islam',
                                'Kristen' => 'Kristen',
                                'Katolik' => 'Katolik',
                                'Hindu' => 'Hindu',
                                'Buddha' => 'Buddha',
                                'Konghucu' => 'Konghucu',
                            ])
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->helperText('Maksimal 20 karakter'),
                    ])->columns(2),

                Forms\Components\Section::make('Kontak')
                    ->schema([
                        Forms\Components\TextInput::make('no_hp')
                            ->label('No HP')
                            ->tel()
                            ->required()
                            ->maxLength(15)
                            ->helperText('Maksimal 15 karakter'),
                        Forms\Components\TextInput::make('no_telp')
                            ->label('No Telepon')
                            ->tel()
                            ->maxLength(15)
                            ->helperText('Maksimal 15 karakter'),
                        Forms\Components\Textarea::make('alamat')
                            ->label('Alamat')
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Informasi Bank')
                    ->schema([
                        Forms\Components\TextInput::make('bank')
                            ->label('Bank')
                            ->maxLength(30)
                            ->helperText('Maksimal 30 karakter'),
                        Forms\Components\TextInput::make('no_rek')
                            ->label('No Rekening')
                            ->maxLength(25)
                            ->helperText('Maksimal 25 karakter'),
                    ])->columns(2),

                Forms\Components\Section::make('Jabatan & Foto')
                    ->schema([
                        Forms\Components\Select::make('jabatan_id')
                            ->label('Jabatan')
                            ->relationship('jabatan', 'nama_jabatan')
                            ->searchable()
                            ->preload(),
                        Forms\Components\FileUpload::make('foto')
                            ->label('Foto')
                            ->image()
                            ->imageEditor()
                            ->directory('karyawan')
                            ->disk('public'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('foto')
                    ->label('Foto')
                    ->circular()
                    ->disk('public')
                    ->size(40),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),
                Tables\Columns\TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('jabatan.nama_jabatan')
                    ->label('Jabatan')
                    ->searchable()
                    ->badge()
                    ->color('success'),
                Tables\Columns\TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'L' => 'info',
                        'P' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('no_hp')
                    ->label('No HP')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tempat_lahir')
                    ->label('Tempat Lahir')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('no_ktp')
                    ->label('No KTP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('agama')
                    ->label('Agama')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('no_telp')
                    ->label('No Telepon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('bank')
                    ->label('Bank')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('no_rek')
                    ->label('No Rekening')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('jabatan_id')
                    ->label('Jabatan')
                    ->relationship('jabatan', 'nama_jabatan')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options([
                        'L' => 'Laki-laki',
                        'P' => 'Perempuan',
                    ]),
                SelectFilter::make('agama')
                    ->label('Agama')
                    ->options(function () {
                        return Karyawan::distinct()->pluck('agama', 'agama')->toArray();
                    }),
                Filter::make('tanggal_lahir')
                    ->form([
                        DatePicker::make('dari_tanggal')
                            ->label('Dari Tanggal'),
                        DatePicker::make('sampai_tanggal')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_lahir', '>=', $date),
                            )
                            ->when(
                                $data['sampai_tanggal'],
                                fn (Builder $query, $date): Builder => $query->whereDate('tanggal_lahir', '<=', $date),
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
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
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
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'view' => Pages\ViewKaryawan::route('/{record}'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
            'import' => Pages\ImportKaryawan::route('/import'),
        ];
    }
}
