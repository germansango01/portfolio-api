<?php

namespace App\Http\Controllers\Api;

class MenuItemController extends BaseController
{
    public function index()
    {
        $items = MenuItem::with('children')->orderBy('position')->get();

        return $this->sendData([
            'items' => MenuItemResource::collection($items)->resolve(),
        ], __('menu.success_list'));
    }
}
