<?php

namespace App\Services;

use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HistoryExport;
use PDF;

class HistoryManager
{
    protected $history = [];

    // Adicionar registro de atualização
    public function addRecord($date, $key, $value, $details = [])
    {
        $this->history[] = [
            'date' => $date->format('Y-m-d'),
            'type' => 'Atualização',
            'key' => $key,
            'value' => $value,
            'details' => $details,
        ];
    }

    // Adicionar registro de amortização
    public function addAmortizationRecord($date, $debt)
    {
        $this->history[] = [
            'date' => $date->format('Y-m-d'),
            'type' => 'Amortização',
            'capital' => $debt['capital'],
            'interest' => $debt['interest'],
        ];
    }

    // Adicionar registro de multa
    public function addFineRecord($description, $amount, $date = null)
    {
        $this->history[] = [
            'date' => $date ? $date->format('Y-m-d') : now()->format('Y-m-d'),
            'type' => 'Multa',
            'description' => $description,
            'amount' => $amount,
        ];
    }

    // Adicionar registro de honorários
    public function addFeeRecord($description, $amount, $date = null)
    {
        $this->history[] = [
            'date' => $date ? $date->format('Y-m-d') : now()->format('Y-m-d'),
            'type' => 'Honorários',
            'description' => $description,
            'amount' => $amount,
        ];
    }

    // Obter histórico detalhado (evolução mês a mês)
    public function getDetailedHistory()
    {
        return $this->history;
    }

    // Obter histórico em formato de planilha (dados tabulares)
    public function getSpreadsheetData()
    {
        // Organizar os dados em formato de planilha
        $spreadsheetData = [];
        foreach ($this->history as $record) {
            $row = [
                'Data' => $record['date'],
                'Tipo' => $record['type'],
            ];

            // Adicionar informações adicionais conforme o tipo
            switch ($record['type']) {
                case 'Atualização':
                    $row['Chave'] = $record['key'];
                    $row['Valor'] = number_format($record['value'], 2, ',', '.');
                    if (!empty($record['details'])) {
                        $row = array_merge($row, $record['details']);
                    }
                    break;

                case 'Amortização':
                    $row['Capital'] = number_format($record['capital'], 2, ',', '.');
                    $row['Juros'] = number_format($record['interest'], 2, ',', '.');
                    break;

                case 'Multa':
                case 'Honorários':
                    $row['Descrição'] = $record['description'];
                    $row['Valor'] = number_format($record['amount'], 2, ',', '.');
                    break;
            }

            $spreadsheetData[] = $row;
        }
        return $spreadsheetData;
    }

    // Obter histórico resumido
    public function getSummary()
    {
        $summary = [
            'total_capital' => 0,
            'total_interest' => 0,
            'total_fines' => 0,
            'total_fees' => 0,
            'total_updates' => 0,
        ];

        foreach ($this->history as $record) {
            switch ($record['type']) {
                case 'Atualização':
                    $summary['total_updates'] += $record['value'];
                    if ($record['key'] === 'capital') {
                        $summary['total_capital'] = $record['value'];
                    } elseif ($record['key'] === 'interest') {
                        $summary['total_interest'] = $record['value'];
                    }
                    break;

                case 'Multa':
                    $summary['total_fines'] += $record['amount'];
                    break;

                case 'Honorários':
                    $summary['total_fees'] += $record['amount'];
                    break;

                case 'Amortização':
                    $summary['total_capital'] = $record['capital'];
                    $summary['total_interest'] = $record['interest'];
                    break;
            }
        }

        $summary['grand_total'] = $summary['total_capital'] + $summary['total_interest'] + $summary['total_fines'] + $summary['total_fees'];

        return $summary;
    }

    // Método para exportar o histórico para Excel
    public function exportToExcel($fileName = 'historico.xlsx')
    {
        $data = $this->getSpreadsheetData();

        return Excel::download(new HistoryExport($data), $fileName);
    }

    // Método para exportar o histórico para PDF
    public function exportToPdf($fileName = 'historico.pdf')
    {
        $data = $this->getSpreadsheetData();

        // Carregar a view e passar os dados
        $pdf = PDF::loadView('history.pdf', ['data' => $data]);

        // Retornar o PDF para download
        return $pdf->download($fileName);
    }
}
