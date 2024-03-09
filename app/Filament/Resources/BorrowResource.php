<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BorrowResource\Pages;
use App\Filament\Resources\BorrowResource\RelationManagers;
use App\Models\Borrow;
use Closure;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class BorrowResource extends Resource
{
    protected static ?string $model = Borrow::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $modelLabel = 'Book Loan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Name')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),
                Select::make('book_id')
                    ->label('Title Book')
                    ->relationship('book', 'title')
                    ->searchable()
                    ->required()
                    ->preload(),
                TextInput::make('number_of_borrow')
                    ->integer()
                    ->rules([
                        function (Get $get) {
                            return function (string $attribute, $value, Closure $fail) use ($get) {
                                $query = DB::table('books')->where('id', $get('book_id'))->first();
                                $bookStock = $query->stock;
                                if ($value > $bookStock) {
                                    $fail('The book is out of stock.');
                                }
                            };
                        },
                    ])
                    ->required(),
                DateTimePicker::make('return_of_book')
                    ->visibleOn('edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No books has been borrowed')
            ->emptyStateDescription('Once you borrow the book, it will appear here.')
            ->columns([
                ImageColumn::make('book.image'),
                TextColumn::make('user.name'),
                TextColumn::make('book.title'),
                TextColumn::make('number_of_borrow'),
                TextColumn::make('deadline'),
                TextColumn::make('return_of_book'),
                TextColumn::make('charge')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListBorrows::route('/'),
            'create' => Pages\CreateBorrow::route('/create'),
            'edit' => Pages\EditBorrow::route('/{record}/edit'),
        ];
    }
}
