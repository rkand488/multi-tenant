<?php

namespace App\Central\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Stores superadmin-managed application-wide settings as key/value pairs.
 *
 * @property string $key
 * @property string|null $value
 */
class SystemSetting extends Model
{
    /** @var string */
    protected $connection = 'central';

    /** @var string */
    protected $primaryKey = 'key';

    /** @var string */
    protected $keyType = 'string';

    /** @var bool */
    public $incrementing = false;

    /** @var list<string> */
    protected $fillable = ['key', 'value'];
}
