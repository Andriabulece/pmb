<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserAccessResource\Pages;
use App\Models\UserAccess;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserAccessResource extends Resource
{
    protected static ?string $model = UserAccess::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';
    protected static string | \UnitEnum | null $navigationGroup = 'Hak Akses & Pengguna';
    protected static ?string $navigationLabel = 'Scope Akses Prodi Staf';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name', fn ($query) => $query->whereDoesntHave('roles', fn ($q) => $q->where('name', 'mahasiswa')))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Staf / Dosen / Admin'),
                Forms\Components\Select::make('study_program_id')
                    ->relationship('studyProgram', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('Program Studi Yang Diizinkan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->searchable()->sortable()->label('Nama Staf / Dosen'),
                Tables\Columns\TextColumn::make('user.email')->label('Email'),
                Tables\Columns\TextColumn::make('user.roles.name')->badge()->color('warning')->label('Role'),
                Tables\Columns\TextColumn::make('studyProgram.name')->searchable()->sortable()->badge()->color('success')->label('Scope Prodi'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->label('Diberikan Pada'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUserAccesses::route('/'),
        ];
    }
}
