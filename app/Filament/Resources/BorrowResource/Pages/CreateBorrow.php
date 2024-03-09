<?php

namespace App\Filament\Resources\BorrowResource\Pages;

use App\Filament\Resources\BorrowResource;
use App\Models\Book;
use Exception;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateBorrow extends CreateRecord
{
    protected static string $resource = BorrowResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['deadline'] = now()->addDays(7);
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $query = DB::table('books')->where('id', $data['book_id'])->first();
        $updatedStock = (int) $query->stock - $data['number_of_borrow'];

        DB::beginTransaction();

        try {
            $book = Book::find($data['book_id']);
            $book->stock = $updatedStock;
            $book->save();
            
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
        }
        
        return static::getModel()::create($data);
    }
}
