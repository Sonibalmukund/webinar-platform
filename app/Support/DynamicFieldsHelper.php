<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DynamicFieldsHelper
{
    /**
     * Check if a field key or label represents a standard user field that already has its own dedicated column.
     */
    public static function isStandardField(?string $key, ?string $label): bool
    {
        $normalize = fn ($str) => strtolower(trim(str_replace(['_', '-', ' ', '.', ':', '/'], '', (string)$str)));
        $normKey = $normalize($key);
        $normLabel = $normalize($label);

        $standard = [
            'name', 'fullname', 'firstname', 'lastname', 'username',
            'email', 'emailaddress',
            'mobile', 'mobilenumber', 'phone', 'phonenumber', 'contact', 'contactnumber'
        ];

        return in_array($normKey, $standard, true) || in_array($normLabel, $standard, true);
    }

    /**
     * Attach dynamic field key-value pairs to a collection or paginator of rows.
     * Excludes duplicate standard fields (Name, Email, Mobile).
     * Resolves numeric geographic IDs (City, State, Country) to their names.
     * Returns an ordered array of unique dynamic field column labels.
     */
    public static function attach(iterable $rows, ?iterable $scopedWebinarIds = null): array
    {
        $collection = $rows instanceof \Illuminate\Pagination\AbstractPaginator
            ? $rows->getCollection()
            : ($rows instanceof Collection ? $rows : collect($rows));

        if ($collection->isEmpty()) {
            return [];
        }

        $userIds = $collection->map(function ($row) {
            return is_object($row) ? ($row->user_id ?? ($row->id ?? ($row->user?->id ?? null))) : null;
        })->filter()->unique()->values()->all();

        $webinarIds = [];
        foreach ($collection as $row) {
            if (!is_object($row)) {
                continue;
            }
            if (isset($row->webinar_id) && $row->webinar_id) {
                $webinarIds[] = (int) $row->webinar_id;
            } elseif (isset($row->registrations) && $row->registrations instanceof \Illuminate\Support\Collection) {
                foreach ($row->registrations as $reg) {
                    if (isset($reg->webinar_id) && $reg->webinar_id) {
                        $webinarIds[] = (int) $reg->webinar_id;
                    }
                }
            } elseif (isset($row->user) && isset($row->user->registrations) && $row->user->registrations instanceof \Illuminate\Support\Collection) {
                foreach ($row->user->registrations as $reg) {
                    if (isset($reg->webinar_id) && $reg->webinar_id) {
                        $webinarIds[] = (int) $reg->webinar_id;
                    }
                }
            }
        }
        $webinarIds = array_values(array_unique(array_filter($webinarIds)));

        // If current authenticated user is a sub-admin, strictly limit to their assigned webinars
        $currentUser = auth()->user();
        if ($currentUser && method_exists($currentUser, 'hasRole') && $currentUser->hasRole('sub-admin')) {
            $accessible = $currentUser->accessibleWebinarIds();
            $accessible = ($accessible instanceof \Illuminate\Support\Collection)
                ? $accessible->map(fn ($id) => (int) $id)->all()
                : array_map('intval', (array) $accessible);
            if (!empty($webinarIds)) {
                $webinarIds = array_values(array_intersect($webinarIds, $accessible));
            } else {
                $webinarIds = $accessible;
            }
            if (empty($webinarIds)) {
                foreach ($collection as $row) {
                    if (is_object($row)) {
                        $row->dynamic_fields = [];
                    }
                }
                return [];
            }
        }

        if ($scopedWebinarIds !== null) {
            $scopedArr = collect($scopedWebinarIds)->map(fn ($id) => (int)$id)->all();
            if (!empty($webinarIds)) {
                $webinarIds = array_values(array_intersect($webinarIds, $scopedArr));
            } else {
                $webinarIds = $scopedArr;
            }
        }

        if (empty($userIds)) {
            return [];
        }

        // 1. Fetch webinar registration answers
        $regQuery = DB::table('registration_answers as ra')
            ->join('registration_fields as rf', 'rf.id', '=', 'ra.registration_field_id')
            ->join('registrations as r', 'r.id', '=', 'ra.registration_id')
            ->whereIn('r.user_id', $userIds)
            ->select([
                'r.user_id',
                'r.webinar_id',
                'rf.label',
                'rf.field_key',
                'ra.value',
            ]);

        if (!empty($webinarIds)) {
            $regQuery->whereIn('r.webinar_id', $webinarIds);
        }

        $regAnswers = $regQuery->get();

        // 2. Fetch global signup field answers only if not scoped to sub-admin
        if ($currentUser && method_exists($currentUser, 'hasRole') && $currentUser->hasRole('sub-admin')) {
            $signupAnswers = collect();
        } else {
            $signupAnswers = DB::table('signup_field_answers as sfa')
                ->join('signup_fields as sf', 'sf.id', '=', 'sfa.signup_field_id')
                ->whereIn('sfa.user_id', $userIds)
                ->select([
                    'sfa.user_id',
                    'sf.label',
                    'sf.field_key',
                    'sfa.value',
                ])
                ->get();
        }

        // Collect geographic IDs for lookup
        $cityIds = [];
        $stateIds = [];
        $countryIds = [];

        $allAnswers = $regAnswers->concat($signupAnswers);
        foreach ($allAnswers as $ans) {
            if (self::isStandardField($ans->field_key, $ans->label)) {
                continue;
            }
            $val = trim((string)$ans->value);
            if (is_numeric($val)) {
                $num = (int)$val;
                $k = strtolower(trim(str_replace(['_', '-'], '', (string)$ans->field_key)));
                $l = strtolower(trim(str_replace(['_', '-'], '', (string)$ans->label)));
                if ($k === 'city' || $l === 'city') {
                    $cityIds[] = $num;
                } elseif ($k === 'state' || $l === 'state') {
                    $stateIds[] = $num;
                } elseif ($k === 'country' || $l === 'country') {
                    $countryIds[] = $num;
                }
            }
        }

        $cityMap = !empty($cityIds) ? DB::table('cities')->whereIn('id', array_unique($cityIds))->pluck('name', 'id')->all() : [];
        $stateMap = !empty($stateIds) ? DB::table('states')->whereIn('id', array_unique($stateIds))->pluck('name', 'id')->all() : [];
        $countryMap = !empty($countryIds) ? DB::table('countries')->whereIn('id', array_unique($countryIds))->pluck('name', 'id')->all() : [];

        // Helper to format/resolve value
        $formatValue = function ($val, $fieldKey, $label) use ($cityMap, $stateMap, $countryMap) {
            if (is_string($val) && (str_starts_with($val, '[') || str_starts_with($val, '{'))) {
                $decoded = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $val = implode(', ', $decoded);
                }
            }

            if (is_numeric($val)) {
                $num = (int)$val;
                $k = strtolower(trim(str_replace(['_', '-'], '', (string)$fieldKey)));
                $l = strtolower(trim(str_replace(['_', '-'], '', (string)$label)));
                if (isset($cityMap[$num]) && ($k === 'city' || $l === 'city')) {
                    $val = $cityMap[$num];
                } elseif (isset($stateMap[$num]) && ($k === 'state' || $l === 'state')) {
                    $val = $stateMap[$num];
                } elseif (isset($countryMap[$num]) && ($k === 'country' || $l === 'country')) {
                    $val = $countryMap[$num];
                }
            }

            return $val;
        };

        // Group by user_id and webinar_id
        $regGrouped = $regAnswers->groupBy(fn ($item) => $item->user_id . '_' . $item->webinar_id);
        $userRegFallback = $regAnswers->groupBy('user_id');
        $signupGrouped = $signupAnswers->groupBy('user_id');

        $dynamicColumns = [];

        foreach ($collection as $row) {
            if (!is_object($row)) {
                continue;
            }

            $uId = $row->user_id ?? ($row->id ?? ($row->user?->id ?? null));
            $wId = $row->webinar_id ?? null;

            $fields = [];

            // Add webinar specific registration answers
            $rowWebinarIds = [];
            if ($wId) {
                $rowWebinarIds[] = (int)$wId;
            } elseif (isset($row->registrations) && $row->registrations instanceof \Illuminate\Support\Collection) {
                $rowWebinarIds = $row->registrations->pluck('webinar_id')->map(fn($id)=>(int)$id)->all();
            } elseif (isset($row->user) && isset($row->user->registrations) && $row->user->registrations instanceof \Illuminate\Support\Collection) {
                $rowWebinarIds = $row->user->registrations->pluck('webinar_id')->map(fn($id)=>(int)$id)->all();
            }

            $key = $uId . '_' . $wId;
            if ($wId && isset($regGrouped[$key])) {
                $items = $regGrouped[$key];
            } else {
                $items = ($userRegFallback->get($uId) ?? collect());
                if (!empty($rowWebinarIds)) {
                    $items = $items->whereIn('webinar_id', $rowWebinarIds);
                }
            }

            foreach ($items as $ans) {
                if (self::isStandardField($ans->field_key, $ans->label)) {
                    continue; // Do not duplicate Name, Email, Mobile
                }
                $val = $formatValue($ans->value, $ans->field_key, $ans->label);
                if ($val !== null && $val !== '') {
                    $fields[$ans->label] = $val;
                    if (!in_array($ans->label, $dynamicColumns, true)) {
                        $dynamicColumns[] = $ans->label;
                    }
                }
            }

            // Add signup answers
            if (isset($signupGrouped[$uId])) {
                foreach ($signupGrouped[$uId] as $ans) {
                    if (self::isStandardField($ans->field_key, $ans->label)) {
                        continue; // Do not duplicate Name, Email, Mobile
                    }
                    if (!isset($fields[$ans->label])) {
                        $val = $formatValue($ans->value, $ans->field_key, $ans->label);
                        if ($val !== null && $val !== '') {
                            $fields[$ans->label] = $val;
                            if (!in_array($ans->label, $dynamicColumns, true)) {
                                $dynamicColumns[] = $ans->label;
                            }
                        }
                    }
                }
            }

            $row->dynamic_fields = $fields;
        }

        if ($rows instanceof \Illuminate\Pagination\AbstractPaginator || $rows instanceof Collection) {
            $rows->dynamic_columns = $dynamicColumns;
        }

        return $dynamicColumns;
    }
}
