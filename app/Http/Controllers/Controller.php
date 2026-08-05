<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected string $link = '';
    protected string $perms = '';
    protected string $title = 'Title';
    protected string $subtitle = '';
    protected string $modalSize = 'mini';
    protected array $breadcrumb = ['Home' => '#'];
    protected array $tableStruct = [];

    protected function render(string $view, array $additional = [])
    {
        $data = [
            'breadcrumb'  => $this->breadcrumb,
            'title'       => $this->title,
            'subtitle'    => $this->subtitle,
            'pageUrl'     => $this->link,
            'pagePerms'   => $this->perms,
            'modalSize'   => $this->modalSize,
            'tableStruct' => $this->tableStruct,
        ];

        // Check permission
        if ($this->perms && !auth()->user()?->can($this->perms . '-view')) {
            abort(403, 'Unauthorized');
        }

        return view($view, array_merge($data, $additional));
    }

    protected function makeButton(array $params = []): string
    {
        $defaults = [
            'id' => '',
            'class' => 'btn-primary',
            'label' => 'Button',
            'tooltip' => '',
            'url' => url('/'),
            'disabled' => false,
            'onClick' => '',
        ];

        $datas = '';
        if (isset($params['datas'])) {
            foreach ($params['datas'] as $k => $v) {
                $datas .= " data-{$k}=\"{$v}\"";
            }
        }

        $type = $params['type'] ?? 'url';
        $params = array_merge($defaults, $params);
        $disabledClass = $params['disabled'] ? ' disabled' : '';

        return match ($type) {
            'edit' => '<a href="' . $params['url'] . '" ' . $datas . ' class="btn btn-sm btn-info' . $disabledClass . '" title="' . ($params['tooltip'] ?: 'Edit') . '"><i class="bi bi-pencil"></i></a> ',

            'modal' => '<button type="button" ' . $datas . ' class="btn btn-sm btn-info btn-edit' . $disabledClass . '" data-id="' . $params['id'] . '" title="Edit"><i class="bi bi-pencil"></i></button> ',

            'delete' => '<button type="button" onclick="deleteData(\'' . $params['url'] . '\')" class="btn btn-sm btn-danger' . $disabledClass . '" title="Hapus"><i class="bi bi-trash"></i></button> ',

            'confirm' => '<button type="button" onclick="confirmData(\'' . $params['url'] . '\')" class="btn btn-sm btn-success' . $disabledClass . '" title="Konfirmasi"><i class="bi bi-check-lg"></i></button> ',

            'verifikasi' => '<button type="button" onclick="verifikasiData(\'' . ($params['code'] ?? '') . '\', \'' . $params['url'] . '\')" class="btn btn-sm btn-success' . $disabledClass . '" title="Verifikasi"><i class="bi bi-check-circle"></i></button> ',

            'detail' => '<a href="' . $params['url'] . '" class="btn btn-sm btn-outline-primary' . $disabledClass . '" title="Detail"><i class="bi bi-eye"></i></a> ',

            default => '<a href="' . $params['url'] . '" ' . $datas . ' class="btn btn-sm ' . $params['class'] . $disabledClass . '" onclick="' . $params['onClick'] . '">' . $params['label'] . '</a> ',
        };
    }
}
