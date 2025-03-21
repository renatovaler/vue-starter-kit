<?php

namespace App\Services;

use Carbon\Carbon;

class CalculationHelper
{
    protected $service;

    public function __construct($service)
    {
        $this->service = $service;
    }

    // Aplicar correção monetária
    public function applyMonetaryCorrection($value, $correctionIndex, $date, $applyInDeflation, &$correctionAmount)
    {
        if ($correctionIndex == 0) {
            $correctionIndex = $this->service->indexManager->getCorrectionRate($date);
        }

        if ($correctionIndex < 0 && !$applyInDeflation) {
            $correctionAmount = 0;
            return $value;
        }

        $correctionAmount = $value * ($correctionIndex / 100);
        return $value + $correctionAmount;
    }

    // Aplicar juros
    public function applyInterest($value, $interestRate, $date, $interestPeriod, $capitalization, &$interestAmount)
    {
        if ($interestRate == 0) {
            $interestRate = $this->service->indexManager->getInterestRate($date);
        }

        $rate = $interestRate / 100;

        if ($interestPeriod == 'daily') {
            $rate /= 365;
        } elseif ($interestPeriod == 'monthly') {
            $rate /= 12;
        }

        if ($capitalization == 'compound') {
            $interestAmount = $value * pow((1 + $rate), 1) - $value;
        } else {
            $interestAmount = $value * $rate;
        }

        return $value + $interestAmount;
    }

    // Aplicar multas
    public function applyFines($value, $fineData, $date, $applyFeesOnFine, $feeRate, $key, $options, $service, &$fineAmount)
    {
        if (is_array($fineData)) {
            $fineRate = $fineData['rate'];
        } else {
            $fineRate = $fineData;
        }

        $fineAmount = $value * ($fineRate / 100);

        // Incidência facultativa de honorários sobre a multa
        if ($applyFeesOnFine) {
            $feeAmount = $fineAmount * ($feeRate / 100);
            $fineAmount += $feeAmount;
        }

        return $value + $fineAmount;
    }

    // Aplicar honorários e custas
    public function applyFees($value, $feeData, $date, $options, $service, &$feeAmount)
    {
        if (is_array($feeData)) {
            $feeRate = $feeData['rate'];
        } else {
            $feeRate = $feeData;
        }

        $feeAmount = $value * ($feeRate / 100);
        return $value + $feeAmount;
    }

    // Observação: Outros métodos, como applySpecificFines, continuarão conforme a versão anterior.
}
