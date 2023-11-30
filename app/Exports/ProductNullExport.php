<?php

namespace App\Exports;


use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductNullExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        return Product::orderBy('category_id')->where('qty','<=',0)->get();
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
