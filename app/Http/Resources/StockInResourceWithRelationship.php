<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StockInResourceWithRelationShip extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'supplier_id' => $this->supplier_id,
            'product_id' => $this->product_id,
            'user_id' => $this->user_id,
            'transaction_number' => $this->transaction_number,
            'van_number' => $this->van_number,
            'date' => $this->date,
            'quantity' => $this->quantity,
            'status'=> $this->status,
            'product' => $this->product,
            'user' => $this->user,
        ];
    }
}
