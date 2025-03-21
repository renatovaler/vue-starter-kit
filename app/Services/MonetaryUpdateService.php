<?php

namespace App\Services;

use Carbon\Carbon;

class MonetaryUpdateService
{
    // Versão do código: 1.6.0

    public $indexManager;
    public $amortizationManager;
    public $historyManager;
    public $calculationHelper;

    protected $options = [
        // Opções padrão
        'capitalization' => 'simple', // 'simple' ou 'compound'
        'apply_fees_on_fine' => false,
        'apply_interest_on_expert_fees' => false,
        'apply_fine_on_expert_fees' => false,
        'apply_adv_fees_on_expert_fees' => false,
        'apply_fine_on_court_fees' => false,
        'apply_interest_on_court_fees' => false,
        'apply_adv_fees_on_court_fees' => false,
        'apply_fine_interest_compensatory' => false,
        'apply_fine_interest_moratory' => false,
        'apply_fine_on_future_installments' => false,
        'interest_period' => 'monthly', // 'daily', 'monthly', 'annual'
        'apply_correction_in_deflation' => true,
        // Opções novas
        'apply_multas_art523' => false,
        'apply_honorarios_art523' => false,
        'apply_litigancia_ma_fe' => false,
        'litigancia_ma_fe_percent' => 0,
        'apply_ato_atentatorio' => false,
        'ato_atentatorio_value' => 0,
        'ato_atentatorio_is_percent' => true,
    ];

    public function __construct($customIndices = [], $amortizations = [], $options = [])
    {
        $this->indexManager = new IndexManager($customIndices);
        $this->amortizationManager = new AmortizationManager($amortizations);
        $this->historyManager = new HistoryManager();

        // Passar a instância atual do serviço para o CalculationHelper
        $this->calculationHelper = new CalculationHelper($this);

        $this->options = array_merge($this->options, $options);
    }

    // Atualizar múltiplos valores entre datas iniciais e finais
    public function updateValues($values, $startDate, $endDate)
    {
        $date = Carbon::parse($startDate);
        $endDate = Carbon::parse($endDate);

        // Armazenar os valores originais para uso posterior
        $this->originalValues = $values;

        while ($date->lte($endDate)) {
            foreach ($values as $key => $value) {
                $updatedValue = $this->applyUpdates($value, $date, $key);
                // Os detalhes serão adicionados dentro do método applyUpdates
                $values[$key] = $updatedValue;
            }
            $date->addDay(); // Atualização diária para precisão "pro-rata-die"
        }

        // Aplicar amortizações
        $values = $this->amortizationManager->applyAmortizations($values, $this);

        // Aplicar multas específicas após a atualização
        $values = $this->calculationHelper->applySpecificFines($values, $this->options, $this);

        return $values;
    }

    // Aplicar atualizações para uma data específica
    protected function applyUpdates($value, $date, $key)
    {
        $indices = $this->indexManager->getIndicesForDate($date);

        $details = [];

        // Aplicar correção monetária
        $correctionAmount = 0;
        $initialValue = $value;
        $value = $this->calculationHelper->applyMonetaryCorrection(
            $value,
            $indices['correction'],
            $date,
            $this->options['apply_correction_in_deflation'],
            $correctionAmount
        );
        $details['Correção Monetária'] = number_format($correctionAmount, 2, ',', '.');

        // Aplicar juros
        $interestAmount = 0;
        $value = $this->calculationHelper->applyInterest(
            $value,
            $indices['interest'],
            $date,
            $this->options['interest_period'],
            $this->options['capitalization'],
            $interestAmount
        );
        $details['Juros'] = number_format($interestAmount, 2, ',', '.');

        // Aplicar multas
        $fineAmount = 0;
        $value = $this->calculationHelper->applyFines(
            $value,
            $indices['fine'],
            $date,
            $this->options['apply_fees_on_fine'],
            $this->indexManager->getFeeRate($date),
            $key,
            $this->options,
            $this,
            $fineAmount
        );
        $details['Multa'] = number_format($fineAmount, 2, ',', '.');

        // Aplicar honorários e custas
        $feeAmount = 0;
        $value = $this->calculationHelper->applyFees(
            $value,
            $indices['fee'],
            $date,
            $this->options,
            $this,
            $feeAmount
        );
        $details['Honorários'] = number_format($feeAmount, 2, ',', '.');

        // Registrar no histórico com detalhes
        $this->historyManager->addRecord($date, $key, $value, $details);

        return $value;
    }

    // Método para obter o valor original (antes das atualizações)
    public function getOriginalValue($key)
    {
        return $this->originalValues[$key] ?? 0;
    }

    // Obter histórico completo, analítico
    public function getUpdateHistory()
    {
        return $this->historyManager->getDetailedHistory();
    }
}
