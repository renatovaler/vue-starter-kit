<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <h1 class="text-2xl font-bold mb-4">Formulário com Checkboxes e Tabela Dinâmica</h1>
      <form @submit.prevent="handleSubmit" class="space-y-4">
        <!-- Novo campo para Nome do Cálculo -->
        <div>
          <label for="calculoNome" class="block text-lg">Nome do Cálculo:</label>
          <input type="text" id="calculoNome" v-model="calculoNome" class="border border-gray-300 p-2 w-full" required />
        </div>

        <!-- Checkbox para incluir nome no relatório -->
        <div class="flex items-center">
          <input type="checkbox" id="incluirNomeRelatorio" v-model="incluirNomeRelatorio" class="mr-2" />
          <label for="incluirNomeRelatorio" class="text-lg">Desejo que o nome do cálculo apareça no relatório final</label>
        </div>

        <!-- Campo para corrigir valores até -->
        <div>
          <label for="corrigirAte" class="block text-lg">Corrigir valores até:</label>
          <input type="date" id="corrigirAte" v-model="corrigirAte" class="border border-gray-300 p-2 w-full" required />
        </div>

        <!-- Seleção do índice de correção monetária -->
        <div>
          <label for="indiceCorrecao" class="block text-lg">Selecione o índice de correção monetária:</label>
          <select id="indiceCorrecao" v-model="indiceCorrecao" class="border border-gray-300 p-2 w-full">
            <option value="IPCA">IPCA</option>
            <option value="IGP-M">IGP-M</option>
          </select>
        </div>

        <!-- Seleção para correção monetária pró-rata -->
        <div>
          <label for="correcaoProRata" class="block text-lg">Fazer correção monetária pró-rata?</label>
          <select id="correcaoProRata" v-model="correcaoProRata" class="border border-gray-300 p-2 w-full">
            <option value="sim">Sim</option>
            <option value="nao">Não</option>
          </select>
        </div>

        <!-- Seleção para utilizar índices negativos -->
        <div>
          <label for="indicesNegativos" class="block text-lg">Utilizar índices negativos?</label>
          <select id="indicesNegativos" v-model="indicesNegativos" class="border border-gray-300 p-2 w-full">
            <option value="sim">Sim</option>
            <option value="nao">Não</option>
          </select>
        </div>

        <!-- Modelo de apresentação do cálculo -->
        <div class="block text-lg">Modelo de apresentação do cálculo:</div>
        <div class="flex items-center">
          <input type="radio" id="resumida" value="resumida" v-model="modeloApresentacao" class="mr-2" />
          <label for="resumida">Memória de cálculo resumida</label>
        </div>
        <div class="flex items-center">
          <input type="radio" id="mesAMes" value="mesAMes" v-model="modeloApresentacao" class="mr-2" />
          <label for="mesAMes">Apresentação do cálculo mês a mês</label>
        </div>
        <div class="flex items-center">
          <input type="radio" id="planilha" value="planilha" v-model="modeloApresentacao" class="mr-2" />
          <label for="planilha">Mostrar cálculo em formato planilha</label>
        </div>

        <!-- Checkbox para calcular também -->
        <div class="block text-lg">Além da correção monetária, desejo calcular também:</div>
        <div v-for="(checkbox, index) in adicionais" :key="index" class="flex items-center">
          <input type="checkbox" :id="'adicional-' + index" v-model="checkbox.checked" @change="updateTableColumns" class="mr-2" />
          <label :for="'adicional-' + index" class="text-lg">{{ checkbox.label }}</label>
        </div>

        <div class="mt-6">
          <h2 class="text-xl font-semibold mb-2">Tabela Dinâmica</h2>
          <table class="min-w-full border border-gray-300">
            <thead>
              <tr class="bg-gray-100">
                <th @click="toggleSorting('text')" class="cursor-pointer border border-gray-300 p-2">
                  Texto (Obrigatório) <span v-if="sorting.column === 'text'">{{ sorting.direction === 'asc' ? '🔼' : '🔽' }}</span>
                </th>
                <th @click="toggleSorting('value')" class="cursor-pointer border border-gray-300 p-2">
                  Valor (Obrigatório) <span v-if="sorting.column === 'value'">{{ sorting.direction === 'asc' ? '🔼' : '🔽' }}</span>
                </th>
                <th v-for="(checkbox, index) in selectedCheckboxes" :key="index" class="border border-gray-300 p-2">
                  {{ checkbox.label }}
                </th>
                <th class="border border-gray-300 p-2">Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, rowIndex) in sortedData" :key="rowIndex">
                <td class="border border-gray-300 p-2">
                  <input v-model="row.text" class="border border-gray-300 p-1 w-full" required />
                </td>
                <td class="border border-gray-300 p-2">
                  <input v-model="row.value" type="number" class="border border-gray-300 p-1 w-full" required />
                </td>
                <td v-for="(checkbox, colIndex) in selectedCheckboxes" :key="colIndex" class="border border-gray-300 p-2">
                  <input v-model="row[`col${colIndex}`]" class="border border-gray-300 p-1 w-full" />
                  <button 
                    @click.prevent="openDialog(rowIndex)" 
                    class="bg-gray-500 text-white px-2 py-1 rounded"
                  >
                    <Gear />
                  </button>
                </td>
                <td class="border border-gray-300 p-2">
                  <button 
                    @click.prevent="removeRow(rowIndex)" 
                    class="bg-red-500 text-white px-2 py-1 rounded"
                    :disabled="tableData.length === 1"  
                  >
                    Remover
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
          <button @click.prevent="addRow" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">
            Adicionar Linha
          </button>
        </div>

        <button type="submit" class="mt-4 bg-green-500 text-white px-4 py-2 rounded">
          Enviar
        </button>
      </form>
      <Dialog v-model="isDialogOpen">
        <template #header>
          <h2>Dados Personalizados</h2>
        </template>
        <template #default>
          <input v-model="customData" placeholder="Insira dados personalizados" class="border border-gray-300 p-2 w-full" />
        </template>
        <template #footer>
          <button @click="saveData" class="bg-green-500 text-white px-4 py-2 rounded">Salvar</button>
          <button @click="isDialogOpen = false" class="bg-gray-500 text-white px-4 py-2 rounded">Cancelar</button>
        </template>
      </Dialog>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Settings as Gear } from 'lucide-vue-next';

