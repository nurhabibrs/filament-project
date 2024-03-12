<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BorrowResource\Pages;
use App\Filament\Resources\BorrowResource\RelationManagers;
use App\Models\Book;
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

    protected static ?string $navigationGroup = 'Books Management';

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
                    ->visibleOn('create')
                    ->required(),
                DateTimePicker::make('return_book')
                    ->visibleOn('edit'),
            ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading('No book has been borrowed')
            ->emptyStateDescription('Once you borrow the book, it will appear here.')
            ->columns([
                ImageColumn::make('book.image'),
                TextColumn::make('user.name'),
                TextColumn::make('book.title'),
                TextColumn::make('number_of_borrow'),
                TextColumn::make('deadline'),
                TextColumn::make('return_book'),
                TextColumn::make('charge')
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function ($record) {
                        $query = DB::table('books')->where('id', $record['book_id'])->first();
                        $returnedStock = (int) $query->stock + $record['number_of_borrow'];

                        DB::beginTransaction();

                        try {
                            $book = Book::find($record['book_id']);
                            $book->stock = $returnedStock;
                            $book->save();
                            
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                        }
                    }),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make()
                    ->before(function ($record){
                        $query = DB::table('books')->where('id', $record['book_id'])->first();
                        $updatedStock = (int) $query->stock - $record['number_of_borrow'];

                        DB::beginTransaction();

                        try {
                            $book = Book::find($record['book_id']);
                            $book->stock = $updatedStock;
                            $book->save();
                            
                            DB::commit();
                        } catch (\Exception $e) {
                            DB::rollBack();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    // ...
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

    public static function getEloquentQuery(): Builder
    {
        
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
