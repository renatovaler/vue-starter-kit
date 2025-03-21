<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class BcbCodes extends Enum
{
    const TAXA_LEGAL = 29543;        // Taxa de juros - Selic diária
    const SELIC_DIARIA = 11;        // Taxa de juros - Selic diária
    const IPCA = 433;               // Índice Nacional de Preços ao Consumidor Amplo (IPCA)
    const IPCA_15 = 7478;           // Índice de Preços ao Consumidor-Amplo (IPCA) - 15
    const IGP_M = 189;              // Índice Geral de Preços - Mercado (IGP-M)
    const INPC = 188;               // Índice Nacional de Preços ao Consumidor (INPC)
    // Adicione outros índices conforme necessário
}
