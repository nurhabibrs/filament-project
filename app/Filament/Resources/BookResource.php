<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookResource\Pages;
use App\Filament\Resources\BookResource\RelationManagers;
use App\Models\Book;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class BookResource extends Resource
{
    protected static ?string $model = Book::class;

    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationGroup = 'Books Management';

    public static function form(Form $form): Form
    {
        $nameOfBook = Str::random(5) . "-";
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                TextInput::make('slug')
                    ->required(),
                Select::make('category_id')
                    ->relationship(name: 'category', titleAttribute: 'name')
                    ->searchable()
                    ->createOptionForm([
                        TextInput::make('name')
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                    ])
                    ->required()
                    ->preload(),
                TextInput::make('writer')
                    ->required(),
                TextInput::make('year')
                    ->required(),
                TextInput::make('pages')
                    ->required(),
                TextInput::make('stock')
                    ->required(),
                FileUpload::make('image')
                    ->getUploadedFileNameForStorageUsing(
                        fn (TemporaryUploadedFile $file): string => (string) str(strtolower($nameOfBook . $file->getClientOriginalName()))
                            ->prepend('book-'),
                    )
                    ->image()
                    ->imageEditor()
                    ->disk('public')
                    ->directory('images')
                    ->required()
                    ->columnSpanFull(),
            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Group::make('category.name')
                    ->collapsible(),
                Group::make('writer')
                    ->collapsible(),
                Group::make('year')
                    ->collapsible(),
            ])
            ->emptyStateHeading('No book found')
            ->emptyStateDescription('Once you add book, it will appear here.')
            ->emptyStateIcon('heroicon-o-book-open')
            ->columns([
                ImageColumn::make('image'),
                TextColumn::make('title')->searchable(),
                TextColumn::make('category.name')->searchable(),
                TextColumn::make('writer')->searchable(),
                TextColumn::make('year'),
                TextColumn::make('pages'),
                TextColumn::make('stock')->numeric(),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->relationship(name: 'category', titleAttribute: 'name'),
                Tables\Filters\TrashedFilter::make(),
                
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                    // ->icon('heroicon-m-pencil-square')
                    // ->iconButton(),
                Tables\Actions\DeleteAction::make(),
                    // ->icon('heroicon-o-trash')
                    // ->color('danger')
                    // ->iconButton(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
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
            RelationManagers\BorrowsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBooks::route('/'),
            'create' => Pages\CreateBook::route('/create'),
            'edit' => Pages\EditBook::route('/{record}/edit'),
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