// Definindo os checkboxes
const checkboxes = ref([
  { label: 'Checkbox 1', checked: false },
  { label: 'Checkbox 2', checked: false },
  { label: 'Checkbox 3', checked: false },
  { label: 'Checkbox 4', checked: false },
  { label: 'Checkbox 5', checked: false },
  { label: 'Checkbox 6', checked: false },
]);

// Variáveis para os novos campos
const calculoNome = ref('');
const incluirNomeRelatorio = ref(false);
const corrigirAte = ref('');
const indiceCorrecao = ref('IPCA');
const correcaoProRata = ref('nao');
const indicesNegativos = ref('nao');
const modeloApresentacao = ref('resumida');

// Adicionais
const adicionais = ref([
  { label: 'Custas', checked: false },
  { label: 'Juros moratórios', checked: false },
  { label: 'Juros compensatórios', checked: false },
  { label: 'Multa', checked: false },
  { label: 'Honorários', checked: false },
  { label: 'Honorários sucumbenciais', checked: false },
  { label: 'Multa Art. 523 NCPC', checked: false },
]);

// Outras opções
const outrasOpcoes = ref([
  { label: 'Desejo informar uma descrição para cada valor', checked: false },
  { label: 'Desejo informar valores já pagos (Selecionar se o valor é principal ou dedução)', checked: false },
  { label: 'Desejo que, no relatório do cálculo (formato planilha), os valores sejam ordenados por ordem crescente com base na data informada', checked: false },
  { label: 'Desejo inserir os dados do processo e outros detalhes na primeira página do relatório de cálculo', checked: false },
  { label: 'Desejo manter o valor nominal caso haja deflação no período, ou seja, caso o valor corrigido ficar menor que o valor original', checked: false },
]);

