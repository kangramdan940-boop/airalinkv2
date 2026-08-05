<?php

namespace App\Models\Traits;

trait Utilities
{
    public function lpad(string $field, int $length = 2, string $padder = ' '): string
    {
        return str_pad($this->$field, $length, $padder, STR_PAD_LEFT);
    }

    public static function options(string|callable $display, string $id = 'id', array $params = [], ?string $default = null): string
    {
        $q = static::query();

        $params = array_merge(['valuePrefix' => ''], $params);

        if (isset($params['filters'])) {
            foreach ($params['filters'] as $key => $value) {
                if (is_numeric($key) && is_callable($value)) {
                    $q->where($value);
                } else {
                    $q->where($key, $value);
                }
            }
        }

        if (isset($params['orders'])) {
            foreach ($params['orders'] as $key => $value) {
                if (is_numeric($key)) {
                    $key = $value;
                    $value = 'asc';
                }
                $q->orderBy($key, $value);
            }
        }

        $ret = '';
        if ($default !== false) {
            $default ??= '(Pilih Salah Satu)';
            $ret = '<option value="">' . $default . '</option>';
        }

        if (is_string($display)) {
            $q->orderBy($display, 'asc');
            $r = $q->pluck($display, $id);

            foreach ($r as $i => $v) {
                $i = $params['valuePrefix'] . $i;
                $selected = isset($params['selected']) &&
                    (is_array($params['selected']) ? in_array($i, $params['selected']) : $i == $params['selected']);
                $ret .= '<option value="' . $i . '"' . ($selected ? ' selected' : '') . '>' . $v . '</option>';
            }
        } elseif (is_callable($display)) {
            $r = $q->get();
            foreach ($r as $d) {
                $i = $params['valuePrefix'] . $d->$id;
                $selected = isset($params['selected']) &&
                    (is_array($params['selected']) ? in_array($i, $params['selected']) : $i == $params['selected']);
                $ret .= '<option value="' . $i . '"' . ($selected ? ' selected' : '') . '>' . $display($d) . '</option>';
            }
        }

        return $ret;
    }
}
