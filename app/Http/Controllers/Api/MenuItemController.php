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

    public function store(MenuItemRequest $request)
    {
        $item = MenuItem::create($request->validated());

        return $this->sendData([
            'item' => (new MenuItemResource($item))->resolve(),
        ], __('menu.success_create'), 201);
    }

    public function show(MenuItem $menuItem)
    {
        $menuItem->load('children');

        return $this->sendData([
            'item' => (new MenuItemResource($menuItem))->resolve(),
        ], __('menu.success_show'));
    }

    public function update(MenuItemRequest $request, MenuItem $menuItem)
    {
        $menuItem->update($request->validated());

        return $this->sendData([
            'item' => (new MenuItemResource($menuItem))->resolve(),
        ], __('menu.success_update'));
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return $this->sendSuccess(__('menu.success_delete'));
    }
}
