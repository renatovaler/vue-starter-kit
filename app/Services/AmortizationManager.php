<?php

namespace App\Services;

use Carbon\Carbon;

class AmortizationManager
{
    protected $amortizations = [];

    public function __construct($amortizations = [])
    {
        $this->amortizations = $amortizations;
    }

    // Aplicar amortizações
    public function applyAmortizations($debt, $service)
    {
        foreach ($this->amortizations as $amortization) {
            $date = Carbon::parse($amortization['date']);
            $amount = $amortization['amount'];
            $applyToCapital = $amortization['applyToCapital'];

            // Atualizar o débito até a data da amortização
            $debt = $service->updateValues($debt, $debt['date'], $date->format('Y-m-d'));
            $debt['date'] = $date->format('Y-m-d');

            // Realizar a amortização
            if ($applyToCapital) {
                $debt['capital'] -= $amount;
            } else {
                $debt['interest'] -= $amount;
                if ($debt['interest'] < 0) {
                    $debt['capital'] += $debt['interest'];
                    $debt['interest'] = 0;
                }
            }

            // Registrar no histórico
            $service->historyManager->addAmortizationRecord($date, $debt);
        }
        return $debt;
    }
}
