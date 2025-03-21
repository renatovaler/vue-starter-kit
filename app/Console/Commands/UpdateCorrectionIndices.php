<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BcbDataService;
use Carbon\Carbon;
use App\Enums\BcbCodes;

class UpdateCorrectionIndices extends Command
{
    /**
     * O nome e assinatura do comando console.
     *
     * @var string
     */
    protected $signature = 'indices:update';

    /**
     * A descrição do comando console.
     *
     * @var string
     */
    protected $description = 'Atualiza os índices de correção monetária do BCB e salva no banco de dados';

    /**
     * Executa o comando console.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Atualizando índices de correção monetária...');

        $bcbService = new BcbDataService();

        // Definir os códigos dos índices que deseja atualizar
        $indicesCodes = [
            BcbCodes::IPCA,
            BcbCodes::IGP_M,
            BcbCodes::INPC,
            // Adicione outros índices conforme necessário
        ];

        // Definir o período para atualização
        $today = Carbon::today();
        $startDate = $today->subDays(10); // Por exemplo, buscar os últimos 10 dias
        $endDate = $today;

        $bcbService->updateCorrectionIndices($indicesCodes, $startDate, $endDate);

        $this->info('Índices atualizados com sucesso!');

        return 0;
    }
}
