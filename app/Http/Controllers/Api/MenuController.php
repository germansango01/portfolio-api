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
     * @OA\Get(
     *     path="/api/menus/{menuId}",
     *     summary="Retrieve menu items by menu ID",
     *     tags={"Menus"},
     *     @OA\Parameter(
     *         name="menuId",
     *         in="path",
     *         required=true,
     *         description="ID of the menu",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Menu items retrieved successfully."
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Menu not found."
     *     )
     * )
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
            'parent' =>MenuResource::make($menu)->resolve(),
            'menus' => MenuItemResource::collection($items)->resolve(),
        ], __('menu.success_list'));
    }
}
