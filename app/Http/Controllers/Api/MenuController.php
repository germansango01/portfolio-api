<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\MenuItemResource;
use App\Http\Resources\MenuResource;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends BaseController
{
    /**
     * Retrieve menu items by menu ID.
     */
    public function index(Request $request, int $menuId): JsonResponse
    {
        $menu = Menu::find($menuId);

        if (!$menu) {
            return $this->sendError(__('messages.menu_not_found'), 404);
        }

        $items = MenuItem::with('children')
            ->where('menu_id', $menu->id)
            ->orderBy('position')
            ->get();

        return $this->sendData([
            'menu' =>MenuResource::make($menu)->resolve(),
            'items' => MenuItemResource::collection($items)->resolve(),
        ], __('menu.success_list'));
    }
}
