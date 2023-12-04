<?php

namespace Webbundels\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model as LaravelModel;

abstract class Model extends LaravelModel
{

    public function scopeFilter($query, $filters = [])
    {
        // Filter trough default column filters
        $query = $this->filterColumn($query, $filters, 'id');
        $query = $this->filterColumn($query, $filters, 'id', true);
        $query = $this->filterColumn($query, $filters, 'name');
        $query = $this->filterColumn($query, $filters, 'name', true);

        // Loop trough all filters, check and preform some default filters.
        foreach ($filters as $key => $value) {

            // Preform default filters on the given 'value'.
            if (is_string($value)) {
                $query = $this->filtersOnValue($query, $value);
            }

            // If 'key' isn't a string: there is nothing to filter on so go to next filter
            if (! is_string($key)) continue;

            // Add an whereHas statement to the query
            $query = $this->addWhereHasStatementToQuery($query, $key, $value);

            // Preform default filters on the given 'key' and 'value'.
            $query = $this->filtersOnKeyAndValue($query, $key, $value);
        }

        // Check for some default statement to add to the given 'query'.
        $query = $this->addTakeStatementToQuery($query, $filters);
        $query = $this->addSkipStatementToQuery($query, $filters);
        $query = $this->addWithTrashedStatementToQuery($query, $filters);
        $query = $this->addOnlyTrashedStatementToQuery($query, $filters);
        $query = $this->addWithoutScopeStatementToQuery($query, $filters);
        $query = $this->addWithoutScopesStatementToQuery($query, $filters);
        $query = $this->addOrderByStatementToQuery($query, $filters);

        return $query;
    }

    // Add with statement to the given 'query'
    public function scopeWiths($query, $with)
    {
        $relationWheres = [];
        $withCount = [];

        // Loop trough all 'with' values.
        foreach($with as $key => $value) {

            // Add withCount statement to the array 'withCount'.
            list($with, $withCount, $foundWithCount) = $this->addWithCountStatementToQuery(
                $key, $value, $with, $withCount
            );

            // If 'value' is an array.
            // Then: Add 'key' as key to the given array 'with',
            // with as value an closure that filters the 'key' relation
            // with the filter function.
            if (is_array($value) and ! $foundWithCount) {
                $with[$key] = function($q) use ($value) {
                    return $q->filter($value);
                };
            }
        }

        if ($withCount) {
            $query = $query->withCount($withCount);
        }

        return $query->with($with);
    }

    // If the given 'key' or 'value' start with 'count'.
    // Then: Add an withCount statement to the query,
    // and remove the current key/value pair from the given array 'with'.
    protected function addWithCountStatementToQuery($key, $value, $with, $withCount)
    {
        $foundWithCount = false;
        if (Str::contains($key, 'count') and ! Str::contains($key, 'country') and ! Str::contains($key, 'account')) {
            if (Str::contains($key, '.')) {
                $relation = Str::before($key, '.count');
                $innerRelation = lcFirst(Str::after($key, '.count'));

                $with[$relation] = function($query) use ($innerRelation, $value) {
                    return $query->withCount([
                        $innerRelation => function($q) use ($value) {
                            return $q->filter($value);
                        }
                    ]);
                };
            } else {
                $relation = lcFirst(substr($key, 5));
                $withCount[$relation] = function($q) use ($value) {
                    return $q->filter($value);
                };
            }
            unset($with[$key]);
            $foundWithCount = true;
        } elseif (is_string($value) and Str::contains($value, 'count') and ! Str::contains($value, 'country') and ! Str::contains($value, 'account')) {
            if (Str::contains($value, '.')) {
                $relation = Str::before($value, '.count');
                $innerRelation = lcFirst(Str::after($value, '.count'));
                $with[$relation] = function($query) use ($innerRelation) {
                    return $query->withCount($innerRelation);
                };
            } else {
                $relation = lcFirst(substr($value, 5));
                $withCount[] = $relation;
            }
            unset($with[$key]);
            $foundWithCount = true;
        }

        return [$with, $withCount, $foundWithCount];
    }

    // Check given 'key' and 'value' on default filters.
    protected function filtersOnValue($query, $value)
    {
        $query = $this->filterOnNull($query, $value);
        $query = $this->filterOnNull($query, $value, true);

        return $query;
    }

    // Check given 'key' and 'value' on default filters.
    protected function filtersOnKeyAndValue($query, $key, $value)
    {
        // If the given 'key' does not contain _is in the given 'key': return the given 'query'.
        if (! Str::contains($key, ['_is', '_>', '_<'])) return $query;

        $not = Str::contains($key, ['_not', '_!=']);
        $equal = Str::contains($key, ['_equal', '_<=', '_>=']);

        $query = $this->filterOnGreaterThan($query, $key, $value, $equal);
        $query = $this->filterOnLessThan($query, $key, $value, $equal);
        if (! is_array($value)) {
            $query = $this->filterOnEqual($query, $key, $value, $not);
        } else {
            $query = $this->filterOnIsInArray($query, $key, $value, $not);
        }

        return $query;
    }


