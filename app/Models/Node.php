<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Node extends Model
{
    protected $table = 'nodes';

    protected $fillable = [
        'parent',
        'title',
        'created_at',
        'updated_at',
    ];

    /**
     *  Parent node relationship
     */
    public function parentNode(): BelongsTo
    {
        return $this->belongsTo(Node::class, 'parent');
    }

    /**
     *  Chlidren nodes relationship
     */
    public function children(): HasMany
    {
        return $this->hasMany(Node::class, 'parent');
    }

    /**
     * 
     */
    public function getCreatedAtAttribute($value)
    {

        $tz = request()->header('Timezone', config('app.timezone'));
        return Carbon::parse($value)->timezone($tz);
    }
  public function childrenRecursive()
{
    return $this->children()->with('childrenRecursive');
}

}
