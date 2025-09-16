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
        'path',
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

    public function type()
    {
        return $this->belongsTo(\App\Models\tenant\CcLookupValue::class, 'type_id');
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

    public function updatePathAndDepthRecursively(int $level = 0): void
    {
        if ($level > 20) {
            \Log::warning("Recursion limit hit updating location path/depth at ID {$this->id}");
            return;
        }

        $newDepth = $this->computeDepth();
        $newPath = $this->computePath();

        if ($this->depth !== $newDepth || $this->path !== $newPath) {
            $this->depth = $newDepth;
            $this->path = $newPath;
            $this->saveQuietly();
        }

        $this->loadMissing('children');

        foreach ($this->children as $child) {
            $child->updatePathAndDepthRecursively($level + 1);
        }
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'parent_id', 'type', 'code', 'path', 'depth'])
            ->useLogName('Locations')
            ->logOnlyDirty();
    }

    public function canDelete(): bool
    {
        return !$this->children()->exists();
    }


    //
    // This function is called only from the test data seeder so could potentially be moved into the seeder
    public static function updateAllPathsAndDepths(): void
    {
        static::whereNull('parent_id')->get()->each->updatePathAndDepthRecursively();
    }

    public function cascadePathUpdate(): void
    {
        $oldPath = $this->getOriginal('path');
        $newPath = $this->computePath();

        if ($oldPath === $newPath) {
            return;
        }

        // Update self
        $this->path = $newPath;
        $this->depth = $this->computeDepth();
        $this->saveQuietly();

        // Update children where path starts with oldPath + ' /'
        CcLocation::where('path', 'like', $oldPath.' /%')->get()->each(function ($child) use ($oldPath, $newPath) {
            // Replace just the prefix
            $child->path = $newPath.substr($child->path, strlen($oldPath));
            $child->depth = substr_count($child->path, ' / ') + 1;
            $child->saveQuietly();
        });
    }

}