    // If the given string 'key' ends with an version of _greater_than.
    // Then: Add the corresponding where statement to the given 'query'.
    protected function filterOnGreaterThan($query, string $key, $value, bool $equal = false)
    {
        $type = $equal ? ['_>=', '_is_greater_or_equal_than'] : ['_>', '_greater_than'];

        if (Str::endsWith($key, $type)) {
            $type = Str::endsWith($key, $type[0]) ? $type[0] : $type[1];
            $column = $this->getTable() . '.' . Str::before($key, $type);

            $operator = $equal ? '>=' : '>';

            $query = $query->where($column, $operator, $value);
        }

        return $query;
    }

    // If the given string 'key' ends with an version of _lesser_than.
    // Then: Add the corresponding where statement to the given 'query'.
    protected function filterOnLessThan($query, string $key, $value, bool $equal = false)
    {
        $type = $equal ? ['_<=', '_is_lower_or_equal_than'] : ['_<', '_is_lower_than'];

        if (Str::endsWith($key, $type)) {
            $type = Str::endsWith($key, $type[0]) ? $type[0] : $type[1];
            $column = $this->getTable() . '.' . Str::before($key, $type);
            $operator = $equal ? '<=' : '<';

            $query = $query->where($column, $operator, $value);
        }

        return $query;
    }

    // If the given string 'key' ends with _is or _is_not.
    // Then: Add an = or != where statement to the given 'query'.
    protected function filterOnEqual($query, string $key, $value, bool $not = false)
    {
        $type = $not ? ['_is_not', '_!='] : ['_is', '_='];

        if (Str::endsWith($key, $type)) {
            $type = Str::endsWith($key, $type[0]) ? $type[0] : $type[1];
            $column = $this->getTable() . '.' . Str::before($key, $type);

            if ($value === null) {
                $function = ($not ? 'whereNotNull' : 'whereNull');
                $query = $query->{$function}($column);
            } else {
                $operator = ($not ? '!=' : '=');
                $query = $query->where($column, $operator, $value);
            }
        }

        return $query;
    }

    // If the given string 'key' ends with _is_in or _is_not.
    // Then: Add an whereIn or WhereNotIn statement to the given 'query'.
    protected function filterOnIsInArray($query, string $key, $value, bool $not = false)
    {
        $type = $not ? ['_is_not_in', '_!='] : ['_is_in', '_='];

        if (Str::endsWith($key, $type)) {
            $type = Str::endsWith($key, $type[0]) ? $type[0] : $type[1];
            $column = $this->getTable() . '.' . Str::before($key, $type);
            $query = $query->whereIn($column, $value, 'and', $not);
        }

        return $query;
    }

    // If the given string 'value' ends with _is_not_null or _is_null.
    // Then: Add an whereNull or whereNotNull statement to the given 'query'.
    protected function filterOnNull($query, $value, bool $not = false)
    {
        $type = $not ? '_is_not_null' : '_is_null';

        if (Str::endsWith($value, $type)) {
            $column = $this->getTable() . '.' . Str::before($value, $type);
            $query = $query->whereNull($column, 'and', $not);
        }

        return $query;
    }


    // IF an singular or plural version of the given string 'column' exists in the given array 'filters'.
    // Then: Add an the corresponding 'where' statement to the given 'query'.
    protected function filterColumn($query, array $filters , string $column, bool $not = false)
    {
        // Set the singular and plural version of the given column 'column',
        // And append '-not' if given the boolean 'not' is true.
        $append = ($not ? '_not' : '');
        $singularColumn = Str::singular($column) . $append;
        $pluralColumn = Str::plural($column) . $append;

        // If 'singularColumn' or 'pluralColumn' exists as key in the given array 'filters'.
        // Then: Add the where filter to to given 'query'.
        if (array_key_exists($singularColumn, $filters) or array_key_exists($pluralColumn, $filters)) {
            // If 'singularColumn' exists as key in filters: Set that value in 'value'.
            // Else: set 'value' to the value of 'pluralColumn' in filters.
            $value = array_key_exists($singularColumn, $filters) ? $filters[$singularColumn] : $filters[$pluralColumn];

            // Set 'tableColumn' to the given string 'column' and prepend the table name.
            $tableColumn = $this->getTable() . '.' . $column;

            // If 'value' is an array: Add an whereIn statement to the 'query'.
            // Else: Add an where statement to then 'query'.
            if (is_array($value)) {
                $query = $query->whereIn($tableColumn, $value, 'and', $not);
            } else {
                $query = $query->where($tableColumn, ($not ? '!=' : '='), $value);
            }
        }

        return $query;
    }


