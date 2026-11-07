<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudyProgramResource\Pages;
use App\Models\StudyProgram;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StudyProgramResource extends Resource
{
    protected static ?string $model = StudyProgram::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';
    protected static string | \UnitEnum | null $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Program Studi';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\Select::make('faculty_id')
                    ->relationship('faculty', 'name')
                    ->required()
                    ->label('Fakultas'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->label('Nama Program Studi'),
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->label('Kode Prodi'),
                Forms\Components\Select::make('degree')
                    ->options([
                        'S1' => 'S1 (Sarjana)',
                        'S2' => 'S2 (Magister)',
                        'S3' => 'S3 (Doktor)',
                    ])
                    ->required()
                    ->label('Jenjang'),
                Forms\Components\TextInput::make('wa_group_url')
                    ->url()
                    ->label('Link WhatsApp Group Resmi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->badge()->label('Kode'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->label('Nama Prodi'),
                Tables\Columns\TextColumn::make('degree')->badge()->color('info')->label('Jenjang'),
                Tables\Columns\TextColumn::make('faculty.name')->label('Fakultas'),
                Tables\Columns\TextColumn::make('wa_group_url')->limit(30)->label('Link WA Group'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ManageStudyPrograms::route('/'),
        ];
    }
}
