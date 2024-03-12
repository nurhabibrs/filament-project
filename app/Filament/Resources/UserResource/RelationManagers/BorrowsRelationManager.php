<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Group;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BorrowsRelationManager extends RelationManager
{
    protected static string $relationship = 'borrows';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Forms\Components\TextInput::make('book_id')
                //     ->required()
                //     ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('book_id')
            // ->groups([
            //     Group::make('category.name')
            //         ->collapsible(),
            //     Group::make('writer')
            //         ->collapsible(),
            //     Group::make('year')
            //         ->collapsible(),
            // ])
            ->emptyStateHeading('No books found')
            ->emptyStateDescription('Once you add book, it will appear here.')
            ->emptyStateIcon('heroicon-o-book-open')
            ->columns([
                ImageColumn::make('book.image')
                    ->label('Avatar'),
                TextColumn::make('book.title')->searchable()
                    ->label('Title'),
                TextColumn::make('book.category.name')->searchable(),
                TextColumn::make('book.writer')
                    ->label('Writer')
                    ->searchable(),
                TextColumn::make('book.year')
                    ->label('Year'),
                TextColumn::make('book.pages')
                    ->label('Pages'),
                TextColumn::make('book.stock')
                    ->label('Stock Now')
                    ->numeric(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                // Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