    // If take exists as key in the given array 'filters'.
    // Then: Add a take(limit) statement to the given 'query'
    // with the given value of 'take' in 'filters'
    protected function addTakeStatementToQuery($query, $filters)
    {
        if (array_key_exists('take', $filters)) {
            $query = $query->take($filters['take']);
        }

        return $query;
    }

    // If skip exists as key in the given array 'filters'.
    // Then: Add a skip statement to the given 'query'
    // with the given value of 'take' in 'filters'
    protected function addSkipStatementToQuery($query, $filters)
    {
        if (array_key_exists('skip', $filters)) {
            $query = $query->skip($filters['skip']);
        }

        return $query;
    }

    // If with_trashed exists as key in the given array 'filters'.
    // Then: Add a withTrashed(include deleted_at not null) statement to the given 'query'.
    protected function addWithTrashedStatementToQuery($query, $filters)
    {
        if (in_array('with_trashed', $filters, true)) {
            $query = $query->withTrashed();
        }

        return $query;
    }

    // If only_trashed exists as key in the given array 'filters'.
    // Then: Add a withTrashed(include deleted_at not null) statement to the given 'query'.
    protected function addOnlyTrashedStatementToQuery($query, $filters)
    {
        if (in_array('only_trashed', $filters, true)) {
            $query = $query->onlyTrashed();
        }

        return $query;
    }

    // If without_scope exists as key in the given array 'filters'.
    // Then: Add a withoutGLobalScope statement to the given 'query'
    // with as value the value of 'without-scope' in the given array 'filters'.
    protected function addWithoutScopeStatementToQuery($query, $filters)
    {
        if (array_key_exists('without_scope', $filters)) {
            $query = $query->withoutGlobalScope($filters['without_scope']);
        }

        return $query;
    }

    // If without_scopes exists as key in the given array 'filters'.
    // Then: Add a withoutGLobalScopes statement to the given 'query'.
    protected function addWithoutScopesStatementToQuery($query, $filters)
    {
        if (in_array('without_scopes', $filters, true)) {
            $query = $query->withoutGlobalScopes();
        }

        return $query;
    }

    // If order_by or order_by_desc exists as key in the given array 'filters'.
    // Then: Add a orderBy statement to the given 'query'
    // with as value the value of order_by(_desc) in the given array 'filters'.
    protected function addOrderByStatementToQuery($query, $filters)
    {
        if (array_key_exists('sort', $filters)) {
            $columns = explode(',', $filters['sort']);
            foreach ($columns as $column) {
                if (Str::startsWith($column, '-')) {
                    $query = $query->orderBy(Str::after($column, '-'), 'desc');
                } else {
                    $query = $query->orderBy($column);
                }
            }
        } elseif (array_key_exists('order_by', $filters) and is_array($filters['order_by'])) {
            foreach ($filters['order_by'] as $orderBy) {
                if (is_array($orderBy)) {
                    $query = $this->addOrderByStatementToQuery($query, $orderBy);
                } elseif (Str::beginsWith($orderBy, 'order_by_desc_')) {
                    $column = Str::before($orderBy, 'order_by_desc_');
                    $query = $query->orderBy($column, 'desc');
                } elseif (Str::beginsWith($orderBy, 'order_by_')) {
                    $column = Str::before($orderBy, 'order_by_');
                    $query = $query->orderBy($column);
                }
            }
        } elseif (array_key_exists('order_by', $filters)) {
            $query = $query->orderBy($filters['order_by']);
        } elseif (array_key_exists('order_by_desc', $filters)) {
            $query = $query->orderBy($filters['order_by_desc'], 'desc');
        }

        return $query;
    }

    // If the 'given' string key is an relation of 'this'.
    // Then: Add an whereHas statement to the given 'query', use the filter method
    // to filter trough the relations filters with as value the given array 'value'.
    // If the 'given' string started with an exclamation mark, do the same but with
    // an whereDoesntHave statement.
    protected function addWhereHasStatementToQuery($query, string $key, $value)
    {
        if (substr($key, 0, 1) == '!') {
            $key = substr($key, 1);
            $query = $query->whereDoesntHave($key, function($query) use ($value) {
                return $query->filter($value);
            });
        } elseif (is_array($value) and method_exists($this, explode('.', $key)[0])) {
            $query = $query->whereHas($key, function($query) use ($value) {
                return $query->filter($value);
            });
        }

        return $query;
    }

}
