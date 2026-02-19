<?php

namespace App\Observers;

use App\Http\Services\AuditTrailService;
use Illuminate\Database\Eloquent\Model;

class GenericObserver
{
    // Fields to never log
    protected array $skipFields = [
        'password',
        'remember_token',
        'updated_at',
        'created_at',
    ];

    // Map model class to module name
    protected array $moduleMap = [
        'PropertyMaster'           => 'Property Inspection',
        'PropertyLocationAccess'   => 'Property Inspection',
        'PropertyPhoto'            => 'Property Inspection',
        'PropertySummary'          => 'Property Inspection',
        'PropertyInspectionSignoff'=> 'Property Inspection',
        'User'                     => 'User Management',
        'Role'                     => 'Role Management',
    ];

    // Get resource and module from model class name automatically
    private function getResource(Model $model): string
    {
        return class_basename($model);
    }

    private function getModule(Model $model): string
    {
        $resource = class_basename($model);
        return $this->moduleMap[$resource] ?? 'General';
    }

    public function created(Model $model): void
    {
        $data = collect($model->toArray())
            ->except($this->skipFields)
            ->toArray();

        AuditTrailService::log(
            action:     'created',
            resource:   $this->getResource($model),
            oldValue:   [],
            newValue:   $data,
            moduleName: $this->getModule($model),
            notes:      $this->getResource($model) . " #{$model->id} created"
        );
    }

    public function updated(Model $model): void
    {
        $dirtyFields = collect($model->getDirty())
            ->except($this->skipFields)
            ->toArray();

        if (empty($dirtyFields)) return;

        $originalFields = collect($model->getOriginal())
            ->only(array_keys($dirtyFields))
            ->toArray();

        foreach ($dirtyFields as $field => $newValue) {
            $oldValue = $originalFields[$field] ?? null;

            if ($oldValue == $newValue) continue;

            AuditTrailService::log(
                action:     'field_updated',
                resource:   $this->getResource($model),
                oldValue:   [$field => $oldValue],
                newValue:   [$field => $newValue],
                moduleName: $this->getModule($model),
                notes:      "'{$field}' changed from '{$oldValue}' to '{$newValue}'"
            );
        }
    }

    public function deleted(Model $model): void
    {
        $data = collect($model->toArray())
            ->except($this->skipFields)
            ->toArray();

        AuditTrailService::log(
            action:     'deleted',
            resource:   $this->getResource($model),
            oldValue:   $data,
            newValue:   [],
            moduleName: $this->getModule($model),
            notes:      $this->getResource($model) . " #{$model->id} deleted"
        );
    }
}