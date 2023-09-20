<?php

namespace App\Exports;


use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        return Product::orderBy('category_id')->where('qty','>',0)->get();
    }
    public function map($product): array
    {
        return [
            $product->name,
            $product->code,
            $product->category->name,
            $product->qty,
        ];
    }

    public function headings(): array
    {
        return [
            'اسم الصنف',
            'الباركود',
            'اسم التصنيف',
            'الكمية المتاحة',
        ];
    }
}
