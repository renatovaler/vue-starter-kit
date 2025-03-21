<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\Calculation;
use App\Services\MonetaryUpdateService;
use App\Http\Requests\StoreCalculationRequest;

class CalculationController extends Controller
{
    protected $service;
    protected $results;

    /**
     * Exibir a lista de cálculos (Index).
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $calculations = Calculation::orderBy('updated_at', 'desc')->get();

        // Retorna a view através do Inertia
        return Inertia::render('Calculations/Index', [
            'calculations' => $calculations,
        ]);
    }

    /**
     * Exibir o formulário de criação (Create).
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Calculations/Create');
    }

    /**
     * Salvar um novo cálculo no banco de dados (Store).
     *
     * @param  \App\Http\Requests\StoreCalculationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(StoreCalculationRequest $request)
    {
        Calculation::create($request->validated());

        return redirect()->route('calculations.index')->with('success', 'Cálculo criado com sucesso!');
    }

    /**
     * Exibir os detalhes de um cálculo específico (Show).
     *
     * @param  \App\Models\Calculation  $calculation
     * @return \Inertia\Response
     */
    public function show(Calculation $calculation)
    {
        return Inertia::render('Calculations/Show', [
            'calculation' => $calculation,
        ]);
    }

    /**
     * Exibir o formulário de edição de um cálculo (Edit).
     *
     * @param  \App\Models\Calculation  $calculation
     * @return \Inertia\Response
     */
    public function edit(Calculation $calculation)
    {
        return Inertia::render('Calculations/Edit', [
            'calculation' => $calculation,
        ]);
    }

    /**
     * Atualizar um cálculo no banco de dados (Update).
     *
     * @param  \App\Http\Requests\StoreCalculationRequest  $request
     * @param  \App\Models\Calculation  $calculation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(StoreCalculationRequest $request, Calculation $calculation)
    {
        $calculation->update($request->validated());

        return redirect()->route('calculations.index')->with('success', 'Cálculo atualizado com sucesso!');
    }

    /**
     * Excluir um cálculo do banco de dados (Destroy).
     *
     * @param  \App\Models\Calculation  $calculation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Calculation $calculation)
    {
        $calculation->delete();

        return redirect()->route('calculations.index')->with('success', 'Cálculo excluído com sucesso!');
    }  


    public function processCalculations(Request $request)
    {
        // Validar os dados recebidos
        $validated = $request->validate([
					'values' => 'required|array|min:1',
					'values.*.key' => 'required|string',
					'values.*.amount' => 'required|numeric|min:0',
					'start_date' => 'required|date',
					'end_date' => 'required|date|after_or_equal:start_date',
					'custom_indices' => 'nullable|array',
					'custom_indices.*.start' => 'required|date',
					'custom_indices.*.end' => 'required|date|after_or_equal:custom_indices.*.start',
					// Adicione outras validações conforme necessário
					'amortizations' => 'nullable|array',
					'amortizations.*.date' => 'required|date',
					'amortizations.*.amount' => 'required|numeric|min:0',
					'amortizations.*.applyToCapital' => 'required|boolean',
				], [
					// Mensagens de erro personalizadas (opcional)
					'values.*.key.required' => 'A chave é obrigatória.',
					'values.*.amount.required' => 'O valor é obrigatório.',
					'values.*.amount.min' => 'O valor deve ser maior ou igual a zero.',
					'custom_indices.*.start.required' => 'A data inicial do período é obrigatória.',
					'custom_indices.*.end.required' => 'A data final do período é obrigatória.',
					'custom_indices.*.end.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
					// ...
				]);

        // Obter os dados do formulário
        $values = $this->formatValues($validated['values']);
        $startDate = $validated['start_date'];
        $endDate = $validated['end_date'];
        $customIndices = $validated['custom_indices'] ?? [];
        $amortizations = $validated['amortizations'] ?? [];
        $options = $validated['options'] ?? [];

        // Instanciar o serviço
        $this->service = new MonetaryUpdateService($customIndices, $amortizations, $options);

        // Realizar as atualizações
        $updatedValues = $this->service->updateValues($values, $startDate, $endDate);

        // Obter o histórico
        $history = $this->service->getUpdateHistory();

        // Obter o resumo
        $summary = $this->service->historyManager->getSummary();

        // Armazenar os resultados na sessão para exportação
        Session::put('history', $history);
        Session::put('summary', $summary);

        return Inertia::render('Calculations', [
            'updatedValues' => $updatedValues,
            'history' => $history,
            'summary' => $summary,
        ]);
    }

    public function exportExcel()
    {
        $history = Session::get('history');

        if (!$history) {
            return redirect()->route('calculations.form')->with('error', 'Nenhum cálculo para exportar.');
        }

        $historyManager = new \App\Services\HistoryManager();
        $historyManager->history = $history; // Restaura o histórico

        return $historyManager->exportToExcel('historico_atualizacao.xlsx');
    }

    public function exportPdf()
    {
        $history = Session::get('history');

        if (!$history) {
            return redirect()->route('calculations.form')->with('error', 'Nenhum cálculo para exportar.');
        }

        $historyManager = new \App\Services\HistoryManager();
        $historyManager->history = $history; // Restaura o histórico

        return $historyManager->exportToPdf('historico_atualizacao.pdf');
    }

    private function formatValues($valuesInput)
    {
        $values = [];
        foreach ($valuesInput as $item) {
            $values[$item['key']] = $item['amount'];
        }
        return $values;
    }
}
