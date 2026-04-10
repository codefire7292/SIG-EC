<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'group', 'type'];

    /**
     * Get all settings from a specific group as an associative array.
     */
    public static function getGroup($group)
    {
        return self::where('group', $group)
            ->get()
            ->pluck('casted_value', 'key')
            ->toArray();
    }

    /**
     * Get a single setting value by key.
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->casted_value : $default;
    }

    /**
     * Get the value casted to its type.
     */
    public function getCastedValueAttribute()
    {
        switch ($this->type) {
            case 'boolean':
                return (bool) $this->value;
            case 'integer':
                return (int) $this->value;
            case 'json':
                return json_decode($this->value, true);
            default:
                return $this->value;
        }
    }
}
