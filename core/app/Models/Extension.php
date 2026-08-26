<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Extension extends Model
{
    use GlobalStatus;

    protected $casts = [
        'shortcode' => 'object',
    ];

    protected $hidden = ['script','shortcode'];

    public function scopeGenerateScript()
    {
        $script = $this->script;
        if ($this->shortcode) {
            foreach ($this->shortcode as $key => $item) {
                $val = is_object($item) ? (@$item->value ?? '') : (is_array($item) ? (@$item['value'] ?? '') : (string)$item);
                $script = str_replace('{{' . $key . '}}', $val, $script);
            }
        }
        return $script;
    }
}
