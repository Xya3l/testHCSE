<?php

namespace App\Models;

use App\Enums\ProfileStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Profile
 *
 * @property string $id UUID
 * @property string $last_name
 * @property string $first_name
 * @property string $picture
 * @property ProfileStatus $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static Builder|Profile active()
 */
class Profile extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'profiles';

    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = true;

    protected $casts = [
        'status' => ProfileStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $fillable = [
        'last_name',
        'first_name',
        'picture',
        'status',
    ];

    public function scopeActive(Builder $query): Builder {
        return $query->where('status', ProfileStatus::ACTIVE->value);
    }
}
