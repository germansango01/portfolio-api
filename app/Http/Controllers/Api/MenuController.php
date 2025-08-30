<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\MenuRequest;
use App\Http\Resources\MenuItemResource;
use App\Models\MenuItem;

class MenuController extends BaseController
{
    /**
     * Retrieve menu items by menu ID.
     */
    public function index(MenuRequest $request)
    {
        $menuId = $request->input('menu_id');

        $items = MenuItem::with('children')
            ->where('menu_id', $menuId)
            ->orderBy('position')
            ->get();

        return $this->sendData([
            'items' => MenuItemResource::collection($items)->resolve(),
        ], __('menu.success_list'));
    }
}
