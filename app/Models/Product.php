<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'vendor_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'stock',
        'status',   // draft | submitted | approved
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /* ------------------------
     | Relationships
     |-------------------------*/
    public function vendor()
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    /* ------------------------
     | Query Scopes
     |-------------------------*/
    /**
     * Only approved products.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Only products with stock > 0.
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /* ------------------------
     | Helpers / Accessors
     |-------------------------*/
    /**
     * First image URL or a fallback.
     *
     * Usage: $product->first_image_url
     */
    public function getFirstImageUrlAttribute(): string
    {
        $media = $this->relationLoaded('media')
            ? $this->media->first()
            : $this->media()->first();

        return $media
            ? asset('storage/' . $media->path)
            : asset('images/default-product.png'); // put a default image here
    }

    public function getFirstImagePathAttribute(): ?string
    {
        $media = $this->relationLoaded('media')
            ? $this->media->first()
            : $this->media()->first();

        return $media ? $media->path : null;
    }
}
