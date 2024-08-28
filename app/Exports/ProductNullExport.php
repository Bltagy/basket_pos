<?php

namespace App\Exports;


use App\Customer;
use App\Product;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductNullExport implements FromCollection, WithMapping, WithHeadings
{
    public function collection()
    {
        return Customer::whereDoesntHave('sales', function ($subQuery) {
            return $subQuery->where(
                'created_at', '>', Carbon::now()->subMonth(6)->toDateTimeString()
            );
        })
                       ->whereHas('sales')
                       ->with('laetstSales')
                       ->get();
    }
    public function map($product): array
    {
//        $latest = $product->ProductPurchase()->latest()->first();
        return [
            $product->name,
            $product->phone_number,
            $product->address,
            $product->laetstSales->created_at,
            $product->sales()->count()
        ];
    }

    public function headings(): array
    {
        return [
            'اسم العميل',
            'الموبايل',
            'العنوان',
            'تاريخ اخر اوردر',
            'عدد الاوردرات الكلي',
        ];
    }
}
