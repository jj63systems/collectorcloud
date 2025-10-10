<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;


class CcItem extends Model
{
    use UsesTenantConnection;

    protected $table = 'cc_items';

    protected $fillable = [
        'team_id',
        'accessioned_at',
        'accessioned_by',
        'description',
        'name',
        // f001 to f100
        'f001', 'f002', 'f003', 'f004', 'f005', 'f006', 'f007', 'f008', 'f009', 'f010',
        'f011', 'f012', 'f013', 'f014', 'f015', 'f016', 'f017', 'f018', 'f019', 'f020',
        'f021', 'f022', 'f023', 'f024', 'f025', 'f026', 'f027', 'f028', 'f029', 'f030',
        'f031', 'f032', 'f033', 'f034', 'f035', 'f036', 'f037', 'f038', 'f039', 'f040',
        'f041', 'f042', 'f043', 'f044', 'f045', 'f046', 'f047', 'f048', 'f049', 'f050',
        'f051', 'f052', 'f053', 'f054', 'f055', 'f056', 'f057', 'f058', 'f059', 'f060',
        'f061', 'f062', 'f063', 'f064', 'f065', 'f066', 'f067', 'f068', 'f069', 'f070',
        'f071', 'f072', 'f073', 'f074', 'f075', 'f076', 'f077', 'f078', 'f079', 'f080',
        'f081', 'f082', 'f083', 'f084', 'f085', 'f086', 'f087', 'f088', 'f089', 'f090',
        'f091', 'f092', 'f093', 'f094', 'f095', 'f096', 'f097', 'f098', 'f099', 'f100',
    ];

    protected $casts = [
        'accessioned_at' => 'date',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(CcTeam::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'accessioned_by');
    }
}
