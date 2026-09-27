<?php

namespace App\Services;

/**
 * Centralized marks validation so the same rules apply whether marks arrive
 * via bulk CSV import or a single-record API call.
 *
 * @return array{0: string, 1: ?string} [status, error_message]
 */
class MarksValidationService
{
    public function validate(float $marksObtained, float $maxMarks): array
    {
        if (is_nan($marksObtained)) {
            return ['rejected', 'Marks value is not numeric.'];
        }

        if ($marksObtained < 0) {
            return ['rejected', 'Marks cannot be negative.'];
        }

        if ($marksObtained > $maxMarks) {
            return ['rejected', "Marks ({$marksObtained}) exceed the component's max marks ({$maxMarks})."];
        }

        return ['valid', null];
    }
}
