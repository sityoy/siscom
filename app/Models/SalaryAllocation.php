<?php

namespace App\Models;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use Illuminate\Database\Eloquent\Model;

class SalaryAllocation extends Model
{
    public function allocatedAmount(BigDecimal $profit): BigDecimal
    {
        $value = BigDecimal::of((string) $this->value);

        return $this->mode === 'percent'
            ? BigDecimal::max('0', $profit)->multipliedBy($value)->dividedBy(100, 2, RoundingMode::HalfUp)
            : $value;
    }
}
