<?php

namespace App\Filament\Resources\BorrowResource\Pages;

use App\Filament\Resources\BorrowResource;
use App\Models\Borrow;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditBorrow extends EditRecord
{
    protected static string $resource = BorrowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
            // ...
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->update($data);

        if ($data['return_book'] > $record['deadline']) {
            DB::beginTransaction();

            try {
                $borrow = Borrow::find($record['id']);
                $borrow->charge = 1000;
                $borrow->save();
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        } else {
            DB::beginTransaction();

            try {
                $borrow = Borrow::find($record['id']);
                $borrow->charge = 0;
                $borrow->save();
                
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }
        }

        return $record;
    }
}
