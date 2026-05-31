<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDiscountCodeRequest;
use App\Http\Requests\UpdateDiscountCodeRequest;
use App\Models\DiscountCode;

class ManagerDiscountCodeController extends Controller
{
    public function index()
    {
        return view('manager.discount-codes.index', [
            'discountCodes' => DiscountCode::query()
                ->with('createdBy')
                ->latest()
                ->paginate(20),
            'types' => $this->types(),
        ]);
    }

    public function store(StoreDiscountCodeRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        DiscountCode::create($data);

        return redirect()
            ->route('manager.discount-codes.index')
            ->with('success', 'Kod rabatowy zostal dodany.');
    }

    public function edit(DiscountCode $discountCode)
    {
        return view('manager.discount-codes.edit', [
            'discountCode' => $discountCode->load('createdBy'),
            'types' => $this->types(),
        ]);
    }

    public function update(UpdateDiscountCodeRequest $request, DiscountCode $discountCode)
    {
        $discountCode->update($request->validated());

        return redirect()
            ->route('manager.discount-codes.index')
            ->with('success', 'Kod rabatowy zostal zaktualizowany.');
    }

    public function toggle(DiscountCode $discountCode)
    {
        $discountCode->update([
            'is_active' => ! $discountCode->is_active,
        ]);

        return redirect()
            ->route('manager.discount-codes.index')
            ->with('success', 'Status kodu rabatowego zostal zmieniony.');
    }

    private function types(): array
    {
        return [
            DiscountCode::TYPE_PERCENT => 'Procentowy',
            DiscountCode::TYPE_FIXED => 'Kwotowy',
        ];
    }
}
