<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\User;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\Label;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Select;
use Illuminate\Support\Collection;
use Orchid\Screen\Fields\CheckBox;

class UserRoleLayout extends Rows
{
    /**
     * Views.
     *
     * @return array
     * @throws \Throwable|\Orchid\Screen\Exceptions\TypeException
     */
    public function fields(): array
    {
        $fields[] = Select::make('roles.')
            ->options($this->query
                ->getContent('roles')
                ->pluck('name', 'slug')
                ->toArray())
            ->value(function () {
                return $this->query
                    ->getContent('roles')
                    ->where('active', true)
                    ->pluck('name', 'slug')
                    ->toArray();
            })
            ->multiple()
            ->horizontal()
            ->title(__('Name role'));

        $permissionFields = $this->generatedPermissionFields($this->query->getContent('permission'));

        return array_merge($fields, $permissionFields);
    }

    /**
     * @param Collection $permissionsRaw
     *
     * @return array
     * @throws \Throwable|\Orchid\Screen\Exceptions\TypeException
     */
    public function generatedPermissionFields(Collection $permissionsRaw) : array
    {
        foreach ($permissionsRaw as $group => $items) {
            $fields[] = Label::make($group)
                ->title($group)
                ->horizontal()
                ->hr(false);

            foreach (collect($items)->chunk(4) as $chunks) {
                $fields[] = Field::group(function () use ($chunks) {
                    foreach ($chunks as $permission) {
                        $permissions[] = CheckBox::make('permissions.'.base64_encode($permission['slug']))
                            ->placeholder($permission['description'])
                            ->value($permission['active'])
                            ->sendTrueOrFalse()
                            ->hr(false);
                    }

                    return $permissions ?? [];
                });
            }
        }

        return $fields ?? [];
    }
}
