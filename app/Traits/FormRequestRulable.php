<?php

namespace App\Traits;

use Illuminate\Validation\Rule;

trait FormRequestRulable
{
    protected function pageRules(int $min = 10, int $max = 100): array
    {
        return [
            'page' => ['bail', 'required', 'integer', 'min:1'],
            'perPage' => ['bail', 'required', 'integer', "min:{$min}", "max:{$max}"]
        ];
    }

    protected function sortRules(string|array $sortBy): array
    {
        return [
            'sortBy' => ['bail', 'required', 'string', Rule::in((array) $sortBy)],
            'sortDirection' => ['bail', 'required', 'string', Rule::in(['asc', 'desc'])]
        ];
    }

    protected function dateRules(): array
    {
        return [
            'startDate' => ['bail', 'required', 'string', 'date_format:Y-m-d', 'before_or_equal:end_date'],
            'endDate' => ['bail', 'required', 'string', 'date_format:Y-m-d']
        ];
    }

    protected function searchRules(string|array $searchBy): array
    {
        return [
            'searchQuery' => ['bail', 'nullable', 'string'],
            'searchBy' => ['bail', 'required_with:search_query', 'string', Rule::in((array) $searchBy)]
        ];
    }
}
