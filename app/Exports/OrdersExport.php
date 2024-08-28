<?php

namespace App\Exports;


use App\Product;
use App\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        return Sale::where(
            'created_at', '>=', Carbon::now()->subMonth(6)->toDateTimeString()
        )->get();
    }
    public function map($product): array
    {
//        $latest = $product->ProductPurchase()->latest()->first();
        return [
            $product->name,
            $product->code,
            $product->price,
            $product->qty,
//            $latest ? $latest->created_at->format('Y/m/d h:i a') : '------',
        ];
    }

    public function headings(): array
    {
        return [
            'اسم الصنف',
            'الباركود',
            'السعر',
            'الكمية المتاحة',
            'اخر اضافة',
        ];
    }
}
