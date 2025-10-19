<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductImage extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['product_id', 'image'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) $model->id = (string) Str::uuid();
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
