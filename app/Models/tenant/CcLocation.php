<?php

namespace App\Models\tenant;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class CcLocation extends Model
{
    use UsesTenantConnection, LogsActivity;

    protected $fillable = [
        'name',
        'parent_id',
        'type',
        'code',
        'path',   // now stores the *human-readable* full name path
        'depth',
    ];

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function computeDepth(): int
    {
        return $this->parent ? $this->parent->computeDepth() + 1 : 1;
    }

    public function computePath(): string
    {
        return $this->parent
            ? $this->parent->computePath().' / '.$this->name
            : $this->name;
    }

    public function updateHierarchyMetadata(): void
    {
        $this->depth = $this->computeDepth();
        $this->path = $this->computePath();
        $this->saveQuietly(); // Prevent re-triggering events

        foreach ($this->children as $child) {
            $child->updateHierarchyMetadata();
        }
    }

    protected static function booted(): void
    {
        static::saving(function (CcLocation $location) {
            $location->depth = $location->computeDepth();
            $location->path = $location->computePath();
        });

        static::saved(function (CcLocation $location) {
            $location->load('children');

            foreach ($location->children as $child) {
                $child->updateHierarchyMetadata();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'parent_id', 'type', 'code', 'path', 'depth'])
            ->useLogName('Locations')
            ->logOnlyDirty(); // optional: use a custom log name
    }

    public function canDelete(): bool
    {
        return !$this->children()->exists(); // Fast + memory efficient
    }
}
