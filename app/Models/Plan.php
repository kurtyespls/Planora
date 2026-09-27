<?php

namespace App\Models;

use App\Services\PlanoraService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'hotel_name',
        'budget',
        'total_days',
        'rest_days',
        'ai_recommendation',
        'ai_provider',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rest_days' => 'array',
        'budget' => 'decimal:2',
        'total_days' => 'integer',
    ];

    /**
     * Label shown in the plan list and on the plan page: the traveller's own
     * title when they set one, otherwise the hotel the trip was built around.
     */
    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?: $this->hotel_name;
    }

    /**
     * Ang rest schedule ay naka-save bilang legacy label ('Morning') o custom
     * na oras na pinili ng traveller ('14:00-16:00'), kaya human-readable na
     * label ang ipinapakita ng plan page para sa dalawang format.
     *
     * @return array<int, string>
     */
    public function getRestScheduleLabelsAttribute(): array
    {
        $labels = [];

        foreach ($this->rest_days ?? [] as $entry) {
            if (is_string($entry) && trim($entry) !== '') {
                $labels[] = PlanoraService::restEntryLabel($entry);
            }
        }

        return $labels;
    }
}