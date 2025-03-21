<?php

namespace App\Services;

use App\Models\BcbData;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Enums\BcbCodes;

class BcbDataService
{
    /**
     * Atualiza os índices de correção monetária do BCB para um determinado período.
     *
     * @param array $indicesCodes
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    public function updateCorrectionIndices(array $indicesCodes, Carbon $startDate, Carbon $endDate): void
    {
        foreach ($indicesCodes as $code) {
            $this->getAndSaveIndexData($code, $startDate, $endDate);
        }
    }

    /**
     * Busca e salva os dados de um índice específico no banco de dados.
     *
     * @param int $bcbCode
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return void
     */
    protected function getAndSaveIndexData(int $bcbCode, Carbon $startDate, Carbon $endDate): void
    {
        $formattedStartDate = $startDate->format('d/m/Y');
        $formattedEndDate = $endDate->format('d/m/Y');

        // URL da API do BCB para obter dados da série
        $url = "https://api.bcb.gov.br/dados/serie/bcdata.sgs.$bcbCode/dados?formato=json&dataInicial=$formattedStartDate&dataFinal=$formattedEndDate";
        $response = Http::get($url);

        // Verifica se a requisição foi bem sucedida
        if ($response->successful()) {
            $data = $response->json();

            foreach ($data as $item) {
                $date = Carbon::createFromFormat('d/m/Y', $item['data'])->format('Y-m-d');
                $value = floatval(str_replace(',', '.', $item['valor']));

                BcbData::updateOrCreate(
                    [
                        'bcb_code' => $bcbCode,
                        'data' => $date,
                    ],
                    [
                        'valor' => $value,
                    ]
                );
            }
        } else {
            // Tratamento de erro
            \Log::error("Falha ao obter dados do BCB para o código $bcbCode.");
        }
    }

    /**
     * Obtém a taxa do índice na data especificada.
     *
     * @param int $bcbCode
     * @param Carbon $date
     * @return float
     */
    public function getRateByDate(int $bcbCode, Carbon $date): float
    {
        // Formatar a data
        $formattedDate = $date->format('Y-m-d');

        // Verificar se os dados estão armazenados no banco de dados
        $bcbData = BcbData::where('bcb_code', $bcbCode)
            ->where('data', '<=', $formattedDate)
            ->orderBy('data', 'desc')
            ->first();

        if ($bcbData) {
            return $bcbData->valor;
        } else {
            // Retornar 0 ou tratar conforme necessidade
            return 0.0;
        }
    }
}
