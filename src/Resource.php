<?php

namespace Webbundels\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

class Resource extends JsonResource
{
    public function relation($relation, $modelName = null)
    {
        // If modelName is not given as parameter: Set it to the value of the given relation.
        if (! $modelName) {
            $modelName = $relation;
        }

        // Check if the given relation is loaded. If the relation is not loaded, the resource
        // will know it does not have to do anything with the relation because of this variable.
        $relationIsLoaded = array_key_exists($relation, $this->resource->getRelations());

        // If the relation is not loaded: return an empty 'when'.
        if (! $relationIsLoaded) {
            return $this->when(false, '');
        }

        // Get the modelName in singular form and set first char to uppercase for classname purposes.
        $singularModelName = ucfirst(Str::singular($modelName));

        // Get the modelName in plural form and set first char to uppercase for namespace purposes.
        $pluralModelName = ucfirst(Str::plural($modelName));

        // Build the default resource class of the singularModelName and pluralModelName as string.
        $resourceClass = 'App\Http\Resources\\' . $pluralModelName . '\\' . $singularModelName;

        // If the the relation is an 'to-one' relation: return an resourceClass.
        // Else: return an ResourceCollectionClass.
        $resourceClass .= ($this->resource->{$relation} instanceof Collection ? 'Collection' : 'Resource');
        
        // If the builded collection class exists, instantiate it and return it in "resource when" function.
        if (! class_exists($resourceClass)) {
            return $this->when($relationIsLoaded, $this->resource->{$relation});
        }

        // Return the relation in an "resource when" function.
        return $this->wrapRelation($this->when($relationIsLoaded, new $resourceClass($this->resource->{$relation})));
    }

    // Wrap the relation in an array, set the data under key 'data'.
    public function wrapRelation($data) 
    {
        return ['data' => $data];
    }
}