// Inicializando a tabela com uma linha
const tableData = ref<any[]>([
  {
    text: '',
    value: '',
  },
]);

// Computed para obter checkboxes selecionados
const selectedCheckboxes = computed(() => {
  return adicionais.value.filter(checkbox => checkbox.checked);
});

// Função para adicionar uma nova linha
const addRow = () => {
  const newRow: { text: string; value: string; [key: string]: string } = {
    text: '',
    value: '',
  };
  for (let i = 0; i < selectedCheckboxes.value.length; i++) {
    newRow[`col${i}`] = ''; // Adiciona um campo vazio para cada checkbox selecionado
  }
  tableData.value.push(newRow);
};

// Função para remover uma linha
const removeRow = (index: number) => {
  tableData.value.splice(index, 1);
};

// Função para manipular a submissão do formulário
const handleSubmit = () => {
  // Lógica para processar os dados do formulário
  console.log('Dados do formulário:', {
    calculoNome: calculoNome.value,
    incluirNomeRelatorio: incluirNomeRelatorio.value,
    corrigirAte: corrigirAte.value,
    indiceCorrecao: indiceCorrecao.value,
    correcaoProRata: correcaoProRata.value,
    indicesNegativos: indicesNegativos.value,
    modeloApresentacao: modeloApresentacao.value,
    adicionais: adicionais.value,
    outrasOpcoes: outrasOpcoes.value,
    tableData: tableData.value,
  });
};

// Variáveis para ordenação
const sorting = ref({
  column: '',
  direction: 'asc',
});

// Função para alternar a ordenação
const toggleSorting = (column: string) => {
  if (sorting.value.column === column) {
    sorting.value.direction = sorting.value.direction === 'asc' ? 'desc' : 'asc';
  } else {
    sorting.value.column = column;
    sorting.value.direction = 'asc';
  }
};

// Computed para dados ordenados
const sortedData = computed(() => {
  const data = [...tableData.value];
  if (sorting.value.column) {
    data.sort((a, b) => {
      const modifier = sorting.value.direction === 'asc' ? 1 : -1;
      if (a[sorting.value.column] < b[sorting.value.column]) return -1 * modifier;
      if (a[sorting.value.column] > b[sorting.value.column]) return 1 * modifier;
      return 0;
    });
  }
  return data;
});

// Função para atualizar as colunas da tabela com base nos checkboxes selecionados
const updateTableColumns = () => {
  tableData.value.forEach(row => {
    selectedCheckboxes.value.forEach((checkbox, index) => {
      if (!(`col${index}` in row)) {
        row[`col${index}`] = ''; // Adiciona um campo vazio se a coluna não existir
      }
    });
  });
};

const isDialogOpen = ref(false);
const customData = ref('');
const currentRowIndex = ref(-1); // Para identificar a linha atual

const openDialog = (rowIndex: number) => {
  currentRowIndex.value = rowIndex; // Armazena o índice da linha atual
  customData.value = tableData.value[rowIndex].customData || ''; // Carrega dados existentes
  isDialogOpen.value = true; // Abre o modal
};

const saveData = () => {
  if (currentRowIndex.value >= 0) {
    tableData.value[currentRowIndex.value].customData = customData.value; // Salva dados personalizados
  }
  isDialogOpen.value = false; // Fecha o modal
};

const breadcrumbs: BreadcrumbItem[] = [
  {
      title: 'Dashboard',
      href: '/dashboard',
  },
];
</script>

<style scoped>
/* Adicione estilos personalizados aqui, se necessário */
</style>