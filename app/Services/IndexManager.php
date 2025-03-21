<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\BcbData;
use App\Enums\BcbCodes;

class IndexManager
{
    protected $indices = [];
    protected $defaultIndex = [
        'correction' => 0,
        'interest' => 0,
        'fine' => 0,
        'fee' => 0,
    ];

    public function __construct($customIndices = [])
    {
        $this->indices = $this->processIndices($customIndices);
    }

    // Processar índices personalizados e preencher lacunas
    protected function processIndices($indices)
    {
        // Ordenar índices por data de início
        usort($indices, function ($a, $b) {
            return Carbon::parse($a['start'])->timestamp <=> Carbon::parse($b['start'])->timestamp;
        });

        // Detectar sobreposição de períodos
        for ($i = 1; $i < count($indices); $i++) {
            if (Carbon::parse($indices[$i]['start'])->lte(Carbon::parse($indices[$i - 1]['end']))) {
                throw new \Exception("Sobreposição detectada entre períodos.");
            }
        }

        // Preencher lacunas com valores padrão (0%)
        $filledIndices = [];
        for ($i = 0; $i < count($indices); $i++) {
            $filledIndices[] = $indices[$i];

            if ($i < count($indices) - 1) {
                $currentEnd = Carbon::parse($indices[$i]['end']);
                $nextStart = Carbon::parse($indices[$i + 1]['start']);

                if ($currentEnd->lt($nextStart->subDay())) {
                    $filledIndices[] = [
                        'start' => $currentEnd->addDay()->format('Y-m-d'),
                        'end' => $nextStart->subDay()->format('Y-m-d'),
                        'indices' => $this->defaultIndex
                    ];
                }
            }
        }

        return $filledIndices;
    }

    // Obter índices para uma data específica
    public function getIndicesForDate($date)
    {
        foreach ($this->indices as $period) {
            $start = Carbon::parse($period['start']);
            $end = Carbon::parse($period['end']);

            if ($date->between($start, $end)) {
                return $period['indices'];
            }
        }
        // Retornar valores padrão se nenhum índice personalizado for encontrado
        return $this->defaultIndex;
    }

    // Obter taxa de correção monetária para uma data específica
    public function getCorrectionRate($date)
    {
        $indices = $this->getIndicesForDate($date);
        $correction = $indices['correction'];

        if (isset($indices['use_index']) && $indices['use_index']) {
            $correction = $this->getIndexRateFromDatabase($indices['index_name'], $date);
        }

        return $correction;
    }

    // Obter taxa de juros para uma data específica
    public function getInterestRate($date)
    {
        $indices = $this->getIndicesForDate($date);
        $interest = $indices['interest'];

        if (isset($indices['use_index']) && $indices['use_index']) {
            $interest = $this->getIndexRateFromDatabase($indices['index_name'], $date);
        }

        return $interest;
    }

    // Método para obter taxa do índice a partir do banco de dados
    protected function getIndexRateFromDatabase($indexName, $date)
    {
        // Mapear o nome do índice para o código BCB
        switch (strtoupper($indexName)) {
            case 'SELIC':
                $bcbCode = BcbCodes::SELIC_DIARIA;
                break;
            case 'IPCA':
                $bcbCode = BcbCodes::IPCA;
                break;
            case 'IGPM':
            case 'IGP-M':
                $bcbCode = BcbCodes::IGP_M;
                break;
            case 'INPC':
                $bcbCode = BcbCodes::INPC;
                break;
            // Adicione outros índices conforme necessário
            default:
                return 0;
        }

        // Formatar a data
        $formattedDate = Carbon::parse($date)->format('Y-m-d');

        // Buscar o valor do índice no banco de dados
        $bcbData = BcbData::where('bcb_code', $bcbCode)
            ->where('data', '<=', $formattedDate)
            ->orderBy('data', 'desc')
            ->first();

        if ($bcbData) {
            return $bcbData->valor;
        } else {
            // Se não encontrar o valor para a data específica, retornar 0 ou lidar conforme necessidade
            return 0;
        }
    }

    // Obter taxa de multa para uma data específica
    public function getFineRate($date)
    {
        $indices = $this->getIndicesForDate($date);
        return $indices['fine'];
    }

    // Obter taxa de honorários para uma data específica
    public function getFeeRate($date)
    {
        $indices = $this->getIndicesForDate($date);
        return $indices['fee'];
    }
}
