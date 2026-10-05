<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SenderId extends Model
{
    use HasFactory;


     /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'company_id',
        'is_approved',
        'company_phone'
    ];

    /**
     * Strip leading/trailing whitespace and internal spaces from sender ID labels.
     */
    public static function normalizeName(?string $name): ?string
    {
        if ($name === null) {
            return null;
        }

        $trimmed = trim($name);

        if ($trimmed === '') {
            return null;
        }

        return preg_replace('/\s+/', '', $trimmed);
    }

    public function setNameAttribute(?string $value): void
    {
        $this->attributes['name'] = self::normalizeName($value) ?? $value;
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'company_id', 'company_id');
    }

    public static function findApprovedByName(string $name): ?self
    {
        $normalized = self::normalizeName($name);

        if ($normalized === null) {
            return null;
        }

        return self::query()
            ->where('is_approved', 1)
            ->get()
            ->first(fn (self $record) => self::normalizeName($record->getRawOriginal('name')) === $normalized);
    }

    public function username()
    {
        
        return $this->belongsTo(User::class, 'company_id','user_id');
    }

    public function getIsApprovedAttribute($value) {
        if($value == 1){
            return 'APPROVED';
        }
        elseif($value == 2){
            return 'PENDING APPROVAL';
        }
        else{
            return 'REJECTED';
        }
        
    }
}
