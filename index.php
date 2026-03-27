<?php
/**
 * Gerador de Contratos VOGA - Versão PHP Simplificada
 * Autor: Manus (Assistente AI)
 * Descrição: Sistema para upload de faturas TXT, edição de planos por linha e geração de contratos.
 */

// Configuração dos Planos VOGA
$voga_plans = [
    ['id' => 100, 'network' => 'TIM', 'standard' => 'Plano Markup 5Gb', 'provider' => 'VOGA 5Gb - TIM', 'price' => 34.99, 'data' => 5120],
    ['id' => 101, 'network' => 'TIM', 'standard' => 'Plano Markup 8Gb', 'provider' => 'VOGA 8Gb - TIM', 'price' => 44.99, 'data' => 8192],
    ['id' => 102, 'network' => 'TIM', 'standard' => 'Plano Markup 12Gb', 'provider' => 'VOGA 12Gb - TIM', 'price' => 49.99, 'data' => 12288],
    ['id' => 103, 'network' => 'TIM', 'standard' => 'Plano Markup 22Gb', 'provider' => 'VOGA 22Gb - TIM', 'price' => 59.99, 'data' => 22528],
    ['id' => 104, 'network' => 'TIM', 'standard' => 'Plano Markup 30Gb', 'provider' => 'VOGA 30Gb - TIM', 'price' => 69.99, 'data' => 30720],
    ['id' => 105, 'network' => 'TIM', 'standard' => 'Plano Markup 40Gb', 'provider' => 'VOGA 40Gb - TIM', 'price' => 79.99, 'data' => 40960],
    ['id' => 106, 'network' => 'TIM', 'standard' => 'Plano Markup 45Gb', 'provider' => 'VOGA 45Gb - TIM', 'price' => 89.99, 'data' => 46080],
    ['id' => 700, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 10 Gb + 5Gb (Port) - Voz Ilimitado', 'provider' => 'VOGA 10 GB + 5GB - VIVO', 'price' => 59.99, 'data' => 15360],
    ['id' => 701, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 15 Gb + 5Gb (Port) - Voz Ilimitado', 'provider' => 'VOGA 15 GB + 5GB - VIVO', 'price' => 64.99, 'data' => 20480],
    ['id' => 702, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 25 Gb + 5Gb (Port) - Voz Ilimitado', 'provider' => 'VOGA 25 GB + 5GB - VIVO', 'price' => 89.99, 'data' => 30720],
    ['id' => 704, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 5 Gb + 3Gb (Port) - Voz Ilimitado', 'provider' => 'VOGA 5 GB + 3GB - VIVO', 'price' => 49.99, 'data' => 8192],
    ['id' => 705, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 3 Gb + 2Gb (Port) - Voz Ilimitado', 'provider' => 'VOGA 3 GB + 2GB - VIVO', 'price' => 39.99, 'data' => 5120],
    ['id' => 706, 'network' => 'VIVO', 'standard' => 'Plano Markup Vivo 1 Gb - Voz Ilimitado', 'provider' => 'VOGA 1 GB - VIVO', 'price' => 24.99, 'data' => 1024],
];

// Dados da VOGA (Emitente)
$voga_data = [
    'name' => "VOGA INOVAÇÕES TECNOLÓGICAS LTDA",
    'cnpj' => "34.490.277/0001-61",
    'address' => "R ARISTIDES THOMAZ BALLERINI, 185",
    'neighborhood' => "JARDIM IPE",
    'city' => "POÇOS DE CALDAS",
    'state' => "MG",
    'cep' => "37.704-206",
];

// Lógica de Geração de Documentos (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $client_data = json_decode($_POST['client_data'], true);
    $selected_plans = $_POST['selected_plans']; // Array [index => plan_id]
    $operator = $_POST['operator'];
    $fidelity = $_POST['fidelity'];
    $commercial_terms = $_POST['commercial_terms'];
    $sla = isset($_POST['sla']) ? "Customizado" : "Padrão (99%)";

    $timestamp = date('d/m/Y');
    $filename_prefix = preg_replace('/[^a-z0-9]/i', '_', $client_data['clientName']);

    if ($action === 'generate_term') {
        $content = "TERMO DE ADESÃO\n\n";
        $content .= "DADOS DO CLIENTE\n";
        $content .= "Nome/Razão Social: {$client_data['clientName']}\n";
        $content .= "CNPJ/CPF: {$client_data['clientCNPJ']}\n";
        $content .= "Endereço: {$client_data['clientAddress']}, {$client_data['clientNeighborhood']}, {$client_data['clientCity']} - {$client_data['clientState']}\n\n";
        
        $content .= "DADOS DO CONTRATO\n";
        $content .= "Operadora: {$operator}\n";
        $content .= "Fidelidade: " . ($fidelity == 'none' ? 'Sem Fidelidade' : "$fidelity meses") . "\n";
        $content .= "SLA: $sla\n\n";

        $content .= "LINHAS E PLANOS VOGA\n";
        $total_new = 0;
        foreach ($client_data['lines'] as $idx => $line) {
            $plan_id = $selected_plans[$idx];
            $plan = null;
            foreach ($voga_plans as $p) if ($p['id'] == $plan_id) $plan = $p;
            
            $plan_name = $plan ? $plan['provider'] : "Não selecionado";
            $plan_price = $plan ? $plan['price'] : 0;
            $total_new += $plan_price;
            
            $content .= "Linha: {$line['number']} | Plano: $plan_name | Valor: R$ " . number_format($plan_price, 2, ',', '.') . "\n";
        }
        $content .= "\nTOTAL MENSAL: R$ " . number_format($total_new, 2, ',', '.') . "\n\n";
        $content .= "CONDIÇÕES COMERCIAIS\n" . ($commercial_terms ?: "Sem observações") . "\n\n";
        $content .= "Data: $timestamp";

        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="Termo_Adesao_' . $filename_prefix . '.txt"');
        echo $content;
        exit;
    }
    // Adicionar lógica para outros documentos se necessário
}

// Lógica de Processamento do TXT (AJAX/In-page)
$extracted_data = null;
if (isset($_FILES['invoice_txt']) && $_FILES['invoice_txt']['error'] == 0) {
    $content = file_get_contents($_FILES['invoice_txt']['tmp_name']);
    $lines = explode("\n", $content);
    
    // Parser Simples (Replicando lógica do JS)
    $client_name = "Cliente não identificado";
    $client_cnpj = "";
    $client_address = "";
    $extracted_lines = [];

    foreach ($lines as $line) {
        if (strpos($line, "DISTRIBUIDORA") !== false) {
            $client_name = trim($line);
            if (preg_match('/(\d{2}\.\d{3}\.\d{3}\/\d{4}-\d{2})/', $line, $m)) $client_cnpj = $m[1];
        }
        if (preg_match('/N(\d{2}-\d{5}-\d{4})/', $line, $m)) {
            $extracted_lines[] = ['number' => $m[1], 'plan' => 'Extraído do TXT', 'value' => 0];
        }
    }

    $extracted_data = [
        'clientName' => $client_name,
        'clientCNPJ' => $client_cnpj,
        'clientAddress' => "Endereço extraído do TXT",
        'clientNeighborhood' => "Bairro",
        'clientCity' => "Cidade",
        'clientState' => "UF",
        'lines' => $extracted_lines,
        'totalValue' => "0,00"
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador VOGA PHP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-50 min-h-screen p-4 md:p-8">
    <div class="max-w-5xl mx-auto">
        <header class="text-center mb-8">
            <h1 class="text-3xl font-bold text-slate-900">Gerador de Contratos VOGA</h1>
            <p class="text-slate-600">Versão PHP Simplificada para TXT</p>
        </header>

        <?php if (!$extracted_data): ?>
        <!-- Upload Card -->
        <div class="bg-white p-8 rounded-2xl shadow-sm border border-slate-200 text-center">
            <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
                <div class="border-2 border-dashed border-slate-300 rounded-xl p-12 hover:border-blue-400 transition-colors cursor-pointer" onclick="document.getElementById('fileInput').click()">
                    <div class="text-slate-400 mb-4">
                        <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    </div>
                    <p class="text-slate-600">Arraste a fatura TXT aqui ou clique para selecionar</p>
                    <input type="file" name="invoice_txt" id="fileInput" class="hidden" accept=".txt" onchange="this.form.submit()">
                </div>
                <button type="button" onclick="document.getElementById('fileInput').click()" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700">Selecionar Arquivo</button>
            </form>
        </div>
        <?php else: ?>
        <!-- Editor Card -->
        <form action="" method="post">
            <input type="hidden" name="client_data" value='<?php echo json_encode($extracted_data); ?>'>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Configurações -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200">
                        <h3 class="font-bold text-slate-900 mb-4">Dados do Cliente</h3>
                        <div class="space-y-3 text-sm">
                            <p><span class="text-slate-500">Nome:</span><br><strong><?php echo $extracted_data['clientName']; ?></strong></p>
                            <p><span class="text-slate-500">CNPJ:</span><br><strong><?php echo $extracted_data['clientCNPJ']; ?></strong></p>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 space-y-4">
                        <h3 class="font-bold text-slate-900">Configurações</h3>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Rede de Destino</label>
                            <select name="operator" id="operatorSelect" class="w-full border-slate-300 rounded-lg text-sm" onchange="filterPlans()">
                                <option value="VIVO">VIVO (via TELECALL)</option>
                                <option value="TIM">TIM (via SURF)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Fidelidade</label>
                            <select name="fidelity" class="w-full border-slate-300 rounded-lg text-sm">
                                <option value="none">Sem Fidelidade</option>
                                <option value="12">12 Meses</option>
                                <option value="24">24 Meses</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="sla" id="slaCheck" class="rounded text-blue-600">
                            <label for="slaCheck" class="text-sm text-slate-700">Incluir SLA Customizado</label>
                        </div>
                        <button type="submit" name="action" value="generate_term" class="w-full bg-green-600 text-white py-3 rounded-lg font-bold hover:bg-green-700 shadow-md transition-all">GERAR CONTRATO (TXT)</button>
                        <a href="index.php" class="block text-center text-sm text-slate-500 hover:underline">Carregar outra fatura</a>
                    </div>
                </div>

                <!-- Tabela de Linhas -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Número</th>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase">Novo Plano VOGA</th>
                                    <th class="p-4 text-xs font-bold text-slate-500 uppercase text-right">Valor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($extracted_data['lines'] as $idx => $line): ?>
                                <tr>
                                    <td class="p-4 text-sm font-medium text-slate-900"><?php echo $line['number']; ?></td>
                                    <td class="p-4">
                                        <select name="selected_plans[<?php echo $idx; ?>]" class="plan-select w-full border-slate-200 rounded-lg text-xs" onchange="updateTotal()">
                                            <option value="">Selecione um plano...</option>
                                            <?php foreach ($voga_plans as $plan): ?>
                                            <option value="<?php echo $plan['id']; ?>" data-network="<?php echo $plan['network']; ?>" data-price="<?php echo $plan['price']; ?>">
                                                <?php echo $plan['provider']; ?> (R$ <?php echo number_format($plan['price'], 2, ',', '.'); ?>)
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td class="p-4 text-sm font-bold text-blue-600 text-right line-price">R$ 0,00</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="bg-blue-50">
                                <tr>
                                    <td colspan="2" class="p-4 text-sm font-bold text-blue-900 text-right">NOVO TOTAL MENSAL:</td>
                                    <td id="grandTotal" class="p-4 text-lg font-black text-blue-900 text-right">R$ 0,00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </form>

        <script>
            function filterPlans() {
                const network = document.getElementById('operatorSelect').value;
                document.querySelectorAll('.plan-select').forEach(select => {
                    Array.from(select.options).forEach(opt => {
                        if (opt.value === "") return;
                        opt.style.display = opt.getAttribute('data-network') === network ? 'block' : 'none';
                    });
                    // Reset if current selected is hidden
                    const selected = select.options[select.selectedIndex];
                    if (selected && selected.value !== "" && selected.style.display === 'none') select.value = "";
                });
                updateTotal();
            }

            function updateTotal() {
                let total = 0;
                document.querySelectorAll('.plan-select').forEach(select => {
                    const opt = select.options[select.selectedIndex];
                    const price = opt && opt.value !== "" ? parseFloat(opt.getAttribute('data-price')) : 0;
                    total += price;
                    select.closest('tr').querySelector('.line-price').innerText = 'R$ ' + price.toLocaleString('pt-BR', {minimumFractionDigits: 2});
                });
                document.getElementById('grandTotal').innerText = 'R$ ' + total.toLocaleString('pt-BR', {minimumFractionDigits: 2});
            }
            
            // Iniciar filtro
            window.onload = filterPlans;
        </script>
        <?php endif; ?>
    </div>
</body>
</html>
